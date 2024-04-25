<?php


namespace App\Services;


use Illuminate\Support\Arr;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * Class Cart
 * @package App\Services
 */
class Cart
{
    const UTF8_ENABLED = true;
    /**
     * @var string
     */
    public $product_id_rules = '\.a-z0-9_-';

    /**
     * @var string
     */
    public $product_name_rules = '\w \'\-\.\:;,&()';

    /**
     * @var bool
     */
    public $product_name_safe = TRUE;

    /**
     * @var array|int[]
     */
    protected $_contents = [];

    /**
     * @var Session
     */
    protected $session;

    protected $coupon = [];

    /**
     * Cart constructor.
     * @param SessionStorageInterface|null $storage
     * @param array $options
     */
    public function __construct(SessionStorageInterface $storage = null, array $options = [])
    {
        if (!$storage) {
            $options = array_merge(['gc_maxlifetime' => 604800], $options);
            $storage = new NativeSessionStorage($options);
        }

        $this->session = new Session($storage);
        $this->_contents = $this->session->get('cart_contents');
        $this->coupon = $this->session->get('coupon');
        if ($this->_contents === NULL) {
            $this->_contents = ['cart_total' => 0, 'total_items' => 0];
        }
        if($this->coupon === NULL){
            $this->coupon = [];
        }
    }

    /**
     * Add item into cart
     * @param array $items
     * @throws \Exception
     */
    public function add(array $items = [])
    {
        if (!is_array($items) or count($items) === 0) {
            throw new \Exception('The add method must be passed an array containing data.');
        }

        $save_cart = FALSE;

        if (isset($items['id'])) {
            if (($rowid = $this->_insert($items))) {
                $save_cart = TRUE;
            }
        } else {
            foreach ($items as $val) {
                if (is_array($val) && isset($val['id'])) {
                    if ($this->_insert($val)) {
                        $save_cart = TRUE;
                    }
                }
            }
        }
        // Save the cart data if the insert was successful
        if ($save_cart === TRUE) {
            $this->_save_cart();
            if($this->hasCoupon()) {
                $this->applyCoupon(); // after saving contents apply coupon code if any
            }
            return $rowid ?? true;
        }
        return FALSE;
    }

    /**
     * Helper function to insert each item into cart
     * @param array $items
     * @return string
     * @throws \Exception
     */
    private function _insert(array $items = [])
    {
        if (!is_array($items) or count($items) === 0) {
            throw new \Exception('The add method must be passed an array containing data.');
        }

        // Does the $items array contain an id, quantity, price, and name?  These are required
        if (!isset($items['id'], $items['qty'], $items['price'], $items['name'])) {
            throw new \Exception('The cart array must contain a product ID, quantity, price, and name.');
        }

        $items['qty'] = (int)$items['qty'];

        if ($items['qty'] == 0) {
            throw new \Exception('The product quantity must be at least 1');
        }
        //validate product ID
        if (!preg_match('/^[' . $this->product_id_rules . ']+$/i', $items['id'])) {
            throw new \Exception('Invalid product ID.  The product ID can only contain alpha-numeric characters, dashes, and underscores');
        }
        //validate product name
        if ($this->product_name_safe && !preg_match('/^[' . $this->product_name_rules . ']+$/i' . (self::UTF8_ENABLED ? 'u' : ''), $items['name'])) {
            throw new \Exception('An invalid name was submitted as the product name: ' . $items['name'] . ' The name can only contain alpha-numeric characters, dashes, underscores, colons, and spaces');
        }

        $items['discount'] = (float)$items['discount'];
        $items['price'] = (float)$items['price'];
//        if($items['discount'] > 0 && $items['discount'] <= $items['price'] && !$this->hasCoupon()){
//            $items['price'] = $items['price'] - $items['discount'];
//        }

        if (isset($items['options']) && count($items['options']) > 0) {
            $rowid = md5($items['id'] . serialize($items['options']));
        } else {
            $rowid = md5($items['id']);
        }
        // grab quantity if it's already there and add it on
        $old_quantity = isset($this->_contents[$rowid]['qty']) ? (int)$this->_contents[$rowid]['qty'] : 0;

        $items['rowid'] = $rowid;
        $items['qty'] += $old_quantity;
        $this->_contents[$rowid] = $items;

        return $rowid;
    }

