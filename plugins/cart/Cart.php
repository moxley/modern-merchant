<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * The Shopping Cart
 * @package cart
 */
class cart_Cart extends mvc_Model
{
    public $id;
    public $creation_date;
    public $lines = array();
    public $live = true;
    private $ship_calc;

    /**
     * @var cart_Shipping
     */
    private $_shipping;

    /**
     * @var cart_Billing
     */
    private $_billing;
    
    /**
     * @var array
     */
    public $payment;
    
    private $ship_types = array();
    private $_shipping_method_id;
    private $_shipping_total_override;
    public  $payment_method_id;
    private $_payment_method;
    public $order_id;
    public $customer_id;
    public $cust_approved = false;
    private $_payed = false;
    public $complete = false;
    public $unique_code;
    public $sid;
    public $error;
    public $user_messages = array();
    public $order_date;
    public $billing_same = true;
    public $comments;
    
    private $shipping_functions = array();
    private $_shipping_method;
    
    function __construct($properties=array())
    {
        parent::__construct($properties);
        $this->creation_date = mm_time();
        $this->unique_code = uniqid("cart_");
        $this->payment_method_id = payment_PaymentMethod::defaultMethodId();
    }
    
    function afterAdd()
    {
        mvc_Hooks::notifyListeners('cart.created', $this);
    }
    
    function &match($attrib, $value)
    {
        $matches = array();
        for( $i=0; $i < count($this->lines); $i++ )
        {
            $line =& $this->lines[$i];
            $v = $line->$attrib;
            if( isset($v) && $v == $value ) $matches[] =& $line;
        }
        
        return $matches;
    }
    
    function &matchNot($attrib, $value)
    {
        $matches = array();
        for( $i=0; $i < count($this->lines); $i++ )
        {
            $line =& $this->lines[$i];
            $v = $line->$attrib;
            if( !isset($v) || $v != $value ) $matches[] =& $line;
        }
        
        return $matches;
    }
    
    function getCustomerApproved()
    {
        return $this->cust_approved;
    }
    
    function getSubTotal()
    {
        $sub_total = 0;
        foreach ($this->lines as $line) {
            $sub_total += $line->getTotal();
        }
        return mm_price($sub_total);
    }
    
    function getShippingTotal()
    {
        if (!isset($this->_shipping_total)) {
            if (!$this->shipping_method) {
                $this->_shipping_total = 0;
            }
            else {
                $this->_shipping_total = mm_price($this->shipping_method->calculateAmount($this));
            }
        }
        return $this->_shipping_total;
    }
    
    function setShippingTotal($total)
    {
        $this->_shipping_total = $total;
    }
    
    function getShippingMethod()
    {
        if (!$this->_shipping_method) {
            if (!$this->shipping_method_id) {
                $this->_shipping_method = null;
            }
            else {
                $dao = new shipping_ShippingMethodDAO;
                $this->_shipping_method = $dao->fetch($this->shipping_method_id);
            }
        }
        return $this->_shipping_method;
    }
    
    function setShippingMethodId($id)
    {
        $this->_shipping_method = null;
        $this->_shipping_total = null;
        $this->_shipping_method_id = $id;
    }
    
    function setShippingMethod($method)
    {
        $this->_shipping_method = $method;
        if ($method) {
            $this->_shipping_method_id = $method->id;
        }
        else {
            $this->_shipping_method_id = null;
        }
        $this->_shipping_total = null;
    }
    
    /**
     * Indicates whether user chose a shipping method.
     *
     * Used on the main cart page to determine whether to shipping the shipping charge.
     *
     * @return boolean
     */
    function userChoseShippingMethod()
    {
        return $this->_shipping_method_id;
    }
    
    function getShippingMethodId()
    {
        if (!isset($this->_shipping_method_id)) {
            $dao = new shipping_ShippingMethodDAO;
            $methods = array_values($dao->getAllActive());
            if (count($methods) === 1) {
                $this->_shipping_method_id = $methods[0]->id;
                return $this->_shipping_method_id;
            }
            else {
                return shipping_ShippingMethod::defaultMethodId();
            }
        }
        else {
            return $this->_shipping_method_id;
        }
    }
    
