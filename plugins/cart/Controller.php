<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Controller for the shopping cart and checkout.
 * @package cart
 */
class cart_Controller extends mvc_PublicController
{
    private $_customer;
    
    function __construct() {
        parent::__construct();
        $this->title = "Shopping Cart";
    }

    /**
     * Action definition for showing the shopping cart.
     */
    function runCartAction()
    {
        $this->getCart();
    }
    
    function runAddAction()
    {
        $this->getCart();
        
        // Add lineitem to cart
        $sku_qty_assoc = $this->parseProducts($this->getRequest());
        if (!$sku_qty_assoc) {
            $this->addWarning("No sku specified");
        }
        else {
            foreach ($sku_qty_assoc as $sku=>$qty) {
                $this->cart->addBySku($sku, $qty);
            }
        }

        if ($errors = $this->cart->validateLines()) {
            $this->cart->adjustLines();
            $this->addWarnings($errors);
        }
        
        $this->cart->save();
        mm_setCart($this->cart);
        
        $this->redirectToAction('cart');
        return false;
    }
    
    function parseProducts($request)
    {
        $sku = gv($request, 'sku');
        $qty = gv($request, 'qty', 1);
        if ($sku) {
            if ($qty < 1) $qty = 1;
            return array($sku=>$qty);
        }
        
        $add = $this->req('add');
        if (!$add) return array();
        
        $products = array();
        foreach ($add as $key=>$value) {
            if (trim($key) && intval($value) > 0) {
                $products[$key] = intval($value);
            }
        }
        
        return $products;
    }
    
    function runRemoveAction()
    {
        $this->getCart();
        $sku = $this->getRequiredParam('sku');
        $line = $this->cart->removeLineBySku($sku);
        if ($line) {
            $this->addNotice("Removed item (sku=$sku)");
        }
        $this->cart->save();
        
        $this->redirectToAction('cart');
        return false;
    }
    
    /**
     * Action definition for updating the shopping cart.
     * Performs 'change quantity' and 'remove' functions
     */
    function runUpdateAction()
    {
        $cart = $this->getCart();
        $cart->user_values = $this->req('cart');

        if ($errors = $this->cart->validateForCheckout()) {
            $this->cart->adjustLines();
            $this->addWarnings($errors);
        }
        $this->cart->save();

        $this->redirectToAction('cart');
        return false;
    }
    
    /**
     * Parse updates to the cart
     *
     * This function takes request values and translates them into
     * an associative array. Each element in the array consists of a key
     * representing the product's SKU, and a value representing the product
     * count that should be set.
     * 
     * @return array  The changes to be made to the cart line items
     */
    function parseUpdates($request)
    {
        $sku = gv($request, 'sku');
        $qty = gv($request, 'qty', 1);
        return array($sku => $qty);
    }
    
    function runCheckoutAction()
    {
        $this->getCart();
        $cart_values = $this->req('cart');
        $this->cart->user_values = $this->req('cart');
        
        if ($errors = $this->cart->validateForCheckout()) {
            $this->cart->adjustLines();
            $this->cart->save();
            $this->addWarnings($errors);
            $this->redirectToAction('cart');
            return false;
        }

        if (!$this->cart->is_valid) {
            $this->addWarnings($this->cart->errors);
            $this->redirectToAction('cart');
            return false;
        }
        $this->cart->save();
        $this->redirectToAction('cart.shippingPage');
        return false;
    }
    
    function runContinueShoppingAction()
    {
        $sess = $this->getSession();
        $shoppingUrl = $sess->get("return_url");
        if( !isset($shoppingUrl) ) $shoppingUrl = mm_getConfigValue('urls.catalog.script');
            
        redirect($shoppingUrl);
        return false;
    }
    
    function runDefaultAction()
    {
        $this->redirectToAction('cart.cart');
        return false;
    }

