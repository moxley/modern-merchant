<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package paypal
 */
class paypal_PayPal extends payment_PaymentMethod
{
    /**
     * @var boolean
     */
    public $test_mode;
    /**
     * @var string
     */
    public $account_email;
    /**
     * @var string
     */
    public $ipn_notify;
    /**
     * @var string
     */
    public $currency;
    
    /**
     * @var mm_HttpPoster
     */
    public $_poster;
    
    protected $_cart;
    public $trans;
    
    public $ipn_ignored = false;
    public $sent_ipn_error_email = false;
    public $sent_ipn_order_notice_email = false;

    public $user_message;
    public $public_title = "PayPal";
    public $currency_types = array('USD', 'CAD', 'EUR', 'GBP', 'JPY');
    const LIVE_GATEWAY_URL = 'https://www.paypal.com/cgi-bin/webscr';
    
    /**
     * Stores the current error number.
     *
     * 0 - No error
     * 1 - IPN-specific error
     * 2 - Internal error 
     * 
     * @var int The current error number
     */
    private $errorno = 0;
    
    function getName() { return 'paypal'; }
    function getTitle() { return "PayPal IPN"; }
    
    /**
     * Render the HTML "submit" button for completing the order.
     */
    function renderSubmitOrderButton($controller)
    {
        $sitename = mm_getSetting('site.name');
        
        $https = mm_getConfigValue('urls.https');
        $thankyou = $https . mm_getConfigValue('urls.cart.thank_you');
        $cancel_return = $https . mm_getConfigValue('urls.cart.cancel_payment');
        
        $this->_cart = mm_getCart();
        $dao = new paypal_TransactionDAO;
        $this->trans = $dao->fetchByCartId($this->_cart->id);
        if (!$this->trans) {
            $this->trans = new paypal_TransactionDO;
        }
        $this->trans->populateFromCartAndSession($this->_cart, mm_getSession());
        $this->trans->save();

        $sub_total = str_replace('$', '', $this->_cart->sub_total);
        $total = str_replace('$', '', $this->_cart->total);
        $ship_total = str_replace('$', '', $this->_cart->shipping_total);
        
        $req = $this->cartToIpnRequest($this->_cart);
        $button_params = array();
        foreach ($req as $k=>$v) {
            if (startswith($k, 'submit')) continue;
            $button_params[$k] = $v;
        }
        mm_log("paypal_PayPal#renderSubmitOrderButton(): button params: "
            . var_export($button_params, true) . "\n"
            . "cart: " . $this->_cart->__toString());
?>
<form action="<?php ph($this->getCheckoutUrl()) ?>" method="post">
    <?php foreach ($button_params as $k=>$v): ?>
    <input type="hidden" name="<?php ph($k) ?>" value="<?php ph($v) ?>" />
    <?php endforeach ?>
    <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
</form>
<?php
    }
    
    function renderPaymentForm($controller) {
        // Empty
    }

    /**
    * Handle user's return from external payment site.
    *
    * Override this method to handle situations where the user is
    * returning from an external payment web site. Some services
    * have a feature that lets the originating web site check the
    * status of the payment, and this method could perform that check.
    */
    function returnFromPayment($controller, $input)
    {
        /**
         * TODO: Check the 'payment_status' variable
         *       Should be one of 'Completed', 'Pending', or 'Failed'
         */
    }

    function instantProcessing()
    {
        return FALSE;
    }
    
    function getCheckoutUrl()
    {
        if ($this->test_mode)
        {
            $path = mm_getConfigValue('urls.plugins') . 'paypal/stub_checkout.php';
            return urlPathToFullUrl($path);
        }
        else
        {
            return self::LIVE_GATEWAY_URL;
        }
    }
    
    function getIpnRespondUrl()
    {
        if ($this->test_mode) {
            $path = mm_getConfigValue('urls.plugins') . 'paypal/stub_ipn_server.php';
            $url = mm_getConfigValue('urls.https') . $path;
            return $url;
        }
        else {
            return self::LIVE_GATEWAY_URL;
        }
    }
    
    function getIpnClientUrl()
    {
        $path = mm_getConfigValue('urls.plugins') . 'paypal/ipn.php';
        return urlPathToFullUrl($path, 'https');
    }
    
