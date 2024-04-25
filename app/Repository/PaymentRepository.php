<?php

namespace App\Repository;

use App\Enum\OrderType;
use App\Facades\SeoMeta;
use App\Models\Document;
use App\Models\Jury;
use App\Models\LdeDrsPrice;
use App\Models\LoginLog;
use App\Models\OpayoOrderDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OtherRequests;
use App\Models\Requests;
use App\Models\Survey;
use Carbon\Carbon;
use Cart;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PDF;

class PaymentRepository
{

    public const EXPIRY_DAYS = 180;

    /**
     * Insert order in DB
     * @param $request
     * @return false|string
     */
    public function insertOrder($request)
    {
        if(!session('order_started')){
            session()->forget('basket_order_ids');
        }
        session(['payment_started' => true, 'order_started' => true]);
        $item_types = Cart::itemTypes();
        if (Cart::totalItems() == 0) {
            return false;
        }
        $user_id = auth()->id();
        if (empty($user_id) || $user_id <= 0) {
            return false;
        }

        $requestsRepository = new RequestsRepository();
        $payment_method = $request->payment_method;
        $send_invoice = ($request->business_address || session()->get('add_business_address')) ? 'y' : 'n';
        $status = 'un-approved';
        $is_test = 'n';
        $is_repeated = 'n';
        $transaction_reference = '';

        if (isTestUser()) {
            $is_test = 'y';
        }
        if ($this->isRepeatingUser($user_id)) {
            $is_repeated = 'y';
        }
        session(['is_repeating_user' => $is_repeated]); // set repeating user flag in session
        if ($payment_method == 'card') {
            $payment_method = 'protx';
        } elseif ($payment_method == 'free') {
            $status = 'approved';
        }
        if ($payment_method == 'protx') {
            $transaction_reference = $this->generateTransactionRefernce($is_test);
        }
        $redirect_url = $this->getRedirectUrl($payment_method);

        $pay_for_legal = session('pay_for_legal');
//        check if cart has amount to be paid while payment method is free
        if (Cart::total() > 0 && $payment_method == 'free') {
            return route('cart');
        }

//        $amount_to_pay = 0;
        $coupon = [];
        $discount = 0;
        if (Cart::hasCoupon()) {
            $coupon = Cart::getCoupon();
            $discount = Cart::getTotalDiscount();
        }

        $sub_total = Cart::subTotal();
        $amount = calculateTax((float)$sub_total);
        $tax_amount = $amount - $sub_total;
//        $amount_to_pay += $amount;
        $order_id = session('basket_order_ids');
        //        check if it is edit order case or new order case
        $order_ref = '';
        $edit_order_id = $this->checkOrderExist();
        session(['edit_order_id' => $edit_order_id]);
        if ($edit_order_id > 0) {
            $order = Order::where('user_id', $user_id)->find($order_id[0]);
            $order_reference = $order->order_reference;
        } else {
            if ($pay_for_legal == 'y' && session()->has('pay_for_legal_data')) {
                $pay_for_legal_data = $requestsRepository->getPayForLegalData();
                $order_ref = $this->getOrderRefByItemOption($pay_for_legal_data['request_reference']);
            }
            if(!empty($order_ref)) {
                $pattern = '/[a-zA-Z]$/';
                $order_ref = preg_replace($pattern, '', $order_ref);
                $order_reference = $this->reGenerateReference($order_ref);// regenerate order reference if already paid for this reference
            }else {
                $order_reference = $this->generateOrderRef($item_types[0]);
            }
            $order = new Order();
            $order->order_reference = $order_reference;
        }

        $order->user_id = $user_id;
        $order->login_log_id = $this->getLoginLogId($user_id);
        $order->utm_medium = session('utm_medium');
        $order->jury_id = config('custom.jury_id');
        $order->payment_method = $payment_method;
        $order->transaction_reference = $transaction_reference;

        if (!empty($coupon)) {
            $order->promo_code = $coupon['p_code'];
            $order->promo_percentage = ($coupon['discount_type'] == 'Percentage') ? $coupon['discount'] : 0.00;
            $order->promo_discount = $discount;
        }

        $order->tax_value = config('tax.value');
        $order->sub_total = $sub_total;
        $order->tax_amount = $tax_amount;
        $order->amount = $amount;
        $order->converted_amount = getConvertedAmount($amount, 'USD');
        $order->gbp_converted_amount = getConvertedAmount($amount, 'GBP');
        $order->sale_referer = $_SERVER['HTTP_REFERER'];
        $order->order_type_id = $this->getOrderType();

        $order->status = $status;
        $order->is_test = $is_test;
        $order->is_repeated = $is_repeated;
        $order->show_invoice_address = $send_invoice;
        if (count($item_types) == 1 && $item_types[0] == 'lde') {
            $questionnaire_key = session()->get('questionnaire_key');
            $order->questionnaire_key = $questionnaire_key;
        }
        $order->save();

        if (!$order->id) {
            throw new Exception('Sorry! Order could not be inserted');
        }

        $this->createOrderDetails($order->id, $order_reference);
        // if subscription payment then store separate
        if (count($item_types) == 1 && $item_types[0] == 'subscription') {
            $subscriptionRepo = new SubscriptionRepository();
            $subscriptionRepo->createPayment($order->id);
        }

        $inserted_order_id = $order->id;
        if (!empty($coupon)) { // if promo used then change promo credentials
            $promo = new PromoRepository();
            $promo->updatePromoUsed();
        }

        session(['basket_order_ids' => [$inserted_order_id]]); // set current inserted basket order id in session
        return $redirect_url;
    }
    public function getOrderRefByItemOption(string $item_option)
    {
        $orderDetail = OrderDetail::select('order_reference')->where('item_options', $item_option)->whereNull('deleted_at')->orderBy('id', 'DESC')->first();
        if ($orderDetail) {
            return $orderDetail->order_reference;
        }
        return '';
    }
    /**
     * Re-generate order reference if already paid for old reference
     * @param $order_ref
     * @return mixed|string
     */
    public function reGenerateReference($order_ref)
    {
        $order = Order::select('id', 'order_reference')->where(['order_reference' => $order_ref])->first();
        $a = 65; // add A=65, B=66 ...
        $new_order_ref = $order_ref;
        while ($order) {
            $new_order_ref = $order_ref . chr($a);
            $order = Order::select('id', 'order_reference')->where(['order_reference' => $new_order_ref])->first();
            $a++;
        }
        return $new_order_ref;
    }

