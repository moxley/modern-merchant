<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div class="contentRow">

    <p>Your order has been submitted. Receipt of your order
        will be emailed to you shortly.</p>

    <p>Thank you for shopping !!</p>

    <?php
    if ($this->post_cart) {
        $this->tmp_cart = $this->cart;
        $this->cart = $this->post_cart;
    ?>
    <p><b>Order Number: </b><?php ph($this->cart->order_id); ?></p>
    <?php
        $this->render('cart/order_details');
        $this->cart = $this->tmp_cart;
    }
    ?>

</div>

