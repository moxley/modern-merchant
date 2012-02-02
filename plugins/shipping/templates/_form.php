<?php
/**
 * @package shipping
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<form name="form1" method="post" action="<?php ph($this->adminBaseUrl()) ?>">
    <?php echo $this->hiddenFieldTag('id', $this->shipping_method->id) ?>
    <table width="100%" border="0" cellpadding="2" class="dataTable">
        <?php if ($this->shipping_method->id): ?>
        <tr> 
            <td class="formRowTitle">ID</td>
            <td class="formRowValues"> 
                <?php ph($this->shipping_method->id); ?>
            </td>
        </tr>
        <?php endif ?>
        <tr> 
            <td class="formRowTitle">Name</td>
            <td class="formRowValues">
                <?php echo $this->textField('shipping_method[name]', array('size' => 40)) ?>
            </td>
        </tr>
        <tr> 
            <td class="formRowTitle">Cost Function</td>
            <td class="formRowValues" style="background-color: #FFFFFF">
        
                <code>function getShippingCost($cart) {</code>
                <br />
                <nobr>&nbsp;&nbsp;<?php echo $this->textArea('shipping_method[calc]', array('size' => '60x5'))?></nobr>
                <br><code>}</code>
                </span>
            
            </td>
        </tr>
        <tr>
            <td class="formRowTitle">Is active (available to customers)?</td>
            <td class="formRowValues">
                <?php echo $this->checkBox('shipping_method[active]')?>
            </td>
        </tr>
        <tr>
            <td class="formRowTitle">Set as the default method</td>
            <td class="formRowValues">
                <?php echo $this->checkBox('shipping_method[is_default]')?>
            </td>
        </tr>
        <tr>
            <td class="formRowTitle">Sort Order</td>
            <td class="formRowValues">
                <?php echo $this->textField('shipping_method[sortorder]', array('size' => 3))?>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center">
                <?php if(!$this->shipping_method->id): ?>
                <input type="submit" name="actions[shipping.add]" value="Add">
                <?php else: ?>
                <input type="submit" name="actions[shipping.update]" value="Commit">
                <?php endif ?>
                &nbsp; 
                <input type="reset" name="" value="Reset">
                &nbsp; 
                <input type="submit" name="actions[shipping.cancel]" value="Cancel">
                &nbsp; 
            </td>
    </table>
</form>