    /**
     * Create new order details after deleting previous one if any
     * @param $orderId
     * @param $order_ref
     * @param $item_type
     */
    public function createOrderDetails($orderId, $order_ref)
    {
        OrderDetail::where('order_id', $orderId)->delete(); // first delete previous entries if exist
        $requestsRepository = new RequestsRepository();
        $pay_for_legal = session('pay_for_legal');
        $contents = Cart::contents();
        if (count($contents) > 0) {
            foreach ($contents as $rowId => $content) {
                $request_reference = '';
                $item_type = $content['options']['item_type'];
                if ($pay_for_legal != 'y') {
                    if (session('edit_order_id') == 0) { // new order case
                        $request = $requestsRepository->createRequest(
                            $item_type,
                            $requestsRepository->generateRequestReference($item_type),
                            item_id: $content['id']
                        ); // create a request if applicable
                        if ($request) {
                            $request_reference = $request->request_reference;
                        }
                    } else {
                        // edit order case
                        $previous_order = OrderDetail::where('order_id', $orderId)->where('item_type', '!=', 'document')->withTrashed()->pluck('item_options', 'item_id')->toArray();
                        if($previous_order){
                            $request_reference = $previous_order[$content['id']] ?? null;
                        }
                    }
                }
                $order_detail = new OrderDetail();

                $order_detail->order_id = $orderId;
                $order_detail->order_reference = $order_ref;
                $order_detail->item_id = $content['id'];
                $order_detail->type_name = $content['options']['item_category'];
                if ($pay_for_legal == 'y') { // if pay for legal case then get values from pay for legal data bcz values were updated after user login
                    $order_detail->item_type = 'pay_for_legal';
                    $order_detail->drs_availed = 'x';
                    if ($order_detail->item_id === 0) {
                        $pay_for_legal_data = session('pay_for_legal_data');
                        $order_detail->item_id = $pay_for_legal_data['request_id'];
                        $order_detail->type_name = $pay_for_legal_data['request_title'];
                    }
                } else {
                    $order_detail->item_type = $content['options']['item_type'];
                    $order_detail->drs_availed = 'n';
                }

                if ($item_type == 'document' || $item_type == 'drs' || $item_type == 'lde') {
                    $order_detail->drs_availed = $this->isDrsAvailed($order_detail->item_id);
                }

                $order_detail->item_price = $content['price'];
                $order_detail->item_discount = (float)Cart::getItemDiscount($rowId);

                if ($item_type == 'drs' && !$this->isDocPurchased($order_detail->item_id)) { // means price holds drs and doc prices
                    $docRepo = new DocumentRepository();
                    $document = $docRepo->getDocumentById($order_detail->item_id);
                    $order_detail->item_price = $document->drs_price;
                }
//                    add owner id and royalty if any TODO: royalty for drs and document
                if ($order_detail->item_type == 'document' || $order_detail->item_type == 'drs') {
                    $royalty_data = $this->getRoyaltyData($order_detail->item_id, $order_detail->item_type, $order_detail->item_price, $order_detail->item_discount);
                    $order_detail->owner_id = $royalty_data['owner_id'];
                    $order_detail->royalty = $royalty_data['royalty'];
                }

                if ($item_type == 'lde') {
                    $order_detail->item_options = $content['options']['answer_key'];
                } elseif ($item_type == 'drs') {
                    if(empty($request_reference)){ // for edit order case if user adds/changes Lawyer assist
                        $request = $requestsRepository->createRequest(
                            $item_type,
                            $requestsRepository->generateRequestReference($item_type),
                            item_id: $content['id']
                        ); // create a request if applicable
                        if ($request) {
                            $request_reference = $request->request_reference;
                        }
                    }
                    $order_detail->item_options = $request_reference;

                } elseif ($item_type == 'subscription') {
                    $order_detail->item_options = $content['options']['period'];
                } elseif ($pay_for_legal == 'y') {
                    $pay_for_legal_data = session('pay_for_legal_data');
                    $order_detail->item_options = filterReference($pay_for_legal_data['request_reference']);
                }

                $order_detail->save();
                // if it is lawyer assist service, then add a related document automatically
                if ($item_type == 'drs' && !$this->isDocPurchased($content['id'])) {
                    $this->addLaywerAssistDocument($orderId, $order_ref, $order_detail->item_id, $order_detail->item_discount);
                }
                if (!$order_detail->id) {
                    throw new Exception('Sorry! Order details could not be added');
                }
            }
        }
    }


