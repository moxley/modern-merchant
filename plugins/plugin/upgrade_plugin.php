<?php
/**
 * Upgrade a plugin.
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

require 'init.php';

$name = @$_SERVER['argv'][1];

if (!$name) {
    fprintf(STDERR, "Usage: php scripts/upgrade_plugin.php NAME\n");
    exit(2);
}

$manager = new plugin_Manager;
$plugin = $manager->getPluginForName($name);
if (!$plugin) {
    fprintf(STDERR, "Plugin '$name' does not exist.\n");
    exit(1);
}

$result = $plugin->managedUpgrade();
if (!$result) {
    foreach ($plugin->errors as $error) {
        fprintf(STDERR, "$error\n");
    }
    exit(1);
}

echo "Upgraded plugin {$plugin->name}\n";
