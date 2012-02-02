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
 * 1. Fake IPN server (this script) hits paypal/ipn.php with the order
 * 2. ipn.php checks the order, and hits the paypal
 * 3. 
 *
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<html>
    <head>
        <title>Fake PayPal</title>
    </head>
    <body>
        <h1>Fake PayPal</h1>
    
        <form method="POST" action="<?php ph($_SERVER['PHP_SELF']) ?>">
            <input type="submit" value="Submit Payment" />
        </form>
    
    </body>
</html>
