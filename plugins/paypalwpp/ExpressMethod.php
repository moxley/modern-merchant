<?php
/**
 * @package paypalwpp
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypalwpp_ExpressMethod extends paypalwpp_BaseMethod
{
    public $public_title = "PayPal";

    function getName() { return 'paypalwpp_express'; }
    function getTitle() { return "PayPal WPP Express Checkout"; }
    
    /**
     * Get the Paypal URL that the user will be redirected to from Modern Merchant.
     */
    public function getExpressCheckoutUrl($token)
    {
        if ($this->environment == 'live') {
            $host = "www.paypal.com";
        }
        else {
            $host = "www.sandbox.paypal.com";
        }
        $url = "https://$host/cgi-bin/webscr?" .
            'cmd=_express-checkout&' .
            'token=' . urlencode($token);
        return $url;
    }

    public function setAsSelectedMethod()
    {
        $cart = mm_getCart();
        $cart->payment_method_id = $this->id;
    }

    function prepareAPI() {
        $this->api = new WebsitePaymentsPro;
        $this->api->prepare(
            $this->{$this->environment}->api_username,
            $this->{$this->environment}->api_password,
            $this->{$this->environment}->api_signature,
            '',
            $this->environment
        );
    }

    public function getNewToken($cart)
    {
        $environment = $this->environment;
        if (!$environment) throw new Exception("paypalwpp: No environment set");
        $this->prepareAPI();
        $this->paypal = $this->api->selectOperation('SetExpressCheckout');

        $return_url = urlPathToFullUrl('/?a=cart.returnFromExpress', 'https');
        $cancel_url = urlPathToFullUrl('/?a=cart.cancelExpress', 'https');
        $payment_action = 'Sale'; // or Order

        $this->paypal->setParams($cart->total, $return_url, $cancel_url, $payment_action);
        
        $this->paypal->execute();
        
        $success = $this->getPaypalSuccess();
        
        if ($success)
        {
            $response = $this->paypal->getAPIResponse();
            $token = $response->Token;
            $sess = mm_getSession();
            $sess->set('paypalwpp.token', $token);
            
            return $token;
        }
        else {
            return null;
        }
    }
    
    /**
     * Get customer's checkout details from Paypal.
     *
     * @return boolean True if success
     */
    function getExpressCheckoutDetails($token)
    {
        $this->prepareAPI();
        $this->paypal = $this->api->selectOperation('GetExpressCheckoutDetails');
        $this->paypal->setParams($token);

        $this->paypal->execute();
        
        if ($this->getPaypalSuccess()) {
            return $this->response->GetExpressCheckoutDetailsResponseDetails;
        }
        else {
            return null;
        }
    }
    
    /**
     * Send the Express payment to Paypal.
     */
    function doExpressCheckoutPayment($token, $cart)
    {
        $this->cart = $cart;
        $this->prepareAPI();
        $this->paypal = $this->api->selectOperation('DoExpressCheckoutPayment');

        $payment_action = 'Sale'; // or Order
        
        // TODO: Set payer id

        $payment_details = $this->getPaypalPaymentDetails();
        $this->paypal->setParams(
            $payment_action,
            $token,
            $this->payer_id,
            $payment_details);
            
        $this->populatePaypalLines($this->paypal);

        $this->paypal->execute();
        
        // Just because success() returns true doesn't mean the payment was successful
        
        return $this->getPaypalSuccess();
    }
    
    public function loadBillingInfo($token)
    {
        $sess = mm_getSession();
        $saved_token = $sess->get('paypalwpp.token');
        if (!$saved_token) {
            $this->addError("No saved token");
            return false;
        }
        else if (!$token) {
            $this->addError("Missing 'token' parameter");
            return false;
        }
        else if ($token != $saved_token) {
            $this->addError("Token doesn't match original");
            return false;
        }

        $details = $this->getExpressCheckoutDetails($token);
        if (!$details) {
            return false;
        }
        $this->setOrderValues($details);

        return $details;
    }
    
    public function setOrderValues($details)
    {
        $payer = $details->PayerInfo;

        $payer_id = $payer->PayerID;
        $pname = $payer->PayerName;
        $addr = $payer->Address;

        $cart = mm_getCart();
        $cart->shipping->first_name = $pname->FirstName;
        $cart->shipping->last_name  = $pname->LastName;
        $cart->shipping->business   = $payer->PayerBusiness;
        $cart->shipping->address_1  = $addr->Street1;
        $cart->shipping->address_2  = $addr->Street2;
        $cart->shipping->city       = $addr->CityName;
        $cart->shipping->state      = $addr->StateOrProvince;
        $cart->shipping->country    = $addr->Country;
        $cart->shipping->zip        = $addr->PostalCode;
        if (!$cart->shipping->email) {
            $cart->shipping->email = $payer->Payer;
        }
        
        $cart->billing = new paypalwpp_Billing;
        $cart->billing->email = $payer->Payer;

        $cart->payment['payer_id'] = $payer_id;
        $cart->save();
    }
    
    function getPaypalSuccess()
    {
        if ($this->paypal->success())
        {
            $this->response = $this->paypal->getAPIResponse();
            if ($this->response->Ack == 'Success') {
                return true;
            }
            else {
                $this->addErrorsFromException($this->response);
                return false;
            }
        } 
        else
        {
            $ex = $this->paypal->getAPIException();
            $this->addErrorsFromException($ex);
            return false;
        } 
    }

    /**
     * Start the processing of the payment.
     * 
     * This is a plugin hook that overrides the same method in
     * <tt>payment_PaymentMethod</tt>
     *
     * @return int
     * 
     * 0 = Processing started sucessfully
     * 1 = An error occured while trying to start payment processing
     */
    public function process($cart)
    {
        $sess = mm_getSession();
        $token = $sess->get('paypalwpp.token');
        $this->is_payed = $this->doExpressCheckoutPayment($token, $cart);
        return $this->is_payed;
    }

    function renderPaymentForm($controller)
    {
        // Do nothing
    }
    
    function getPayerId()
    {
        return $this->cart->payment['payer_id'];
    }
}
