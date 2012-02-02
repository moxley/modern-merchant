<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class customer_Controller extends mvc_PublicController
{
    private $_dao;

    function beforeAction($action)
    {
        if (!$action || $action == "default") return true;
        $customer = mm_getCustomer();
        if (!$customer) {
            $this->addWarning("Please log in to access this area");
            $this->redirectToAction("user.login", array('transition' => 'customer.account'));
            return false;
        }
        return true;
    }
    
    function runDefaultAction()
    {
        // Empty
    }

    function runAccountAction()
    {
        
    }
    
    function runUserAction()
    {
        $this->customer = mm_getCustomer();
        if ($this->is_post) {
            $values = $this->req('customer');
            $this->customer->billing->email = $values['billing']['email'];
            $this->customer->billing->save();
            $this->addNotice("Updated your account");
        }
        else {
            if (!$this->cusomter) {
                $this->customer = new customer_Customer;
                $this->customer->user = mm_getUser();
            }
        }
    }
    
    function runOrdersAction()
    {
        $this->customer = mm_getCustomer();
        if ($this->customer) {
            $this->orders = $this->customer->findOrders(array('offset' => 0, 'limit' => 20, 'order' => 'order_date desc'));
        }
        else {
            $this->orders = array();
        }
    }
    
    function getDao()
    {
        if (!$this->_dao) {
            $this->_dao = new customer_CustomerDAO;
        }
        return $this->_dao;
    }
}
