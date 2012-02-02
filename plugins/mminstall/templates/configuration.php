<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php $this->render('mminstall/results'); ?>

<h2>Step 6: Save Configuration</h2>

<form method="post" action="?a=mminstall.configuration">
<h3>Do you want to run Modern Merchant in debug mode?</h3>
<p>Debug mode sets up a number of configuration values to display extra debugging information
    and enable logging. It is useful for developers. Some of these settings
    are not safe in a live, production web site. See the configuration file
    at mm/conf/config.php, after this step, for more details.</p>
<p>
    <?php echo $this->checkBoxTag('debug_mode', '1', false) ?>
    <label for="debug_mode_1">Enable debugging mode.</label>
</p>

    <p><input type="submit" value="Write configuration file &gt;" /></p>
</form>
