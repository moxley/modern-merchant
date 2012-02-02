<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

function mm_version($from_file=false)
{
    if ($from_file) {
        return trim(file_get_contents(MM_LIB . '/version.txt'));
    }
    else {
        return mm_getConfigValue('version');
    }
}

function mm_shutdown()
{
    /*
     Simulate PHP 5.0.5 nonsense
    */
    global $MM_CONTEXT, $MM_CONFIG;
    unset($MM_CONTEXT);
    unset($MM_CONFIG);
        
    global $MM_SHUTDOWN_FUNCTIONS;
    if (!isset($MM_SHUTDOWN_FUNCTIONS) || !$MM_SHUTDOWN_FUNCTIONS) return;
    foreach ($MM_SHUTDOWN_FUNCTIONS as $f) {
        try {
            if (is_string($f)) {
                $f();
            }
            else if (is_array($f) && count($f) > 1) {
                $obj = $f[0];
                $method = $f[1];
                $obj->$method();
            }
        }
        catch (Exception $e) {
            trigger_error("Exception thrown in mm_shutdown(): " . $e->getMessage(), E_USER_WARNING);
        }
    }
}
    
function mm_registerShutdown($arg)
{
    global $MM_SHUTDOWN_FUNCTIONS;
    if (!isset($MM_SHUTDOWN_FUNCTIONS)) {
        $MM_SHUTDOWN_FUNCTIONS = array();
    }
    $MM_SHUTDOWN_FUNCTIONS[] = $arg; // function
}

function mm_logExecutionTime()
{
    $time = microtime(true) - $GLOBALS['MM_START_TIME'];
    mm_log("Request time: " . $time . " seconds (" . sprintf("%0.6f", 1/$time) . " requests/second)");
}

function mm_includeClass($class) {
    $load = str_replace( "_", "/", $class ) . ".php";
    $paths = explode(PATH_SEPARATOR, ini_get('include_path'));
    foreach ($paths as $path) {
        $full_path = "$path/$load";
        if (is_file($full_path)) {
            include_once($full_path);
            break;
        }
    }
}

function mm_initializeAutoloaderIfNotInitialized() {
    if (function_exists('__autoload')) return false;
    
    function __autoload( $class )
    {
        static $loaded = array();
            
        if (isset($loaded[$class])) return;
        mm_includeClass($class);
        //if (!include_once( $load )) {
        //    debug_print_backtrace();
        //    return;
        //}
        $all_classes = get_declared_classes();
        if (in_array($class, $all_classes)) {
            $loaded[$class] = TRUE;
        }
    }
    return true;
}

function mm_cleanUpIncludePath() {
  $paths = explode(PATH_SEPARATOR, ini_get('include_path'));
  $new_paths = array();
  foreach ($paths as $path) {
    if ($path) {
      $new_paths[] = $path;
    }
  }
  $include_path = implode(PATH_SEPARATOR, $new_paths);
  ini_set('include_path', $include_path);
  return $include_path;
}
    
function mm_addIncludePathsIfNotAdded() {
    static $mm_includePathsAdded = false;
    if ($mm_includePathsAdded) return false;

    mm_addIncludePath(MM_LIB);
    mm_addIncludePath(mm_getConfigValue('filepaths.plugins'));
    mm_cleanUpIncludePath();
        
    $mm_includePathsAdded = true;
    return true;
}

function mm_addIncludePath($path) {
    $paths = explode(PATH_SEPARATOR, ini_get('include_path'));
    if (!in_array($path, $paths)) {
        $paths[] = $path;
    }
    ini_set('include_path', implode(PATH_SEPARATOR, $paths));
}

function mm_getPluginObject($plugin)
{
    $class = $plugin . '_Plugin';
    $base = mm_getConfigValue('filepaths.plugins');
    if (!file_exists("$base/$plugin/Plugin.php")) {
        throw new Exception("File $base/$plugin/Plugin.php does not exist");
    }
    $obj = new $class;
    return $obj;
}

/**
 * Get the names of the installed plugins, sorted by priority.
 */
function mm_getInstalledPluginNames()
{
    $installed_names = array();
    $settings = mm_getSettingsAsAssoc();
    $priority_lookup = array();
    foreach ($settings as $name=>$value) {
        if (preg_match('/^plugins\.([^\.]+)\.priority$/', $name, $matches)) {
            $priority_lookup[$matches[1]] = $value;
        }            
    }
        
    foreach ($settings as $name=>$value) {
        if ($value && preg_match('/^plugins\.([^\.]+)\.installed$/', $name, $matches)) {
            $installed_names[] = $matches[1];
            if (!isset($priority_lookup[$matches[1]])) {
                $priority_lookup[$matches[1]] = 0;
            }
        }
    }

    asort($priority_lookup);
    $priority_lookup = array_reverse($priority_lookup, true);
    $ordered = array_keys($priority_lookup);
    $installed = array();
    foreach ($ordered as $name) {
        if (in_array($name, $installed_names)) $installed[] = $name;
    }
    return $installed;
}
    
function mm_loadPlugins() {
    $manager = new plugin_Manager;
    $manager->initializePlugins();
}

/**
 * The the plugin directories, sorted by alpha.
 */
function mm_getPluginDirs()
{
    $manager = new plugin_Manager;
    return $manager->getPluginDirs();
}

function mm_getAllPlugins()
{
    global $MM_CONFIG;
        
    $all = mm_getPluginDirs();
    $plugins_path = $MM_CONFIG['filepaths.plugins'];
    $plugins = array();
    foreach ($all as $entry) {
        $file = "$plugins_path/$entry/Plugin.php";
        if (is_file($file)) {
            mm_require_absolute_once($file);
            $class = $entry . '_Plugin';
            $priority = 0;
            if (class_exists($class, false)) {
                $plugin = new $class;
                if (method_exists($plugin, 'info')) {
                    $info = $plugin->info();
                    if (isset($info['priority'])) {
                        $priority = $info['priority'];
                    }
                }
                $plugins[$priority][] = $plugin;
            }
        }
    }
    ksort($plugins);
    $final = array();
    foreach ($plugins as $priority=>$plist) {
        foreach ($plist as $plugin) $final[] = $plugin;
    }
    return $final;
}

function mm_getPluginName($object)
{
    $classname = get_class($object);
    $parts = explode('_', $classname);
    return $parts[0];
}

function mm_getConfig() {
    global $MM_CONFIG;
    return new mm_Configuration($MM_CONFIG);
}
    
function mm_getConfigAsAssoc() {
    return $GLOBALS['MM_CONFIG'];
}

function mm_getDatabase() {
    global $MM_DATABASE_OBJ;
        
    if (!isset($MM_DATABASE_OBJ)) {
        $MM_DATABASE_OBJ = new db_Database;
    }
    return $MM_DATABASE_OBJ;
}

function mm_getConfigValue($name, $default=null) {
    return gv($GLOBALS['MM_CONFIG'], $name, $default);
}

function mm_setConfigValue($name, $value) {
    return $GLOBALS['MM_CONFIG'][$name] = $value;
}

