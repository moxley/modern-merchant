<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<fieldset>
    <legend>User</legend>
    <div class="row">
        <label for="customer[user][username]">Username</label>
        <?php echo $this->textField('customer[user][username]') ?>
    </div>
    <div class="row">
        <label for="customer[user][new_password]">
            <span title="<?php ph($this->customer->user->password) ?>" style="text-decoration: underline">Password</span>
        </label>
        <?php echo $this->textField('customer[user][new_password]') ?>
    </div>
    <div class="row">
        <label for="customer[user][confirm_password]">Confirm Password</label>
        <?php echo $this->textField('customer[user][confirm_password]') ?>
    </div>
</fieldset>

<fieldset>
    <legend>Billing</legend>
    <?php $this->field_prefix = "customer[billing_address]" ?>
    <?php $this->render('addr/_form') ?>
</fieldset>

<fieldset>
    <legend>Shipping</legend>
    <?php $this->field_prefix = "customer[shipping_address]" ?>
    <?php $this->render('addr/_form') ?>
</fieldset>
