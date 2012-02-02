<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Payment Processor for Authorize.net
 *
 * You can test the live gateway with any of these credit card numbers.
 *
 * 370000000000002  American Express  
 * 6011000000000012 Discover  
 * 5424000000000015 MasterCard  
 * 4007000000027    Visa
 */
class authnet_AuthNetProcessor extends mvc_Model
{
    const LIVE_GATEWAY_URL = 'https://secure.authorize.net/gateway/transact.dll';
    const DUMMY_GATEWAY_URL = '/mm/plugins/authnet/stub_gateway.php';
    
    /**
     * Payment method object.
     * @var authnet_AuthNet
     */
    public $method;
    
    public $http_response;
    
    function __construct($method=null, $params=array())
    {
        $this->method = $method;
        parent::setPropertyValues($params);
    }
    
    function getAccountId() {
        return $this->method->account_id;
    }
    
    function getTranKey() {
        return $this->method->tran_key;
    }
    
    function getEmailReceipt() {
        return $this->method->email_receipt;
    }
    
    function getTransType() {
        return $this->method->trans_type;
    }
    
    function getTestMode() {
        return $this->method->test_mode;
    }
    
    function getGatewayUrl() {
        if ($this->test_mode == 'hard_test') {
            return mm_getConfigValue('urls.https') . self::DUMMY_GATEWAY_URL;
        }
        else {
            return self::LIVE_GATEWAY_URL;
        }
    }
    
    /**
     * Build the request string to pass to Authorize.net
     */
    function buildRequestString() {
        $req =
            'x_version=3.1'
            .'&x_delim_data=TRUE'
            .'&x_delim_char=%2C'
            .'&x_encap_char=%22'
            .'&x_login='.urlencode($this->account_id)
            .'&x_tran_key='.urlencode($this->tran_key)
            .'&x_amount='.number_format($this->amount, 2)
            .'&x_card_num='.urlencode($this->payment->cc_number)
            .'&x_exp_date='.urlencode(date('my', $this->payment->getExpirationDate()));
        
        $type = $this->trans_type;
        $req .= '&x_type='.urlencode($type);

        if( $this->address->first_name ) $req .= '&x_first_name='.urlencode($this->address->first_name);
        if( $this->address->last_name ) $req .= '&x_last_name='.urlencode($this->address->last_name);
        if( $this->address->street_address ) $req .= '&x_address='.urlencode($this->address->street_address);
        if( $this->address->city ) $req .= '&x_city='.urlencode($this->address->city);
        if( $this->address->state ) $req .= '&x_state='.urlencode($this->address->state);
        if( $this->address->zip ) $req .= '&x_zip='.urlencode($this->address->zip);
        if( $this->address->phone ) $req .= '&x_phone='.urlencode($this->address->phone);
        
        $send_receipt = $this->email_receipt;
        if( $send_receipt && $this->address->email )
        {
            $req .= '&x_email='.urlencode($this->address->email);
        }

        if( $this->test_mode ) {
            $req .= '&x_test_request=TRUE';
        }

        return $req;
    }

    function process()
    {
        $req = $this->buildRequestString();
        $poster = new mm_HttpPoster;
        
        $this->http_response = $poster->post($this->gateway_url, $req);
        $values = explode('","', 
            substr($this->http_response, 1, strlen($this->http_response)-2));
        
        $result = new authnet_AuthNetResult;
        $result->setRespValues($values);
        return $result;
    }
    
    function getAddress()
    {
        return $this->method->cart->billing;
    }
    
    function getPayment()
    {
        return $this->method->payment;
    }
    
    function getAmount()
    {
        return $this->method->cart->total;
    }
}
