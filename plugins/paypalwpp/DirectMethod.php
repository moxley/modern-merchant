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
class paypalwpp_DirectMethod extends paypalwpp_BaseMethod
{
    public $public_title = "Credit Card";
    private $_payment;
    function getName() { return 'paypalwpp_direct'; }
    function getTitle() { return "PayPal WPP Direct Checkout"; }
    
    function getCardTypes()
    {
        $card_types = array(
            'Visa'       => 'Visa',
            'MasterCard' => 'MasterCard',
            'AmEx'       => "American Express",
            'Discover'   => "Discover"
        );
        return $card_types;
    }
    
    public function callService($methodName, $paymentParams)
    {
        // Set up your API credentials, PayPal end point, and API version.
        $api_url = "https://api-3t.paypal.com/nvp";
        if($this->environment === "sandbox" || $this->environment === "beta-sandbox") {
            // PayPal's sandbox is broken
            //$api_url = "https://api-3t.{$this->environment}.paypal.com/nvp";
            
            // Call our own paypal-nvp.php script, which always, naively returns Success
            $api_url = mm_getConfigValue('urls.https') . mm_getConfigValue('urls.mm_root') . 'webscripts/paypal-nvp.php';
        }

        $e = $this->environment;
        $params = array(
            'METHOD'        => $methodName,
            'VERSION'       => '51.0',
            'USER'          => $this->$e->api_username,
            'PWD'           => $this->$e->api_password,
            'SIGNATURE'     => $this->$e->api_signature,
            'API_Endpoint'  => $api_url
        );
        $params = array_merge($params, $paymentParams);
        $logParams = $params;
        $logParams['ACCT'] = payment_PaymentMethod::maskCCNumber($params['ACCT']);
        $logParams['CVV2'] = 'xxx';
        mm_log(__CLASS__ . '#' . __FUNCTION__ . ': params: ', var_export($logParams, true));

        mm_log(__CLASS__ . '#' . __FUNCTION__ . ': URL: ', $api_url);
        $poster = new mm_HttpPoster;
        $httpResponse = $poster->post($api_url, $params, array('certificate' => dirname(__FILE__) . '/paypal.crt'));
        mm_log(__CLASS__ . '#' . __FUNCTION__ . ': paypal raw response: ', $httpResponse);

        if (!$httpResponse) {
            trigger_error("$methodName failed: {$poster->error_message}", E_USER_WARNING);
            $this->addError("Failed to contact payment gateway");
            return false;
        }

        // Extract the response details.
        $httpResponseAr = explode("&", $httpResponse);

        $httpParsedResponseAr = array();
        foreach ($httpResponseAr as $i => $value) {
            $tmpAr = explode("=", $value);
            $key = isset($tmpAr[0]) ? urldecode($tmpAr[0]) : null;
            if ($key) {
                $value = isset($tmpAr[1]) ? urldecode($tmpAr[1]) : null;
                $httpParsedResponseAr[$key] = $value;
            }
        }

        if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
            trigger_error("Invalid HTTP Response for POST request($nvpreq) to $api_url.\n", E_USER_WARNING);
            return false;
        }

