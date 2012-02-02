<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div id="breadcrumb">
    Checkout:
    <a href="?a=cart.cart">Your Cart</a> &gt;
    <a href="?a=cart.shippingPage">Shipping</a> &gt;
    <a href="?a=cart.billingPage">Billing</a> &gt;
    <a href="?a=cart.paymentPage">Payment</a> &gt;
    Verify
</div>

<p style="color: red">Your order is <u>not yet</u> complete.</p>

<p>Please check your order below and press the "Place Your Order!" button when the information looks correct.</p>

<br />

<?php $this->render('cart/order_details'); ?>

<table width="100%" cellpadding="3" cellspacing="2" border="0" class="dataTable">
  <tr>
    <td colspan="2" align="center">
    
    <?php
    if (method_exists($this->cart->payment_method, 'renderSubmitOrderButton')) {
        $this->cart->payment_method->renderSubmitOrderButton($this->controller);
    } else {
    ?>
        <form name="paymentForm" action="?action=cart.submitOrder" method="POST" accept-charset="utf-8">
          <input type=submit value="Place Your Order!" name="submit" />
        </form>
    <?php } ?>

    </td>
  </tr>
</table>
