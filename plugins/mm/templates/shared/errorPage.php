<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
    <form action="<?php $this->writeUrl(array('type'=>'cartAction', 'action'=>'sendErrorComment')); ?>" method="post">
        <input type="hidden" name="error_id" value="<?php ph(gv($this->output, 'error_id')) ?>" />
        <table border="0" cellspacing="2" cellpadding="5">
            <tr>
                <td colspan="2">
                    <p>To help us better assist you, please enter an 
                    email address so that we might contact you regarding
                    this error.</p>
                </td>
            </tr>
            <tr>
                <td>E-mail Address</td>
                <td><input type="text" name="email" size="30"></td>
            </tr>
            <tr>
                <td>Comment</td>
                <td><textarea name="comment" cols="40" rows="5" wrap="virtual"></textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" value="submit">
                </td>
            </tr>
        </table>
    </form>
