<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Data access object for global settings
 */
class setting_SettingDAO
{
    static function clearCache() {
        global $MM_SETTING_DAO_ASSOC;
        if (isset($MM_SETTING_DAO_ASSOC)) {
            $MM_SETTING_DAO_ASSOC = array();
        }
    }
    function &getAllAssoc()
    {
        global $MM_SETTING_DAO_LIST;
        global $MM_SETTING_DAO_ASSOC;
        global $MM_USE_DATABASE;
        
        if (!isset($MM_SETTING_DAO_ASSOC) || !$MM_SETTING_DAO_ASSOC) {
            $MM_SETTING_DAO_ASSOC = array();
            $MM_SETTING_DAO_LIST = array();
            if (!mm_getConfigValue('kernel.active')) {
                $MM_SETTING_DAO_ASSOC = array();
                $MM_SETTING_DAO_LIST = array();
            }
            else {
                $dbh = mm_getDatabase();
                $sql = "select * from mm_setting order by name";
                $records = $dbh->getAllAssoc($sql);
                foreach ($records as $record) {
                    $setting = $this->parseRow($record);
                    $MM_SETTING_DAO_LIST[] = $setting;
                    $MM_SETTING_DAO_ASSOC[$setting->name] = $setting->value;
                }
            }
        }
        
        return $MM_SETTING_DAO_ASSOC;
    }
    
    function getCachedList() {
        global $MM_SETTING_DAO_LIST;
        $this->getAllAssoc();
        return $MM_SETTING_DAO_LIST;
    }
    
    function fetch($id)
    {
        $sql = "select id, name, value, type from mm_setting WHERE id=?";
        $db = mm_getDatabase();
        $row = $db->getOneAssoc($sql, array($id));
        return $this->parseRow($row);
    }

    function fetchByName($name, $default)
    {
        $sql = "select id, name, value, type from mm_setting WHERE name=?";
        $db = mm_getDatabase();
        $row = $db->getOneAssoc($sql, array($name));
        return $this->parseRow($row);
    }
    
    function find()
    {
        $dbh = mm_getDatabase();
        $sql = "select id, name, value, type from mm_setting order by name";
        $records = $dbh->getAllAssoc($sql);
        $settings = array();
        foreach ($records as $row) {
            $settings[] = $this->parseRow($row);
        }
        return $settings;
    }
    
    function parseRow($row)
    {
        $setting = new setting_Setting;
        $setting->id = (int) gv($row, 'id');
        $setting->name = gv($row, 'name');
        $setting->type = gv($row, 'type');
        $value = gv($row, 'value');
        if ($setting->type == 'boolean') {
            $setting->value = $value ? true : false;
        }
        else if (!isset($value)) {
            $setting->value = null;
        }
        else if ($setting->type == 'integer') {
            $setting->value = (int) $value;
        }
        else {
            $setting->value = $value;
        }
        $setting->description = gv($row, 'description');
        $setting->sortorder = gv($row, 'sortorder');
        return $setting;
    }
    
    function getAllAsAssocMulti()
    {
        $assoc = $this->getAllAssoc();
        $conf = new mm_Configuration($assoc);
        return $conf->toAssocMulti();
    }
    
    function lookup($name, $force=false)
    {
        if ($force) {
            return $this->getByName($name);
        }
        else {
            $assoc = $this->getAllAssoc();
            if (!array_key_exists($name, $assoc)) {
                throw new mm_NoMatchException("No setting found with name=$name");
            }
            return $assoc[$name];
        }
    }
    
    function get($name, $default = null, $force = false)
    {
        if ($force) {
            $setting = $this->fetchByName($name, $default);
            return $setting ? $setting->value : $default;
        }
        else {
            $assoc = $this->getAllAssoc();
            return gv($assoc, $name, $default);
        }
    }
    
    function set($name, $value, $force=false)
    {
        global $MM_SETTING_DAO_ASSOC;
        if (!$force) {
            $assoc = $this->getAllAssoc();
        }

        $str_value = $value;
        $type = 'string';
        if (is_bool($value) ) {
            $str_value = $value ? "1" : "0";
            $type = 'boolean';
        }
        else if (is_int($value)) {
            $str_value = "$value";
            $type = 'integer';
        }
        else if (is_float($value)) {
            $str_value = "$value";
            $type = 'float';
        }
        
        if (!$force) {
            $new = !array_key_exists($name, $assoc);
        }
        else {
            $sql = "select id from mm_setting WHERE name=?";
            $db = mm_getDatabase();
            $id = $db->getOne($sql, array($name));
            $new = $id ? false : true;
        }
        
        if ($new) {
            $sql = "insert into mm_setting (name, type, value) values (" .
                dq($name) . "," . dq($type) . "," . dq($str_value) . ")";
        }
        else {
            $sql = "update mm_setting set value=".dq($str_value)
                ." where name=".dq($name);
        }
        $MM_SETTING_DAO_ASSOC[$name] = $value;
        $dbh = mm_getDatabase();
        $dbh->execute($sql);
        
        return true;
    }

    function deleteByName($name)
    {
        global $MM_SETTING_DAO_ASSOC;
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $dbh->query("delete from mm_setting where name=" . $fmt->fString($name));
        unset($MM_SETTING_DAO_ASSOC[$name]);
    }

    function deleteByPrefix($name)
    {
        global $MM_SETTING_DAO_ASSOC;
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $dbh->query("delete from mm_setting where name like '" . $fmt->fSubString($name) . "%'");
        foreach ($MM_SETTING_DAO_ASSOC as $k=>$v) {
            if (startswith($k, $name)) {
                unset($MM_SETTING_DAO_ASSOC[$k]);
            }
        }
    }

    /**
     * Get a group of configuration key-value pairs
     *
     * Use this to limit the configuration values to a local portion of the configuration
     *
     * @param string Configuration sub-key, for instance, 'payment_method.paypal' retreives all the values who's keys start with 'payment_method.paypal'
     * @return object Configuration
     */
    function &getGroup($base)
    {
        global $MM_SETTING_DAO_ASSOC;
        
        $assoc = $this->getAllAssoc();
        
        $keys = array_keys($MM_SETTING_DAO_ASSOC);
        $group = array();
        for( $i=0; $i < count($keys); $i++ )
        {
            if( strpos($keys[$i], $base) === 0 )
            {
                if( strlen($keys[$i])+1 > $base )
                {
                    $new_key = substr($keys[$i], strlen($base)+1);
                    $group[$new_key] =& $MM_SETTING_DAO_ASSOC[$keys[$i]];
                }
                else
                {
                    $group[] =& $MM_SETTING_DAO_ASSOC[$keys[$i]];
                }
            }
        }
        
        return new mm_Configuration($group);
    }
}
