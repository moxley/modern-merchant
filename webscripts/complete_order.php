<?php
/**
 * @package webscript
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

include_once('../init.php');
$user = mm_getUser();
if (!$user->isAdmin()) redirect(mm_actionToUri(mm_getConfigValue('actions.admin_login')));

function mm_completeOrder() {
    try {
        print "Finding order...<br />\n";
        $input = getRequest();
        $action = $input['action'];
        $sid = $input['sid'];
        $dao = new sess_SessionDAO;
        $sess = $dao->fetchBySid($sid);
        if (!$sess) {
            print "<p><b>Session not found</b></p>\n";
            return;
        }
        $cart = $sess->get('cart');
        if (!$cart) {
            print "<p><b>No cart found in session</b></p>\n";
            return;
        }
        print "<pre>";
        var_export($cart);
        $cartObj = $cart;
        print "\n\n";
        include('../templates/email/cart.php');
        print "</pre>\n";
        if ($action == 'complete') {
            if (gv($input, 'payed')) {
                $cart->payed = true;
            }
            $order = $cart->completeOrder();
            print "Completed the order. order_id={$order->id}\n";
        }
    }
    catch (Exception $e) {
        print "Failed: " . $e->getMessage() . "<br />\n";
    }
}

?>
<html>
<head>
<title>Complete Order</title>
</head>
<body>

<h1>Complete Order</h1>

<?php
if ($_POST) {
    mm_completeOrder();
}
$input = getRequest();
?>

<form method="POST" action="complete_order.php">
    <input type="hidden" name="action" value="lookup" />
    sid: <input type="text" name="sid" size="40" value="<?php ph(gv($input, 'sid')) ?>" /><br />
    <input type="submit" value="Lookup" />
</form>

<form method="POST" action="complete_order.php">
    <input type="hidden" name="action" value="complete" />
    sid: <input type="text" name="sid" size="40" value="<?php ph(gv($input, 'sid')) ?>" /><br />
    Set payed: <input type="checkbox" name="payed" value="1" /><br />
    <input type="submit" value="Find and Complete" />
</form>

</body>
</html>
