<?php
/**
 * @package shipping
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class shipping_ShippingMethod extends mvc_Model
{
    public $id;
    public $name;
    public $active;
    public $cost;
    public $calc;
    protected $_is_default = null;
    protected $_is_default_changed = false;
    public $sortorder = 0;
    
    static function defaultMethodId()
    {
        return mm_getSetting('default_shipping_method');
    }

    function getId() { return $this->id; }
    function getName() { return $this->name; }
    function getTitle() { return $this->getName(); }
    function isActive() { return $this->active ? true : false; }
    function getCost() { return sprintf("%0.2f", $this->cost); }
    function getCalc() { return $this->calc; }
    
    function calculateAmount($cart)
    {
        $func = create_function('$cart', $this->calc);
        if( !$func ) return;
        if( !function_exists($func) ) {
            throw new Exception("Function doesn't exist: '$func'");
        }
        return $func($cart);
    }
    
    function &getList()
    {
        $dbh = $this->getDatabase();
        $sql = 'select * from mm_shipping_method order by name';
        $rows = $dbh->getAllAssoc($sql);
        return $rows;
    }
    
    function getSqlParams()
    {
        $params = array(
            'name' => $this->name,
            'active' => $this->active ? '1' : '0',
            'calc' => $this->calc
        );
        return $params;
    }
    
    function deactivate()
    {
        if (!$this->active) return;
        $this->active = false;
        $this->save();
    }
    
    function activate()
    {
        if ($this->active) return;
        $this->active = true;
        $this->update();
    }
    
    function afterValidate()
    {
        $this->active = (boolean) $this->active;
    }
    
    function setIsDefault($isDefault)
    {
        if (isset($this->_is_default) && $isDefault != $this->_is_default) {
            $this->_is_default_changed = true;
        }
        $this->_is_default = $isDefault;
    }
    
    function getIsDefault()
    {
        return $this->_is_default;
    }
    
    function afterSave()
    {
        if ($this->_is_default_changed) {
            if ($this->_is_default) {
                mm_setSetting('default_shipping_method', $this->id);
            } else if (mm_getSetting('default_shipping_method') == $this->id) {
                mm_setSetting('default_shipping_method', null);
            }
            $this->_is_default_changed = false;
        }
    }
}
