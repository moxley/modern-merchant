<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>

<?php $this->render('mminstall/results'); ?>

<h2>Check Apache mod_rewrite</h2>

<form method="post" action="?a=mminstall.modRewrite">
    <p><input type="submit" value="Check mod_rewrite &gt;" /></p>
</form>

