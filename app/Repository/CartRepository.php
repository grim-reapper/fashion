<?php

namespace App\Repository;

use App\Models\UserSubscription;
use Cart;
use Illuminate\Support\Arr;
class CartRepository
{
    
    protected $product;
    protected $payment;

    public function __construct(ProductRepository $products, PaymentRepository $paymentRepository)
    {
        $this->product = $products;
        $this->payment = $paymentRepository;
    }
    
    /**
     * adds an item to cart, first removes if already added
     * @param $item_id
     * @param $item_type
     * @param $item_category
     * @param array $params
     * @return bool
     */

    public function addItemToCart($item_id, $item_type, $item_category, array $params = []): bool
    {
        $discount = Arr::get($params, 'discount', 0);
        $price = Arr::get($params, 'price', 0);
        $period = Arr::get($params, 'period');
        $only_drs = Arr::get($params, 'only_drs', 'n');

        $title = $item_category;
        $drs_available = 'x';
        if ($item_type == 'product' || $item_type == 'drs') {
            $docData = $this->product->getDocPrices($item_id);
            $price = $docData->price;
            $title = $docData->product_title;
            if ($item_type == 'drs' && $docData->drs_available == 'y') {
                // add doc price into drs if user buy lawyer assist service
                if(!$this->isDocPurchased($item_id)){
                    $price = $docData->drs_price + $price;
                }else {
                    $only_drs = 'y';
                    $price = $docData->drs_price;
                }
                $sub_discount = $this->getDrsDiscountIfSubscribed($price);
                if($sub_discount > $discount){
                    $discount = $sub_discount;
                }
                $title = $docData->product_title;
            }
            // check if user is subscribed and limit not reached then apply subscription discount
            if ($price > 0 && $this->isSubscriptionDiscountApplicable($item_type)) {
                $price = 0;
                $discount = 0;
            }
            $drs_available = $docData->drs_available;
            session()->forget('pay_for_legal');
        }elseif($item_type == 'subscription'){
            session()->forget('pay_for_legal');
            $subscriptionRepo = new SubscriptionRepository();
            $plan = $subscriptionRepo->getById($item_id);
            if ($plan) {
                $title = ucfirst($item_category). ' '. $period . ' '.$item_type;
                $price = ($period == 'monthly') ? $plan->monthly_fee : calculateAnnualFee($plan->monthly_fee, $plan->annual_discount);
            }
        }
        
        if ($discount > 0) {
//            Cart::removeCoupon();
//            Cart::applySpecialDiscount($item_id, $item_type, $item_category, $discount);
        }
        $item = [
            'id' => (int)$item_id,
            'qty' => 1,
            'price' => (float)$price,
            'name' => $title,
            'discount' => $discount,
            'options' => [
                'item_type' => $item_type,
                'item_category' => $item_category,
                'drs_available' => $drs_available,
                'only_drs' => $only_drs,
                'period' => $period,
            ],
        ];
        Cart::add($item);
        return true;
    }
    

    /**
     * Returns true if product already downloaded
     * @param $request
     * @return bool
     */
    public function isDocPurchased($item_id): bool
    {
        $doc_purchased = false;
        $sub_repo = new SubscriptionRepository();
        if (auth()->check() &&
            ($this->payment->isDocPurchased($item_id) || $sub_repo->isSubscriptionDownloaded(
                    $item_id
                ))) {
            $doc_purchased = true;
        }
        return $doc_purchased;
    }
    
}