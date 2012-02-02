<?php
/**
 * Installer initialization.
 *
 * @package mminstall
 */

define('MM_INSTALLER', true);
define('MM_LIB', dirname(dirname(dirname(__FILE__))));
define('DS', DIRECTORY_SEPARATOR);

$GLOBALS['CONFIG_KEYS_TO_SUBSTITUTE'] = array(
    'version',
    'rewrites.enabled',
    'urls.http',
    'urls.https',
    'urls.mm_root',
    'database.name',
    'database.user',
    'database.password',
    'database.host',
    'database.port',
    'database.type',
    'debug.logging',
    'debug.show_exception_trace',
    'debug.email_errors'
);

function mm_loadPreviousConfig() {
    define('MM_CONFIG_FILE', MM_LIB . '/conf/config.php');
    $GLOBALS['MM_CONFIG_OLD'] = array();
    if (file_exists(MM_CONFIG_FILE)) {
        include MM_CONFIG_FILE;
        $GLOBALS['MM_CONFIG_OLD'] = $GLOBALS['MM_CONFIG'];
        unset($GLOBALS['MM_CONFIG']);
    }
}

function mm_loadInstallationConfig() {
    include MM_LIB . '/conf/config_tpl.php';
    if (!isset($_SESSION['MM_CONFIG'])) {
        foreach ($GLOBALS['CONFIG_KEYS_TO_SUBSTITUTE'] as $key) {
            if (array_key_exists($key, $GLOBALS['MM_CONFIG_OLD'])) {
                $GLOBALS['MM_CONFIG'][$key] = $GLOBALS['MM_CONFIG_OLD'][$key];
            }
            else {
                $GLOBALS['MM_CONFIG'][$key] = null;
            }
        }
        $GLOBALS['MM_CONFIG']['kernel.active'] = false;
        if (@$_REQUEST['debug']) {
            $GLOBALS['MM_CONFIG']['debug.logging'] = '1';
            $GLOBALS['MM_CONFIG']['debug.show_exception_trace'] = '1';
        }
        $_SESSION['MM_CONFIG'] = $GLOBALS['MM_CONFIG'];
        $GLOBALS['MM_CONFIG_NEW'] = $GLOBALS['MM_CONFIG'];
    }
    else {
        $GLOBALS['MM_CONFIG'] = $_SESSION['MM_CONFIG'];
    }
}

function mm_setNewConfigValue($key, $value) {
    $_SESSION['MM_CONFIG'][$key] = $value;
    $GLOBALS['MM_CONFIG'][$key] = $value;
}

//function mm_getNewConfigValue($key, $default = null) {
//    return array_key_exists($key, $_SESSION['MM_CONFIG'])
//        ? $_SESSION['MM_CONFIG'][$key]
//        : $default;
//}

include_once MM_LIB . '/plugins/mm/tools.php';

// Class autoloader
mm_initializeAutoloaderIfNotInitialized();

// Add the Modern Merchant classes and functions to the PHP include path
mm_setConfigValue('filepaths.plugins', dirname(dirname(__FILE__)));
mm_addIncludePathsIfNotAdded();

// Start file-based session
if ($_SERVER['HTTP_HOST']) {
    session_start();
    define('MM_SESSION_STARTED', true);

    if (!getAction()) {
        foreach ($_SESSION as $k=>$v) {
            unset($_SESSION[$k]);
        }
    }
}

mm_loadPreviousConfig();
mm_loadInstallationConfig();

// Logging
ini_set('log_errors', $MM_CONFIG['debug.logging']);
ini_set('error_log',  $MM_CONFIG['filepaths.general_log']);
mm_log("\n\n---\nmminstall, line " . __LINE__);
mm_log("kernel.active: " . (mm_getConfigValue('kernel.active') ? "YES" : "NO"));

mm_getSession(); // Advanced session management
