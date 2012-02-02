<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class cart_DummyShippingMethod extends shipping_ShippingMethod
{
    function __construct($values=array()) {
        parent::__construct($values);
        $this->name = "dummy_" . uniqid('');
    }

    function getDao() {
        if (!$this->_dao) {
            $this->_dao = new shipping_ShippingMethodDAO;
        }
        return $this->_dao;
    }
}
