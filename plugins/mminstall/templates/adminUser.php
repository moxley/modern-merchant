<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php $this->render('mminstall/results'); ?>

<h2>Step 5: Administrator User</h2>

<script type="text/javascript">
    function doSkipAdministrator() {
        document.forms[0]._action.value = 'skipAdministrator';
        document.forms[0].submit();
    }
</script>
<form method="post" action="?a=mminstall.adminUser">
    <?php if ($this->has_existing) { ?>
    <p>
        If this is an upgrade, and you have chosen to keep your database data,
        you may skip this step.<br />
        <input type="button" onclick="doSkipAdministrator()" value="Final Step: Skip Administrator &gt;" />
    </p>
    <?php } ?>
    
    <p>Choose a username and password for the initial administrator:</p>
    
    <table>
        <tr>
            <td class="row-title"><label>Username:</label></td>
            <td>
                <?php echo $this->textField('admin[username]') ?>
            </td>
        </tr>
        <tr>
            <td class="row-title"><label>Password:</label></td>
            <td>
                <?php echo $this->textField('admin[new_password]') ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center">
                <input type="submit" value="Add Administrator &gt;" />
            </td>
        </tr>
    </table>
</form>
