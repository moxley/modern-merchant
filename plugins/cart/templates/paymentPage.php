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
    Payment
</div>

<form class="paymentForm" name="paymentForm" action="?a=cart.submitPayment" method="POST" accept-charset="utf-8">
    
    <fieldset class="payment-methods">
        <legend>Payment Methods</legend>

        <div class="summary">
            Choose a payment method for your order:
        </div>

        <?php foreach ($this->getPaymentMethods() as $method): ?>
        <div class="payment-method payment-method-<?php ph($method->name) ?>" style="clear:both">
            <div class="method-option">
                <label for="cart_payment_method_id_<?php ph($method->id); ?>"><?php ph($method->public_title); ?></label>
                <?php echo $this->radioButton('cart[payment_method_id]', $method->id); ?>
            </div>
            
            <div class="payment-fields">
                <?php $method->renderPaymentForm($this->controller); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </fieldset>
    
    <fieldset class="special-instructions">
        <legend>Special Instructions</legend>
        <div class="summary">Provide any instructions, requests or comments here.</div>
        <textarea name="cart[comments]" rows="5" cols="60" wrap="virtual" style="width: 98%"><?php ph($this->cart->comments); ?></textarea>
    </fieldset>
    
    <div class="form-buttons">
        <input type=submit value="Verify Your Order" name="submit" />
    </div>
</form>
