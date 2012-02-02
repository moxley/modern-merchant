<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php $this->render('mminstall/results'); ?>

<h2>Step 4: Plugins and Site Data</h2>

<?php if ($this->plugin_checker->isUpgrade()): ?>
<p>The Modern Merchant plugins are about to be upgraded. Please back up your database before continuing.</p>
<p>This may take a while if you have lots of images.</p>
<?php else: ?>
<p>The database data is about to be added. <span style="text-decoration:underline;color:red">This
    will erase all previous Modern Merchant data
    in the database <i><?php print h(mm_getConfigValue('database.name')) ?></i></span>.</p>
<?php endif ?>

<form method="post" action="?a=mminstall.installPlugins">
    <?php if ($this->plugin_checker->isUpgrade()): ?>
    <p>
        <input type="submit" name="upgrade" value="Upgrade Modern Merchant Plugins &gt;" />
    </p>
    Or, <span style="color: red">erase existing database</span> and add fresh data:
    <?php endif ?>
    <p><input type="submit" value="Install Plugins and Site Data &gt;" /></p>
</form>
