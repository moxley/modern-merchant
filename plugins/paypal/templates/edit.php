<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<h3>Setup Instructions</h3>

<p>You will need to make some changes to your PayPal account settings
    for the paypal payment method to work with your account:</p>
<ol>
    <li>Log into your account. You should be at the Account Overview page.</li>
    <li>Click on &quot;edit profile&quot; under your Account Overview</li>
    <li>You should now be on the Profile Summary page. Click on &quot;Instant Payment Notification&quot;
        under &quot;Selling Preferences&quot;.</li>
    <li>Follow the directions for activating Instant Payment Notification for your account.</li>
    <li>Set the &quot;Notification URL&quot; to
        <code><?php $this->writeUrl(array('url' => mm_getConfigValue('urls.mm_root') . 'mm/plugins/paypal/ipn.php', 'schema' => 'https', 'absolute' => true)) ?></code>
        and save the setting.</li>
    <li>Go back to the &quot;Profile Summary&quot;</li>
    <li>Under &quot;Selling Preferences&quot;, click &quot;Website Payment Preferences&quot;</li>
    <li>Set &quot;Auto Return&quot; to 'On'</li>
    <li>Set the &quot;Return URL&quot; to
        <?php $this->writeUrl(array('action' => 'cart.postOrderPage', 'schema' => 'https', 'absolute' => true)) ?></li>
    <li>Save the setting.</li>    
</ol>

<h3>Test Mode</h3>
<p>Do you want to run PayPal IPN in test mode?</p>
<p>
    <?php echo $this->radioButton('payment_method[test_mode]', true) ?>
    <label for="payment_method_test_mode_1">Test Mode: Shoppers will be directed to a dummy PayPal page to submit payment</label><br />

    <?php echo $this->radioButton('payment_method[test_mode]', false) ?>
    <label for="payment_method_test_mode_0">Live</label>
</p>

<h3>Account Address</h3>
<p>The PayPal account email address</p>
<p><?php echo $this->textField('payment_method[account_email]', array('size' => 40)) ?></p>

<h3>IPN Notification Address</h3>
<p>The e-mail address that will receive an email when an IPN has been sent.</p>
<p><?php echo $this->textField('payment_method[ipn_notify]', array('size' => 40)) ?></p>

<h3>Transaction Currency</h3>
<p>The currency to use for credit card transactions</p>
<p>
    <?php foreach ($this->payment_method->currency_types as $currency): ?>
    <?php   echo $this->radioButton('payment_method[currency]', $currency) ?>
    <label for="payment_method_currency_<?php ph($currency) ?>"><?php ph($currency) ?></label><br />
    <?php endforeach ?>
</p>