    function addLine($line)
    {
        if (!$line->valid) {
            $this->addErrors($line->errors);
            return false;
        }
        $this->lines[] = $line;
        return $line;
    }
    
    /**
     * @return cart_CartLine|boolean  Returns a cart_CartLine object if successful. Otherwise, false.
     */
    function addProduct($product, $qty=1)
    {
        $lines_with_sku = $this->match('sku', $product->sku);
        if ($lines_with_sku) {
            $line = $lines_with_sku[0];
            $line->qty += $qty;
            if (!$line->valid) {
                $line->qty = $qty; // Revert
                $this->addErrors($line->errors);
                return false;
            }
        }
        else {
            $line = new cart_CartLine($product, $qty);
            if (!$this->addLine($line)) return false;
        }
        return $line;
    }
    
    /**
     * @return cart_CartLine|boolean  Returns a cart_CartLine object if successful. Otherwise, false.
     */
    function addBySku($sku, $qty=1) {
        $pdao = new product_ProductDAO;
        $product = $pdao->fetchBySku($sku);
        if (!$product) return null;
        return $this->addProduct($product, $qty);
    }

    /**
     * Update the line items
     *         
     * @param $updates array  An associative array
     * where the keys are the SKUs to update, and
     * the values are the new quantities. If a line
     * item does not exist for a given SKU, a new line
     * item will be created.
     * @return object An error if any occurred
     */
    function update($updates)
    {
        if (!is_array($updates)) return $this->raiseError("Illegal argument");
        $errors = array();
        foreach ($updates as $sku=>$qty) {
            if ($qty < 1) continue;
            $matched =& $this->match('sku', $sku);
            $matchedQty = $matched->qty;
            $matched->qty = $matchedQty + $qty;
            $this->set($matched->getId(), $matched);
        }
        return null;
    }
    
    /**
     * Remove a cart line item by its ID.
     */        
    function removeLineById($id)
    {
        return $this->removeLineBy('id', $id);
    }
    
    function removeLineBySku($sku)
    {
        return $this->removeLineBy('sku', $sku);
    }
    
    function removeLineBy($by, $value)
    {
        if ($by == 'index') return $this->removeLineByIndex($value);
        $new_lines = array();
        $removed_line = null;
        foreach ($this->lines as $index=>$line) {
            if ($line->$by != $value) $new_lines[] = $line;
            else {
                $removed_line = $line;
            }
        }
        $this->lines = $new_lines;
        return $removed_line;
    }
    
    function removeLineByIndex($index) {
        if (!array_key_exists($index, $this->lines)) return null;
        $removed_line = $this->lines[$index];
        unset($this->lines[$index]);
        return $removed_line;
    }

    function getLineIndexById($id)
    {
        foreach( $this->lines as $index=>$line )
        {
            if( $line->id == $id ) return $index;
        }
        
        return -1;
    }
    
    function getLineCount()
    {
        return count($this->lines);
    }
    
    function getLineById($id)
    {
        foreach ($this->lines as $line) {
            if ($line->id == $id) return $line;
        }
        return null;
    }
    
    function getQuantitiesById()
    {
        $quantities = array();
        foreach ($this->lines as $line) {
            $quantities[$line->id] = $line->qty;
        }
        return $quantities;
    }
    
    function setUserValues($values)
    {
        if (!$values) return;
        $this->quantities_by_id = array_delete_at($values, 'quantities_by_id');
        $this->shipping->property_values = gv($values, 'shipping');
        $this->billing->property_values = gv($values, 'billing');
        if (gv($values, 'shipping_method_id')) $this->shipping_method_id = $values['shipping_method_id'];
        if (gv($values, 'payment_method_id')) $this->payment_method_id = $values['payment_method_id'];
        $payment = gv($values, 'payment');
        if ($payment) $this->payment = $payment;
    }
    
    function setQuantitiesById($quantities)
    {
        if (!$quantities) return;
        foreach ($quantities as $id=>$qty) {
            $line = $this->getLineById($id);
            if ($line) {
                if ($qty < 1) {
                    $this->removeLineById($id);
                }
                else {
                    $line->qty = $qty;
                }
            }
        }
    }
    
