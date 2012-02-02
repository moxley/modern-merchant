<?php
/**
 * Upgrade multiple plugins.
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

require 'init.php';

$manager = new plugin_Manager;

$names = array('payment',
    'authnet',
    'paypal',
    'access',
    'addr',
    'admin',
    'auth',
    'authnet',
    'bulkimages',
    'cart',
    'catalog',
    'category',
    'content',
    'customer',
    'db',
    'mm',
    'media',
    'order',
    'paypalwpp',
    'pricing',
    'product',
    'sess',
    'setting',
    'shipping',
    'user'
    );

foreach($names as $name) {
    $plugin = $manager->getPluginForName($name);
    if (!$plugin) {
        fprintf(STDERR, "Plugin '$name' does not exist.\n");
        exit(1);
    }

    $result = $plugin->managedUpgrade();
    if (!$result) {
        fprintf(STDERR, "One or more errors occurred for plugin '{$plugin->name}'\n");
        foreach ($plugin->errors as $error) {
            fprintf(STDERR, "Error: $error\n");
        }
        exit(1);
    }

    echo "Upgraded plugin {$plugin->name}\n";
}

echo "Done.\n";
