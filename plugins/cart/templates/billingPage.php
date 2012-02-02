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
    Billing
</div>

<form name="billingForm" action="?action=cart.submitBilling" method="POST" accept-charset="utf-8">
    <fieldset>
        <legend>Billing Address</legend>

        <label for="cart[billing][first_name]" class="first-name required">First Name</label>
        <?php echo $this->textField('cart[billing][first_name]', array('size'=>'30', 'class' => 'first-name')); ?>

        <label for="cart[billing][last_name]" class="last-name required">Last Name</label>
        <?php echo $this->textField('cart[billing][last_name]', array('size'=>'30', 'class' => 'last-name')); ?>

        <br />

        <label for="cart[billing][company]" class="company">Company</label>
        <?php echo $this->textField('cart[billing][company]', array('size'=>'30', 'class' => 'company')); ?>

        <br />

        <label for="cart[billing][address_1]" class="address address-1 required">Address 1</label>
        <?php echo $this->textField('cart[billing][address_1]', array('size'=>'30', 'class' => 'address address-1')); ?>

        <label for="cart[billing][address_2]" class="address address-2 required">Line 2</label>
        <?php echo $this->textField('cart[billing][address_2]', array('size'=>'30', 'class' => 'address address-2')); ?>

        <br />

        <label class="city required" for="cart[billing][city]">City</label>
        <?php echo $this->textField('cart[billing][city]', array('size'=>'20', 'class'=>'city')); ?>
        
        <br />

        <label for="cart[billing][state]" class="state required">State</label>
        <?php echo $this->textField('cart[billing][state]', array('size'=>'3', 'class'=>'state')); ?>

        <label for="cart[billing][zip]" class="zip required">Zip/Postal Code</label>
        <?php echo $this->textField('cart[billing][zip]', array('size'=>'10', 'class'=>'zip')); ?>

        <br />

        <label for="cart[billing][country]" class="country required">Country</label>
        <select name="cart[billing][country]" id="cart[billing][country]" class="country">
            <option value="">-- Select --</option>
            <option value="US">United States</option>
            <option value="CA">Canada</option>
            <option value="">----------------------------</option>
            <?php echo $this->selectOptions('cart[billing][country]', $this->getCountryOptions()); ?>
        </select>

        <br />

        <label for="cart[billing][email]" class="email required">Email Address</label>
        <?php echo $this->textField('cart[billing][email]', array('size'=>'42', 'class' => 'email')); ?>

        <br />

        <label for="cart_billing_phone_day" class="phone">Phone</label>
        <?php echo $this->textField('cart[billing][phone_day]', array('size'=>'15')); ?>

        <br />

        <div class="form-buttons">
            <?php echo $this->submitButton("Next >") ?>
        </div>
     
    </fieldset>
</form>

<script type="text/javascript" language="Javascript">
    document.billingForm.cart_billing_first_name.focus();
</script>