    function getPaymentMethod()
    {
        if (!$this->_payment_method) {
            if (!$this->payment_method_id) {
                $this->_payment_method = null;
            }
            else {
                $dao = new payment_PaymentMethodDAO;
                $this->_payment_method = $dao->fetch($this->payment_method_id);
                $this->_payment_method->cart = $this;
            }
        }
        return $this->_payment_method;
    }
    
    /**
     * Get the session id of this cart
     */
    function getSessionId()
    {
        return $this->sid;
    }
    
    function getSID()
    {
        return $this->sid;
    }
    
    /**
    * Get the order total
    */
    function getTotal()
    {
        return sprintf("%0.2f", $this->getSubTotal() + $this->getShippingTotal());
    }
    
    function setOrderId($id)
    {
        $this->order_id = $id;
    }

    /**
    * Set the payment method
    */
    function setPaymentMethod($method)
    {
        $this->_payment_method = $method;
        $this->payment_method_id = $method->id;
    }

    /**
     * Set the session id for this cart
     */
    function setSessionId($id)
    {
        $this->sid = $id;
    }
    
    function setSid($sid)
    {
        $this->sid = $sid;
    }

    function reduceInventory()
    {
        mm_log("Top of cart_Cart#reduceInventory()");
        // Adjust inventory
        foreach ($this->lines as $line) {
            $count = $line->product->count;
            if ($count === FALSE) {
                $this->addError("Product \"{$line->description}\" is not available");
            }
            else if ($count === '' || $count === null) {
                continue;
            }
            else if ($count < $line->qty) {
                if ($count == 0) {
                    $this->addError("Product \"{$line->description}\" is sold out. It has been automatically removed from your cart.");
                    $this->removeLineBySku($line->sku);
                }
                else {
                    $this->addError("Maximum quantity exceeded for product \"{$line->description}\". Quantity automatically adjusted.");
                    $line->qty = $count;
                }
            }
        }
        
        if ($this->errors) {
            return false;
        }
        else {
            foreach ($this->lines as $line) {
                if ($line->product->count !== '' && $line->product->count !== null) {
                    $line->product->count -= $line->qty;
                    mm_log("cart_Cart#reduceInventory(): Updating product (id: {$line->product->id}, sku: {$line->product->sku}) inventory count to: {$line->product->count}");
                    if (!$line->product->updateCount()) {
                        $this->addError("Failed to update product quantity");
                    }
                }
            }
            
            return !$this->errors;
        }
    }

    function getOrder()
    {
        if (!$this->_order) {
            $dao = new order_OrderDAO;
            if ($this->order_id) {
                $this->_order = $dao->fetch($this->order_id);
            }
            else if ($this->unique_code) {
                $this->_order = $dao->fetchByUniqueCode($this->unique_code);
            }
        }
        return $this->_order;
    }
    
    static function dao()
    {
        return new cart_CartDAO;
    }
    
    function save()
    {
        if (!$this->is_valid) return false;
        
        $db = mm_getDatabase();
        if ($this->id) {
            $sql = "UPDATE mm_cart SET sid=?, order_id=?, data=? WHERE id=?";
            $params = array($this->sid, $this->order_id, serialize($this), $this->id);
            $db->execute($sql, $params);
        }
        else {
            $sql = "INSERT INTO mm_cart (creation_date, sid, order_id, data) VALUES (?, ?, ?, ?)";
            $params = array(date('Y-m-d, H:i:s', $this->creation_date), $this->sid, $this->order_id, serialize($this));
            $db->execute($sql, $params);
            $this->id = $db->lastInsertId();
        }
        return true;
    }
    
    /**
     * Save an attached order, creating one if necessary.
     *         
     * @return void
     */
    function saveOrder($options=array())
    {
        $order = $this->getOrder();
        if ($order) {
            if (!$this->order_date) $this->order_date = $order->date;
            $order->populateFromCart($this);
            $order->save();
        }
        else {
            $order = new order_Order;
            $this->order_date = time();
            $order->populateFromCart($this);
            mvc_Hooks::notifyListeners('cart.before_create_order', $order);
            $order->save();
            mvc_Hooks::notifyListeners('cart.after_create_order', $order);
            $this->populateFromOrder($order);
            $this->save();
        }
        
        return $order;
    }
    
