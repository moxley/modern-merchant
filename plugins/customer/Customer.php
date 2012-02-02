<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package customer
 */
class customer_Customer extends mvc_Model
{
    public $billing_address_id;
    public $shipping_address_id;
    public $user_id;
    public $modify_user;
    public $created_on;
    
    private $_billing_address;
    private $_shipping_address;
    private $_user;
    
    static function find($options=array())
    {
        $dao = new customer_CustomerDAO;
        return $dao->find($options);
    }
    
    static function fetchByUser($user)
    {
        $dao = new customer_CustomerDAO;
        $options = array(
            'where' => array('user_id=?', $user->id)
        );
        return $dao->fetch($options);
    }
    
    static function fetch($id)
    {
        $dao = new customer_CustomerDAO;
        return $dao->fetch($id);
    }
    
    function getBilling()
    {
        return $this->getBillingAddress();
    }
    
    /**
     * @return addr_Address
     */
    function getBillingAddress()
    {
        if (!$this->_billing_address) {
            if ($this->billing_address_id) {
                $adao = new addr_AddressDAO;
                $this->_billing_address = $adao->fetch($this->billing_address_id);
            }
            else {
                $this->_billing_address = new addr_Address;
            }
        }
        return $this->_billing_address;
    }
    
    function setBilling($billing)
    {
        $this->setBillingAddress($billing);
    }
    
    function setBillingAddress($addr)
    {
        if (is_object($addr)) {
            $this->_billing_address = $addr;
        }
        else {
            $this->billing_address->property_values = $addr;
        }
    }
    
    function getShipping()
    {
        return $this->getShippingAddress();
    }
    
    /**
     * @return addr_Address
     */
    function getShippingAddress()
    {
        if (!$this->_shipping_address) {
            if ($this->shipping_address_id) {
                $adao = new addr_AddressDAO;
                $this->_shipping_address = $adao->fetch($this->shipping_address_id);
            }
            else {
                $this->_shipping_address = new addr_Address;
            }
        }
        return $this->_shipping_address;
    }
    
    function setShipping($shipping)
    {
        $this->setShippingAddress($shipping);
    }
    
    function setShippingAddress($addr)
    {
        if (is_object($addr)) {
            $this->_shipping_address = $addr;
        }
        else {
            $this->shipping_address->property_values = $addr;
        }
    }
    
    /**
     * Copy the given shipping data to the customer's shipping record.
     */
    function updateShipping($shipping)
    {
        $new_record = !$this->shipping_address_id;
        $this->shipping_address->setValuesFromObj($shipping);
        if (!$this->shipping_address->save()) {
            $this->addErrors($this->shipping_address->errors);
            return false;
        }
        else {
            $this->shipping_address_id = $this->shipping_address->id;
            $db = mm_getDatabase();
            $db->execute("UPDATE mm_customer SET shipping_address_id=? WHERE id=?",
                array($this->shipping_address_id, $this->id));
            return true;
        }
    }

    function updateBilling($billing)
    {
        $new_record = !$this->billing_address_id;
        $this->billing_address->setValuesFromObj($billing);
        if (!$this->billing_address->save()) {
            $this->addErrors($this->billing_address->errors);
            return false;
        }
        else {
            $this->billing_address_id = $this->billing_address->id;
            $db = mm_getDatabase();
            $db->execute("UPDATE mm_customer SET billing_address_id=? WHERE id=?",
                array($this->billing_address_id, $this->id));
            return true;
        }
    }
    
    function save()
    {
        // Skip required fields
        if ($this->modify_user && $this->modify_user->isAdmin()) {
            $this->shipping_address->skip_required = true;
            $this->billing_address->skip_required = true;
        }
        
        if ($this->_shipping_address) {
            if (!$this->shipping_address->is_valid) {
                $this->addErrors(array_map(create_function('$e', 'return "Shipping - $e";'), $this->shipping_address->errors));
            }
            else {
                if (!$this->_shipping_address->save()) {
                    return false;
                }
                $this->shipping_address_id = $this->shipping_address->id;
            }
        }
        
        if ($this->_billing_address) {
            if (!$this->billing_address->is_valid) {
                $this->addErrors(array_map(create_function('$e', 'return "Billing - $e";'), $this->billing_address->errors));
            }
            else {
                if (!$this->billing_address->save()) {
                    return false;
                }
                $this->billing_address_id = $this->billing_address->id;
            }
        }
        
        if ($this->user_id || $this->user->username) {
            if (!$this->user->save()) {
                $this->addErrors($this->user->errors);
            }
            $this->user_id = $this->user->id;
        }

        if ($this->errors) return false;
        
        return parent::save();
    }

    function getUser() {
        if (!$this->_user) {
            if ($this->user_id) {
                $this->_user = mvc_Model::fetch('user_User', $this->user_id);
            }
            else {
                $this->_user = new user_User(array('type' => 3));
            }
        }
        return $this->_user;
    }
    
    function setUser($user) {
        if (is_object($user)) {
            $this->_user = $user;
            $this->user_id = $user->id;
        }
        else {
            $this->user->property_values = $user;
        }
    }
    
    function findOrders($options=array())
    {
        $odao = new order_OrderDAO;
        $options['where'] = array('customer_id=?', $this->id);
        return $odao->find($options);
    }
    
    function delete() {
        if (!parent::delete()) return false;
        return $this->user->delete();
    }
}
