<?php
/**
 * @package shipping
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class shipping_ShippingMethodDAO extends mvc_DataAccess
{
    public static $all = null;
    
    function getTable()
    {
        return "mm_shipping_method";
    }
    
    function getAllActive()
    {
        $all = $this->getAll();
        return array_filter($all, create_function('$m', 'return $m->isActive();'));
    }

    function getAll()
    {
        self::$all = $this->find(array('order' => 'sortorder'));
        return self::$all;
    }
    
    function getCount($options=null)
    {
        if (!$options) $options = array();
        
        // Get database connection
        $dbh = mm_getDatabase();
        $query = "
                SELECT COUNT(id)
                FROM mm_shipping_method
        ";
        return $dbh->getOne($query);
    }
    
    public function parseRow($row, $options=array())
    {
        // Get default shipping method
        $default_id = mm_getSetting('default_shipping_method');
        
        $method = new shipping_ShippingMethod;
        foreach ($row as $k=>$v) $method->$k = $v;
        $method->id = (int) $method->id;
        $method->active = $method->active ? true : false;
        $method->sortorder = (int) $method->sortorder;
        $method->is_default = $default_id == $method->id;
        $method->sortorder = (int) $row['sortorder'];
        return $method;
    }
}