function mm_getSettingsAsAssoc($offset='') {
    $dao = new setting_SettingDAO;
    $assoc = $dao->getAllAssoc();
    if ($offset) {
        $offset_with_dot = $offset . '.';
        $group = array();
        foreach ($assoc as $k=>$v) {
            if (startswith($k, $offset_with_dot)) {
                $group[substr($k, strlen($offset_with_dot))] = $v;
            }
        }
        return $group;
    }
    else {
        return $assoc;
    }
}

function mm_getSetting($name, $default=null, $force=false) {
    $dao = new setting_SettingDAO;
    return $dao->get($name, $default, $force);
}

function mm_setSetting($name, $value, $force=true) {
    $dao = new setting_SettingDAO;
    $dao->set($name, $value, $force);
}

function mm_removeSetting($name) {
    $dao = new setting_SettingDAO;
    $dao->deleteByName($name);
}

function mm_getSession() {
    global $MM_SESSION;
    if (!isset($MM_SESSION)) {
        $handler = new sess_SessionHandler;
        $MM_SESSION = $handler->start();
    }
    return $MM_SESSION;
}

function getMessages($type)
{
    $sess = mm_getSession();
    $messages = $sess->get("messages.$type");
    if (!$messages) {
        return array();
    }
    return $messages->messages;
}

function mm_addMessage($msg, $type)
{
    $sess = mm_getSession();
    $messages = $sess->get("messages.$type");
    if (!$messages) {
        $messages = new mvc_Messages;
        $sess->set("messages.$type", $messages);
    }
    $messages->messages[] = $msg;
}

function mm_parseAction($action) {
    if (!$action) return null;
    $parsed = array('query' => '', 'params' => array());
    $parts = explode('?', $action);
    if (count($parts) > 1) {
        $action = $parts[0];
        $parsed['query'] = $parts[1];
        $parsed['params'] = parseQueryString($parts[1]);
    }
    $parts = explode('.', $action);
    $parsed['controller'] = $parts[0];
    $parsed['action'] = gv($parts, 1);
    return $parsed;
}

function mm_actionToUri($action) {
    $parent_dir = mm_getConfigValue('urls.mm_root');
    $parsed = mm_parseAction($action);
    $params = $parsed['params'];
    if (gv($params, 'schema')) {
        $schema = getSchema();
        if ($schema != $params['schema']) {
            $base = mm_getConfigValue('urls.' . $schema) . $parent_dir;
        }
    }
    else {
        $base = $parent_dir;
    }
    $action_path = $parsed['controller'];
    if ($parsed['action']) $action_path .= '.' . $parsed['action'];
    return appendParamsToUrl($base . "?a=" . urlencode($action_path), $params);
}

function mm_getCart()
{
    $id = mm_getSession()->get('cart_id');
    $cart = null;
    if ($id) {
        $cart = mvc_Model::fetch('cart_Cart', $id);
        if ($cart && $cart->live) {
            $cart->id = $id;
        }
    }

    if (!$cart || !$cart->live) {
        $cart = new cart_Cart;
        $cart->sid = session_id();
    }

    return $cart;
}

function mm_setCart($cart)
{
    mm_getSession()->set('cart_id', $cart->id);
}

function mm_getUser() {
    global $MM_USER;
    if (!$MM_USER) {
        $sess = mm_getSession();
        $id = $sess->get('user_id');
        if ($id) {
            $dao = new user_UserDAO;
            $MM_USER = $dao->fetch($id);
        }
    }
    return $MM_USER;
}

function mm_setUser($user) {
    global $MM_USER;
    $MM_USER = $user;
    $sess = mm_getSession();
    if (!$MM_USER) {
        $sess->set('user_id', null);
    }
    else {
        $sess->set('user_id', $MM_USER->id);
    }
}

function mm_getCustomer() {
    global $MM_CUSTOMER;
    if (!$MM_CUSTOMER) {
        $sess = mm_getSession();
        $id = $sess->get('customer_id');
        if ($id) {
            $MM_CUSTOMER = customer_Customer::fetch($id);
        }
    }
    return $MM_CUSTOMER;
}

function mm_setCustomer($customer) {
    global $MM_CUSTOMER;
    $MM_CUSTOMER = $customer;
    $sess = mm_getSession();
    if (!$MM_CUSTOMER) {
        $sess->set('customer_id', null);
    }
    else {
        $sess->set('customer_id', $MM_CUSTOMER->id);
    }
}

function mm_backupDatabase($restore_file=null)
{
    global $MM_CONFIG;
    if (!$restore_file) {
        $version = mm_getSetting('version');
        $dir = $MM_CONFIG['filepaths.images.product'];
        $restore_file = "$dir/restore_" . $version. ".sql";
    }
    $db = $MM_CONFIG['database.name'];
    $u = $MM_CONFIG['database.user'];
    $p = $MM_CONFIG['database.password'];
    $h = $MM_CONFIG['database.host'];
    $command = "mysqldump -Q --add-drop-table -u$u -p$p -h$h $db > $restore_file";
    $ret_val = null;
    $message = system($command, $ret_val);
    if ($ret_val) throw new Exception("Database backup failed ($command): $message");
}
    
function mm_fixSequences()
{
    $dbh = mm_getDatabase();
    $table_rows = $dbh->getAllAssoc("show tables");
    $tables = array();
    foreach ($table_rows as $row) {
        $tables[] = array_pop($row);
    }
    foreach ($tables as $table) {
        if (endswith($table, '_seq')) continue;
        $desc = $dbh->getOneAssoc("describe $table");
        $id_col = $desc['Field'];
        $row = $dbh->getOneAssoc("select max($id_col) from $table");
        $max = array_pop($row);
        if (!$max) $max = 0;
        $seq_table = $table . "_seq";
        if (in_array($seq_table, $tables)) {
            $dbh->query("update $seq_table set id=$max");
        }
    }
}

function mm_tableHasColumn($table, $column)
{
    $dbh = mm_getDatabase();
    $all = $dbh->getAllAssoc("describe $table");
    foreach ($all as $row) {
        if ($row['Field'] == $column) return true;
    }
    return false;
}

function mm_addConfigValues($values) {
    global $MM_CONFIG;
    $file = MM_LIB . '/conf/config.php';
    if (!mm_is_writable($file)) {
        throw new Exception("Cannot write to $file");
    }
    $lines = file($file);
    $last_name = null;
    $last_value = null;
    $lines_out = array();
    foreach ($lines as $line) {
        if (strpos($line, '?>') === 0) {
            foreach ($values as $name=>$value) {
                $lines_out[] = "\t\$MM_CONFIG['$name'] = '$value';\n";
                $MM_CONFIG[$name] = $value;
            }
        }
        $lines_out[] = $line;
    }
    $f = fopen($file, 'w');
    foreach ($lines_out as $line) {
        fwrite($f, $line);
    }
    fclose($f);
}
    
function mm_require_once($script) {
    if ($script[0] == '/' || $script[1] == '\\') {
        return mm_require_absolute_once($script);
    }
    $fullPath = mm_expandPath($script);
    if ($fullPath) {
        $fullPath = realpath($fullPath);
        return mm_require_absolute_once($fullPath);
    } else {
        throw new Exception("Failed to find file to include: $script");
    }
}