    function populateFromOrder($order)
    {
        $this->order_id           = $order->id;
        $this->payment_method_id  = $order->payment_method_id;
        $this->shipping_method_id = $order->shipping_method_id;
        $this->creation_date      = $order->creation_date;
        $this->cust_approved      = $order->cust_approved;
        $this->lines              = $order->lines;
        $this->order_date         = $order->date;
        $this->payed              = $order->payed;
        $this->unique_code        = $order->unique_code;
        $this->id                 = $order->cart_id;
        $this->sid                = $order->sid;
        $this->shipping           = $order->shipping_addr;
        $this->billing            = $order->billing_addr;
        $this->comments           = $order->cust_comments;
        $this->shipping_total     = $order->ship_total;
        $this->customer_id        = $order->customer_id;
    }
    
    function populateFromSession($sess)
    {
        $this->sid = $sess->sid;
    }
    
    function get($order_id)
    {
        $dao = new order_OrderDAO;
        $order = $dao->fetch($order_id);
        $cart = new cart_Cart;
        $cart->populateFromOrder($order);
        return $cart;
    }
        
    function sendCustomerEmail($input=null)
    {
        $site_name = mm_getSetting('site.name');
        $noreply = mm_getSetting('site.noreply');
        $sales_email = mm_getSetting('sales.notify');
        $message = $this->getEmailMessage("customer");
        
        $cust_email = $this->billing->email;
        if (!$cust_email) return false;
        $r = mm_mail(
            $cust_email,
            "Your Order", 
            $message, 
            "From: \"$site_name\" <$sales_email>");
        return $r;
    }
    
    function sendSalesEmail($input=null)
    {
        $site_name = mm_getSetting('site.name');
        $noreply = mm_getSetting('site.noreply');
        
        // Set email address
        $sales_email = $this->getSalesEmail();
        if( !$sales_email ) return false;
        
        $message = $this->getEmailMessage("sales");
        $subject = "$site_name Order #{$this->order_id}";
        if (!$this->payed) $subject .= " pending payment";
        $r = mm_mail(
            $sales_email, 
            $subject,
            $message, 
            "From: \"$site_name\" <$noreply>");
            
        return $r;
    }

    function sendPaymentCustomerEmail($input=null)
    {
        $site_name = mm_getSetting('site.name');
        $noreply = mm_getSetting('site.noreply');
        $sales_email = mm_getSetting('sales.notify');
        $message = $this->getEmailMessage("payment_notification_customer");
        
        $cust_email = $this->billing->email;
        if (!$cust_email) return false;
        $r = mm_mail(
            $cust_email,
            "Your Payment", 
            $message, 
            "From: \"$site_name\" <$sales_email>");
        return $r;
    }

    function sendPaymentSalesEmail($input=null)
    {
        $site_name = mm_getSetting('site.name');
        $noreply = mm_getSetting('site.noreply');
        
        // Set email address
        $sales_email = $this->getSalesEmail();
        if( !$sales_email ) return false;
        
        $message = $this->getEmailMessage("payment_notification_sales");
        $r = mm_mail(
            $sales_email, 
            "$site_name Order #{$this->order_id} payment",
            $message, 
            "From: \"$site_name\" <$noreply>");
            
        return $r;
    }

    function getSalesEmail($input=null)
    {
        $email_str = mm_getSetting('orders.notification');
        if( !$email_str ) return;
        $emails = explode(',', $email_str);
        return $emails[0];
    }
    
    function renderEmail($name)
    {
        mm_renderContent("order.email.".$name, $this);
    }
    
    function getEmailMessage($name)
    {
        ob_start();
        $this->renderEmail($name);
        return ob_get_clean();
    }
    
