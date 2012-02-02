<?php
/**
 * The bootstrap script.
 *
 * @package initialization
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

try {
    if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

    if (!defined('MM_LIB')) {
        if (!isset($_SERVER['MM_LIB'])) {
            define('MM_LIB', dirname(__FILE__));
        } else {
            define('MM_LIB', $_SERVER['MM_LIB']);
        }
    }

    if (!defined('MM_ROOT')) {
        define('MM_ROOT', dirname('MM_LIB'));
    }

    // Time tracking
    $GLOBALS['MM_START_TIME'] = microtime(true);
    $GLOBALS['MM_REQUEST_ID'] = uniqid('req_', true);
    
    // Configuration file path
    global $MM_CONFIG;
    if (!isset($MM_CONFIG)) $MM_CONFIG = array();
    define('MM_CONFIG_FILE', dirname(__FILE__).'/conf/config.php');

    // Define the global functions
    require_once(dirname(__FILE__).'/plugins/mm/tools.php');

    // Include the configuration file
    if (!definedAndTrue('MM_INSTALLER')) {
        ob_start();
        if (!include_once(MM_CONFIG_FILE)) {
            ob_end_clean();
            include(dirname(__FILE__) . '/plugins/mm/templates/under-maintenance.php');
            exit;
        }
        $contents = ob_get_contents();
        if ($contents) {
            ob_end_flush();
        }
        else {
            ob_end_clean();
        }
    }
    
    // Timezone
    if (isset($MM_CONFIG['timezone'])) date_default_timezone_set($MM_CONFIG['timezone']);
    
    // Set up logging
    ini_set('log_errors', $MM_CONFIG['debug.logging']);
    ini_set('error_log',  $MM_CONFIG['filepaths.general_log']);
    
    ini_set('display_errors', false);

    define('REQUEST_ID', uniqid('req'));
    
    mm_log("\n**************************************************\n"
        . "****************** NEW REQUEST *******************\n"
        . "**************************************************\n"
        . "  REQUEST_URI:    " . $_SERVER['REQUEST_URI'] . "\n"
        . "  REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n"
        . "  REMOTE_ADDR:    " . $_SERVER['REMOTE_ADDR'] . "\n"
        . "  HTTP_REFERER:   " . @$_SERVER['HTTP_REFERER'] . "\n"
        . "  Parameters:     " . var_export($_REQUEST, true));
    
    mm_registerShutdown('mm_logExecutionTime');
    register_shutdown_function('mm_shutdown');
    
    // Do we need to go to the installer?
    if (!definedAndTrue('MM_INSTALLER') && is_file(MM_ROOT . '/mminstall.php')) {
        $new_version = mm_version(true);
        $existing_version = mm_version();
        if ($new_version != $existing_version) {
            include(MM_LIB . '/plugins/mm/templates/under-maintenance.php');
            exit;
        }
    }
    
    // Class autoloader
    mm_initializeAutoloaderIfNotInitialized();
    
    // Add the Modern Merchant classes and functions to the PHP include path
    mm_addIncludePathsIfNotAdded();
    
    // Set up the error handler
    if (!defined('MM_NO_ERROR_HANDLER')) {
        if (definedAndTrue('MM_INSTALLER')) {
            //$mm_error_handler = new mminstall_ErrorHandler;
        }
        else {
            require MM_LIB . '/plugins/mvc/ErrorHandler.php';
            global $mm_error_handler;
            $mm_error_handler = new mvc_ErrorHandler;
            $mm_error_handler->activate();
        }
    }
    
    // Detect and set demo mode
    if( !empty($_SERVER['MM_DEMO_MODE']) ) {
        define('MM_DEMO_MODE', TRUE);
    }
    else {
        define('MM_DEMO_MODE', FALSE);
    }
    
    // Load plugins
    if (mm_getConfigValue('environment') != 'test' && !definedAndTrue('MM_INSTALLER')) {
        // Note: this opens a database connection
        mm_loadPlugins();
    }
    
    // Start the session
    //if (mm_getConfigValue('environment') != 'test') {
    if (isset($_SERVER['HTTP_HOST'])) {
        mm_getSession();
    }
    
}
catch (Exception $e) {
    print "<pre>" . h($e->getMessage()) . "</pre>";
}