    /**
     * Shipping page Action
     */
    function runShippingPageAction()
    {
        $this->cart = $this->getCart();
        
        $this->default_shipping_method_id = mm_getSetting('default_shipping_method');
        
        if ($errors = $this->cart->validateForCheckout()) {
            $this->cart->adjustLines();
            $this->cart->save();
            $this->addWarnings($errors);
            $this->redirectToAction('cart');
            return false;
        }
        $this->title = "Checkout: Shipping";
    }
    
    function runCreateAccountAction()
    {
        $values = $this->req('user');
        $this->user = new user_User($this->req('user'));
        $this->user->new_password = $this->user->confirm_password = $values['password'];
        $this->user->setAsCustomer();
        if (!$this->user->save()) {
            $this->addWarnings($this->user->errors);
        }
        else {
            mm_setUser($this->user);
            $this->addNotice("Your account has been created!");
            $this->customer = new customer_Customer;
            $this->customer->user = $this->user;
            if (!$this->customer->save()) {
                $this->addWarnings($this->customer->errors);
            }
            else {
                mm_setCustomer($this->customer);
            }
        }
        $this->redirectToAction('cart.shippingPage');
        return false;
    }
    
    function runLoginAction()
    {
        $this->login = new user_User($this->req('user'));
        if (!($user = $this->login->login())) {
            $this->addWarnings($this->login->errors);
            $this->redirectToAction('cart.shippingPage');
            return false;
        }
        $this->customer = mm_getCustomer();
        if (!$this->customer) {
            $this->customer = new customer_Customer;
            $this->customer->user = $user;
            if (!$this->customer->save()) {
                $this->addWarnings($this->customer->errors);
            }
            mm_setCustomer($this->customer);
        }
        
        // Copy shipping, billing information
        if (!$this->cart->billing->isValid() && $this->customer->billing_address->is_valid) {
            $this->cart->billing = $this->customer->billing_address;
        }
        if (!$this->cart->shipping->isValid() && $this->customer->shipping_address->is_valid) {
            $this->cart->shipping = $this->customer->shipping_address;
        }
        
        $this->addNotice("Hello, {$user->username}!");
        $this->redirectToAction('cart.shippingPage');
        return false;
    }
    
    function runSubmitShippingAction()
    {
        // Check cart's order values
        $cart = $this->getCart();
        
        $cart->setPropertyValues($this->req('cart'));

        if ($errors = $this->cart->validateForCheckout()) {
            $this->cart->adjustLines();
            $this->cart->save();
            $this->addWarnings($errors);
            $this->redirectToAction('cart');
            return false;
        }
        
        if ($errors = $cart->validateShipping()) {
            $this->cart->save();
            $this->addWarnings($errors);
            $this->redirectToAction('cart.shippingPage');
            return false;
        }
        
        /* At this point, shipping is valid */
        
        $customer = mm_getCustomer();
        
        /* Save shipping to customer's record */
        if ($customer) {
            if (!$customer->updateShipping($cart->shipping)) {
                $this->addWarnings($customer->errors);
                $this->redirectToAction('cart.shippingPage');
                return false;
            }
        }
        
        $cart_params = $this->req('cart');
        if (!$cart->billing || $cart->billing->is_empty || $cart_params['shipping']['billing_same']) {
            $shipping_values = (array) $cart->shipping;
            $cart->billing = new cart_Billing($shipping_values);
        }
        
        $billing_errors = array();
        if ($cart_params['shipping']['billing_same']) {
            $billing_errors = $cart->billing->validate();
        }
        
        $this->cart->save();
        if ($billing_errors || !$cart_params['shipping']['billing_same']) {
            $this->addWarnings($billing_errors);
            $this->redirectToAction('cart.billingPage');
            return false;
        }
        else {
            if ($customer && !$customer->updateBilling($cart->billing)) {
                $this->addWarnings($customer->errors);
                $this->redirectToAction('cart.billingPage');
                return false;
            }
            $this->redirectToAction('cart.paymentPage');
            return false;
        }
    }