    function cartToArray()
    {
        $cart = $this;
        $lines = $cart->lines;
        $cart_assoc = array();
        $cart_assoc['lines'] = array();
        for($i=0; $i < count($lines); $i++)
        {
            $line = $lines[$i];
            $line_assoc = array();
            if ($line->product) {
                $line_assoc['product'] = objectToAssoc($line->product);
            }
            else {
                $line_assoc['product'] = null;
            }
            $line_assoc['sku'] = $line->sku;
            $line_assoc['description'] = $line->description;
            $line_assoc['price'] = '$'.sprintf("%.2f", $line->getPrice());
            $line_assoc['quantity'] = $line->getQuantity();
            $line_assoc['qty'] = $line->getQuantity();
            $line_assoc['total'] = '$'.sprintf("%.2f", $line->getTotal());
            $line_assoc['id'] = $line->id;
            $cart_assoc['lines'][] = $line_assoc;
        }
        
        $cart_assoc['sub_total'] = '$'.sprintf("%.2f", $cart->getSubTotal());
        $cart_assoc['shipping'] = '$'.sprintf("%.2f", $cart->getShippingTotal());
        $cart_assoc['total'] = '$'.sprintf("%.2f", $cart->getTotal());
        return $cart_assoc;
    }
    
    function toString()
    {
        return $this->__toString();
    }
    
    function __toString()
    {
        $str = '';
        foreach (array('shipping', 'billing', 'payment') as $section) {
            if ($this->$section) {
                if (is_array($this->$section)) {
                    $str .= "== $section ==\n";
                    foreach ($this->$section as $name=>$value) {
                        $str .= "$name: $value\n";
                    }
                    $str .= "\n";
                } else if (is_object($this->$section)) {
                    $str .= "== $section ==\n";
                    $str .= $this->$section->__toString() . "\n";
                }
                else if (!is_object($this->$section)) {
                    $str .= "'$section' is not an object\n";
                }
            } else {
                $str .= "$section: EMPTY\n";
            }
        }
        $str .= "Payment Method: " . $this->payment_method->name . "\n";
        $str .= "Shipping Method ID: " . $this->shipping_method->id . "\n";
        
        $str .= "\n--- CART ---------------\n";
        foreach ($this->lines as $line) {
            $str .= "SKU='{$line->sku}', DESC='{$line->description}', PRICE='{$line->price}', QTY='{$line->qty}', TOTAL='" . $line->getTotal() . "'\n";
        }
        $str .= "------------------------\n";
        $str .= "--------- SubTotal:  " . $this->sub_total . "\n";
        $str .= "--------- ShipTotal: " . $this->shipping_total . "\n";
        $str .= "--------- Total:     " . $this->total . "\n";
        
        return $str;
    }
    
    /**
     * Perform the steps for a complete order.
     *
     * 1. Validate cart for checkout (includes inventory check)
     * 2. Process payment (unless $alreadyPayed)
     * 3. Mark order as 'payed', 'complete', NOT 'live' and 'cust_approved'
     * 4. Reduce inventory
     * 5. Save order
     * 6. Send order emails
     */
    function processOrder($alreadyPayed=false)
    {
        mm_log("Top of cart_Cart#processOrder()");
        if (!$this->validForOrder()) {
            mm_log("cart_Cart#processOrder(): Failed order validation");
            return false;
        }
        
        if (!$alreadyPayed && !$this->payment_method->process($this)) {
            $this->addErrors($this->payment_method->errors);
            mm_log("cart_Cart#processOrder(): Failed payment processing (payment_method class: " . get_class($this->payment_method) . ")");
            return false;
        }
        
        if (!$this->reduceInventory()) {
            return false;
        }

        $this->payed = $alreadyPayed || $this->payment_method->is_payed;
        $this->cust_approved = true;
        $this->complete = $this->payed;
        $this->live = false;
        mm_log("cart_Cart#processOrder(): Marked flags: " . var_export(array('payed' => $this->payed, 'cust_approved' => $this->cust_approved, 'complete' => $this->complete, 'live' => $this->live), true));
        $this->save();
        
        /*
         * Create/update the order
         */
        $order = $this->saveOrder();
        if (!$order) return false;
        
        // Approve the order
        $this->sendOrderEmails();

        return $order;
    }

