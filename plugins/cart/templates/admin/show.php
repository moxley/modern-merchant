<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<p><a href="?a=cart_admin.list">Browse carts</a></p>

Cart ID: <?php ph($this->cart->id) ?><br />
Creation Date: <?php ph(mm_datetime($this->cart->creation_date)) ?><br />
Order ID: <?php if ($this->cart->order_id) echo $this->linkTag($this->cart->order_id, '?a=order.edit&id=' . $this->cart->order_id) ?><br />
Session SID: <?php if ($this->cart->sid) echo $this->linkTag($this->cart->sid, '?a=sess_admin.show&sid=' . $this->cart->sid) ?><br />

<?php $this->render('cart/order_details')?>
