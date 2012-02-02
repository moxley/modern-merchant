<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<p>
    To configure the Authorize.net payment method for your merchant account, log in to
    authorize.net, go to Settings, click on API Login ID and Transaction Key, and provide
    the information they ask. Enter the resulting API Login ID and Transaction Key in
    the fields below.
</p>

<h3>Authorize.net API Login ID</h3>
<p>Your Authorize.net <u>API</u> Login ID</p>
<p>
    <?php echo $this->textField('payment_method[account_id]') ?>
<p>

<h3>Authorize.net Transaction Key</h3>
<p>Your Authorize.net API Login Transaction Key</p>
<p>
    <?php echo $this->textField('payment_method[tran_key]') ?>
<p>

<h3>Transaction Mode</h3>
<p>Do you want to run this module in test mode?</p>
<p>
    <?php echo $this->radioButton('payment_method[test_mode]', 'hard_test') ?> Hard Test: Connect to dummy gateway; ignore your credentials; all transactions will pass<br />
    <?php echo $this->radioButton('payment_method[test_mode]', 'soft_test') ?> Soft Test: Connect to Authorize.net gateway with your credentials<br />
    <?php echo $this->radioButton('payment_method[test_mode]', false) ?> Live<br />
</p>
<div style="margin-top: 10px">
    Test credit card numbers for Soft Test mode:
    <pre style="background-color: #fff; width: 280px">
370000000000002  American Express  
6011000000000012 Discover  
5424000000000015 MasterCard  
4007000000027    Visa</pre>
</div>

<h3>Transaction Type</h3>
<p>Do you want to capture the payment immediately, or just authorize and hold it for later?</p>
<p>
    <?php echo $this->radioButton('payment_method[trans_type]', 'AUTH_CAPTURE') ?> Authorize and Capture<br />
    <?php echo $this->radioButton('payment_method[trans_type]', 'AUTH_ONLY') ?> Authorize Only
</p>

<h3>Customer Notifications</h3>
<p>Should Authorize.net send a separate e-mail receipt to the customer, in addition to the Modern Merchant order receipt?</p>
<p>
    <?php echo $this->radioButton('payment_method[email_receipt]', true) ?> Send Authorize.net receipt<br />
    <?php echo $this->radioButton('payment_method[email_receipt]', false) ?> Send order receipt only
</p>
