<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr>
        <td valign="top">
            <form name="form1" method="post" action="<?php ph($this->adminBaseUrl()) ?>">
                <?php echo $this->hiddenFieldTag('id', $this->setting->id) ?>
                <table width="100%" border="0" cellpadding="2" class="dataTable">
                    <tr> 
                        <td class="formRowTitle">Name</td>
                        <td class="formRowValues">
                            <?php echo $this->textField('setting[name]') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="formRowTitle">Value</td>
                        <td class="formRowValues">
                            <?php echo $this->textArea('setting[value]', array('size' => '40x10', 'wrap' => 'virtual')) ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <?php if(!$this->setting->id): ?>
                            <input type="submit" name="actions[setting.add]" value="Add" />
                            <?php else: ?>
                            <input type="submit" name="actions[setting.update]" value="Commit"/ >
                            <?php endif ?>
                            &nbsp;
                            <input type="reset" name="" value="Reset" />
                            &nbsp;
                            <input type="submit" name="actions[setting.cancel]" value="Cancel" />
                            &nbsp;
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