    function runShippingMethodPageAction()
    {
    }

    function runSubmitShippingMethodAction()
    {
        $cart = $this->getCart();
        $cart->setPropertyValues($this->req('cart'));
        $this->cart->save();
        if (!$cart->shipping_method_id) {
            $this->addWarning("Please specifiy a shipping method");
            $this->redirectToAction('cart.shippingMethodPage');
            return false;
        }
        $this->redirectToAction('cart.paymentPage');
        return false;
    }
    
    function runBillingPageAction()
    {
        $this->getCart();
        $this->title = "Checkout: Billing";
    }
    
    function runSubmitBillingAction()
    {
        $request = $this->getRequest();
        $cart = $this->getCart();
        $cart->setPropertyValues($this->req('cart'));

        if ($errors = $this->cart->validateForCheckout()) {
            $this->cart->adjustLines();
            $this->cart->save();
            $this->addWarnings($errors);
            $this->redirectToAction('cart');
            return false;
        }

        $this->cart->save();
        if ($errors = $cart->billing->validate()) {
            $this->addWarnings($errors);
            $this->redirectToAction('cart.billingPage');
            return false;
        }
        
        $customer = mm_getCustomer();
        if ($customer && !$customer->updateBilling($cart->billing)) {
            $this->addWarnings($customer->errors);
            $this->redirectToAction('cart.billingPage');
            return false;
        }
        
        $this->redirectToAction('cart.paymentPage');
        return false;
    }
    
    function getPaymentMethods()
    {
        $payment_access = new payment_PaymentMethodDAO;
        return $payment_access->getActiveModules();
    }

    function runPaymentPageAction()
    {
        $cart = $this->getCart();
        $cart->setPropertyValues($this->req('cart'));
        $cart->save();
        $this->title = "Checkout: Payment Method";
    }
    
    function runSubmitPaymentAction()
    {
        // Let the cart know what the payment method is
        $cart = $this->getCart();
        $cart->setPropertyValues($this->req('cart'));

        $errors = $cart->validateForCheckout();
        if ($errors) {
            $this->cart->adjustLines();
            $this->addWarnings($errors);
            $cart->save();
            $this->redirectToAction('cart');
            return false;
        }

        $cart->validatePaymentMethod();
        if ($cart->errors) {
            $this->addWarnings($cart->errors);
            $this->redirectToAction('cart.paymentPage');
            return false;
        }
        
        $this->cart->save();
        $this->session->set('after_order_cart_id', $cart->id);

        $this->redirectToAction('cart.verifyPage');
        return false;
    }
    
    function runFetchTestCartAction() {
        $id = intval($this->req('id'));
        if (!$id) {
            $this->addWarning("Missing 'id' parameter");
            $this->redirectToAction('cart');
            return false;
        }
        ob_start();
        $f = fopen(MM_LIB.'/private/testcart.' . $id . '.data', 'r');
        $error = ob_get_contents();
        ob_end_clean();
        if (!$f) {
            $this->addWarning("Failed to open testcart file: " . $error);
            $this->redirectToAction('cart');
            return false;
        }
        $data = fread($f, 50000);
        fclose($f);
        $cart = unserialize($data);
        
        // Adjust inventory levels to match cart's
        foreach ($cart->lines as $line) {
            $product = $line->product;
            if ($line->qty > $product->count) {
                $product->count = $line->qty;
                $product->save();
            }
        }
        
        $this->setCart($cart);
        $this->redirectToAction('cart.verifyPage');
        return false;
    }
    
    function runSaveTestCartAction() {
        $this->getCart();
        $id = intval($this->req('id'));
        if (!$id) {
            $this->addWarning("Missing 'id' parameter");
            $this->redirectToAction('cart.verifyPage');
            return false;
        }
        $f = fopen(MM_LIB.'/private/testcart.' . $id . '.data', 'w');
        if (!$f) {
            $this->addWarning("Failed top open file");
            $this->redirectToAction('cart.verifyPage');
            return false;
        }
        fwrite($f, serialize($this->cart));
        fclose($f);
        $this->addNotice("Saved cart id '$id'");

        $this->redirectToAction('cart.verifyPage');
        return false;
    }