function mm_expandPath($file)
{
    $paths = explode(PATH_SEPARATOR, ini_get('include_path'));
    foreach ($paths as $path) {
        $full_path = $path . DS . $file;
        if (!file_exists($full_path)) {
            continue;
        } else {
            return $full_path;
        }
    }
    return false;
}
    
function mm_require_absolute_once($script) {
    static $MM_REQUIRED_SCRIPTS;
    if (!isset($MM_REQUIRED_SCRIPTS)) $MM_REQUIRED_SCRIPTS = array();
    if (array_key_exists($script, $MM_REQUIRED_SCRIPTS)) {
        return $MM_REQUIRED_SCRIPTS[$script];
    }
    if (!is_readable($script)) {
        throw new Exception("Cannot read script: $script");
    }
    $MM_REQUIRED_SCRIPTS[$script] = include_once($script);
    return $MM_REQUIRED_SCRIPTS[$script];
}
    
function mm_time()
{
    return (double) time();
}

function mm_encodeSession($array)
{
    if (!$array) return '';
    $data = '';
    foreach ($array as $key=>$value)
    {
        $data .= $key . '|' . serialize($value);
    }
    return $data;
}

/**
 * From php.net:
 * @author bmorel@ssi.fr
 * @date 23-Aug-2005 12:54
 */
function mm_decodeSession($str)
{
    $str = (string)$str;

    $endptr = strlen($str);
    $p = 0;

    $serialized = '';
    $items = 0;
    $level = 0;

    while ($p < $endptr) {
        $q = $p;
        while ($str[$q] != '|')
            if (++$q >= $endptr) break 2;

        if ($str[$p] == '!') {
            $p++;
            $has_value = false;
        } else {
            $has_value = true;
        }

        $name = substr($str, $p, $q - $p);
        $q++;

        $serialized .= 's:' . strlen($name) . ':"' . $name . '";';

        if ($has_value) {
            for (;;) {
                $p = $q;
                switch ($str[$q]) {
                case 'N': /* null */
                case 'b': /* boolean */
                case 'i': /* integer */
                case 'd': /* decimal */
                    do $q++;
                    while ( ($q < $endptr) && ($str[$q] != ';') );
                    $q++;
                    $serialized .= substr($str, $p, $q - $p);
                    if ($level == 0) break 2;
                    break;
                case 'r':
                case 'R': /* reference  */
                    $c = $str[$q];
                    $q+= 2;
                    for ($id = ''; ($q < $endptr) && ($str[$q] != ';'); $q++) $id .= $str[$q];
                    $q++;
                    $serialized .= $c . ':' . ($id + 1) . ';'; /* increment pointer because of outer array */
                    if ($level == 0) break 2;
                    break;
                case 's': /* string */
                    $q+=2;
                    for ($length=''; ($q < $endptr) && ($str[$q] != ':'); $q++) $length .= $str[$q];
                    $q+=2;
                    $q+= (int)$length + 2;
                    $serialized .= substr($str, $p, $q - $p);
                    if ($level == 0) break 2;
                    break;
                case 'a': /* array */
                case 'O': /* object */
                    do $q++;
                    while ( $q < $endptr && $str[$q] != '{' );
                    $q++;
                    $level++;
                    $serialized .= substr($str, $p, $q - $p);
                    break;
                case '}': /* end of array|object */
                    $q++;
                    $serialized .= substr($str, $p, $q - $p);
                    if (--$level == 0) break 2;
                    break;
                default:
                    return false;
                }
            }
        } else {
            $serialized .= 'N;';
            $q+= 2;
        }
        $items++;
        $p = $q;
    }
    return @unserialize( 'a:' . $items . ':{' . $serialized . '}' );
}

/********************************************
    
String handling functions
    
********************************************/

function h($string) {
    return htmlspecialchars((string) $string);
}
function x($string) {
    return htmlspecialchars($string);
}
function ph($string) {
    print h($string);
}
function i($string) {
    return intval($string);
}
    
/**
 * Returns a string, quoted with single-quotes, or the string "NULL"
 *     
 * @param $value
 * @return string
 */
function dq($value) {
    if( $value === null ) return "NULL";
    if(!is_string($value)) $value = (string) $value;
        
    $search = array("\\", "'");
    $replace = array("\\\\", "\\'");
    $value = str_replace($search, $replace, $value);
        
    return "'". $value. "'";
}
    
/**
 * Database integer.
 * 
 * Returns guaranteed integer or NULL
 *     
 * @param $value
 * @return mixed int or boolean
 */
function di($value) {
    if ($value === null) return 'NULL';
    return intval($value);
}
    
function startswith($str, $find)
{
    return substr($str, 0, strlen($find)) == $find;
}
    
function endswith($str, $find)
{
    return substr($str, strlen($str)-strlen($find)) == $find;
}

/**
 * Get a value from an associative array.
 *
 * <p>This function will check the associative array for the 
 * key, and if the key does not exist, it will return the
 * default value-- parameter #3-- instead.</p>
 * <p>By using this to fetch values from associative arrays
 * where it is unclear whether the key exists, PHP 'Notice' messages
 * may be avoided.</p>
 */
function &gv(&$assoc, $key, $default=NULL)
{
    if (!is_array($assoc)) {
        return $default;
    }
    else if (array_key_exists($key, $assoc)) {
        return $assoc[$key];
    }
    else {
        return $default;
    }
}
    
function gvInt($row, $key)
{
    if (array_key_exists($key, $row) && $row[$key] != null) {
        return (int) $row[$key];
    }
    return null;
}
    
function gvFloat($row, $key)
{
    if (array_key_exists($key, $row) && $row[$key] != null) {
        return (float) $row[$key];
    }
    return null;
}
    
function gvBool($row, $key)
{
    if (array_key_exists($key, $row)) {
        return (bool) $row[$key];
    }
    return null;
}

function gvMoney($row, $key)
{
    if (array_key_exists($key, $row)) {
        return sprintf('%0.2f', $row[$key]);
    }
    return null;
}
    
function gvTime($row, $key)
{
    return gvFloat($row, $key);
}
    
function backQuote($string)
{
    $string = str_replace('`', '\\`');
    return "`$string`";
}

if (!function_exists('lcfirst')) {
    function lcfirst($str) {
        if (!$str) return $str;
        $first = $str[0];
        if (ctype_upper($first)) {
            if (strlen($str) == 1) return strtolower($first);
            return strtolower($first) . substr($str, 1);
        }
        else {
            return $str;
        }
    }
}

/**************************************
    
Miscelaneous Functions
        
**************************************/
    
function getmicrotime()
{ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
} 
      
// Create new random Code
function newCode() {
    $seed = time();
    srand($seed);
    return(rand()%100000000);
}

function getSchema()
{
    if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ) return 'https';
    return 'http';
}

