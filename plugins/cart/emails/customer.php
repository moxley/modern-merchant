<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
Thank you for your <?php print mm_getSetting('site.name'); ?> purchase! 

Your merchandise will ship in 1-3 business days<?php if (!$this->payed): ?> after we receive payment<?php endif ?>. Please review your order information. If you have any questions, you can contact us at <?php ph(mm_getSetting('orders.notification')) ?>.

Sincerely,

<?php print mm_getSetting('site.name') . "\n"; ?>

Order Number: <?php print $this->order_id . "\n" ?>

<?php mm_renderContent("order.email.shipping", $this); ?>

<?php mm_renderContent("order.email.billing", $this); ?>

=====================================
Payment Details
-------------------------------------
Payment Type: <?php print $this->payment_method->public_title."\n" ?>
-------------------------------------

<?php mm_renderContent("order.email.comments", $this); ?>

<?php mm_renderContent("order.email.cart", $this); ?>