    /**
    * PayPal Instant Payment Notification handler
    *
    * This handler is called by mm/plugins/paypal/webscripts/ipn.php to
    * record the results of a PayPal transaction for the paypal module
    * to handle.
    *
    * Not only does it handle payments, it handles reversals and refunds as well.
    *
    * A typical payment sequence goes like this:
    *
    * 1. After selecting 'PayPal' for payment type and continuing to the verify page, the customer
    *    clicks on 'Buy Now'.
    * 2. The customer completes the payment at the PayPal.com site.
    * 3. The PayPal "sending" IPN server POSTs to this script
    * 4. This script records the POST to the database and POSTs a modified copy of the request as a new request
    *    to the "receiving" IPN server
    * 5. The customer returns to the primary site with a 'submitOrder' GET request
    * 6. The 'submitOrder' action calls the PayPal module class, which in turn checks the database for the IPN post 
    *    and gets the result of the payment transaction.
    * 7. The 'submitOrder' action either shows the 'Thank You' page, or redirects the customer back to the payment 
    *    page with a notice message indicating payment failure, cancellation, or error.
    */
    function handleIpn($input)
    {
        $this->_ipn_request = $input;
        $this->_cart = null;
        $this->trans = null;
        
        try {
            mm_log("PayPal::handleIpn()");

            /*
             * Extract HTTP POST values and also create a new HTTP POST data string and
             * reply back to the IPN server, but with the 'cmd' parameter appended to the response
             * as required by the PayPal IPN specifications.
             */
            $req = $this->rebuildIpnRequestAsString($this->_ipn_request);
            $resp_data = $req . 'cmd=_notify-validate';

            /*
             * Respond to the IPN and receive a confirmation message
             * Response content should be either "VERIFIED" or "INVALID"
             */
            $this->ipn_response = $this->sendPost($this->getIpnRespondUrl(), $resp_data);
            
            // Handle un-verified response from the IPN server
            if ($this->ipn_response != 'VERIFIED') {
                throw new mvc_BusinessException("PayPal IPN server didn't verify the transaction. Here is the server response:\n-------\n{$this->ipn_response}\n-------");
            }
            
            // Require the 'custom' parameter, which holds the order_id
            $custom = gv($input, 'custom');
            if (!$custom) throw new paypal_IpnNoProcessException("'custom' parameter not specified");

            // Fetch the IPN transaction record
            // Transaction should have been created when the 'submit' button was rendered
            // See renderSubmitOrderButton()
            $cart_id = gv($input, 'custom');
            $this->_cart = mvc_Model::fetch('cart_Cart', $cart_id);
            $trans_dao = new paypal_TransactionDAO;
            $this->trans = $trans_dao->fetchByCartId($cart_id);

            if (!$this->_cart) throw new mvc_BusinessException("Cart not found for given cart_id ($cart_id)");
            if ($this->_cart->payment_method_id != $this->id) {
                mm_log("paypal_PayPal#handleIpn(): Payment method on the cart (payment_method_id={$this->_cart->payment_method_id}) does not match the 'paypal' payment method (id={$this->id}). This is not a regular PayPal order. Aborting IPN.");
                return;
            }
            if (!$this->trans) throw new mvc_BusinessException("Transaction not found for given cart_id ($cart_id)");
            if (!gv($input, 'txn_id')) throw new mvc_BusinessException("'txn_id' parameter not specified");
            if (!$this->trans->txn_id) {
                $this->trans->txn_id = $input['txn_id'];
            }
            if ($input['txn_id'] != $this->trans->txn_id) throw new mvc_BusinessException("Given 'txn_id' value ({$input['txn_id']}) does not match expected txn_id ({$this->trans->txn_id})");
            $this->checkReceiverEmailForIpn();

            $this->trans->status = gv($input, 'payment_status');
            // Statuses: 'Canceled_Reversal','Completed','Denied','Failed','Pending','Refunded','Reversed'
            if (!in_array($this->trans->status, array('Completed', 'Pending'))) {
                $this->trans->save();
                $this->notifyChangeStatus($this->trans);
                return TRUE;
            }
            
            if ($this->_cart->order_id && $this->_cart->order->payed) {
                throw new paypal_IpnNoProcessException("Order already processed");
            }

            $this->checkMcGrossForIpn();

            /*
             * Create or finish an order
             */
            $payment_only = false;
            mm_log("\$this->trans->status", $this->trans->status);
            if ($this->_cart->order) {
                $payment_only = true;
                mm_log("Calling finishUnpaidOrderAsPayed().");
                $order = $this->_cart->finishUnpaidOrderAsPayed();
            } else {
                $this->_cart->validateLines();
                $this->addErrors($this->_cart->errors);
                $this->_cart->payment_method = $this;
                if ($this->trans->status == 'Pending') {
                    mm_log("Calling createOrderNotPayed()");
                    $order = $this->_cart->createOrderNotPayed();
                } else {
                    mm_log("Calling processOrder()");
                    $order = $this->_cart->processOrder(true);
                }
            }
            
            $this->trans->order_id = $this->_cart->order_id;
            if (!$this->trans->save()) {
                throw new Exception("Failed to save IPN transaction");
            }
            if (!$this->_cart->save()) {
                throw new mvc_BusinessException("Failed to save cart: " . implode(', ', $this->_cart->errors));
            }

            /*
             * Save notification to paypal ipn queue
             */
            $this->_cart->save();
            $this->trans->populateFromCart($this->_cart);
            $this->trans->postdata = $req;
            $trans_dao = new paypal_TransactionDAO;
            $trans_dao->update($this->trans);
            
            $message = "Ref: Order ID # {$this->_cart->order->id}\n"
                . "\nA PayPal Instant Payment Notification has successfully been submitted"
                . " for a web site order.";
            if ($this->errors) {
                mm_log(implode("\n", $this->errors));
                $message .= "\n\nWARNING: This order has one or more errors associated with it. Here are the errors:\n";
                foreach ($this->errors as $error) {
                    $message .= "- $error\n";
                }
                $message .= "\n";
            }
            else {
                $message .= ' ';
            }
            if ($payment_only) {
                $message .= "This IPN is for an existing order that was waiting for payment."
                    . " You should have received a payment notification email from the Modern"
                    . " Merchant at the same time as this email.";
            } else if (!$order->payed) {
                $message .= "This order is pending payment from the customer via PayPal. When PayPal"
                    . " deposits the payment to your account, you should receive an email from Modern Merchant"
                    . " that confirms that payment has been recorded with the order.";
            } else {
                $message .= "If you do not receive an order notification email, please check the Modern Merchant"
                    . " administrator's Sales area under 'Orders' to find the order.";
            }
            $to = mm_getSetting('sales.notify');
            $subject = 'PayPal Instant Payment Notification';
            $from = mm_getSetting('site.noreply');
            
            $this->sent_ipn_order_notice_email = $this->mail($to, $subject, $message, "From: $from");
            
            mm_log("Finished creating order from IPN");
            return TRUE;
        }
        catch (Exception $e) {
            mm_log(mm_exceptionToString($e));
            
            if ($e instanceof paypal_IpnNoProcessException) {
                $this->ipn_ignored = true;
                mm_log("Ignoring IPN: " . $e->getMessage());
                return true;
            }
            else if ($e instanceof mvc_BusinessException) {
                $this->addError($e->getMessage());
            }
            else {
                $this->addError("An exception occurred: " . $e->getMessage());
            }
            $this->ipn_mailErrors();
            mm_log(implode("\n", $this->errors));
            return FALSE;
        }
    }
    