function baseUrl()
{
    $proto = getSchema();
    if (!isset($_SERVER["HTTP_HOST"])) throw new Exception("\$_SERVER['HTTP_HOST'] is not set");
    $host = $_SERVER["HTTP_HOST"];

    return "$proto://$host";
}

// Redirect browser using relative or absolute paths or urls
function redirect($rel_file)
{
    // Remove leading './'
    $rel_file = ereg_replace("^\./", "", $rel_file);
        
    if(ereg("^[a-z]+://", $rel_file)) { // absolute url
        header("Location: ".$rel_file);
        return;
    } else if(ereg("^/", $rel_file)) { // relative to document root
        $path = "/";
        $rel_file = ereg_replace("^/", "", $rel_file);
    } else { // relative
        if($rel_file[0] == '?') {
            $path = ereg_replace('^([^?]*)(\?.*)$', '\\1', getenv('REQUEST_URI'));
        }
        else if(ereg("^(.*/)[^/]*$", getenv('REQUEST_URI'), $results))
            $path = $results[1];
        else $path = "/";
    }
    $base = baseUrl();
    $location = $base.$path.$rel_file;
    try {
        header("Location: " . $location);
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
}

function urlPathToFullUrl($path, $force_scheme='http') {
    $base = baseUrl();
    return $base . $path;
}

function array_remove_keys($array, $mixed) {
    $newarray = array();
    if (is_array($mixed)) $keys = $mixed;
    else $keys = array($mixed);
    foreach ($array as $key => $value) {
        if (!in_array($key, $keys)) {
            $newarray[$key] = $array[$key];
        }
    }
    return $newarray;
}

function array_delete_at(&$array, $key, $default=null) {
    if (!is_array($array) && !is_object($array)) {
        throw new Exception("\$array is neither an array nor an object: " . var_export($array, true));
    }
    if (!array_key_exists($key, $array)) return $default;
    $value = $array[$key];
    unset($array[$key]);
    return $value;
}

function array_detect($array_name, $each_name, $expression) {
    $parts = explode(',', $each_name);
    if (count($parts) > 1) {
        $index_name = $parts[0];
        $value_name = $parts[1];
    }
    else {
        $index_name = '$_i';
        $value_name = $parts[0];
    }
    $php = "foreach ($array_name as $index_name => $value_name) {\n" .
        "  if ($expression) {\n" .
        "    return $value_name;\n" .
        "  }\n" .
        "}\n" .
        "return null;";
    return $php;
}
    
function array_ereg($regex, $array) {
    foreach($array as $value) {
        if(ereg($regex, $value)) return $value;
    }
    return;
}

// returns ip of client, even if behind proxy server
function realip() {
    if (getenv(HTTP_X_FORWARDED_FOR)) { 
        $ip = getenv(HTTP_X_FORWARDED_FOR); 
    } 
    else { 
        $ip=getenv(REMOTE_ADDR);
    }
    if (!$ip || $ip == '::1') $ip = '127.0.0.1';
    return $ip;
}
    
/*****************************************************
    
Additional form processing functions
Added 2001-11-25
        
******************************************************/
    
function checkRequired( $input, $required )
//    Purpose:
//        Checks to see which required form fields were not filled in
//    Parameters:
//        input: Hashtable of input variables
//        required: Array of required field names
//    Returns: Array of missing field names
{
    $missing = array();
    foreach( $required as $rfield ) {
        if( !$input[$rfield] ) $missing[] = $rfield;
    }
    return $missing;
}
    
function namesToTitles( $fields, $nameTitles )
//    Purpose:
//        Get a list of field titles from a given list of field names
//    Parameters:
//        fields: array of field names
//        nameTitles: 
//            Hashtable with field names as the keys, 
//            and field titles as the values.
//            Used for translating field names to field titles
//    Returns: array of field titles
{
        
    $titles = array();
    foreach( $fields as $field) {
        $title = $nameTitles[$field];
        if( $title ) $titles[] = $title;
        else $titles[] = $field;
    }
    return $titles;
}

/**
 * Get all http request variables
 *
 * @return array An associative array of all input variables.
 */
function getRequest($request=null)
{
    static $saved_request;
    if (!isset($saved_request) || $request) {
        $request = array_merge($_GET, $_POST);
        if (get_magic_quotes_gpc()) {
            $request = stripslashes_deep($request);
        }
        $files = mvc_FileUpload::convertFiles($_FILES);
        hash_merge($request, $files);
        $saved_request =& $request;
    }
        
    return $saved_request;
}

/**
 * Strip slashes on either a string or each element of an array
 *
 * @return mixed Same type as the first parameter
 */
function stripslashes_deep($value)
{
    if (is_array($value) ) {
        return array_map('stripslashes_deep', $value);
    }
    else if (is_string($value)) {
        return stripslashes($value);
    }
    else {
        return $value;
    }
}

/**
 * Return fieldname,title pairs of fields that weren't found in $input.
 *
 * @param array $input  Hashtable (fieldname,value pairs; the user input)
 * @param array $req  Hashtable (fieldname,title pairs; the list of required fields)
 * @return array  Hashtable (fieldname,title pairs; the missing required fields)
 */
function getMissing($input, $req)
{
        
    $ikeys = array_keys($input);
    
    $missing = array();    
    while( list($rfield, $rval) = each($req) ) {
        if( !in_array($rfield, $ikeys) )
            $missing[$rfield] = $req[$rfield];
    }
    return $missing;
}

function getState( $input=null, $stateName="state" )
//    Purpose:
//        Parse out a requested state from the user input. The state can be
//        one of three different formats:
//            1)    "state" as the field name, and the field's value 
//                as the state name.
//            2)    "state_<name>" as the field name, where the state 
//                name is embeded in the field name following the string 
//                "state_". The value of the field variable is ignored.
//                This format is useful when the state is encoded in an
//                html 'submit' button.
//            3) "state_<name>_[xy]" as the field name. This is same as 
//                #2, but would be used within an html image input tag.
{
        
    if( !is_array($input) ) $input =& getRequest();
        
    // Check the submit buttons first
    foreach( $input as $name=>$value )
    {
        if ($name==$stateName.'s' && is_array($value))
        {
            $keys = array_keys($value);
            return $keys[0];
        }
            
        if( preg_match("/^".$stateName."_(.*)_[xy]$/", $name, $results) ) {
            return str_replace('_', '.', $results[1]);
        }
        if( preg_match("/^".$stateName."_(.*)$/", $name, $results) ) {
            return str_replace('_', '.', $results[1]);
        }
            
    }
        
// Check the fields with the exact name ($stateName)
foreach( $input as $name=>$value ) {
    if( $name == $stateName ) return $value;
}

return;
}
    
function getCommand( $input=null )
{
    return getState($input, "command");
}

function getAction( $input=null )
{
    $action = getState($input, 'action');
    if (!$action) $action = getState($input, 'a');
    return $action;
}
    

/* Password Creation */

// Define password characters
for( $i = 0; $i < 26; $i ++ ) {
    if( chr(ord('a') + $i) != 'l' ) $GLOBALS["passChars"][] = chr( ord('a') + $i );
 }
for( $i = 2; $i < 10; $i++ ) {
    $GLOBALS["passChars"][] = "$i";
 }
for( $i = 2; $i < 10; $i++ ) {
    $GLOBALS["passChars"][] = "$i";
 }
$vowels = array('a', 'e', 'i', 'o', 'u');
for( $i = 0; $i < count($vowels); $i++ ) {
    $GLOBALS["passChars"][] = $vowels[$i];
 }
    
function makePassword($size = 8)
{
        
    $pass = "";
    for( $i = 0; $i < $size; $i++ )
        $pass .= $GLOBALS["passChars"][rand(0,count($GLOBALS["passChars"])-1)];
        
    return $pass;
}

function selectOptions($list, $value, $indent="")
{
    $buffer = "";
    foreach( $list as $option )
    {
        $buffer .= "$indent<option value=\""
            .$option['value']."\""
            .($option['value']==$value?" selected":"")
            .">".h($option['title'])."</option>\n";
    }
    return $buffer;
}

function mysystem($command, $show=false)
{
    if (!($p=popen("($command)2>&1","r"))) return 126;
    while (!feof($p)) {
        $l=fgets($p,1000);
        if ($show) print $l;
    }
    return pclose($p);

    //        return (pclose($p)>>8)&0xFF;
}

function cleanSet($set)
{
    if (!$set) return array();
    $cleanSet = array();
    foreach( $set as $item )
    {
        $cleanSet[] = intval($item);
    }
    $cleanSet = array_unique($cleanSet);

    return $cleanSet;
}
    
function cleanIntList($array)
{
    return implode(', ', cleanSet($array));
}

function hash_merge(&$dest, &$append)
{
    $keys = array_keys($append);
    for( $i=0; $i < count($keys); $i++ )
    {
        $key = $keys[$i];
        $value =& $append[$key];
        if( ! isset($dest[$key]) || ! is_array($value) )
        {
            $dest[$key] =& $value;
        }
        else
        {
            hash_merge($dest[$key], $append[$key]);
        }
    }
}

function backtraceToString($backtrace=null)
{
    if ($backtrace == null) {
        $backtrace = debug_backtrace();
    }
    $str = '';
    for( $i=0; $i<count($backtrace); $i++ )
    {
        $str .= '  ';
        $file = "{UNKNOWN FILE}";
        if (isset($backtrace[$i]['file'])) {
            $file = $backtrace[$i]['file'];
            $file = substr($file, strlen(MM_LIB)+1);
        }
        $str .= '"' . $file;
            
        if ( isset($backtrace[$i+1]['class']) || isset($backtrace[$i+1]['function']) )
        {
            $str .= " : ";
        }
            
        if( isset($backtrace[$i+1]['class']) )
        {
            $str .= $backtrace[$i+1]['class'] . '::';
        }
        if( isset($backtrace[$i+1]['function']) )
        {
            $str .= $backtrace[$i+1]['function'] . '() ';
        }
            
        $line = "{UNKNOWN LINE}";
        if (isset($backtrace[$i]['line'])) $line = $backtrace[$i]['line'];
        $str .=  '" on line ' . $line;

        $str .= "\n";
    }
        
    return $str;
}

/**
 * Send an entry to the log
 */
function mm_log($msg)
{
    if (!gv($GLOBALS['MM_CONFIG'], 'debug.logging')) return;
    
    if (func_num_args() > 1) {
        $args = func_get_args();
        $value = $args[1];
        $msg .= var_export($value, true);
    }

    global $MM_START_TIME;
    global $MM_REQUEST_ID;
    if (!isset($MM_START_TIME)) {
        $MM_START_TIME = microtime(true);
        $MM_REQUEST_ID = uniqid('req_', true);
    }
    $time = number_format((microtime(true) - $MM_START_TIME) * 1000, 0);
    $trace = debug_backtrace();
    $parts = explode('/', str_replace('\\', '/', $trace[0]['file']));
    $file = array_pop($parts);
    if ($parts) {
        $parent = array_pop($parts);
        $file = "$parent/$file";
    }
    $line = $trace[0]['line'];
    //$str = "[$MM_REQUEST_ID $time] ($file, line $line): $msg";
    if (defined('REQUEST_ID')) {
        $str = "(" . REQUEST_ID . ", $file, line $line): $msg";
    } else {
        $str = "($file, line $line): $msg";
    }
    
    /* Strip out sensitive information */
    $lines = preg_split('#\r\n|\r|\n#', $str);
    foreach ($lines as $i=>$line) {
        if (preg_match('#^(.*(cc_number|CreditCardNumber|cc_cvv|CVV2|cc_exp_month|cc_exp_year)[^\d]*)(\d+)(.*)$#', $line, $match)) {
            if ($match[2] == 'cc_number' || $match[2] == 'CreditCardNumber') {
                $lines[$i] = $match[1]
                    . preg_replace(
                        '#^(.*)(.{4})$#',
                        str_repeat('x', strlen($match[3])-4) . substr($match[3], -4),
                        $match[3])
                    . $match[4];            
            } else {
                $lines[$i] = $match[1] . str_repeat('x', strlen($match[3])) . $match[4];
            }
        } else if (preg_match("#^(.*password'?[^']*')((\\'|[^'])*)('.*)$#", $line, $match)) {
            $lines[$i] = $match[1] . 'xxxx' . $match[4];
        }
    }
    $str = implode("\n", $lines);
    
    error_log($str);
}
    
function dmp($var)
{
    ob_start();
    print_r($var);
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}

function dumpH($var, $msg='dumpH') {
    $out = '';
    if ($msg) $out .= $msg . ': ';
    $out .= "<pre>";
    $out .= var_export($var, true);
    $out .= "</pre>\n";
    echo $out;
}

/**
 * @obsolete Use makeQueryString()
 */
function makeQuery($values) {
    return makeQueryString($values);
}

function mm_makeQueryString($values) {
    if (!$values) {
        return '';
    }
    $q = '';
    foreach ($values as $k=>$v) {
        if ($q) $q .= '&';
        $q .= urlencode((string) $k) . '=' . urlencode((string) $v);
    }
    return $q;
}
function makeQueryString($values) {
    return mm_makeQueryString($values);
}

function parseQueryString($query) {
    $pairs = explode('&', $query);
    $params = array();
    foreach ($pairs as $pair) {
        if ($pair) {
            list($key, $value) = array_map('urldecode', explode('=', $pair));
            $params[$key] = $value;
        }
    }
    return $params;
}

function uriQuery($params) {
    $query = '';
    foreach ($params as $k=>$v) {
        if ($query) $query .= '&';
        $query .= urlencode($k) . '=' . urlencode($v);
    }
    return $query;
}

/**
 * @param string $url
 * @param array $params
 */
function appendParamsToUrl($url, $params)
{
    if (!$params) return $url;
    if (is_array($params)) $params = uriQuery($params);
    return appendQueryToUrl($url, $params);
}

function appendQueryToUrl($url, $queryString)
{
    if (empty($queryString)) {
        return $url;
    }
    $n = strlen($url);
    $q_pos = strpos($url, '?');
    if ($q_pos === false)
    {
        if (empty($url) || strpos($url, '/') !== false) {
            return $url . '?' . $queryString;
        }
        else {
            return $url . '&' . $queryString;
        }
    }
    else if ($url[$n-1] == '?')
    {
        return $url . $queryString;
    }
    else
    {
        $first_params = parseQueryString(substr($url, $q_pos + 1));
        $url = substr($url, 0, $q_pos); // url without query
        $second_params = parseQueryString($queryString);
        $params = array_merge($first_params, $second_params); // merge the parameters
        return $url . '?' . makeQueryString($params);
    }
}
    
function mkdirp($path)
{
    if (!$path) {
        throw new Exception("mkdirp(): No path given");
    }
        
    if ($path[0] != '/' && preg_match('/^[a-zA-Z]:\\.*$/', $path)) {
        $path = realpath($path);
    }

    if (file_exists($path)) return true;
        
    if ($path == '/' || preg_match('/^[a-zA-Z]:\\.*$/', $path)) {
        $r = mkdir($path);
        if ($r) chmod($path, 0777);
        return $r;
    }
    else {
        $parent = dirname($path);
        if (!file_exists($parent)) {
            if (!mkdirp($parent)) {
                return false;
            }
            else {
                $r = mkdir($path, 0777);
                if ($r) chmod($path, 0777);
                return $r;
            }
        }
        else {
            $r = mkdir($path, 0777);
            if ($r) chmod($path, 0777);
            return $r;
        }
    }
    return true;
}

/**
 * Copy a file, or a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.0
 * @param       string   $source    The source
 * @param       string   $dest      The destination
 * @return      bool     Returns true on success, false on failure
 */
function copyr($source, $dest, $sep='/')
{
    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }
     
    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
    }
     
    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
     
        // Deep copy directories
        if (is_dir($source . $sep . $entry) && ($dest !== $source.$sep.$entry)) {
            copyr($source.$sep.$entry, $dest.$sep.$entry);
        } else {
            copy($source.$sep.$entry, $dest.$sep.$entry);
        }
    }
     
    // Clean up
    $dir->close();
    return true;
}

