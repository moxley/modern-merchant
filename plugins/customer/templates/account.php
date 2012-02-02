<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

$this->title = "Your Account"
?>

<ul>
    <li><?php echo $this->linkTag('Logout', '?a=user.logout') ?></li>
    <li><?php echo $this->linkTag('Order History', '?a=customer.orders') ?></li>
    <li><?php echo $this->linkTag('User Details', '?a=customer.user') ?></li>
</ul>
