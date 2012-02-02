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
    Shipping
</div>

<?php echo $this->prependShippingForm; ?>

<?php if (!$this->customer): ?>
<form class="customer-login-form" name="customerLoginForm" action="?" method="POST" accept-charset="utf-8">
    <fieldset class="customer-login">
        <legend>Do you have an account with us?</legend>
        <div class="summary">This is optional. Creating an account makes your next checkout easier and it allows us to serve you better.</div>

        <label for="user_username" class="username">Username</label>
        <?php echo $this->textField('user[username]', array('class' => 'username')) ?>
        
        <label for="user_password" class="password">Password</label>
        <?php echo $this->passwordField('user[password]', array('class' => 'password')) ?>
        
        <br />

        <div class="form-buttons">
            <?php echo $this->submitButton("Login", array('name' => 'action_cart_login')) ?>
            <?php echo $this->submitButton("Create Account", array('name' => 'action_cart_createAccount')) ?>
        </div>

    </fieldset>
</form>
<?php endif ?>

<form name="shippingForm" action="?a=cart.submitShipping" method="POST">
    <fieldset class="shipping-method">
        <legend>Shipping Method</legend>
        <?php $this->render('cart/shippingMethod') ?>
    </fieldset>

    <fieldset class="shipping-address">
        <legend>Delivery Address</legend>

        <div class="summary">
<!--
            We need the <span class="required">bold</span> fields to process your order.
-->
        </div>

        <label class="first-name required" for="cart_shipping_first_name">First Name</label>
        <?php echo $this->textField('cart[shipping][first_name]', array('size'=>20)); ?>
        
        <label class="last-name required" for="cart_shipping_last_name">Last Name</label>
        <?php echo $this->textField('cart[shipping][last_name]', array('size'=>20)); ?>
        
        <br />
        
        <label class="company" for="cart_shipping_company">Company</label>
        <?php echo $this->textField('cart[shipping][company]', array('size'=>40, 'class' => 'company')); ?>
        
        <br />
        
        <label class="address address-1" for="cart_shipping_address_1" class="required">Address 1</label>
        <?php echo $this->textField('cart[shipping][address_1]', array('size'=>40, 'class' => 'address address-1')); ?>
        
        <label class="address address-2" for="cart_shipping_address_2" class="required">Line 2</label>
        <?php echo $this->textField('cart[shipping][address_2]', array('size'=>40, 'class' => 'address address-1')); ?>
        
        <br />

        <label class="required" class="city">City</label>
        <?php echo $this->textField('cart[shipping][city]', array('size'=>20, 'class'=>'city')); ?>
        
        <br />

        <label for="cart_shipping_state" class="state">State</label>
        <?php echo $this->textField('cart[shipping][state]', array('size'=>3, 'class'=>'state')); ?>

        <label for="cart_shipping_zip" class="zip required">Zip/Postal Code</label>
        <?php echo $this->textField('cart[shipping][zip]', array('size'=>10, 'class'=>'zip')); ?>
        
        <br />

        <label for="cart_shipping_country" class="country required">Country</label>
        <select name="cart[shipping][country]" id="cart[shipping][country]">
            <option value="">-- Select --</option>
            <option value="US">United States</option>
            <option value="CA">Canada</option>
            <option value="">----------------------------</option>
            <?php echo $this->selectOptions('cart[shipping][country]', $this->getCountryOptions()); ?>
        </select>
        
        <br />

        <label for="cart_shipping_email" class="email required">Email Address</label>
        <?php echo $this->textField('cart[shipping][email]', array('size'=>20, 'class' => 'email')); ?>
        
        <br />

        <label for="cart_shipping_phone_day" class="daytime-phone">Daytime Phone</label>
        <?php echo $this->textField('cart[shipping][phone_day]', array('size'=>20, 'class' => 'daytime-phone')); ?>

        <label for="cart_shipping_phone_night" class="evening-phone">Evening Phone</label>
        <?php echo $this->textField('cart[shipping][phone_night]', array('size'=>20, 'class' => 'evening-phone')); ?>
        
        <br />

    </fieldset>
    
    <fieldset class="billing">
        <legend>Billing</legend>

        <label for="cart_shipping_billing_same_1">Use same information for billing</label>
        <?php echo $this->radioButton('cart[shipping][billing_same]', true); ?>
        
        <br />
            
        <label for="cart_shipping_billing_same_0">Use separate billing address</label>
        <?php echo $this->radioButton('cart[shipping][billing_same]', false); ?>
    </fieldset>
    
    <div class="form-buttons">
        <?php echo $this->submitButton('Next -->') ?>
    </div>
</form>

<script type="text/javascript" language="Javascript">
    document.shippingForm['cart[shipping][first_name]'].focus();
</script>
