<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php $this->render('mminstall/results'); ?>

<h2>Step 3: Database Connection</h2>

<form method="post" action="?a=mminstall.databaseSettings">
    <table>
        <tr>
            <td class="row-title"><label>Database name:</label></td>
            <td>
                <?php echo $this->textField('database[name]') ?>
            </td>
        </tr>
        <tr>
            <td class="row-title"><label>Database host:</label></td>
            <td>
                <?php echo $this->textField('database[host]') ?>
            </td>
        </tr>
        <tr>
            <td class="row-title"><label>Database port:</label></td>
            <td>
                <?php echo $this->textField('database[port]') ?>
            </td>
        </tr>
        <tr>
            <td class="row-title"><label>Database username:</label></td>
            <td>
                <?php echo $this->textField('database[user]') ?>
            </td>
        </tr>
        <tr>
            <td class="row-title"><label>Database password:</label></td>
            <td>
                <?php echo $this->textField('database[password]') ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center">
                <input type="submit" value="Check Connection &gt;" />
            </td>
        </tr>
    </table>
</form>