    /**
     * Update items in cart
     * @param array $items
     * @return bool
     * @throws \Exception
     */
    public function update(array $items = array())
    {
        // Was any cart data passed?
        if (!is_array($items) or count($items) === 0) {
            throw new \Exception('The update method must be passed an array containing data.');
        }

        // You can either update a single product using a one-dimensional array,
        // or multiple products using a multi-dimensional one.  The way we
        // determine the array type is by looking for a required array key named "rowid".
        // If it's not found we assume it's a multi-dimensional array
        $save_cart = FALSE;
        if (isset($items['rowid'])) {
            if ($this->_update($items) === TRUE) {
                $save_cart = TRUE;
            }
        } else {
            foreach ($items as $val) {
                if (is_array($val) && isset($val['rowid'])) {
                    if ($this->_update($val) === TRUE) {
                        $save_cart = TRUE;
                    }
                }
            }
        }

        // Save the cart data if the insert was successful
        if ($save_cart === TRUE) {
            $this->_save_cart();
            $this->applyCoupon(); // after saving contents apply coupon code if any
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Helper function to Update items in cart
     * @param array $items
     * @return bool
     */
    private function _update(array $items = array())
    {
        if (!isset($items['rowid'], $this->_contents[$items['rowid']])) {
            return FALSE;
        }

        // Prep the quantity
        if (isset($items['qty'])) {
            $items['qty'] = (int)$items['qty'];
            // Is the quantity zero?  If so we will remove the item from the cart.
            // If the quantity is greater than zero we are updating
            if ($items['qty'] == 0) {
                unset($this->_contents[$items['rowid']]);
                return TRUE;
            }
        }

        // find updatable keys
        $keys = array_intersect(array_keys($this->_contents[$items['rowid']]), array_keys($items));
        // if a price was passed, make sure it contains valid data
        if (isset($items['price'])) {
            $items['price'] = (float)$items['price'];
        }

        // product id & name shouldn't be changed
        foreach (array_diff($keys, array('id', 'name')) as $key) {
            $this->_contents[$items['rowid']][$key] = $items[$key];
        }

        return TRUE;
    }

    /**
     * Push items into cart
     * @return bool
     */
    private function _save_cart()
    {
        // Let's add up the individual prices and set the cart sub-total
        $this->_contents['total_items'] = $this->_contents['cart_total'] = 0;
        foreach ($this->_contents as $key => $val) {
            // We make sure the array contains the proper indexes
            if (!is_array($val) or !isset($val['price'], $val['qty'])) {
                continue;
            }

            $this->_contents['cart_total'] += $val['price'] * $val['qty'];
            $this->_contents['total_items'] += $val['qty'];
            $this->_contents[$key]['subtotal'] = ($this->_contents[$key]['price'] - $this->_contents[$key]['discount']) * $this->_contents[$key]['qty'];
        }
//        $this->_contents['coupon'] = $this->coupon;

        // Is our cart empty? If so we delete it from the session
        if (count($this->_contents) <= 2) {
            $this->session->remove('cart_contents');
            return FALSE;
        }

        // If we made it this far it means that our cart has data.
        // Let's pass it to the Session class so it can be stored
//        $this->session->set('cart_contents', $this->_contents);
        $this->_save();
        return TRUE;
    }

    /**
     * final save the data in session
     * @return bool
     */
    private function _save()
    {
        $this->session->set('cart_contents', $this->_contents);
        $this->session->set('coupon', $this->coupon);
        return true;
    }

    /**
     * Get items total
     * @param string $item_type
     * @return int|mixed
     */
    public function total($item_type = '')
    {
        if (empty($item_type)) return $this->_contents['cart_total'];

        $total = 0;
        if ($this->totalItems() > 0) {
            foreach ($this->contents() as $content) {
                if ($content['options']['item_type'] == $item_type) {
                    $total += $content['subtotal'];
                }
            }
        }
        return round($total, 2);
    }

    /**
     * Get items total
     * @param string $item_type
     * @return int|mixed
     */
    public function subTotal($item_type = '')
    {
        $total = 0;
        if ($this->totalItems() > 0) {
            foreach ($this->contents() as $content) {
                if (empty($item_type)) {
                    $total += $content['subtotal'];
                }else if($content['options']['item_type'] == $item_type) {
                    $total += $content['subtotal'];
                }
            }
        }
        return round($total, 2);
    }

    /**
     * Remove item from the cart
     * @param $rowid
     * @return bool
     */
    public function remove($rowid)
    {
        // unset & save
        unset($this->_contents[$rowid]);
        $this->_save_cart();
        if($this->hasCoupon()){ // only apply coupon if exist other wise special discount should not effected
            $this->applyCoupon();
        }
        return TRUE;
    }

    /**
     * Get total items in the cart
     * @return int|mixed
     */
    public function totalItems()
    {
        return $this->_contents['total_items'];
    }

    /**
     * Get all items in the cart
     * @param false $newest_first
     * @return array|int[]
     */
    public function contents($sortBy = 'id', $newest_first = FALSE)
    {
        // do we want the newest first?
        $cart = ($newest_first) ? array_reverse($this->_contents) : $this->_contents;

        // Remove these so they don't create a problem when showing the cart table
        unset($cart['total_items']);
        unset($cart['cart_total']);
        return $this->sortBy($cart, $sortBy);
    }

    public function sortBy($cart, $key)
    {
        uasort($cart, function($a,$b) use ($key) {
            return (int)$b[$key] - (int)$a[$key];
        });
        return $cart;
    }

    /**
     * Returns the details of a specific item in the cart
     * @param $row_id
     * @return false|int|mixed
     */
    public function getItem($row_id)
    {
        return (in_array($row_id, array('total_items', 'cart_total'), TRUE) or !isset($this->_contents[$row_id]))
            ? FALSE
            : $this->_contents[$row_id];
    }

    /**
     * Returns TRUE if the rowid passed to this function correlates to an item
     * that has options associated with it.
     * @param string $row_id
     * @return bool
     */
    public function hasOptions($row_id = '')
    {
        return (isset($this->_contents[$row_id]['options']) && count($this->_contents[$row_id]['options']) !== 0);
    }

    /**
     * Product options
     *
     * Returns the an array of options, for a particular product row ID
     *
     * @param string $row_id = ''
     * @return    array
     */
    public function productOptions($row_id = '')
    {
        return isset($this->_contents[$row_id]['options']) ? $this->_contents[$row_id]['options'] : array();
    }

    /**
     * Format Number
     *
     * Returns the supplied number with commas and a decimal point.
     *
     * @param string $n
     * @return    string
     */
    public function formatNumber($n = '')
    {
        return ($n === '') ? '' : number_format((float)$n, 2, '.', '');
    }

    /**
     * Destroy the cart
     *
     * Empties the cart and kills the session
     *
     * @return    void
     */
    public function clear()
    {
        $this->_contents = array('cart_total' => 0, 'total_items' => 0);
        $this->session->remove('cart_contents');
        $this->session->remove('coupon');
    }

    /**
     * Checks either a specific product already added in cart then remove it to add fresh item
     * @param  $product_id
     * @param  string  $type
     * @param  string|null  $category
     * @param  bool|true $optionCompare
     * @return bool
     */
    public function removeItemIfExist($product_id, string $type, string $category = null, bool $optionCompare = true)
    {
        if ($this->totalItems() <= 0) {
            return false;
        }

        foreach ($this->contents() as $content) {
            if ($product_id == $content['id'] && (!$optionCompare || $type == $content['options']['item_type'])) {
                $this->remove($content['rowid']);
                return true;
            }
        }

        return false;
    }

    /**
     * return distinct item types from options array
     *
     * @return array
     */
    public function itemTypes()
    {
        if ($this->totalItems() > 0) {
            return array_unique(Arr::pluck($this->contents(), 'options.item_type'));
        }
        return [];
    }

    public function coupon($couponData)
    {
//        if (empty($this->coupon)){
        $this->removeCoupon();
//        }
        $this->coupon = $couponData;
        $this->applyCoupon();
        return true;
    }

    public function applyCoupon()
    {
        if (empty($this->coupon)){
            $this->removeCoupon();
            return true;
        }

        $discountApplicable = [];
        $is_high_street_promo = false;
        if ($this->coupon['for_drs'] == 'y') $discountApplicable[] = 'drs';
        if ($this->coupon['for_docs'] == 'y') $discountApplicable[] = 'document';
        if ($this->coupon['for_subscription'] == 'y') $discountApplicable[] = 'subscription';
        if ($this->coupon['p_code'] == config('custom.high_street_promo') ) $is_high_street_promo = true;

        if ($this->totalItems() > 0) {
            $total = 0;
            $discount = $this->coupon['discount'];
            $max_discount = config('custom.high_street_max_discount');
            $discount_given = 0;

            foreach ($this->contents() as $rowID => $content) {
                if (in_array($content['options']['item_type'], $discountApplicable) && $content['price'] > 0
                    && promoValidForCategory($this->coupon, $content['id']))
                {

                    if ($this->coupon['discount_type'] == 'Percentage') { // if coupon code is in percentage
                        $applicable_discount = ($this->_contents[$rowID]['price'] / 100) * $discount;

                        if($is_high_street_promo){ // if high street discount applied and already given discount exceeds 60 then limitize this to 60(high street max discount)
                            if($this->_contents[$rowID]['price'] > 100) { // if price > 100 then roundup other wise not
                                $applicable_discount = roundUpToAny($applicable_discount);
                            }
                            if(($discount_given + $applicable_discount) > $max_discount){
                                $applicable_discount = $max_discount - $discount_given;
                            }
                            $discount_given += $applicable_discount;
                        }
                        $this->_contents[$rowID]['discount'] = $applicable_discount;
                    } else { // if promo code is amount based
                        $this->_contents[$rowID]['discount'] = ($discount >= $this->_contents[$rowID]['price'])
                            ? $this->_contents[$rowID]['price']
                            : $discount;
                        $discount = $discount - $this->_contents[$rowID]['discount'];
                    }
                    $this->_contents[$rowID]['subtotal'] = $this->_contents[$rowID]['price'] - $this->_contents[$rowID]['discount'];
                }else{
//                    $this->_contents[$rowID]['subtotal'] = $this->_contents[$rowID]['subtotal'] + $this->_contents[$rowID]['discount'];
                    $this->_contents[$rowID]['subtotal'] = $this->_contents[$rowID]['price'];
                    $this->_contents[$rowID]['discount'] = 0;
                }
                $total += $this->_contents[$rowID]['subtotal'];
            }
            $this->_contents['cart_total'] = $this->formatNumber($total);
            $this->_save();
        }
        return true;
    }

    public function hasCoupon()
    {
        return (empty($this->coupon)) ? false : true;
    }
    public function getCoupon()
    {
        return $this->coupon;
    }

    public function setCoupon(array $couponData)
    {
        $this->coupon = $couponData;
    }

    public function getItemDiscount($cartRowId)
    {
        $discount = 0;
//        if($this->hasCoupon()){
        if ($this->totalItems() > 0) {
            foreach ($this->contents() as $rowID => $content) {
                if($rowID == $cartRowId)
                    return $this->_contents[$rowID]['discount'];
            }
        }
//        }
        return $discount;
    }

    public function getTotalDiscount()
    {
        $discount = 0;
        if($this->hasCoupon()){
            if ($this->totalItems() > 0) {
                foreach ($this->contents() as $rowID => $content) {
                    $discount += $this->_contents[$rowID]['discount'];
                }
            }
        }
        return $this->formatNumber($discount);
    }

    public function removeCoupon()
    {
        $this->coupon = [];
        if ($this->totalItems() > 0) {
            $total = 0;
            foreach ($this->contents() as $rowID => $content) {
                $this->_contents[$rowID]['discount'] = 0;
                $this->_contents[$rowID]['subtotal'] = $this->_contents[$rowID]['price'];
                $total += $this->_contents[$rowID]['subtotal'];
            }
            $this->_contents['cart_total'] = $this->formatNumber($total);
            $this->_save();
        }
        return true;
    }

    public function applySpecialDiscount($product_id, string $type, string $category = null, $discount = 0)
    {
        if ($this->totalItems() > 0) {
            $total = 0;
            foreach ($this->contents() as $rowID => $content) {
                if ($product_id == $content['id'] && $type == $content['options']['item_type'] && $category == $content['options']['item_category'] && $discount > 0) {
                    $this->_contents[$rowID]['discount'] = $discount;
                    $this->_contents[$rowID]['subtotal'] = $this->_contents[$rowID]['price'] - $discount;
                }
                $total += $this->_contents[$rowID]['subtotal'];
            }
            $this->_contents['cart_total'] = $this->formatNumber($total);
            $this->_save();
        }
        return true;
    }
}