/**
 * Delete a file, or a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @param       string   $dirname    Directory to delete
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function rmdirr($dirname, $sep='/')
{
    // Sanity check
    if (!file_exists($dirname)) {
        return true;
    }
    
    // Simple delete for a file
    if (is_file($dirname)) {
        return unlink($dirname);
    }
    
    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
     
        // Deep delete directories      
        if (is_dir($dirname . $sep . $entry)) {
            rmdirr($dirname . $sep . $entry);
        } else {
            unlink($dirname . $sep . $entry);
        }
    }
     
    // Clean up
    $dir->close();
    return rmdir($dirname);
}
    
function objectToAssoc($obj) {
    $assoc = get_object_vars($obj);
    foreach ($assoc as $attrib_name=>$attrib) {
        if (is_object($attrib)) {
            $assoc[$attrib_name] = objectToAssoc($attrib);
        }
    }
    return $assoc;
}
    
function getAttribute($obj_or_array, $attrib_name) {
    if (is_object($obj_or_array)) return $obj_or_array->$attrib_name;
    else return $obj_or_array[$attrib_name]; 
}
    
function defineAutoLoad()
{
    if (function_exists('__autoload')) return;
        
    function __autoload( $class )
    {
        static $loaded = array();
        if (isset($loaded[$class])) return;
        $load = str_replace( "_", "/", $class ) . ".php";
        if (!include_once( $load )) {
            debug_print_backtrace();
            return;
        }
        $loaded[$class] = TRUE;
    }
}
 
function extractOptions($argv) {
    array_shift($argv);
    $new_argv = array();
    $options = array();
    $keys = array_keys($argv);
    for ($i=0; $i < count($keys); $i++) {
        $argv_key = $keys[$i];
        $argv_value = $argv[$argv_key];
        
        if ($argv_value[0] == '-') {
            $op_name = substr($argv_value, 1);
            $i++;
            if ($i < count($keys)) {
                $argv_key = $keys[$i];
                $argv_value = $argv[$argv_key];
                if ($argv_value[0] != '-') {
                    $options[$op_name] = $argv_value;
                }
                else {
                    $options[$op_name] = true;
                    $i--;
                }
            }
            else {
                $options[$op_name] = true;
            }
        }
        else {
            $new_argv[] = $argv_value;
        }
    }
    return array($new_argv, $options);
}

function mm_dotSepToAssoc($source) {
    $dest = array();
    foreach ($source as $key=>$value) {
        $parts = explode('.', $key);
        $left = "\$dest";
        foreach ($parts as $part) {
            $left .= "['$part']";
        }
        eval("$left = \$value;");
    }
    return $dest;
}

function underscore($word) {
    $size = strlen($word);
    $new_word = '';
    for ($i=0; $i < $size; $i++) {
        if (ctype_upper($word[$i])) {
            if ($i > 0) $new_word .= '_';
            $new_word .= strtolower($word[$i]);
        }
        else {
            $new_word .= $word[$i];
        }
    }
    return $new_word;
}

function camelize($word) {
    $size = strlen($word);
    $new_word = '';
    for ($i=0; $i < $size; $i++) {
        $c = $word[$i];
        if ($c == '_') {
            $i++;
            if ($i >= $size) break;
            $c = strtoupper($word[$i]);
        }
        $new_word .= $c;
    }
    return $new_word;
}

function definedAndTrue($const) {
    eval("\$result = defined('$const') && $const;");
    return $result;
}

function mm_mail($to, $subject, $message, $additional_headers=null, $additional_parameters=null) {
    $enabled = mm_getConfigValue('emails.enabled');
    $passed = true;
    if ($enabled) {
        try {
            $passed = mail($to, $subject, $message, $additional_headers, $additional_parameters);
        }
        catch (Exception $e) {
            $passed = false;
            // We trust proper action has been taken to log this failure.
        }
        $status = $passed ? "SENT" : 'FAILED';
    }
    else {
        $status = "DISABLED";
    }
    $status = "($status)";

    if (mm_getConfigValue('log.emails')) {
        mm_log("Email Message $status...\n" .
            "To: $to\nSubject: $subject\n$additional_headers\n\n$message\n----- END OF MESSAGE (to: $to)-----\n");
    }
    
    return $passed;
}

function pluralize($word)
{
    $ut = strtoupper($word);
    if ($ut == 'PERSON') return 'people';
    $len = strlen($word);
    $lastc = $ut[$len-1];
    $lastc2 = substr($ut,$len-2);
    switch ($lastc) {
    case 'S':
        return $word.'es';
    case 'Y':
        return substr($word,0,$len-1).'ies';
    case 'X':
        return $word.'es';
    case 'H':
        if ($lastc2 == 'CH' || $lastc2 == 'SH')
            return $word.'es';
    default:
        return $word.'s';
    }
}

/**
 * Format a date to the format specified in settings or the configuration.
 *
 * @param int $date
 */
