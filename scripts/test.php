<?php
/**
 * Run tests.
 *
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Call Modern Merchant initialization script and set up include_path for testing.
 */
function initializeTestMode()
{
    global $MM_CONFIG;
    
    define('MM_TEST_MODE', true);
    define('MM_NO_ERROR_HANDLER', true);
    if (!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = 'localhost';
    if (!isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '/';
    if (!isset($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
    if (!isset($_SERVER['QUERY_STRING'])) $_SERVER['QUERY_STRING'] = "x=1";
    if (!defined('PHPUnit_MAIN_METHOD')) {
        define('PHPUnit_MAIN_METHOD', 'mm_Test::main');
    }

    $orig_include_path = ini_get('include_path');
    require_once dirname(__FILE__) . '/../conf/config.php';
    $MM_CONFIG['environment'] = 'test';
    $MM_CONFIG['filepaths.general_log'] = dirname($MM_CONFIG['filepaths.general_log']) . "/mm_test.log";
    require_once dirname(__FILE__) . '/../plugins/mm/tools.php';

    list($argv, $options) = extractOptions($_SERVER['argv']);
    foreach ($options as $name=>$value) {
        $MM_CONFIG[$name] = $value;
    }

    require_once 'init.php';
    
    $MM_CONFIG['emails.enabled'] = false;
    
    $paths = explode(PATH_SEPARATOR, ini_get('include_path'));
    $new_paths = array();
    foreach ($paths as $path) {
        //if (!strpos($path, 'pear') && $path != '.') $new_paths[] = $path;
        if ($path != '.') $new_paths[] = $path;
    }
    $include_path = $orig_include_path . PATH_SEPARATOR . implode(PATH_SEPARATOR, $new_paths);
    ini_set('include_path', $include_path);
    
    // Error reporting
    ini_set('error_reporting', E_ALL | E_STRICT);
    ini_set('display_errors', true);
    
}

initializeTestMode();

if (PHPUnit_MAIN_METHOD == 'mm_Test::main') {
    mm_TestSuite::main($_SERVER['argv']);
}