    /**
     * Return next URL path on the basis of payment method selected by user
     * @param $payment_method
     * @return string
     */
    public function getRedirectUrl($payment_method)
    {
        $redirect_url = '/';
        if ($payment_method == 'protx' || $payment_method == 'opayo') {
            $redirect_url = '/opayo';
//            $redirect_url = '/opayo-payment';
        } elseif ($payment_method == 'paypal') {
            $redirect_url = '/paypal-payment';
        } elseif ($payment_method == 'internet') {
            $redirect_url = route('pay-by-ibt');
        } elseif ($payment_method == 'cheque') {
            $redirect_url = route('cheque-payment');
        } elseif ($payment_method == 'paytm') {
            $redirect_url = route('paytm');
        } elseif ($payment_method == 'free') {
            $redirect_url = route('free-order-complete');
        }
        return $redirect_url;
    }

    /**
     * Return cart order IDs stored in session
     * @return false
     */
    public function getBasketOrders($order_ids = null)
    {
        $order_ids = $order_ids ?? session('basket_order_ids');
        if (!$order_ids) {
            return false;
        }
        return Order::select(
            'id',
            'user_id',
            'jury_id',
            'order_reference',
            'payment_method',
            'transaction_reference',
            'amount',
            'converted_amount',
            'gbp_converted_amount',
            'sub_total',
            'tax_amount',
            'confirmation_date',
            'created_at',
            'questionnaire_key',
            'order_type_id',
            'status'
        )->whereIn('id', $order_ids)->with('order_details')->get();
    }

