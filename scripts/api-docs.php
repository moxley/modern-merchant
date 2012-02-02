<?php
/**
 * Build PHP API documentation.
 *
 * This script requires phpDocumentor in your include_path.
 *
 * @package build
 */
require 'phpDocumentor/Setup.inc.php';

$phpdoc = new phpDocumentor_setup;

$settings = array(
    'title'              => "Modern Merchant API",
    'target'             => dirname(dirname(__FILE__)) . '/api-docs',
    'directory'          => dirname(dirname(__FILE__)) . '/htdocs',
    'sourcecode'         => 'on',
    'defaultpackagename' => 'modernmerchant',
    'ignore'             => '*/.svn/*'
);
$directory = dirname(dirname(__FILE__)) . '/htdocs';

if (@$_SERVER['argv'][1]) {
    $settings['directory'] = $_SERVER['argv'][1];
}

$version = trim(file_get_contents($directory . '/mm/conf/version.txt'));
$settings['title'] = "Modern Merchant $version API";

global $_phpDocumentor_setting;
foreach ($settings as $key=>$value) {
    $_phpDocumentor_setting[$key] = $value;
}

$phpdoc->readCommandLineSettings();

$phpdoc->setIgnore($settings['ignore']);
$phpdoc->setTitle($settings['title']);
$phpdoc->setTargetDir($settings['target']);
$phpdoc->setDirectoriesToParse($settings['directory']);
//$phpdoc->setFilesToParse(implode(',', findPhpFiles($directory)));

$phpdoc->setupConverters();
$phpdoc->createDocs();

function findPhpFiles($dir)
{
    $files = array();
    $d = dir($dir);
    while (($entry = $d->read()) !== false) {
        if ($entry == '.' || $entry = '..') continue;
        $file = $dir . '/' . $entry;
        if (is_dir($file)) {
            $files = array_merge(findPhpFiles($file));
        }
        else if (preg_match('/\.php$/', $entry)) {
            $files[] = $file;
        }
    }
    return $files;
}

