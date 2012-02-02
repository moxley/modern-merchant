<?php
/**
 * @package webscript
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

include('../init.php');
$user = mm_getUser();
if (!$user->isAdmin()) {
    redirect(mm_actionToUri(mm_getConfigValue('actions.admin_login')));
    exit;
}
$input = getRequest();
?>
<html>
<head>
    <title>Parse PHP Session</title>
</head>
<body>

<h1>Parse PHP Session Data</h1>

<form method="POST" action="parse_session.php">
    <div>session_id: <input type="text" name="session_id" value="<?php ph(gv($input, 'session_id')) ?>" /></div>
    <div>or Data: <textarea name="data" cols="80" rows="10"><?php ph(gv($input, 'data')) ?></textarea></div>
    <div><input type="submit" value="Parse" /></div>
</form>

<?php
if ($_POST) {
    $session = null;
    $session_id = $input['session_id'];
    if ($session_id) {
        $dao = new sess_SessionDAO;
        $sess = $dao->fetch($session_id);
        if ($sess) $data = $session = $sess->data;
    }

    if (!$session) {
        $data = $input['data'];
        $session = mm_decodeSession($data);
    }
    print "<pre>" . h(var_export($session, true));
    
    if (is_array($session) && isset($session['cart'])) {
        $cart = $session['cart'];
        print "FOUND CART:\n";
        print "--------------------------\n";
        foreach ($cart->lines as $line) {
            print "sku=" . $line->sku . ", desc=" . $line->description;
            print ", price=" . $line->price . ", qty=" . $line->qty;
            print ", total=" . $line->getTotal() . "\n";
        }
        print "--------------------------\n";
        print "SubTotal: " . $cart->getSubTotal() . "\n";
        print "ShipTotal: " . $cart->getShipTotal() . "\n";
        print "Total: " . $cart->getTotal() . "\n";
    }
    print "</pre>";
}
?>

</body>
</html>
