<?php
/**
 * CLI-based installer.
 *
 * @package scripts
 */

define('MM_INSTALLER', true);
define('MM_LIB', dirname(dirname(__FILE__)));
$GLOBALS['MM_CONFIG'] = array('filepaths.plugins' => MM_LIB . '/plugins');
include dirname(__FILE__) . '/../init.php';
include 'plugins/mminstall/cli.php';