        return $httpParsedResponseAr;
    }

    /**
     * Start the processing of the payment.
     * 
     * This is a plugin hook that overrides the same method in
     * <tt>payment_PaymentMethod</tt>
     *
     * @return int
     * 
     * 0 = Processing started successfully
     * 1 = An error occurred while trying to start payment processing
     */
    public function process($cart)
    {
        $this->cart = $cart;
        $this->payment = $cart->payment;
        $this->cart->payment_method = $this;
        
        if ($errors = $this->cart->validateForOrder()) {
            $this->addErrors($errors);
            return false;
        }
        
        if (!$this->environment) throw new Exception("paypalwpp: No environment set");
        $environment = $this->environment;
        if (!$this->api_username || !$this->$environment->api_username) {
            $this->addError("paypalwpp: API Username is not set");
            return false;
        }
        if (!$this->api_password || !$this->$environment->api_password) {
            $this->addError("paypalwpp: API Password is not set");
            return false;
        }
        if (!$this->api_signature || !$this->$environment->api_signature) {
            $this->addError("paypalwpp: API Signature is not set");
            return false;
        }
        
        $expDateYear = $this->payment->cc_exp_year;
        $expDateMonth = $this->payment->cc_exp_month;
        $padDateMonth = urlencode(str_pad($expDateMonth, 2, '0', STR_PAD_LEFT));
        $expDate = "{$padDateMonth}{$expDateYear}";

        // Test params:
        // Card Type: Visa
        // Card Number: 4242424242424242
        // CVV2: 111
        
        // Payment parameters
        $params = array(
            'PAYMENTACTION'  => 'Sale', // 'Authorization' or 'Sale'
            'AMT'            => $this->cart->total, // Sale amount
            'CREDITCARDTYPE' => $this->payment->cc_type,
            'ACCT'           => $this->payment->cc_number, // Credit Card Number
            'EXPDATE'        => $expDate,
            'CVV2'           => $this->payment->cc_cvv,
            'FIRSTNAME'      => $this->cart->billing->first_name,
            'LASTNAME'       => $this->cart->billing->last_name,
            'STREET'         => $this->cart->billing->address_1,
            'CITY'           => $this->cart->billing->city,
            'STATE'          => $this->cart->billing->state,
            'ZIP'            => $this->cart->billing->zip,
            'COUNTRYCODE'    => $this->cart->billing->country,
            'CURRENCYCODE'   => 'USD'
        );
        $res = $this->callService('DoDirectPayment', $params);
        if (!$res) {
            return false;
        }
        
        $this->response = $res;

        if ($res['ACK'] === 'Success') {
            //Array
            //(
            //    [TIMESTAMP] => 2012-01-04T15:13:13Z
            //    [CORRELATIONID] => c20fe6e5b8684
            //    [ACK] => Success
            //    [VERSION] => 51.0
            //    [BUILD] => 2278658
            //    [AMT] => 2.00
            //    [CURRENCYCODE] => USD
            //    [AVSCODE] => X
            //    [CVV2MATCH] => M
            //    [TRANSACTIONID] => 94B260357L881872X
            //)
            $this->is_payed = true;
        }
        else {
            //Array
            //(
            //    [TIMESTAMP] => 2012-01-04T15:05:00Z
            //    [CORRELATIONID] => 5a1a55827d6a9
            //    [ACK] => Failure
            //    [VERSION] => 51.0
            //    [BUILD] => 2278658
            //    [L_ERRORCODE0] => 10001
            //    [L_SHORTMESSAGE0] => Internal Error
            //    [L_LONGMESSAGE0] => The transaction could not be loaded
            //    [L_SEVERITYCODE0] => Error
            //    [AMT] => 2.00
            //    [CURRENCYCODE] => USD
            //)
            $this->is_payed = false;
            $this->addError($this->response['L_SHORTMESSAGE0'] . ': ' . $this->response['L_LONGMESSAGE0']);
        }

        return $this->is_payed;
    }
    
    function createRequest()
    {
        $paypal = $this->api->selectOperation('DoDirectPayment');

        $paypal->setParams(
            'Sale',
            $this->getPaypalPaymentDetails(),
            $this->getPaypalCreditCardDetails(),
            realip(),
            $this->getSessionId());

        $currency_id = 'USD';
        $this->populatePaypalLines($paypal);

        return $paypal;
    }

    /**
     * Get HTML to verify the payment details to the customer.
     *
     * This is a plugin hook that overrides the same method in
     * <tt>payment_PaymentMethod</tt>
     */
    function getVerifyHtml($controller, $input)
    {
        // Hide most of credit card number
        $cc_number = $input['cc_number'];
        $last4 = substr( $cc_number, -4 );
        $paypalwpp_cc_number = str_repeat('x', strlen($cc_number)-4) . $last4;
        $exp_date = $input['cc_exp_month'].'/'.$input['cc_exp_year'];

        $out_public_title = h($this->getPublicTitle());
        $out_cc_number = h($paypalwpp_cc_number);
        $out_exp_date = h($exp_date);
        $contents =<<<END_DISPLAY
      $out_public_title<br />
      Card Number: $out_cc_number<br>
      Expiration: $out_exp_date<br>
END_DISPLAY;
        
        return $contents;
    }
    
    function validatePayment()
    {
        parent::validatePayment();
        
        if (!$this->payment) {
            $this->addError("Payment details haven't been set");
        }
        else if (!is_object($this->payment)) {
            $this->addError("Payment is not an object");
        }
        return $this->errors;
    }
    
    function setPayment($payment)
    {
        if (!$payment) {
            $this->_payment = null;
        }
        else {
            $this->_payment = new payment_CreditCardPayment($payment);
        }
    }
    
    function getPayment()
    {
        $payment = parent::getPayment(); // An array, from the cart
        $this->setPayment($payment);
        return $this->_payment;
    }
}