    function notifyChangeStatus($trans)
    {
        mm_log("PayPal IPN Change Status: {$trans->status}");
        
        $request_dump = var_export($this->_ipn_request, true);
        
        $message = "PayPal has set a new status for this transaction: " . $trans->status . "\n"
            . "\n"
            . "Additional information:\n"
            . "  cart ID: $trans->cart_id\n"
            . "  order ID: $trans->order_id\n"
            . "  IPN data: $request_dump\n";
        
        $from = mm_getSetting('site.noreply');
        $to_orders = mm_getSetting('orders.notification');
        $webmaster = mm_getSetting('webmaster.notification');
        $to = $to_orders . ",\r\n\t" . $webmaster;
        // Remove duplicate addresses
        $to = implode(",\r\n\t", array_unique(preg_split('/,\s*/', $to)));
        $subject = "PayPal IPN Status Change";
        
        return $this->mail($to, $subject, $message, "From: $from");
    }
    
    function checkReceiverEmailForIpn()
    {
        // Get the account email address
        if (!$this->account_email) throw new mvc_BusinessException("Missing account_email setting in PayPal user settings.");

        // Match receiver_email value with account's actual email address
        if (gv($this->_ipn_request, 'receiver_email') != $this->account_email) {
            throw new mvc_BusinessException("receiver_email ({$this->_ipn_request['receiver_email']}) " .
                    "doesn't match your PayPal account email " .
                    "({$this->account_email})");
        }
    }
    
