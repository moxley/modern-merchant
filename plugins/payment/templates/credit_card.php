<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Credit Card payment form
 */
?>
<fieldset>
    <label for="cart_payment_cc_name" class="cc-name">Name on Card</label>
    <?php echo $this->textField('cart[payment][cc_name]', array('size'=>40, 'class' => 'cc-name')); ?>
    <br />

    <label for="cart[payment][cc_type]" class="cc-type">Card Type</label>
    <select name="cart[payment][cc_type]" class="cc-type">
<?php
    $types = array(
        'Visa'       => 'Visa',
        'MasterCard' => 'MasterCard',
        'AmEx'       => "American Express",
        'Discover'   => "Discover"
    );
    echo $this->selectOptions('cart[payment][cc_type]', $types);
?>
    </select>

    <br />

    <label for="cart[payment][cc_number]" class="cc-number">Credit Card Number</label>
    <?php echo $this->textField('cart[payment][cc_number]', array('size'=>40, 'class' => 'cc-number')); ?>
    
    <br />

    <label for="cart[payment][cc_exp_month]" class="cc-exp-month">Expiration Date</label>
    <select name="cart[payment][cc_exp_month]" class="cc-exp-month">
        <?php echo $this->selectOptions('cart[payment][cc_exp_month]', $this->payment_method->getMonthOptions()); ?>
    </select>

    <select name="cart[payment][cc_exp_year]" class="cc-exp-year">
        <?php echo $this->selectOptions('cart[payment][cc_exp_year]', $this->payment_method->getYearOptions()); ?>
    </select>
    
    <br />

    <label for="cart[payment][cc_cvv]" class="cc-cvv">CVV</label>
    <?php echo $this->passwordFieldTag('cart[payment][cc_cvv]', "", array('size'=>3, 'autocomplete'=>'off', 'class' => 'cc-cvv')); ?>

</fieldset>
