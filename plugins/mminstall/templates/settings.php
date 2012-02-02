<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php $this->render('mminstall/results'); ?>

<h2>Step 5: Basic Settings</h2>

<form method="post" action="?a=mminstall.settings">
    <div class="row" style="margin-bottom: 10px">
        <label style="display:block;font-weight:bold">Web Site Name</label>
        <?php echo $this->textField('settings[site_name]') ?>
    </div>
    <div class="row" style="margin-bottom: 10px">
        <label style="display:block;font-weight:bold">Email Notifications</label>
        <?php echo $this->textField('settings[email]') ?>
    </div>
    <div class="row" style="margin-bottom: 10px">
        <input type="submit" value="Save &gt;&gt;" />
    </div>
</form>