function mm_date($date)
{
    if (!$date) return '';
    if (!is_numeric($date)) $date = strtotime($date);
    $format = mm_getSetting('date_format', mm_getConfigValue('date_format', "m/d/Y"));
    return date($format, $date);
}

function mm_datetime($date)
{
    if (!$date) return '';
    if (!is_numeric($date)) $date = strtotime($date);
    $format = mm_getSetting('datetime_format', mm_getConfigValue('datetime_format', 'm/d/Y \a\\t g:i:s a'));
    return date($format, $date);
}

function mm_pricenumber($price)
{
    return sprintf("%0.2f", $price);
}

function mm_price($price)
{
    return sprintf("%0.2f", $price);
}

class mm_ContentRenderer {
    public $content_renderer_target;
    function __construct($target=null) {
        $this->content_renderer_target = $target;
    }
    function contentRendererRenderByName($name) {
        $da = new content_ContentDAO;
        $content = $da->fetchByName($name);
        if (!$content) return;
        $this->contentRendererRender($content);
    }
    function contentRendererRender($content) {
        if (!is_object($content)) {
            echo "object:<pre>" . var_export($content, true) . "<br/>\n";
            exit;
        }
        if ($content->type == 'plain') {
            echo nl2br(h($content->body));
        }
        if ($content->type == 'php') {
            eval('?>' . $content->body);
        }
        else {
            echo $content->body;
        }
    }
    function __call($method, $args) {
        if ($this->content_renderer_target) {
            return call_user_func_array(array($this->content_renderer_target, $method), $args);
        }
    }
    function __get($name) {
        if ($this->content_renderer_target) {
            return $this->content_renderer_target->$name;
        }
    }
    function __set($name, $value) {
        if ($this->content_renderer_target) {
            $this->content_renderer_target->$name = $value;
        }
    }
}