    function createOrderNotPayed()
    {
        // Approve the order
        $this->cust_approved = true;
        // Save the order
        $order = $this->saveOrder();
        $this->populateFromOrder($order);
        $this->reduceInventory();
        $order->populateFromCart($this);
        $this->live = false;
        $this->sendOrderEmails();
        
        return $order;
    }
    
    function finishUnpaidOrderAsPayed()
    {
        $this->complete = true;
        $this->payed = true;
        $this->live = false;
        if (!$this->order_date) $this->order_date = time();
        $this->order->populateFromCart($this);
        $this->saveOrder();
        $this->sendPaymentEmails();
        
        return true;
    }
    
    function sendOrderEmails()
    {
        // Send emails
        $passed = array();
        $passed[] = $this->sendCustomerEmail();
        $passed[] = $this->sendSalesEmail();
        
        return $passed[0] && $passed[1];
    }
    
    function sendPaymentEmails()
    {
        // Send emails
        $passed = array();
        $passed[] = $this->sendPaymentCustomerEmail();
        $passed[] = $this->sendPaymentSalesEmail();
        
        return $passed[0] && $passed[1];
    }
    
    function isOrderComplete()
    {
        return $this->complete;
    }
    
    function getCartSession()
    {
        $sid = $this->getSessionId();
        if (!$sid)
        {
            $this->raiseError("No session id");
            return null;
        }
        $dao = new sess_SessionDAO;
        return $dao->fetchBySid($sid);    
    }
        /**
    * Returns TRUE if the cart has more of a particular SKU than is available in inventory.
    *
    *
    * @return boolean
    */
    function hasTooMany($sku)
    {
    }
        /**
    * Returns the number of items in the cart.
    *
    * @return int
    */
    function getItemCount()
    {
        $count = 0;
        foreach ($this->lines as $line) {
            $count += $line->qty;
        }
        return $count;
    }
    
    function getWeight()
    {
        $weight = 0;
        foreach ($this->lines as $line) {
            $weight += $line->qty * $line->product->weight;
        }
        return $weight;
    }
    /**
     * Reduce the item counts in the cart to inventory levels.
     *
     * @return array  A list of affected SKUs and the reduction amounts.
     */
    function reduceAllToInventory()
    {
    }
        /**
    * Reduce the item count of a SKU to match inventory level.
    *
    * @return int  The amount of reduction.
    */
    function reduceToInventory($sku, $inventory)
    {
    }
        /**
    * Get a list of all the SKUs in the cart.
    *
    * @return array  The list of SKUs
    */
    function getSkuList()
    {
    }
    
    /**
    * Get a list quantities of each SKU in the cart.
    *
    * @return array  A hash, for which each element contains a product SKU as the key,
    *                and an item count for the SKU as the value.
    */
    function getSkuQuantities()
    {
        // Holds the return value
        $quantities = array();
        
        $lines =& $this->lines;
        
        // Populate $quantities:
        // Loop through each cart line, recording quantity of every SKU
        for( $i=0; $i < count($lines); $i++ )
        {
            $line =& $lines[$i];
            $sku = $line->sku;
            $qty = $line->qty;
            if( isset($quantities[$sku]) ) $quantities[$sku] += $qty;
            else $quantities[$sku] = $qty;
        }
            return $quantities;
    }
    
    function getCart($order_id=0)
    {
        $sess = mm_getSession();
        
        if (!$order_id) {
            $order_id = $sess->get('order_id');
        }
        
        $cartObj = NULL;
        if( $order_id )
        {
            $cartModel = new cart_Cart;
            $cartObj = $cartModel->get($order_id);
        }
        if( !$cartObj )
        {
            $cartObj = new cart_Cart;
            $cartObj->setSID($sess->sid);
            $sess->set('cart', $cartObj);
        }
        
        return $cartObj;
    }
    
    /**
     * Get a cart associated with the user's session.
     * 
     * @return mixed cart or boolean
     */
    function getUserCart()
    {
        $sess = mm_getSession();
        $cart = $sess->get('cart');
        if (!$cart) {
            $cart = new cart_Cart;
            $sess->set('cart', $cart);
        }
        return $cart;
    }

    function getRemoteAddr()
    {
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        else {
            return '127.0.0.1';
        }
    }

