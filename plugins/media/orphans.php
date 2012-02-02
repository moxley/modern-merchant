<?php

/**
 * Remove orphaned images
 *
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

include_once('init.php');
$dbh = $MM_CONTEXT->getDatabase();
$key = 'filepaths.product_images';
$config = $MM_CONTEXT->getConfig();
$path = $config->get($key);
$d = dir($path);
for ($i=0; false !== ($product=$d->read()); $i++ ) {
    if ($product == '.' || $product == '..') continue;
    deleteImageIfNotExists("$path/$product");
}
$d->close();

function deleteImageIfNotExists($path) {
    global $dbh;
    // Get product id from path
    $name = basename($path);
    $parts = explode('.', $name);
    // filename format: "{$media->owner_abstract_type}.{$media->owner_id}.{$media->id}.{$media->ext}";
    
    if (count($parts) < 4) return;
    $id = intval($parts[2]);
    if ($id < 1) return;
    
    $query = 'select media_id from mm_media where id=' . intval($id);
    $one = $dbh->getOne($query);
    if (!$one) {
        print "Deleting $path for media id=$id\n";
        unlink($path);
    }
    return;
}
