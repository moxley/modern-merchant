<?php
/**
 * Two purposes:
 * 
 * 1. Send IPN to the web site (ipn.php)
 * 2. Validate order that is reported from web site
 *
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

include_once('../../init.php');

$input = getRequest();
$cmd = gv($input, 'cmd');
if ($cmd == '_notify-validate') {
    /* Client has received the IPN and is calling
       the IPN server to validate the IPN */
    print 'VERIFIED';
    return;
}

if (gv($input, 'order_id')) {
    $order_dao = new order_OrderDAO;
    $order = $order_dao->fetch($input['order_id']);
    if (!$order) throw new Exception("Failed to find order for id={$input['order_id']}");
    $cart = $order->cart;
    if (!$cart) throw new Exception("Order (id={$input['order_id']}) does not have an attached cart");
    $txn_dao = new paypal_TransactionDAO;
    $trans = $txn_dao->fetchByCartId($cart->id);
    if (!$trans) throw new Exception("Cart {$cart->id} does not have an attached paypal ipn transaction");
    $txn_id = $trans->txn_id;
    $paypal = $cart->payment_method;
} else {
    $cart = mm_getCart();
    $txn_id = strtoupper(makePassword(17));
    $paypal = $cart->payment_method;
    if (!$paypal || !($paypal instanceof paypal_PayPal)) $paypal = new paypal_PayPal;
}
$config = mm_getConfig();
$payment_status = 'Completed';
$writer = new mvc_HtmlWriter;
?>
<html>
    <head>
        <title>Manual IPN</title>
    </head>
</head>
<body>
    <h1>Manual IPN</h1>
    
    <form method="GET" action="<?php ph($_SERVER['PHP_SELF']) ?>">
        Order ID:
        <?php echo $writer->textFieldTag('order_id', gv($input, 'order_id'), array('size' => '3'))?>
        <input type="submit" value="Fetch Order"/>
    </form>
    
    <form method="POST" action="<?php ph($paypal->getIpnClientUrl()) ?>">
        <table>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Submit" />
                </td>
            </tr>
            <tr>
                <td>payment_status</td>
                <td><?php echo $writer->textFieldTag('payment_status', $payment_status) ?></td>
            </tr>
            <tr>
                <td>txn_id</td>
                <td><?php echo $writer->textFieldTag('txn_id', $txn_id) ?></td>
            </tr>
            <tr>
                <td>receiver_email</td>
                <td><?php echo $writer->textFieldTag('receiver_email', $paypal->account_email) ?></td>
            </tr>
            <tr>
                <td>business</td>
                <td><?php echo $writer->textFieldTag('business', $paypal->account_email) ?></td>
            </tr>
            <tr>
                <td>item_name</td>
                <td><?php echo $writer->textFieldTag('item_name', mm_getSetting('site.name') . ' Web Order') ?></td>
            </tr>
            <tr>
                <td>amount</td>
                <td><?php echo $writer->textFieldTag('amount', $cart->getTotal()) ?></td>
            </tr>
            <tr>
                <td>mc_gross</td>
                <td><?php echo $writer->textFieldTag('mc_gross', $cart->getTotal()) ?></td>
            </tr>
            <tr>
                <td>no_note</td>
                <td><input type="text" name="no_note" value="1" /></td>
            </tr>
            <tr>
                <td>currency_code</td>
                <td><input type="text" name="currency_code" value="USD" /></td>
            </tr>
            <tr>
                <td>lc</td>
                <td><input type="text" name="lc" value="US" /></td>
            </tr>
            <tr>
                <td>custom</td>
                <td><?php echo $writer->textFieldTag('custom', $cart->id) ?></td>
            </tr>
            <tr>
                <td>first_name</td>
                <td><?php echo $writer->textFieldTag('first_name', $cart->billing->first_name) ?></td>
            </tr>
            <tr>
                <td>last_name</td>
                <td><?php echo $writer->textFieldTag('last_name', $cart->billing->last_name) ?></td>
            </tr>
            <tr>
                <td>address1</td>
                <td><?php echo $writer->textFieldTag('address1', $cart->billing->address_1) ?></td>
            </tr>
            <tr>
                <td>address2</td>
                <td><?php echo $writer->textFieldTag('address2', $cart->billing->address_2) ?></td>
            </tr>
            <tr>
                <td>city</td>
                <td><?php echo $writer->textFieldTag('city', $cart->billing->city) ?></td>
            </tr>
            <tr>
                <td>state</td>
                <td><?php echo $writer->textFieldTag('state', $cart->billing->state) ?></td>
            </tr>
            <tr>
                <td>zip</td>
                <td><?php echo $writer->textFieldTag('zip', $cart->billing->zip) ?></td>
            </tr>
            <tr>
                <td>night_phone_a</td>
                <td><?php echo $writer->textFieldTag('night_phone_a', $cart->billing->phone_day) ?></td>
            </tr>
            <tr>
                <td>day_phone_a</td>
                <td><?php echo $writer->textFieldTag('day_phone_a', $cart->billing->phone_night) ?></td>
            </tr>
            <tr>
                <td>return</td>
                <td><?php echo $writer->textFieldTag('return', mm_getConfigValue('urls.https') . mm_getConfigValue('urls.cart.thank_you')) ?></td>
            </tr>
            <tr>
                <td>cancel_return</td>
                <td><?php echo $writer->textFieldTag('cancel_return', mm_getConfigValue('urls.https') . mm_getConfigValue('urls.cart.cancel_payment')) ?></td>
            </tr>
            <tr>
                <td>rm</td>
                <td><input type="text" name="rm" value="1" /></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Submit" />
                </td>
            </tr>
        </table>
    </form>
    
</body>