function mm_renderContent($name, $from_parent)
{
    $renderer = new mm_ContentRenderer($from_parent);
    $renderer->contentRendererRenderByName($name);
}

function mm_parse_version($version) {
    $match = array();
    preg_match('/(\d+)(\.(\d+))?(\.(\d+))?(([a-z]+)(\d+)?)?/', $version, $match);
    $parts = array(
        @$match[1],
        @$match[3],
        @$match[5],
        @$match[7],
        @$match[8]
    );
    return $parts;
}

function mm_compare_versions($v1, $v2) {
    $suffixes = array('a' => 0, 'b' => 1, 'rc' => 2, '' => 3);

    $v1_parsed = mm_parse_version($v1);
    $v2_parsed = mm_parse_version($v2);

    //echo "v1_parsed: " . var_export($v1_parsed, true) . "\n";
    //echo "v2_parsed: " . var_export($v2_parsed, true) . "\n";
    
    if ($v1_parsed[0] > $v2_parsed[0]) return 1;
    if ($v1_parsed[0] < $v2_parsed[0]) return -1;

    if ($v1_parsed[1] > $v2_parsed[1]) return 1;
    if ($v1_parsed[1] < $v2_parsed[1]) return -1;

    if ($v1_parsed[2] > $v2_parsed[2]) return 1;
    if ($v1_parsed[2] < $v2_parsed[2]) return -1;
    
    if ($suffixes[$v1_parsed[3]] > $suffixes[$v2_parsed[3]]) return 1;
    if ($suffixes[$v1_parsed[3]] < $suffixes[$v2_parsed[3]]) return -1;
    
    if ($v1_parsed[4] > $v2_parsed[4]) return 1;
    if ($v1_parsed[4] < $v2_parsed[4]) return -1;
    
    return 0;
}


function mm_next_version($version) {
    $dirty_parsed = mm_parse_version($version);
    $parsed = array();
    foreach ($dirty_parsed as $part) {
        if ($part !== null) {
            $parsed[] = $part;
        }
        else {
            break;
        }
    }
    $number = 0;
    foreach (array_reverse($parsed) as $i=>$part) {
        $number += $part * pow(10, $i);
    }
    $number++;
    $next_version = array();
    $log10 = log10($number);
    for ($i = 0; $i < count($parsed); $i++) {
        array_unshift($next_version, $number % 10);
        $number /= 10;
    }
    return implode('.', $next_version);
}

function mm_is_writable($path)
{
    if (startswith(strtolower(PHP_OS), 'win')) {
        if (is_dir($path)) {
            $filepath = $path . DIRECTORY_SEPARATOR . time();
            $fp = @fopen($filepath, 'w');
            if (!$fp) {
                return false;
            }
            else {
                fclose($fp);
                unlink($filepath);
                return true;
            }
        }
        else {
            $fp = @fopen($path, 'a');
            if (!$fp) {
                return false;
            }
            else {
                fclose($fp);
                return true;
            }
        }
    }
    else {
        return is_writable($path);
    }
}

/**
 * Sanitize text that may contain HTML.
 */
function mm_sanitize($text) {
    $allowed_tags = array('a', 'code');
    
}

class HtmlSanitizer {
    public $attribute_pattern;
    public $tag_pattern;

    function __construct() {
        $attribute_pattern = '(\s+[a-z][a-z0-9_-]*)(\s*=\s*"([^"]*)")';
        $this->attribute_pattern = "@$attribute_pattern@i";
        $this->tag_pattern = "@<(/)?([a-z][a-z0-9_-]*)(($attribute_pattern)*)\s*>@i";
    }
    
    /**
     * Parse a string of tag attributes.
     * The parser only handles properly-formed attributes.
     */
    function parse_attributes($input) {
        preg_match_all($this->attribute_pattern, $input, $matches, PREG_SET_ORDER);
        $attribs = array();
        foreach ($matches as $match) {
            $name = trim($match[1]);
            if (isset($match[3])) {
                $attribs[$name] = htmlspecialchars_decode($match[3]);
            }
            else {
                $attribs[$name] = true;
            }
        }
        return $attribs;
    }
    
    function sanitation_tags() {
        return array(
            array('tag' => 'a', 'attributes' => array('href')),
            array('tag' => 'code'));
    }
    
