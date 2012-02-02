<?php
/**
 * PayPal Instant Payment Notification script
 *
 * This script is called by PayPal's IPN service to
 * record the results of a PayPal transaction for the paypal module
 * to handle.
 *
 * Not only does it handle payments, it handles reversals and refunds as well.
 *
 * A typical payment sequence goes like this:
 *
 * 1. After selecting 'PayPal' for payment type and continuing to the Order Verification page, the customer
 *    clicks on 'Buy Now'.
 * 2. The customer completes the payment at the PayPal.com site.
 * 3. The PayPal "sending" server sends IPN to this script as a POST request.
 * 4. This script records the POST to the database and reflects the POST to the "receiving" IPN server
 * 5. The customer returns to the primary site with a 'submitOrder' GET request
 * 6. The 'submitOrder' action calls the PayPal module class, which in turn checks the database for the IPN post 
 *    and gets the result of the payment transaction.
 * 7. The 'submitOrder' action either shows the 'Thank You' page, or redirects the customer back to the payment 
 *    page with a notice message indicating payment failure, cancellation, or error.
 *
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

// Make sure the script doesn't quit just because the caller disconnects
ignore_user_abort(TRUE);

// Includes
//list($_SERVER['MM_CLIENT'],$unique_code) = explode(':', $_REQUEST['custom']);
include_once('../../init.php');
restore_error_handler(); // Restore the error handler
ini_set("log_errors", TRUE);

$methods = new payment_PaymentMethodDAO;
$module = $methods->getModuleByClass('paypal_PayPal');
if (!isset($request)) $request = getRequest();
$r = $module->handleIpn($request);
if( !$r ) print "Error.";
else print "Thank You.";

return;