    function runVerifyPageAction()
    {
        $this->getCart();
        $this->cart->validateForCheckout();
        if ($this->cart->errors) {
            $this->cart->adjustLines();
            $this->cart->save();
            $this->addWarnings($this->cart->errors);
            $this->redirectToAction('cart');
            return false;
        }
        $this->title = "Checkout: Verify Your Information";
        $this->setTemplate('cart/verifyPage');
    }
    
    function runCancelPaymentAction()
    {
        $this->addWarning("Payment Cancelled");
        $this->setTemplate('cart/paymentPage');
    }

    function runSubmitOrderAction()
    {
        $this->getCart();

        if (!$this->cart->processOrder()) {
            $this->addWarnings($this->cart->errors);
            $this->cart->adjustLines();
            $this->cart->save();
            if ($this->cart->lines) {
                $this->redirectToAction('cart.verifyPage');
                return false;
            }
            else {
                $this->redirectToAction('cart');
                return false;
            }
        }
        
        $this->order_id = $this->cart->order_id;
        $this->cart->save();
        $this->setCart(null);
        $this->setPostCart($this->cart);
        
        $this->redirectToAction('cart.postOrderPage');
        return false;
    }
    
    function runPostOrderPageAction()
    {
        $sess = $this->getSession();
        $id = $sess->get('after_order_cart_id');
        if (!$id) {
            $id = $sess->get('cart_id');
            if ($id) {
                $this->post_cart = mvc_Model::fetch('cart_Cart', $id);
                $this->post_cart->live = false;
                $this->post_cart->save();
                $this->setCart(null);
            }
        }
        else if ($this->post_cart = mvc_Model::fetch('cart_Cart', $id)) {
            $this->setCart(null);
            $this->post_cart->live = false;
            $this->post_cart->save();
            $sess->set('after_order_cart_id', null);
        }
        
        $this->setTemplate('cart/postOrderPage');
    }
    
    function runClearSessionAction()
    {
        $this->setCart(null);
        $this->redirectToAction('cart');
        return false;
    }
        
    function runSubmitErrorAction()
    {
        $this->error_id = $this->req('error_id');
        $this->redirectToAction('cart.errorPage');
        return false;
    }
        
    function runErrorPageAction()
    {
        $this->webmaster_email = $this->getWebmasterEmail($request);
            
        $this->user_error_message = "An error occured with the ordering system."
            ." The webmaster has been contacted regarding this error.";
    }
        
    /********************************
     UTILITY METHODS 
    ********************************/
    
    function getCart()
    {
        $this->cart = mm_getCart();
        return $this->cart;
    }

    function setCart($cart)
    {
        if (!$cart) {
            $this->getSession()->unsetVar('cart_id');
        }
        else {
            $cart->sid = session_id();
            $this->getSession()->set('cart_id', $cart);
        }
    }
    
    function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = mm_getCustomer();
        }
        return $this->_customer;
    }
    
    function setPostCart($cart)
    {
        if (!$cart) {
            $this->getSession()->unsetVar('after_order_cart_id');
        }
        else {
            $this->getSession()->set('after_order_cart_id', $cart->id);
        }
    }
    
    function getShippingMethods()
    {
        // Get default shipping method
        $default_method_id = mm_getSetting('default_shipping_method');

        // Execute SQL to retrieve active shipping methods
        $cart = $this->getCart();
        $shipping_dao = new shipping_ShippingMethodDAO;
        return $shipping_dao->getAllActive();
    }
    
    function price($price) {
        return "$" . mm_pricenumber($price);
    }
    
    function getCountryOptions() {
        $dao = new addr_Countries;
        return $dao->getAll();
    }
    
    function getModuleName() {
        return 'cart';
    }
}
