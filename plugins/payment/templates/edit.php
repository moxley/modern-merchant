<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<style type="text/css">
h3 { font-size: 10pt; margin-bottom: 0px; padding-bottom: 0px; }
p { margin: 0px; padding: 0px; }
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr> 
        <td valign="top">
            <form name="form1" method="post" action="<?php ph($this->adminBaseUrl()) ?>?a=payment.update">
                <?php echo $this->hiddenFieldTag('id', $this->payment_method->id) ?>
                <table width="100%" border="0" cellpadding="2" class="dataTable">
                    <?php if ($this->id): ?>
                    <tr> 
                        <td class="formRowTitle" width="100">ID</td>
                        <td class="formRowValues"> 
                            <?php ph($this->id); ?>
                        </td>
                    </tr>
                    <?php endif ?>
                    <tr> 
                        <td class="formRowTitle"><h1><?php ph($this->payment_method->title); ?> Payment Method</h1></td>
                        <td class="formRowValues"> 
                        </td>
                    </tr>
                    <tr>
                        <td class="formRowValues" colspan="2">
                            <p></p>
                            <h3>Enable <?php ph($this->payment_method->title) ?> Module</h3>
                            <p>Do you want to accept <?php ph($this->payment_method->title) ?> payments?</p>
                            <p>
                                <?php echo $this->radioButton('payment_method[active]', true) ?>
                                <label for="payment_method_active_1">Enable</label><br>

                                <?php echo $this->radioButton('payment_method[active]', false) ?>
                                <label for="payment_method_active_0">Disable</label>
                            </p>
                            
                            <div>
                                <h3>Public Name</h3>
                                <p>The name of this payment method as seen on the payment page</p>
                                <p>
                                    <?php echo $this->textField('payment_method[public_title]') ?>
                                </p>
                            </div>
                            
                            <?php print $this->settings_form_html; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <h3>Sort order of display</h3>
                            <p>Sort order of display. Lowest is displayed first.</p>
                            <p>
                                <?php echo $this->textField('payment_method[sortorder]', array('size' => 3)) ?>
                            </p>
                        </td>
                    </tr>
                    <tr> 
                        <td colspan="2" align="center">
                            <?php if(!$this->payment_method->id): ?>
                            <input type="submit" name="actions[payment.submitNew]" value="Add">
                            <?php else: ?>
                            <input type="submit" name="actions[payment.update]" value="Commit">
                            <?php endif ?>
                            &nbsp;
                            <input type="reset" name="" value="Reset">
                            &nbsp;
                            <input type="submit" name="actions[payment.cancel]" value="Cancel">
                            &nbsp;
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
