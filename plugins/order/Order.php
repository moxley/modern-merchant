<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class order_Order extends mvc_Model
{
    public $id;
    public $order_date;
    public $creation_date;
    public $modify_username;
    public $ship_total;
    public $ship_date;
    public $shipping_method_id;
    public $payment_method_id;
    public $customer_id;
    public $tracking;
    public $unique_code;
    public $sid;
    public $cust_comments;
    public $notes;
    
    public $cust_approved = false;
    public $checkout_complete = false;
    public $previous_customer = false;

    public $lines = array();
    public $billing_addr;
    public $shipping_addr;
    
    private $_modify_user;
    private $_shipping_method;
    private $_payed;
    private $_customer;
    private $_cart_id;
    private $_cart;
    
    /*
     * May want to add methods:
     * getWeight()
     */
        
    function __construct()
    {
        $this->creation_date = mm_time();
        $this->order_date = mm_time();
    }
    
    static function createFromCart($cart)
    {
        $order = new order_Order;
        $order->populateFromCart($cart);
        return $order;
    }
        
    function populateFromCart($cart)
    {
        $this->lines = $cart->lines;
        $this->id = $cart->order_id;
        $this->payment_method_id = $cart->payment_method_id;
        $this->shipping_method_id = $cart->shipping_method_id;
        $this->cust_approved = $cart->cust_approved;
        $this->cust_comments = $cart->comments;
        $this->payed = $cart->payed;
        $this->checkout_complete = $cart->complete;
        $this->order_date = $cart->order_date;
        $this->creation_date = $cart->creation_date;
        $this->unique_code = $cart->unique_code;
        $this->cart_id = $cart->id;
        $this->sid = $cart->sid;
            
        //$this->sub_total = $cart->sub_total;
        $this->ship_total = $cart->shipping_total;
        //$this->total = $cart->total;
        
        $this->billing_addr = $cart->billing;
        $this->shipping_addr = $cart->shipping;
    }
    
    function getTotal()
    {
        return number_format($this->getSubTotal() + $this->ship_total, 2);
    }
    
    function getSubTotal()
    {
        $sub_total = 0;
        foreach( $this->lines as $line )
        {
            $sub_total += round($line->getTotal() * 100);
        }
        return number_format($sub_total / 100, 2);
    }
    
    function getShippingTotal()
    {
        cart_Cart::getShippingTotal();
    }
    
    function setAdminValues($values) {

        if (!$this->unique_code) {
            $this->order_date = strtotime($values['order_date']);
        }
        $this->ship_date = strtotime($values['ship_date']);
        if (!$this->shipping_method_id || $values['shipping_method_id'] != $this->shipping_method_id) {
            $this->shipping_method_id = $values['shipping_method_id'];
            $this->ship_total = $this->calculateShipTotal();
        }
        else {
            $this->shipping_method_id = $values['shipping_method_id'];
            $this->ship_total = ($values['ship_total']=='' ? null : mm_pricenumber($values['ship_total']));
        }
        $this->payment_method_id = $values['payment_method_id'];
        $this->tracking          = $values['tracking'];
        $this->cust_approved     = gv($values, 'cust_approved') ? true : false;
        $this->payed             = gv($values, 'payed') ? true : false;
        $this->notes             = $values['notes'];
        $this->lines_with_id     = $values['lines_with_id'];
        $this->billing_addr      = $this->parseAddress($values['billing_addr']);
        $this->shipping_addr     = $this->parseAddress($values['shipping_addr']);
        $this->previous_customer = gv($values, 'previous_customer') ? true : false;
        $this->total             = mm_pricenumber($this->getSubTotal() + $values['ship_total']);
    }
    
    function calculateShipTotal()
    {
        $code = $this->shipping_method->calc;
        $func = create_function('$cart', $code);
        if (!$func) {
            throw new Exception("Shipping function failed for shipping method '{$this->shipping_method->name}'");
        }
        return mm_pricenumber($func($this));
    }
    
    function parseAddress($assoc, $addr=null)
    {
        if (!$addr) $addr = new addr_Address;
        $addr->setPropertyValues($assoc);
        return $addr;
    }
    
    function setModifyUser($user)
    {
        $this->_modify_user = $user;
        $this->modify_username = $user->username;
    }
    
    function getLinesWithId()
    {
        $lookup = array();
        foreach ($this->lines as $line) {
            $lookup[$line->id] = $line;
        }
        return $lookup;
    }
    
    function addLine($line)
    {
        foreach ($this->lines as $i=>$l) {
            if ($l->sku == $line->sku) {
                $this->lines[$i]->qty += $line->qty;
                return;
            }
        }
        $this->lines[] = $line;
    }
    
    function setLinesWithId($lookup)
    {
        $lines_to_keep = array();
        foreach ($this->lines as $line) {
            if (array_key_exists($line->id, $lookup)) {
                if (!gv($lookup, 'delete')) {
                    $line->admin_values = $lookup[$line->id];
                    $lines_to_keep[] = $line;
                }
            }
        }
        $this->lines = $lines_to_keep;
        
        $pdao = new product_ProductDAO;
        foreach ($lookup as $id=>$values) {
            if ($id < 0) {
                $line = new cart_CartLine;
                $product = $pdao->fetchBySku($values['sku']);
                if ($product) {
                    $line->product = $product;
                    $this->addLine($line);
                }
            }
        }
    }
    
    function delete() {
        $this->dao->delete($this);
    }
    
    function getShippingMethod() {
        if (!$this->_shipping_method) {
            $id = $this->shipping_method_id;
            if (!$id) {
                $this->_shipping_method = null;
            }
            else {
                $sdao = new shipping_ShippingMethodDAO;
                $this->_shipping_method = $sdao->fetch($id);
            }
        }
        return $this->_shipping_method;
    }
    
    function setPayed($p)
    {
        $this->_payed = ($p && $p !== 'F');
    }
    
    function getPayed()
    {
        return $this->_payed && $this->_payed !== 'F';
    }
    
    function __wakeup()
    {
        $vars = get_object_vars($this);
        if (array_key_exists('payed', $vars)) {
            $this->setPayed($vars['payed']);
            unset($this->payed);
        }
    }
    
    function getBilling()
    {
        return $this->billing_addr;
    }
    
    function getShipping()
    {
        return $this->shipping_addr;
    }
    
    function getCustomer()
    {
        if (!$this->_customer) {
            if ($this->customer_id) {
                $this->_customer = customer_Customer::fetch($this->customer_id);
                $this->customer_id = $this->_customer_id;
            }
        }
        return $this->_customer;
    }
    
    function setCustomer($customer)
    {
        $this->_customer = $customer;
        if ($customer) {
            $this->customer_id = $customer->id;
        }
        else {
            $this->customer_id = null;
        }
    }
    
    /**
     * Get the cart_id property value.
     */
    function getCartId()
    {
        if (!$this->_cart) {
            return $this->_cart_id;
        } else {
            return $this->_cart->id;
        }
    }
    
    /**
     * Set the cart_id property value.
     *
     * If $id is NULL, sets underlying Cart object to NULL as well.
     */
    function setCartId($id)
    {
        if (!$id) {
            $this->_cart = null;
            $this->_cart_id = null;
        } else if ($id != $this->_id) {
            $this->_cart_id = $id;
            $this->_cart = null;
        }
    }
    
    /**
     * Get cart object associated with the order.
     *
     * This function autoloads the cart object.
     */
    function getCart()
    {
        if (!$this->_cart) {
            if ($this->_cart_id) {
                $cart_dao = new cart_CartDAO;
                $this->_cart = $cart_dao->fetch($this->_cart_id);
                if (!$this->_cart) {
                    trigger_error("Failed to find cart for id={$this->_cart_id}", E_USER_WARNING);
                }
            }
        }
        return $this->_cart;
    }
    
    /**
     * Set the cart object to be associated with the order.
     *
     * Also sets the $cart_id property to the $id of the given cart.
     * If $cart is NULL, sets the $cart_id property to NULL as well.
     */
    function setCart($cart)
    {
        if (!$cart) {
            $this->_cart_id = null;
            $this->_cart = null;
        } else {
            $this->_cart = $cart;
            $this->_cart_id = $cart->id;
        }
    }
}