    /**
     * Updates Order status as approved
     * @param null $vendorTxCode
     * @param null $opayoVpsTxId
     * @return false
     */
    public function updateOrderStatus($vendorTxCode = null, $opayoVpsTxId = null): bool
    {
        $order_ids = session('basket_order_ids');
        if (!$order_ids && !empty($vendorTxCode) && !empty($opayoVpsTxId)) {
            Log::error('Order ids in session are not found but vendorTxCode found so reset order ids');
            $order_ids = $this->getOrderIdsByTransactionReference($vendorTxCode, $opayoVpsTxId);
            if ($order_ids) {
                session(['basket_order_ids' => $order_ids]);
            }
        }
        if (!$order_ids) {
            Log::error('Order ids still not found');
            return false;
        }
        $order['status'] = 'approved';
        $order['opayo_vpstxid'] = $opayoVpsTxId;
        $order['confirmation_date'] = now();
        $order_detail['payment_status'] = 'Paid';

        Order::whereIn('id', $order_ids)->update($order);
        OrderDetail::whereIn('order_id', $order_ids)->update($order_detail);
        Log::info('Order status updated as approved');

//        $this->addSurvey(); // functionality not being used so stopped
        $this->updateDrsAvailed();
        $subscriptionRepo = new SubscriptionRepository();
        $subscriptionRepo->updateStatus($order_ids);
        return true;
    }

    public function isOrderApproved($token)
    {
        if (!empty($token)) {
            return Order::where('opayo_vpstxid', $token)
                ->where('status', 'approved')
                ->where('user_id', auth()->id())
                ->exists();
        }
        return false;
    }

