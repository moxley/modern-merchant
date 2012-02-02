<?php
/**
 * @package paypalwpp
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package paypalwpp
 */
class paypalwpp_BaseMethod extends payment_PaymentMethod
{
    const TEST_API_USERNAME  = "merchant_api1.moxleydata.com";
    const TEST_API_PASSWORD  = "Z2JBCULBJANG3Z7V";
    const TEST_API_SIGNATURE = "ABO31d4TlYVf7In5qZ.rZr.bte7sACgGeN5yEiETFGyAEGWHNlwg-X8q";

    public $caller;
    public $response;
    public $certificates;
    
    public $environment;
    public $currency_id;
    
    private $_api;
    
    /**
     * @var paypalwpp_Environment
     */
    public $live;
    /**
     * @var paypalwpp_Environment
     */
    public $sandbox;
    
    function __construct($values=null)
    {
        parent::__construct($values);
        $this->live = new paypalwpp_Environment;
        $this->sandbox = new paypalwpp_Environment;
        $this->environment = "sandbox";
        $this->currency_id = 'USD';
        require_once dirname(__FILE__) . '/phppaypalpro/paypal_base.php';
    }
    
    /**
     * Plugin hook: Overrides same method in <tt>payment_PaymentMethod</tt>
     */
    function preProcessSettingsForm($controller)
    {
        $path = mm_getConfigValue('filepaths.plugins').'/paypalwpp/certs/*';
        $this->certificates = array_map('basename', glob($path));
    }
    
    function getSettingsFormHtml($controller)
    {
        ob_start();
        $controller->render('paypalwpp/edit');
        return ob_get_clean();
    }
    
    /**
     * Plugin hook: Overrides same method in <tt>payment_PaymentMethod</tt> 
     */
    function postProcessSettingsForm($controller)
    {
    }
    
    function passed()
    {
        return $this->result() == PAYMENT_METHOD_PASSED;
    }

    function declined()
    {
        return $this->result() == PAYMENT_METHOD_DECLINED;
    }

    function hasError()
    {
        return $this->errors ? true : false;
    }

    function getUserMessage()
    {
        if ($this->hasError()) {
            return $this->response->getMessage();
        }
        else {
            $errors = $this->response->getErrors();
            if (is_array($errors)) $error = $errors[0];
            else $error = $errors;
            return $error->getLongMessage();
        }
    }
    
    public function result()
    {
        if (!$this->response) return PAYMENT_METHOD_NOT_STARTED;
        if (Services_PayPal::isError($this->response)) {
            return PAYMENT_METHOD_ERROR;
        }
        $ack = $this->response->getAck();
        if ($ack == 'Pending') {
            return PAYMENT_METHOD_PENDING;
        }
        if ($ack == "Failure") {
            return PAYMENT_METHOD_DECLINED;
        }
        else if ($ack == "Success") {
            return PAYMENT_METHOD_PASSED;
        }
        else {
            return null;
        }
    }

    protected function getCaller()
    {
        $this->caller = paypalwpp_ServicesUtil::getCaller();
        return $this->caller;
    }

    protected function isLiveEnvironment()
    {
        return $this->environment == 'live';
    }

    function getApi()
    {
        if (!$this->_api) {
            $this->_api = new WebsitePaymentsPro();
        }
        return $this->_api;
    }
    
    /**
     * Get a populated "ShipToAddress" Paypal object
     */
    function getPaypalShippingAddress()
    {
        return $this->getPaypalAddress('shipping');
    }
    
    /**
     * Get a populated Paypal billing address object ("Payer's billing address information")
     */
    function getPaypalBillingAddress()
    {
        return $this->getPaypalAddress('billing');
    }
    
    function getPaypalAddress($type)
    {
        return PayPalTypes::AddressType(
            $this->cart->$type->name,
            $this->cart->$type->address_1,
            $this->cart->$type->address_2,
            $this->cart->$type->city,
            $this->cart->$type->state,
            $this->cart->$type->zip,
            $this->cart->$type->country,
            $this->cart->$type->phone);
    }
    
    function getPaypalPersonName()
    {
        return PayPalTypes::PersonNameType(
            $this->cart->billing->salutation,
            $this->cart->billing->first_name,
            $this->cart->billing->middle_name,
            $this->cart->billing->last_name);
    }
    
