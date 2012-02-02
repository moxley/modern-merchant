<?php
/**
 * Fake PayPal user interface
 *
 * User Thread:
 * 1. User at Verify Order page clicks the PayPal button
 * 2. User at Fake PayPal page (this script), clicks "Submit Payment"
 * 3. User at Order Confirmation page.
 *
 * IPN Thread:
 * 1. Fake IPN server (stub_ipn_server.php) hits paypal/ipn.php with the order
 * 2. ipn.php checks the order, and makes a new confirmation request back to
 *    stub_ipn_server.php, which responds with 'VALID' or 'INVALID'
 *
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

include_once '../../init.php';

// Define function that sends IPN to web site
function paypal_sendIPN($query_string, $options=array())
{
    $dao = new payment_PaymentMethodDAO;
    $paypal = $dao->getModuleByClass('paypal_PayPal');
    $cart = mm_getCart();
    
    $query = parseQueryString($query_string);
    
    $ipn_values = array();
    $ipn_values['txn_id'] = strtoupper(makePassword(17));
    mm_log("options", $options);
    $ipn_values['payment_status'] = gv($options, 'payment_status', 'Completed');
    mm_log("payment_status", $ipn_values['payment_status']);
    $ipn_values['receiver_email'] = $paypal->account_email;
    $ipn_values['mc_gross'] = mm_price($cart->total);
    
    ob_start();
    $paypal->handleIpn(array_merge($query, $ipn_values));
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}

$input = getRequest();
if (isset($input['cmd']) && $input['cmd'] == 'submit_payment') {
    $query_string = "";
    $i = 0;
    foreach ($input as $key=>$value) {
        if ($key == 'cmd') continue;
        if ($i > 0) $query_string .= '&';
        $query_string .= urlencode($key) . '=' . urlencode($value);
        $i++;
    }
    
    // Send IPN to web site
    $result = paypal_sendIPN($query_string, $input);
    if (stripos($result, 'thank you') !== false) {
        //unset($_SESSION[USER_SESSION_DATA]['data']['order_id']);
        //unset($_SESSION[USER_SESSION_DATA]['data']['cart']);
    }
    
    // Redirect to return URL
    $url = mm_getConfigValue('urls.https') . '/?action=Return+To+Merchant';
    redirect($url);
    exit;
}
?>

<html>
    <head>
        <title>Fake PayPal Checkout</title>
        <style type="text/css">
            .col1 { width: 300px; text-align: right }
        </style>
    </head>
    <body>
        
        <table width="100%">
            <tr>
                <td>
                    <?php ph($input['business']) ?>
                </td>
                <td width="200px">
                    Payments by PayPal
                </td>
            </tr>
        </table>
        
        <hr style="border: 3px solid #336699" />
        
        <h1>Fake Checkout</h1>        

        <hr />
        <table>
            <tr>
                <td class="col1">Pay To: </td>
                <td><?php ph($input['business']) ?></td>
            </tr>
            <tr>
                <td class="col1">Payment For: </td>
                <td><?php ph($input['item_name']) ?></td>
            </tr>
            <tr>
                <td class="col1">Currency: </td>
                <td><?php ph($input['currency_code']) ?></td>
            </tr>
            <tr>
                <td class="col1">Amount:</td>
                <td><?php ph($input['amount']) ?></td>
            </tr>
            <tr>
                <td class="col1">Shipping &amp; Handling: </td>
                <td><?php ph($input['shipping']) ?></td>
            </tr>
            <tr>
                <td class="col1">Total Amount: </td>
                <td><?php ph(sprintf("%0.2f", $input['shipping'] + $input['amount'])) ?></td>
            </tr>
        </table>
        
        <hr />
        
        <form method="POST" action="<?php ph($_SERVER['PHP_SELF']) ?>">
            <label for="payment_status">Payment Status:</label><br/>
            &nbsp; <input type="radio" name="payment_status" value="Completed" id="payment_status_Completed" checked="checked"/>
            <label for="payment_status_Complete">Completed</label>
            <br/>
            &nbsp; <input type="radio" name="payment_status" value="Pending" id="payment_status_Pending"/>
            <label for="payment_status_Pending">Pending</label>
            <br/>
    
            <input type="hidden" name="cmd" value="submit_payment" />
            
            <input type="hidden" name="business" value="<?php ph($input['business']) ?>">
            <input type="hidden" name="item_name" value="<?php ph($input['item_name']) ?>">
            <input type="hidden" name="amount" value="<?php ph($input['amount']) ?>">
            <input type="hidden" name="no_note" value="<?php ph($input['no_note']) ?>">
            <input type="hidden" name="currency_code" value="<?php ph($input['currency_code']) ?>">
            <input type="hidden" name="lc" value="<?php ph($input['lc']) ?>">
            <input type="hidden" name="custom" value="<?php ph($input['custom']) ?>">

            <input type="hidden" name="first_name" value="<?php ph($input['first_name']) ?>">
            <input type="hidden" name="last_name" value="<?php ph($input['last_name']) ?>">
            <input type="hidden" name="address1" value="<?php ph($input['address1']) ?>">
            <input type="hidden" name="address2" value="<?php ph($input['address2']) ?>">
            <input type="hidden" name="city" value="<?php ph($input['city']) ?>">
            <input type="hidden" name="state" value="<?php ph($input['state']) ?>">
            <input type="hidden" name="zip" value="<?php ph($input['zip']) ?>">
            <input type="hidden" name="night_phone_a" value="<?php ph($input['night_phone_a']) ?>">
            <input type="hidden" name="day_phone_a" value="<?php ph($input['day_phone_a']) ?>">
            <input type="hidden" name="return" value="<?php ph($input['return']) ?>">
            <input type="hidden" name="cancel_return" value="<?php ph($input['cancel_return']) ?>">
            <input type="hidden" name="rm" value="<?php ph($input['rm']) ?>"><!-- rm==1: (Return Method) when returning use GET method with no parameters -->

            Submit payment now
            and return to web site: <input type="submit" value="Send Payment" />
        </form>
            
    </body>
</html>
