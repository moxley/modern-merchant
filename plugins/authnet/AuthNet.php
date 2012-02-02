<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package authnet
 */
class authnet_AuthNet extends payment_PaymentMethod
{
    public $result = null;
    
    /**
     * @var string
     */
    public $account_id;
    /**
     * @var string
     */
    public $test_mode;
    /**
     * @var string
     */
    public $tran_key;
    /**
     * @var boolean
     */
    public $email_receipt;
    /**
     * @var string
     */
    public $trans_type;
    
    function getName() { return 'authnet'; }
    function getTitle() { return "Authorize.net AIM"; }
    
    /**
     * Start the processing of the payment.
     * 
     * This is a plugin hook that overrides the same method in
     * <tt>payment_PaymentMethod</tt>
     *
     * @return int
     * 
     * true  = Processing successful
     * false = Processing failed
     */
    function process($cart)
    {
        $this->cart = $cart;
        $processor = new authnet_AuthNetProcessor($this);
        $this->result = $processor->process();
        if ($this->result->declined()) {
            $this->addError("Card Declined: " . $this->result->getUserMessage());
        }
        else if ($this->result->hasError()) {
            $this->addError("An error occurred in processing the card: " . $this->result->getUserMessage());
        }
        
        if ($this->errors) {
            return false;
        }
        else {
            $this->is_payed = true;
            return true;
        }
    }

    /**
     * Payment result.
     *
     * This is a plugin hook that overrides the same method in
     * <tt>payment_PaymentMethod</tt>
     *
     * @return int
     *
     * 0 = No payment processing has been started
     * 1 = Payment accepted
     * 2 = Payment rejected
     * 3 = Processing is still in progress
     */
    function result()
    {
        if( $this->result == NULL ) return PAYMENT_METHOD_NOT_STARTED;
        else if ($this->result->hasError() ) return PAYMENT_METHOD_ERROR;
        else if( $this->result->passed() === true ) return PAYMENT_METHOD_PASSED;
        else if( $this->result->declined() === true ) return PAYMENT_METHOD_DECLINED;
        else return PAYMENT_METHOD_ERROR;
    }
    
    function getUserMessage()
    {
        if( $this->result == NULL ) return NULL;
        return $this->result->getUserMessage();
    }

    function passed()
    {
        if( $this->result == NULL ) return FALSE;
        return $this->result->passed();
    }
    
    function declined()
    {
        if( $this->result == NULL ) return FALSE;
        return $this->result->declined();
    }
    
    function hasError()
    {
        if( $this->result == NULL ) return FALSE;
        return $this->result->hasError();
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
        $cc_number = $input['authnet_cc_number'];
        $last4 = substr( $cc_number, -4 );
        $authnet_cc_number = str_repeat('x', strlen($cc_number)-4) . $last4;
        $exp_date = $input['authnet_cc_exp_month'].'/'.$input['authnet_cc_exp_year'];

        ob_start();
?>
      <?php print h($this->getPublicTitle()) ?><br>
      Card Number: <?php print h($authnet_cc_number); ?><br>
      Expiration: <?php print h($exp_date) ?><br>
<?php
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * Indicates that this payment method plugin uses synchronous
     * processing (while the customer waits for a response),
     * as opposed to asynchronous processing that happens independently of
     * the customer's page requests.
     *
     * This is a plugin hook that overrides the same method in
     * <tt>payment_PaymentMethod</tt>
     */
    function synchronousProcessing()
    {
        return TRUE;
    }

    function install()
    {
        $this->public_title  = "Credit Card";
        $this->active        = true;
        $this->account_id    = "authnetuser";
        $this->test_mode     = 'hard_test';
        $this->tran_key      = 'abc123xyzHHEELLLLOO';
        $this->email_receipt = false;
        $this->trans_type    = "AUTH_CAPTURE";
        return parent::install();
    }

    /**
     * @return payment_CreditCardPayment
     */
    function getPayment()
    {
        $p = parent::getPayment();
        if (!$p) {
            $p = new payment_CreditCardPayment;
        }
        else if (is_array($p)) {
            $p = new payment_CreditCardPayment($p);
        }
        return $p;
    }
}