    function getPaypalPayerInfo()
    {
        return PayPalTypes::PayerInfoType(
            $this->cart->billing->email,      // Payer: "Email address of payer"
            '',                               // PayerID: "Unique encrypted PayPal customer account number"
            'verified',                       // PayerStatus
            $this->getPaypalPersonName(),     // PayerName
            $this->cart->billing->country,    // PayerCountry
            $this->cart->billing->company,    // PayerBusiness
            $this->getPaypalBillingAddress(), // Address
            $this->cart->billing->phone       // ContactPhone
        );
    }
    
    function getPaypalCreditCardDetails()
    {
        return PayPalTypes::CreditCardDetailsType(
            $this->payment->cc_type,      // CreditCardType
            $this->payment->cc_number,    // CreditCardNumber
            $this->payment->cc_exp_month, // ExpMonth
            $this->payment->cc_exp_year,  // ExpYear
            $this->getPaypalPayerInfo(),  // CardOwner "Details about the owner of the credit card"
            $this->payment->cc_cvv);      // CVV2
    }
    
    function getPaypalPaymentDetails()
    {
        return PayPalTypes::PaymentDetailsType(
            $this->cart->total,                // OrderTotal
            $this->cart->sub_total,            // ItemTotal
            $this->cart->shipping_total,       // ShippingTotal
            $this->cart->handling_total,       // HandlingTotal
            $this->cart->tax_total,            // TaxTotal
            "Purchase from " . mm_getSetting('site.name'), // OrderDescription
            $this->cart->id,                   // Custom: "A free-form field for your own use." (up to 256 bytes)
            $this->cart->order_id,             // InvoiceID: "Your own invoice or tracking number, as set by you in the InvoiceID element of SetExpressCheckoutRequest"
            'modernmerchant.org',              // ButtonSource (optional): "An identification code for use by third-party applications to identify transactions."
            '',                                // NotifyURL (optional): "Your URL for receiving Instance Payment Notification (IPN) about this transaction."
            $this->getPaypalShippingAddress(), // ShipToAddress
            array(),                           // PaymentDetailsItem (optional): "Details about each individual item included in the order"
            'USD'                              // CurrencyID: (Not part of specifications)
        );
    }
    
    function getSessionId()
    {
        $sess = mm_getSession();
        $unique_session_id = $sess->sid;
        return $unique_session_id;
    }
    
    function populatePaypalLines($paypal)
    {
        foreach ($this->cart->lines as $line) {
            $paypal->addPaymentItem($line->description, 'Item Number ' . $line->sku,
                $line->qty,
                $line->tax,
                $line->price,
                $this->currency_id);
        }
    }

    function addErrorsFromException($ex)
    {
        if ($ex instanceof SoapFault) {
            $this->addError($ex->getMessage());
        }
        else if (!is_array($ex->Errors)) {
            $this->addError($ex->Errors->LongMessage);
        }
        else {
            foreach ($ex->Errors as $error) {
                $this->addError($error->LongMessage);
            }
        }
    }
    
    function install()
    {
        if (!$this->checkPrerequisites()) {
            return false;
        }
        
        $this->environment = 'sandbox';
        $this->sandbox->api_username = self::TEST_API_USERNAME;
        $this->sandbox->api_password = self::TEST_API_PASSWORD;
        $this->sandbox->api_signature = self::TEST_API_SIGNATURE;
        return parent::install();
    }
    
    function activate()
    {
        if (!$this->checkPrerequisites()) {
            return false;
        } else {
            return parent::activate();
        }
    }
    
    function checkPrerequisites()
    {
        if (!defined('SOAP_1_1')) {
            $this->addError('PayPal WPP: Cannot activate: Missing the required "SOAP" PHP extension');
            return false;
        } else {
            return true;
        }
    }
    
    function getApiUsername()
    {
        if (!$this->environment || !$this->{$this->environment}) return null;
        $env = $this->{$this->environment};
        return $env->api_username;
    }
    
    function getApiPassword()
    {
        if (!$this->environment || !$this->{$this->environment}) return null;
        $env = $this->{$this->environment};
        return $env->api_password;
    }

    function getApiSignature()
    {
        if (!$this->environment || !$this->{$this->environment}) return null;
        $env = $this->{$this->environment};
        return $env->api_signature;
    }
}
