<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
=====================================
Shipping Detail
-------------------------------------
Shipping Method: <?php echo $this->shipping_method->name . "\n" ?>
<?php
$this->address = $this->shipping;
mm_renderContent('order.email.address', $this);
?>
-------------------------------------