    function sanitize($input) {
        $tags = $this->sanitation_tags();
        $output = "";
        $offset = 0;
        while (preg_match($this->tag_pattern, $input, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $prefix = substr($input, $offset, $match[0][1]-$offset);
            $output .= htmlspecialchars($prefix);
            $match_string = $match[0][0];
            $match_offset = $match[0][1];
            $slash = $match[1][0];
            $tag = $match[2][0];
            $attribs_string = $match[3][0];
            $tag_def = null;
            foreach ($tags as $d) {
                if ($d['tag'] == $tag) {
                    $tag_def = $d;
                }
            }
            if (!$tag_def) {
                $output .= htmlspecialchars($match_string);
            }
            else {
                $output .= '<' . $slash . $tag;
                if ($slash) {
                    $output .= '>';
                }
                else {
                    $attribs = $this->parse_attributes($attribs_string);
                    $attribs_buffer = array();
                    foreach ($attribs as $k=>$v) {
                        if (isset($tag_def['attributes']) && in_array($k, $tag_def['attributes'])) {
                            $buf = ' ';
                            $buf .= $k;
                            if ($v !== true) {
                                $buf .= '="' . htmlspecialchars($v) . '"';
                            }
                            $attribs_buffer[] = $buf;
                        }
                    }
                    $output .= implode('', $attribs_buffer) . '>';
                }
            }
            $offset = $match_offset + strlen($match_string);
        }
        if ($offset < strlen($input)-1) {
            $output .= htmlspecialchars(substr($input, $offset));
        }
        return $output;
    }
}

function sanitize($input) {
    global $html_sanitizer;
    if (!isset($html_sanitizer)) $html_sanitizer = new HtmlSanitizer;
    return $html_sanitizer->sanitize($input);
}

/**
 * Turns partial URL (for an external site) into a complete URL.
 */
function mm_fixUrl($url) {
    $url = trim($url);
    if (empty($url)) return null;
    if (!preg_match('@^[a-z]+://@i', $url)) $url = "http://$url";
    $parsed = parse_url($url);
    $url = gv($parsed, 'scheme') . '://' . gv($parsed, 'host');
    if ($port = gv($parsed, 'port')) {
        $url .= ':' . $port;
    }
    $url .= gv($parsed, 'path', '/');
    if ($query = gv($parsed, 'query')) {
        $url .= '?' . $query;
    }
    if ($fragment = gv($parsed, 'fragment')) {
        $url .= '#' . $fragment;
    }
    return $url;
}

function mm_exceptionToString($e)
{
    $out = "Exception thrown: " . $e->getMessage() . "\n";
    $file = str_replace(MM_LIB . DS, 'MM_LIB' . DS, $e->getFile());
    $out .= "  \"$file\" on line " . $e->getLine() . "\n";
    $out .= mm_backtraceToString($e->getTrace());
    return $out;
}

function mm_backtraceToString($backtrace)
{
    $str = '';
    for ($i=0; $i < count($backtrace); $i++) {
        $str .= '  ' . mm_makeTraceLine($backtrace, $i) . "\n";
    }
    
    return $str;
}

function mm_makeTraceLine($backtrace, $i)
{
    $str = '#' . $i . ' ';
    if (isset($backtrace[$i]['file'])) {
        $file = $backtrace[$i]['file'];
        $file = str_replace(MM_LIB . DS, 'MM_LIB' . DS, $file);
    }
    else {
        $file = "{UNKNOWN FILE}";
    }
    $str .= $file;
    
    $line = "{UNKNOWN LINE}";
    if (isset($backtrace[$i]['line'])) {
        $line = $backtrace[$i]['line'];
    }
    $str .=  ' (' . $line . ')';

    if (isset($backtrace[$i+1]['class']) || isset($backtrace[$i+1]['function'])) {
        $str .= ": ";
        if (isset($backtrace[$i+1]['class'])) {
            $str .= $backtrace[$i+1]['class'] . '::';
        }
        if (isset($backtrace[$i+1]['function'])) {
            $str .= $backtrace[$i+1]['function'] . '() ';
        }
    }
    
    if (isset($backgrace[$i]['code'])) {
        $str .= ': ' . $backgrace[$i]['code'];
    }
    return $str;
}

function triggerErrorFromException($exception, $errno=null)
{
    global $mm_error_handler;
    if (!$errno) $errno = E_USER_WARNING;
    $trace = $exception->getTrace();
    $mm_error_handler->handler($errno, $exception->getMessage(), $trace[0]['file'], $trace[0]['line'], array(), $trace);
}

/**
 * Returns data for generating pagination links, for navigating result sets that are
 * too large for one page.
 */
function mm_pagedNav($count, $offset, $max_results, $max_links)
{
    if ($extra_params === null) $extra_params = '';
    
    // Calculate $current_page_index
    $current_page_index = intval($offset / $max_results);

    // Calculate $first_page_index
    $first_page_index = intval($current_page_index / $max_links) * $max_links;
        
    // Calculate $num_links
    if ($count - ($first_page_index * $max_results) >= ($max_results * $max_links)) {
        $num_links = $max_links;
    }
    else {
        $num_links = ceil(($count - ($first_page_index * $max_results)) / $max_results);
    }
    //if ($num_links == 1 && $count < ($max_results * $max_links)) {
    //    return;
    //}
        
    $numbered = array();
        
    for ($link_index = 0; $link_index < $num_links; $link_index++) {
        $link_data = array();
        
        // Set the page number
        $link_data["page_number"] = $first_page_index + $link_index + 1;
        
        // Set the link flag
        if ( ($first_page_index + $link_index) == $current_page_index ) {
            $link_data["current_page"] = true;
        }
        else {
            $link_data["current_page"] = false;
        }
        
        // Set the offset
        $t_offset = ($first_page_index + $link_index) * $max_results;
        $link_data['offset'] = $t_offset;
        
        $numbered[$link_index] = $link_data;
    }

    // Set "previous page" details
    $previous = array();
    if ($current_page_index > 0) {
        $link_data = array();
        
        // Get offset
        $t_offset = ($current_page_index - 1) * $max_results;
        $link_data['offset'] = $t_offset;
        
        $previous = $link_data;
    }
    
    // Set "next page" details
    $next = array();
    if (($current_page_index + 1 ) * $max_results < $count) {
        $link_data = array();
        
        // Get offset
        $t_offset = ($current_page_index + 1) * $max_results;
        $link_data['offset'] = $t_offset;
        
        $next = $link_data;
    }

    // Populate the ResultsNav object
    return array(
        'count'        => $count,
        'offset'       => $offset,
        'max_per_page' => $max_results,
        'max_links'    => $max_links,
        'extra_params' => $extra_params,
        'numbered'     => $numbered,
        'previous'     => $previous,
        'next'         => $next
    );
}

function mm_pagedNavLink($link, $query, $label=null, $uriPath='')
{
    $offsetQuery = $link['offset'] != 0 ? 'offset=' . $link['offset'] : '';
    $query = appendQueryToUrl($query, $offsetQuery);
    $url = $uriPath . '?' . $query;
    if (!$label) {
        $label = $link['page_number'];
    }
    return '<a href="' . h($url) . '">' . h($label) . '</a>' . "\n";
}

function mm_pagedNavHtml($count, $offset, $max_results, $max_links, $params=null, $uriPath=null)
{
    if ($uriPath === null) {
        $uriPath = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
    }
    
    if (is_array($params)) {
        unset($params['offset']);
        $query = makeQuery($params);
    }
    else {
        $query = '';
    }
    $out = '';
    $nav = mm_pagedNav($count, $offset, $max_results, $max_links);
    if ($nav['previous']) {
        $out .= mm_pagedNavLink($nav['previous'], $query, 'Prev', $uriPath);
    }
    foreach ($nav['numbered'] as $link) {
        if ($link['current_page']) {
            $out .= h($link['page_number']) . "\n";
        }
        else {
            $out .= mm_pagedNavLink($link, $query, '', $uriPath);
        }
    }
    if ($nav['next']) {
        $out .= mm_pagedNavLink($nav['next'], $query, 'Next', $uriPath);
    }

    return array($out, $nav);
}
