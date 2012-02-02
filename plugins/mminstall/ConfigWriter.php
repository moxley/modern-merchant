<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package mminstall
 */
class mminstall_ConfigWriter extends mminstall_Checker {
    public $debug_mode = false;
    
    function check() {
        $result = new mminstall_CheckerResult("Writing configuration file");
        $tpl = MM_LIB . "/conf/config_tpl.php";
        $file = MM_LIB . "/conf/config.php";
        $lines = @file($tpl);
        if ($lines === false) {
            $result->fail("Could not read $file");
        }
        else {
            $fp = @fopen($file, 'w');
            if (!$fp) {
                $result->fail("Could not write to $file");
            }
            else {
                mm_setNewConfigValue('debug.logging', $this->debug_mode ? '1' : '0');
                mm_setNewConfigValue('debug.show_exception_trace', $this->debug_mode ? '1' : '0');
                mm_setNewConfigValue('debug.email_errors', $this->debug_mode ? '0' : '1');
                $search = array();
                $replace = array();
                foreach ($GLOBALS['CONFIG_KEYS_TO_SUBSTITUTE'] as $key) {
                    $search[] = '@' . $key . '@';
                    $replace[] = mm_getConfigValue($key);
                }
                $r = $this->req;
                $db = new db_Database;
                foreach ($lines as $line) {
                    fwrite($fp, str_replace($search, $replace, $line));
                }
                fclose($fp);
                if (fileowner($file) == get_current_user()) {
                    @chmod($file, 0666);
                }
            }
        }
        $this->addResult($result);
    }
}

