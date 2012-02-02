<?php
/**
 * Reinstall a plugin
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

require 'init.php';

$name = @$_SERVER['argv'][1];

if (!$name) {
    fprintf(STDERR, "Usage: php scripts/reinstall_plugin.php NAME\n");
    exit(2);
}

$manager = new plugin_Manager;
$plugin = $manager->getPluginForName($name);
if (!$manager->installRaw($plugin)) {
    foreach ($manager->errors as $error) {
        fprintf(STDERR, "  Error: {$error}\n");
    }
    exit(2);
}

echo "Reinstalled $name\n";
exit(0);