    /**
     * Updates order details entries when a DRS is availed for any document
     * @return bool
     */
    public function updateDrsAvailed()
    {
        $order_ids = session('basket_order_ids');
        $user_id = auth()->id();
        if (!$order_ids || !$user_id) {
            return false;
        }
        $current_order_details = OrderDetail::select('item_id', 'order_id')->whereIn('order_id', $order_ids)
            /* ->where(
             'order_reference',
             'LIKE',
             '%-DRS-%'
         )*/
            ->where(['item_type' => 'drs', 'payment_status' => 'Paid'])->get();
        if (count($current_order_details) > 0) {
            foreach ($current_order_details as $detail) {
                $doc_id = $detail->item_id;
                $query = OrderDetail::query();
                if (session()->has('drs_availed_order_ids')) {
                    $order_ids = session()->get('drs_availed_order_ids');
                    if (count($order_ids) > 0) {
                        $query->whereIn('order_id', $order_ids);
                    }
                }
                $order_detail = $query->where(
                    ['item_type' => 'document', 'payment_status' => 'Paid', 'item_id' => $doc_id]
                )->with('order', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                })->orderBy('id', 'desc')->first();

                if ($order_detail) {
                    $order_detail->drs_availed = 'y';
                    $order_detail->save();
                }
            }
            session()->forget('drs_availed_order_ids');
        }
        return true;
    }

    /**
     * Generate order Invoice
     * @return bool
     */
    public function generateInvoice($order_reference = '', $user = null)
    {
        if (empty($order_reference)) {
            $order_ids = session('basket_order_ids');
            $user_id = auth()->id();
            if (!$order_ids || empty($order_ids) || !$user_id) {
                return false;
            }
            $orders = Order::select(
                'id',
                'user_id',
                'order_reference',
                'promo_code',
                'promo_percentage',
                'promo_discount',
                'confirmation_date',
                'tax_amount',
                'sub_total',
                'amount',
                'order_type_id',
                'show_invoice_address'
            )
                ->whereIn('id', $order_ids)->where(['status' => 'approved'])
                ->with('order_details', function ($query) {
                    $query->select(
                        'id',
                        'order_id',
                        'item_id',
                        'item_price',
                        'item_type',
                        'type_name',
                        'item_discount',
                        'payment_status',
                        'item_options'
                    )->where('payment_status', 'Paid');
                })
                ->get();
        } else {
            $orders = Order::select(
                'id',
                'user_id',
                'order_reference',
                'promo_code',
                'promo_percentage',
                'promo_discount',
                'confirmation_date',
                'tax_amount',
                'sub_total',
                'amount',
                'order_type_id',
                'show_invoice_address'
            )
                ->where('order_reference', $order_reference)->whereIn('status',  ['approved', 'refunded'])
                ->with('order_details', function ($query) {
                    $query->select(
                        'id',
                        'order_id',
                        'item_id',
                        'item_price',
                        'item_type',
                        'type_name',
                        'item_discount',
                        'payment_status',
                        'item_options'
                    )->where('payment_status', 'Paid');
                })
                ->get();
        }

//        $orders = Order::select('id', 'user_id', 'order_reference', 'promo_code', 'promo_percentage', 'promo_discount', 'confirmation_date', 'tax_amount', 'sub_total', 'amount', 'show_invoice_address')
//            ->whereIn('id', $order_ids)->where(['status' => 'approved'])
//            ->with('order_details', function ($query) {
//                $query->select('id', 'order_id', 'item_id', 'item_price', 'item_type', 'type_name', 'item_discount', 'payment_status', 'item_options')->where('payment_status', 'Paid');
//            })
//            ->get();
        $order_types = [1,3,5];
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                if ($order->amount <= 0) {
                    continue;
                }
                if(empty($user)) {
                    $user_repo = new UserRepository();
                    $user =  $user_repo->getById($order->user_id);
                }
                $item_types = $order->order_details->pluck('item_type')->toArray();
                if(!in_array('pay_for_legal', $item_types)){
                    $order_types[] = 2;
                }
                if ($order->order_type_id->value === OrderType::SUBSCRIPTION) {
                    $this->generateSubscriptionInvoice($order, $user);
                } elseif (in_array($order->order_type_id->value, OrderType::values(except:$order_types))) {
                    $this->generateRequestInvoice($order, $user);
                } else {
                    $this->generateLdcInvoice($order, $user);
                }
//                else {
//                    $this->generateRequestInvoice($order);
//                }
            }
        }
        return true;
    }

    /**
     * Generate LDE order invoice
     * @param $order
     * @return bool
     */
    public function generateLdcInvoice($order, $user)
    {
//        $user = auth()->user();
//        if (!$user) {
//            $userRepo = new UserRepository();
//            $user = $userRepo->getById($order->user_id);
//        }

        $doc_ids = [];
        $lde_doc = collect();
        foreach ($order->order_details as $detail) {
            if (Str::contains($order->order_reference, '-LDE-')) {
                $lde_doc->id = $detail->item_id;
                $lde_doc->reference_code = $order->order_reference;
                $lde_doc->document_title = $detail->type_name;
            } else {
                $doc_ids[] = $detail->item_id;
            }
        }
        if (!empty($doc_ids)) {
            $documents = Document::select('id', 'reference_code', 'document_title')->whereIn('id', $doc_ids)->get();
        } else {
            $documents = $lde_doc;
        }

        $support_email = "support@" . Str::replace('www.', '', config('jury.domain'));
        $file_name = "NetLawman-payment-receipt-" . $order->order_reference . ".pdf";

//        to check preview on browser
//        return view('cart.ldc-invoice', ['order' => $order, 'user' => $user, 'documents' => $documents, 'support_email' => $support_email]);
        $html = view(
            'cart.ldc-invoice',
            ['order' => $order, 'user' => $user, 'documents' => $documents, 'support_email' => $support_email]
        )->render();

        $path = Storage::disk('public')->path('invoices/');

//        PDF::loadView('cart.ldc-invoice', compact(['order', 'user', 'documents', 'support_email']))->save($path . $file_name);

        PDF::loadHTML($html)->save($path . $file_name);
        return $path . $file_name;
    }


    /**
     * Returns order by order reference code
     * @param string $order_reference
     * @return false
     */
    public function getOrderByReference($order_reference = '')
    {
        if (empty($order_reference)) {
            return false;
        }

        return Order::select(
            'id',
            'user_id',
            'jury_id',
            'order_reference',
            'order_type_id',
            'payment_method',
            'amount',
            'transaction_reference',
            'confirmation_date',
            'status',
            'opayo_vpstxid'
        )->where('order_reference', $order_reference)->first();
    }

    /**
     * Returns payment provider names
     * @param false $order
     * @return string
     */
    public function getPaymentProvider($order = false)
    {
        if (!$order) {
            return '';
        }

        if (empty($order->transaction_reference) || $order->transaction_reference == '0') {
            $provider = $order->payment_method;
            if ($provider == 'protx') {
                return 'SagePay';
            } elseif ($provider == 'paypal') {
                return 'PayPal';
            }
        }
        return '';
    }

    /**
     * Returns invoices
     * @return mixed
     */
    public function getInvoices()
    {
        $user_id = auth()->id();
        return Order::select('id', 'jury_id', 'order_reference', 'confirmation_date', 'amount')
            ->where(['user_id' => $user_id])
            ->whereIn('status', ['approved', 'refunded'])
            ->whereRaw("amount != refund_amount")
            ->where('amount', '>', 0)
            ->orderBy('confirmation_date', 'desc')->get();
    }


    /**
     * Returns cheque and internet transfer payment data
     * @param $request
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function getChequeInternetData($request)
    {
        $page = new PagesRepository();
        $page = $page->getStaticPageContents($request);
        if (!isset($page->id)) {
            return redirect(route('404'));
        }
        $docRepo = new DocumentRepository();

        $orders = $this->getBasketOrders();
        $documents = [];
        $amount = 0;
        $order_refs = [];
        foreach ($orders as $order) {
            $amount += $order->amount;
            $order_refs[] = $order->order_reference;
            foreach ($order->order_details as $order_detail) {
                if ($order_detail->item_type == 'document' || $order_detail->item_type == 'drs') {
                    $documents[] = $docRepo->getDocumentForListing($order_detail->item_id);
                }
            }
        }
        $order_refs = implode(', ', $order_refs);
        $amount = number_format($amount, 2);
        $page->page_content = str_replace('#total_amount#', config('jury.currency') . $amount, $page->page_content);
        $page->page_content = str_replace('#order_id#', $order_refs, $page->page_content);
        $page->page_content = refineContents($page->page_content);

        SeoMeta::setTitle($page->meta_title);
        SeoMeta::setDescription($page->meta_description);
        SeoMeta::setKeywords($page->meta_keywords);

        return compact('orders', 'documents', 'page');
    }


    public function storeOpayoOrderDetail($data, $transaction_reference = null)
    {
        $order_ids = session('basket_order_ids');
        if ($transaction_reference != null) {
            OpayoOrderDetail::select('order_id')->where('transaction_reference', $transaction_reference)
                ->get()->map(function ($item) use ($data) {
                    OpayoOrderDetail::where('order_id', $item->order_id)->update($data);
                });
            return true;
        }
        if (empty($order_ids)) {
            return false;
        }
        $order_detail = OpayoOrderDetail::select('order_id')->whereIn('order_id', $order_ids)
            ->get()
            ->pluck('order_id')
            ->unique()
            ->toArray();
        foreach ($order_ids as $orderId) {
            if (in_array($orderId, $order_detail)) {
                $opayo_order_detail = OpayoOrderDetail::where('order_id', $orderId)->first();
            } else {
                $opayo_order_detail = new OpayoOrderDetail();
            }
            $data['order_id'] = $orderId;
            $opayo_order_detail->fill($data);
            $opayo_order_detail->save($data);
        }
        return true;
    }

    public function getOrderById(int $orderId)
    {
        return Order::find($orderId);
    }

    public function makeHttpRequest($order_id, $jury_id)
    {
        $domain = Jury::find($jury_id)->domain;
        try {
            Http::asForm()
                ->withHeaders([
                    'referer' => config('app.url'),
                ])
                ->post('https://' . $domain . '/api/process-order-request', ['order_id' => $order_id]);
        }catch (\Exception $exception){
            info($exception->getMessage());
        }
    }
    

}