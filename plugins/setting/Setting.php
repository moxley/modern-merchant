<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Data object representing a global setting
 */
class setting_Setting extends mvc_Model
{
    var $listmode_normal = 1;
    var $listmode_all = 2;
    public $name;
    public $value;
    
    function getList($mode=1)
    {
        $dbh = $this->getDatabase();
            // Run query
        $sql = "select name, value from mm_setting where "
            ."not (name like 'payment_method%') "
            ."order by name";
        $settings = $dbh->getAllAssoc($sql);
        
        return $settings;
    }
    
    function _fetch($name)
    {
        $dbh = $this->getDatabase();
        // Run query
        $sql = "select name, value from mm_setting where "
            ."name=".dq($name)
            ."order by name";
        $row = $dbh->query($sql)->fetchAssoc();
        if ($row == null) return null;
        $setting = new setting_Setting;
        $setting->name = $row['name'];
        $setting->value = $row['value'];
        
        return $setting;
    }
    
    function lookup($name)
    {
        $setting = $this->_fetch($name);
        if ($setting == null) return null;
        return $setting->value;
    }
    
    function update()
    {
        $sql = "update mm_setting set value=? where id=?";
        return $this->getDatabase()->query($sql, array($this->value, $this->id));
    }
    
    function save()
    {
        if ($this->id) {
            $this->update();
        }
        else {
            $this->add();
        }
        return $this;
    }
    
    function setAdminValues($values) {
        $this->value = gv($values, 'value');
    }
}
