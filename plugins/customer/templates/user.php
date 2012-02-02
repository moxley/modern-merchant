<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

?>
<?php $this->title = "Your Account Details" ?>
<?php $this->renderToArea('.secondLevelNav .area1', 'customer/_nav') ?>

<form method="POST" action="?a=customer.user">
    <div class="row">
        <label for="customer_user_username">Username:</label>
        <?php ph($this->customer->user->username) ?>
    </div>

    <div class="row">
        <label for="customer_billing_email">Email</label>
        <?php echo $this->textField('customer[billing][email]') ?>
    </div>

    <div class="row">
        <input type="submit" value="Update" />
    </div>
</form>
