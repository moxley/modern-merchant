<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class pricing_Pricing extends mvc_Model
{
    public $id;
    public $value;
    public $type;
    public $name;
    public $valid_types = array('multiply', 'add', 'override');
    public $valid_type_options = array('multiply' => 'Multiply (price = BasePrice x Value)', 'add' => 'Add (price = BasePrice + Value)', 'override' => 'Override (price = Value)');
    private $_categories;
    private $dao;
    
    function __construct()
    {
        $this->type = 'multiply';
        $this->value = 1.0;
        $this->dao = new pricing_PricingDAO;
    }
    
    function getValidTypes()
    {
        return array_keys($this->valid_type_options);
    }
    
    function apply($price)
    {
        if (!$this->type) {
            throw new Exception("No type defined for this pricing");
        }
        switch ($this->type) {
        case 'multiply':
            return $this->value * $price;
        case 'add':
            return $this->value + $price;
        case 'override':
            return $this->value;
        default:
            throw new Exception("Unknown pricing type: " . $this->type);
        }
    }
    
    function validate()
    {
        if (!$this->name) {
            $this->addError("Missing name");
        }
        if (!$this->type) {
            $this->addError("Missing type");
        }
        else if (!in_array($this->type, $this->valid_types)) {
            $this->addError("Invalid type: ") . $this->type;
        }
    }

    function setAdminValues($values=null)
    {
        if (!$values) $values = array();
        $this->name = gv($values, 'name');
        $this->type = gv($values, 'type');
        $this->value = gvFloat($values, 'value');
        $this->category_ids = cleanSet(gv($values, 'category_ids'));
    }
    
    function save()
    {
        $dao = new pricing_PricingDAO;
        $this->errors = $this->validate();
        if ($this->errors) return false;
        
        if (!$this->id) {
            $dao->add($this);
            $dao->addCategoryIdsToPricing($this, $this->category_ids);
        }
        else {
            $dao->update($this);
            
            $old_categories = $dao->getCategories($this);
            $old_category_ids = array_map(
                create_function('$c', 'return $c->id;'),
                $old_categories);
            $new_category_ids = $this->category_ids;

            // Add and delete pricing-categories
            $add_category_ids = array_diff($new_category_ids, $old_category_ids);
            $del_category_ids = array_diff($old_category_ids, $new_category_ids);
            $this->dao->addCategoryIdsToPricing($this, $add_category_ids);
            $this->dao->deleteCategoryIdsFromPricing($this, $del_category_ids);
        }
        return $this;
    }
    
    function delete() {
        $dao = new pricing_PricingDAO;
        $dao->delete($this);
    }
    
    function getCategories() {
        if (!$this->_categories) {
            $this->_categories = $this->dao->getCategories($this);
        }
        return $this->_categories;
    }
}
