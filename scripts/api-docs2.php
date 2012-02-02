<?php
require 'phpDocumentor/Setup.inc.php';

$phpdoc = new phpDocumentor_setup;

$settings = array(
    'title'              => "Modern Merchant API",
    'target'             => dirname(dirname(__FILE__)) . '/api-docs',
    'directory'          => dirname(dirname(__FILE__)) . '/htdocs',
    'sourcecode'         => 'on',
    'defaultpackagename' => 'modernmerchant'
);

if (@$_SERVER['argv'][1]) {
    $settings['directory'] = $_SERVER['argv'][1];
}

$version = trim(file_get_contents($settings['directory'] . '/mm/conf/version.txt'));
$settings['title'] = "Modern Merchant $version API";

global $_phpDocumentor_setting;
foreach ($settings as $key=>$value) {
    $_phpDocumentor_setting[$key] = $value;
}

$phpdoc->readCommandLineSettings();

$phpdoc->setTitle($settings['title']);
$phpdoc->setTargetDir($settings['target']);
$phpdoc->setDirectoriesToParse($settings['directory']);

$phpdoc->setupConverters();
$phpdoc->createDocs();
