<?php
/**
 * @package plugin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<form method="POST" action="?action=plugin_admin.update&amp;name=<?php ph($this->plugin->name) ?>">
    <div>
        <?php echo $this->formFields($this->plugin->getFormFields()); ?>
        <input type="submit" value="Save" />
        <input type="button" value="Cancel" onclick="window.location='?action=plugin_admin.list'" />
    </div>
</form>
