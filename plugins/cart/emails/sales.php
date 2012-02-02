<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
An order has been placed from <?php print mm_getSetting('site.name'); ?>.
<?php if (!$this->payed): ?>This order has not yet been payed. If this payment will be automatically made by a third-party processor, you will receive a payment notification, and the order will be marked as payed.<?php endif ?>

Order Number: <?php print $this->order_id . "\n" ?>

<?php mm_renderContent("order.email.shipping", $this) ?>

<?php mm_renderContent("order.email.billing", $this) ?>

=====================================
Payment Details
-------------------------------------
Payment Type: <?php print $this->payment_method->public_title . ': ' . $this->payment_method->title . "\n" ?>
-------------------------------------

<?php mm_renderContent("order.email.comments", $this) ?>

<?php mm_renderContent("order.email.cart", $this); ?>
