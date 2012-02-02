<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package cart
 */
class cart_CartLine extends mvc_Model
{
    public $id;
    public $sku;
    public $description;
    public $qty = 1;
    public $price = 0;
    public $tax = 0;

    public $wanted = array('id', 'price', 'qty', 'sku', 'description');
    
    /* Keep track of how many items to remove from this line item in case of error */
    public $remove_self_qty = 0;
    
    private $_product;

    static function nextId() {
        return uniqid('line_');
    }
        
    function __construct($arg1=null, $qty=null)
    {
        $this->id = self::nextId();
        if (is_object($arg1)) {
            $product = $arg1;
            $this->setProduct($product);
            $this->qty = isset($qty) ? $qty : 1;
        }
        else if (is_array($arg1)) {
            parent::__construct($arg1);
        }
    }
        
    function getPrice() {
        return $this->price;
    }
        
    function getQuantity() {
        return $this->qty;
    }
    
    function getTotal()
    {
        return sprintf('%0.2f', ((float) $this->price) * i($this->qty));
    }
    
    function getSku()
    {
        return $this->sku;
    }
        
    function getDescription()
    {
        return $this->product->name;
    }
        
    function getId()
    {
        return $this->id;
    }
        
    function equals($line)
    {
        if (!($line instanceof cart_CartLine)) return false;
        if ($this->sku != $line->sku) return false;
        if ($this->qty != $line->qty) return false;
        if ($this->sku != $line->sku) return false;
        if ($this->price != $line->price) return false;
        return true; 
    }
    
    function &__get($name) {
        $method_name = 'get' . ucfirst($name);
        if (method_exists($this, $method_name)) {
            $return = $this->$method_name();
            return $return;
        }
        else {
            throw new Exception("Property '$name' doesn't exist in " . __CLASS__);
        }
    }
    
    function validate() {
        $this->remove_self_qty = 0; // Keep track of how many items to remove from this line item
        if (!$this->sku) {
            $this->addError("No SKU specified for this Cart Line");
            $this->remove_self_qty = $this->qty ? $this->qty : 1;
        }
        else {
            $product = $this->getProduct();
            $title = $product->name;
            $sku = $product->sku;
            if ($this->qty < 1) {
                $this->addError("Product \"$title\" (sku=$sku): There are no instances of this Product in your cart");
                $this->remove_self_qty = $this->qty ? $this->qty : 1;
            }
            else if (!$product->is_for_sale) {
                $this->addError("We're sorry. The item \"$title\" (sku=$sku) is no longer available");
                $this->remove_self_qty = $this->qty ? $this->qty : 1;
            }
            else if ($product->count !== '' && $product->count !== null && $this->qty > $product->count) {
                $reduced_by = $this->qty - $product->count;
                $this->addError("We're sorry. The item \"$title\" (sku=$sku) has only $product->count in stock. Cart quantity automatically reduced by $reduced_by");
                $this->remove_self_qty = $reduced_by;
            }
        }
        return $this->errors;
    }
    
    function setAdminValues($values) {
        $this->price = gv($values, 'price', $this->price);
        $this->qty = gv($values, 'qty', $this->qty);
    }
    
    function setProduct($product) {
        $this->_product = $product;
        $this->sku = $product->sku;
        $this->price = $product->adjusted_price;
        $this->description = $product->name;
    }
    
    function getProduct() {
        if (!$this->_product) {
            $pdao = new product_ProductDAO;
            $this->_product = $pdao->fetchBySku($this->sku);
        }
        return $this->_product;
    }
    
    function __sleep() {
        $this->_product = null;
        return array_keys(get_object_vars($this));
    }
}
