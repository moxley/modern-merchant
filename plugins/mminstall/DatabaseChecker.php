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
class mminstall_DatabaseChecker extends mminstall_Checker
{
    function check()
    {
        global $MM_CONFIG;

        $result = new mminstall_CheckerResult("MySQL API for PHP");
        $this->database['type'] = null;
        if (class_exists('mysqli')) {
            $this->database['type'] = 'mysqli';
            $result->error_msg = "API: mysqli";
        }
        else if (function_exists('mysql_connect')) {
            $this->database['type'] = 'mysql';
            $result->error_msg = "API: mysql";
        }
        else {
            $result->fail("The 'mysql' or 'mysqli' PHP extension is not installed");
        }
        $this->addResult($result);
        if (!$result->pass) return;

        // Check database permissions
        $result = new mminstall_CheckerResult("Database connection");
        $defaults = array('port' => 3306, 'host' => 'localhost', 'user' => 'root');
        foreach (array('name', 'user', 'password', 'host', 'port', 'type') as $name) {
            $value = gv($this->database, $name, gv($defaults, $name));
            mm_setNewConfigValue('database.' . $name, $value);
        }

        try {
            $db = new db_Database;
        }
        catch (Exception $e) {
            $result->fail($e->getMessage());
        }
        $this->addResult($result);
        if (!$result->pass) return;
        
        $result = new mminstall_CheckerResult("Test table creation");
        try {
            $db->execute("DROP TABLE IF EXISTS mm_installer_test");
            $db->execute("CREATE TABLE mm_installer_test (id int, primary key (id))");
            $db->execute("DROP TABLE mm_installer_test");
        }
        catch (Exception $e) {
            $result->fail($e->getMessage());
        }
        $this->addResult($result);
        if (!$result->pass) return;
        
    }
}