    function checkMcGrossForIpn()
    {
        // Check that the price, mc_gross, and currency, 
        // mc_currency, are correct for the item, item_name or item_number.
        if ($this->_cart->total != $this->_ipn_request['mc_gross']) {
            throw new mvc_BusinessException("Given total " . $this->_cart->total . " doesn't match recorded total {$this->_ipn_request['mc_gross']}.");
        }
    }
    
    function saveOrderForIpn()
    {
        $this->_cart->validateLines();
        $this->addErrors($this->_cart->errors);
        if (!$this->_cart->order_id) {
            $this->_cart->payment_method = $this;
            $order = $this->_cart->createOrderNotPayed();
        }

        $this->trans->order_id = $this->_cart->order_id;
        if (!$this->trans->save()) {
            throw new Exception("Failed to save IPN transaction");
        }

        if (!$this->_cart->save()) {
            throw new mvc_BusinessException("Failed to save cart: " . implode(', ', $this->_cart->errors));
        }
    }
    
    function rebuildIpnRequestAsString($input)
    {
        $req = '';
        foreach( $input as $key=>$value ) {
            $req .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        return $req;
    }
    
    function ipn_mailErrors()
    {
        $order_id = null;
        $sid = null;
        $cart_id = null;
        $trans_id = null;
        $cart_info = "(No cart record was found)";
        if ($this->trans) {
            $trans_id = $this->trans->id;
            $sid = $this->trans->sid;
            $cart_id = $this->trans->cart_id;
            if (!$this->_cart) {
                $this->_cart = $this->trans->cart;
            }
        }
        if ($this->_cart) {
            $order_id = $this->_cart->order_id;
            $sid = $this->_cart->getSID();
            $cart_id = $this->_cart->id;
            $cart_info = $this->_cart->toString();
        }
        if (!$cart_id) {
            $cart_id = gv($this->_ipn_request, 'custom');
        }
        $to_orders = mm_getSetting('orders.notification');
        
        $request_dump = var_export($this->_ipn_request, true);
        $error_str = implode($this->errors, "\n  - ");
        $message = <<<END_MESSAGE
One or more errors occurred when the PayPal IPN server tried to submit a transaction
notification to Modern Merchant.

Here is the error list:
  - $error_str

Additional data:
  request: $request_dump
  order_id = $order_id
  sid = $sid
  cart_id = $cart_id
  ipn transaction id = $trans_id

If there is an order_id, an order has been saved, but not marked as Payed. Additionally, no order email has been sent to the customer or $to_orders.

-- ORDER --
$cart_info

END_MESSAGE;
    
        $from = mm_getSetting('site.noreply');
        $webmaster = mm_getSetting('webmaster.notification');
        $to = $to_orders . ",\r\n\t" . $webmaster;
        // Remove duplicate addresses
        $to = implode(",\r\n\t", array_unique(preg_split('/,\s*/', $to)));
        $subject = "PayPal IPN Failure";
        
        $to_array = array($to, $to_orders);
        $this->sent_ipn_error_email = $this->mail($to, $subject, $message, "From: $from");
        return $this->sent_ipn_error_email;
    }
    
    function process($cart)
    {
        $order_id = $cart->order_id;
        if( !$order_id )
        {
            $this->addError("No order_id found in cart");
            $this->errorno = 1;
            return false;
        }
        
        $sql = sprintf('SELECT status FROM mm_paypal_ipn_trans '
            .'WHERE order_id=%d', $order_id);
        $dbh = mm_getDatabase();
        try {
            $res = $dbh->query($sql);
        }
        catch (Exception $e) {
            $this->addError("Internal error");
            $this->errorno = 2; // 2 = Internal error
            return false;
        }
        $row =& $res->fetchAssoc();
        if( !$row )
        {
            $this->addError("No IPN transaction found");
            $this->errorno = 1;
            return false;
        }
        $status = $row[0];
        $res->close();
        if( !$status )
        {
            $this->addError("No status found in IPN transaction record");
            $this->errorno = 1;
            return false;
        }
        
        $this->status = $status;
        $this->is_payed = true;
        
        return true;
    }

    /**
    * Payment result.
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
        if( $this->status == NULL && !$this->user_message )
        {
            $this->user_message = "Payment method's process() method hasn't been called yet.";
            return PAYMENT_METHOD_ERROR;
        }
        else if( $this->status == 'Completed' )
        {
            $this->user_message = "Passed";
            return PAYMENT_METHOD_PASSED;
        }
        else if( $this->status == 'Denied' )
        {
            $this->user_message = "Payment Declined";
            return PAYMENT_METHOD_DECLINED;
        }
        else
        {
            return PAYMENT_METHOD_ERROR;
        }
    }

    function fetchOrder($order_id)
    {
        // Get database connection
        $dbh = mm_getDatabase();
        $sql = 'SELECT id, total FROM mm_order WHERE order_id='.intval($order_id);
        return $dbh->getOneAssoc($sql);
    }
    
    function mail($to, $subject, $message, $headers)
    {
        return mm_mail($to, $subject, $message, $headers);
    }
    
    function getCartById($id)
    {
        return cart_Cart::get($id);
    }
    
    /**
     * Make an HTTP 'post' request and return the response body.
     *
     * @param string $url
     * @param string $query
     */
    function sendPost($url, $query)
    {
        return $this->getPoster()->post($url, $query);
    }
    
    function getPoster()
    {
        if (!$this->_poster) {
            $this->_poster = new mm_HttpPoster;
        }
        return $this->_poster;
    }

    function setPoster($poster)
    {
        $this->_poster = $poster;
    }
    
    function install()
    {
        $this->public_title = 'PayPal';
        $this->active = true;

        $this->account_email = 'example@example.com';
        $this->ipn_notify = 'example@example.com';
        $this->currency = 'USD';
        $this->test_mode = true;
        
        return parent::install();
    }

    function uninstall()
    {
        parent::uninstall();
        $install_queries = "DROP TABLE IF EXISTS `mm_paypal_ipn_trans`";
        $dbh = mm_getDatabase();
        $dbh->query($install_queries);
    }
    
    function cartToIpnRequest($cart)
    {
        $https = mm_getConfigValue('urls.https');
        $thankyou = $https . mm_getConfigValue('urls.cart.thank_you');
        $cancel_return = $https . mm_getConfigValue('urls.cart.cancel_payment');

        $request["cmd"]           = "_xclick";
        $request["business"]      = $this->account_email;
        $request["item_name"]     = mm_getSetting('site.name', 'Modern Merchant') . " Web Order";
        $request["amount"]        = mm_price($cart->sub_total);
        $request["shipping"]      = mm_price($cart->shipping_total);
        $request["no_note"]       = "1";
        $request["currency_code"] = "USD";
        $request["lc"]            = "US";
        $request["custom"]        = $this->_cart->id;
        $request["first_name"]    = $this->_cart->billing->first_name;
        $request["last_name"]     = $this->_cart->billing->last_name;
        $request["address1"]      = $this->_cart->billing->address_1;
        $request["address2"]      = $this->_cart->billing->address_2;
        $request["city"]          = $this->_cart->billing->city;
        $request["state"]         = $this->_cart->billing->state;
        $request["zip"]           = $this->_cart->billing->zip;
        $request["night_phone_a"] = $this->_cart->billing->phone_night;
        $request["day_phone_a"]   = $this->_cart->billing->phone_day;
        $request["return"]        = $thankyou;
        $request["cancel_return"] = $cancel_return;
        $request["rm"]            = "1";
        $request["notify_url"]    = $this->ipn_client_url;
        $request["submit.x"]      = '10';
        $request["submit.y"]      = '10';
        
        return $request;
    }
    
    function validatePayment() {
        return array();
    }

    function getSettings() {
        $names = array('test_mode', 'account_email', 'ipn_notify', 'currency');
        $settings = array();
        foreach ($names as $k) {
            $settings[$k] = $this->$k;
        }
        return $settings;
    }
}
