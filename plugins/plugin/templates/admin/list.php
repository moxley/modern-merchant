<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<table class="records">
    <thead>
        <tr>
            <td>Plugin</td>
            <td>Installed</td>
            <td>Active</td>
            <td>Priority</td>
            <td>Settings</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->plugins as $plugin): ?>
        <tr>
            <td><?php ph($plugin->name); ?></td>
            <td><?php ph($plugin->installed ? 'Yes' : 'No'); ?></td>
            <td><?php ph($plugin->active ? 'Yes' : 'No'); ?></td>
            <td><?php ph($plugin->priority); ?></td>
            <td>
                <?php echo $this->linkTag('settings', "?action=plugin_admin.edit&name=$plugin->name"); ?>

                <?php if ($plugin->installed): ?>
                <?php echo $this->linkTag('uninstall', "?action=plugin_admin.uninstall&name=$plugin->name"); ?>
                <?php else: ?>
                <?php echo $this->linkTag('install', "?action=plugin_admin.install&name=$plugin->name"); ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
