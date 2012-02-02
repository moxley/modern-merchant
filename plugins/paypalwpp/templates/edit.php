<?php
/**
 * @package paypalwpp
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<fieldset>
    <legend>Sandbox/Live Mode</legend>
    <p>Do you want to run this module in Sandbox (test) or Live mode?</p>
    <p>
        <?php echo $this->radioButton('payment_method[environment]', 'sandbox') ?>
        <label for="payment_method_environment_sandbox">Sandbox (test) mode</label><br />

        <?php echo $this->radioButton('payment_method[environment]', 'live') ?>
        <label for="payment_method_environment_live">Live mode</label>
    </p>
</fieldset>
<br />

<fieldset>
    <legend>Sandbox (test) Environment</legend>
     <p>You can ignore this section if you do not want to test <?php ph($this->payment_method->title) ?> in the Sandbox environment.</p>

    <h3>Your Sandbox Username</h3>
    <p>
        <?php echo $this->textField('payment_method[sandbox][api_username]', array('size' => 40)) ?>
    <p>

    <h3>Your Sandbox Password</h3>
    <p>
        <?php echo $this->textField('payment_method[sandbox][api_password]', array('size' => 40)) ?>
    <p>

    <h3>Your Sandbox Signature</h3>
    <p>
        <?php echo $this->textField('payment_method[sandbox][api_signature]', array('size' => 60)) ?>
    <p>

<?php if (false): ?>
    <h3>Your Sandbox certificate</h3>
    <p>
        <code>mm/plugins/paypalwpp/certs/</code>
        <select name="payment_method[sandbox][certificate]">
            <option value="">-- Select --</option>
            <?php echo $this->selectOptions('payment_method[sandbox][certificate]', $this->payment_method->certificates) ?>
        </select>
    </p>
<?php endif; ?>
</fieldset>
<br />

<fieldset>
    <legend>Live Environment</legend> 
    <h3>Your Live username</h3>
    <p>
        <?php echo $this->textField('payment_method[live][api_username]', array('size' => 40)) ?>
    <p>

    <h3>Your Live password</h3>
    <p>
        <?php echo $this->textField('payment_method[live][api_password]', array('size' => 40)) ?>
    <p>

    <h3>Your Live Signature</h3>
    <p>
        <?php echo $this->textField('payment_method[live][api_signature]', array('size' => 60)) ?>
    <p>

<?php if (false): ?>
    <h3>Your Live certificate</h3>
    <p>
        <code>mm/plugins/paypalwpp/certs/</code>
        <select name="payment_method[live][certificate]">
            <option value="">-- Select --</option>
            <?php echo $this->selectOptions('payment_method[live][certificate]', $this->payment_method->certificates) ?>
        </select>
    </p>
<?php endif ?>

</fieldset>