    function nextLineId() {
        return cart_CartLine::nextId();
    }
    
    function validateLines() {
        foreach ($this->lines as $line) {
            $line->validate();
            $this->addErrors($line->errors);
        }
        return $this->errors;
    }
    
    function adjustLines() {
        // Reduce quantities [and remove lines]
        foreach ($this->lines as $i=>$line) {
            if ($line->remove_self_qty > 0) {
                $line->qty -= $line->remove_self_qty;
            }
            if ($line->qty < 1) {
                array_delete_at($this->lines, $i);
            }
        }
    }
    
    function validateMinimumOrder() {
        if (!$this->lines) {
            $this->addError("There are no items in your cart");
        }
        else {
            $this->validateLines();
        }
        return $this->errors;
    }
    
    function validateForCheckout() {
        $this->validateMinimumOrder();
        return $this->errors;
    }
    
    function validForCheckout() {
        $this->validateForCheckout();
        return !$this->errors;
    }
    
    function validateShippingMethod() {
        if (!$this->shipping_method_id) {
            $this->addError("Please select a shipping method");
        }
        return $this->errors;
    }
    
    function validateShipping() {
        $this->validateForCheckout();
        if ($this->shipping->is_empty) {
            $this->addError("Please provide shipping information");
        }
        else {
            $this->addErrors($this->shipping->validate());
        }
        return $this->errors;
    }
    
    function validateBilling() {
        if (!$this->billing) {
            $this->addError("Please provide billing information");
        }
        else {
            $this->addErrors($this->billing->validate());
        }
        return $this->errors;
    }
    
    function validatePaymentMethod() {
        if (!$this->payment_method_id) {
            $this->addError("Please select a payment method");
        }
        else if (!$this->payment_method) {
            $this->addError("No payment method for id={$this->payment_method_id}");
        }
        else {
            $this->addErrors($this->payment_method->validatePayment());
        }
        return $this->errors;
    }
    
    function validateForOrder()
    {
        $this->validateMinimumOrder();
        $this->validateBilling();
        $this->validateShipping();
        $this->validatePaymentMethod();
        return $this->errors;
    }
    
    function validForOrder()
    {
        $this->validateForOrder();
        return !$this->errors;
    }
    
    function __sleep() {
        $this->_payment_method = null;
        $this->_shipping_method = null;
        $this->_customer = null;
        return array_keys(get_object_vars($this));
    }

    function getHandlingTotal()
    {
        return 0;
    }
    
    function getTaxTotal()
    {
        return 0;
    }

    /**
     * @return cart_Billing
     */
    function getBilling()
    {
        if (!$this->_billing) {
            $this->_billing = new cart_Billing;
        }
        return $this->_billing;
    }
    
    function setBilling($billing)
    {
        if (is_array($billing)) {
            $this->billing->property_values = $billing;
        }
        else if (is_object($billing)) {
            $this->_billing = $billing;
        }
        else if (!$billing) {
            $this->_billing = null;
        }
    }
    
    /**
     * @return cart_Shipping
     */
    function getShipping()
    {
        if (!$this->_shipping) {
            $this->_shipping = new cart_Shipping;
        }
        return $this->_shipping;
    }
    
    /**
     * @param cart_Shipping $shipping
     */
    function setShipping($shipping)
    {
        if (is_array($shipping)) {
            $this->shipping->property_values = $shipping;
        }
        else if (is_object($shipping)) {
            $this->_shipping = $shipping;
        }
        else if (!$shipping) {
            $this->_shipping = null;
        }
    }
    
    function getCustomer()
    {
        if (!$this->_customer) {
            if ($this->customer_id) {
                $this->_customer = customer_Customer::fetch($this->customer_id);
            }
        }
        return $this->_customer;
    }
    
    function setCustomer($customer)
    {
        if (!$customer) {
            $this->customer_id = null;
        }
        else {
            $this->customer_id = $customer->id;
            $this->_customer = $customer;
        }
    }
    
    function getPayed()
    {
        return $this->_payed;
    }
    
    function setPayed($payed)
    {
        $this->_payed = $payed ? true : false;
        if ($this->_payed) {
            $this->payment = array();
        }
    }
}
