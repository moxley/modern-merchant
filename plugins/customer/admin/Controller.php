<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class customer_admin_Controller extends admin_Controller
{
    private $_dao;
    
    function runDefaultAction()
    {
        $this->setForward('customer_admin.list');
    }
    
    function runListAction()
    {
        $this->count = $this->dao->count();
        $this->offset = $this->req('offset', 0);
        $this->customers = customer_Customer::find(array(
            'offset' => $this->offset,
            'limit' => $this->max_results));
        $this->results_nav = $this->getResultsNav(
            $this->count,
            $this->offset,
            $this->max_results,
            8,
            array('a' => 'order.list'));
            
        $this->title = "Customers";
    }
    
    function runSearchAction()
    {
        $this->offset = $this->req('offset', 0);
        $this->q = $this->req('q');
        $this->max_per_page = 50;
        $this->max_links = 10;
        list($this->customers, $this->count) = $this->dao->findBySearch($this->q, $this->offset, $this->max_per_page);

        $extra_params = array('a'=>'customer_admin.search');
        $extra_params['q'] = $this->q;
        $this->results_nav = $this->getResultsNav(
            $this->count,
            $this->offset,
            $this->max_per_page,
            $this->max_links,
            $extra_params);

        $this->setTemplate('customer/admin/list');
    }
    
    function runNewAction()
    {
        $this->customer = new customer_Customer;
        $this->title = "Create Customer Account";
    }
    
    function runAddAction()
    {
        $this->customer = new customer_Customer($this->req('customer'));
        $this->customer->modify_user = mm_getUser();
        if (!$this->customer->save()) {
            $this->addWarnings($this->customer->errors);
            $this->setTemplate('customer/admin/new');
        } else {
            $this->addNotice("Created customer account");
            $this->redirectToAction('customer_admin.edit', array('id' => $this->customer->id));
            return false;
        }
    }
    
    function runEditAction()
    {
        $this->requireCustomer();
        $this->title = "Edit Customer Details";
    }
    
    function runUpdateAction()
    {
        $this->requireCustomer();
        $this->customer->property_values = $this->req('customer');
        $this->customer->modify_user = mm_getUser();
        if (!$this->customer->save()) {
            $this->addWarnings($this->customer->errors);
            $this->setTemplate('customer/admin/edit');
            return;
        }
        $this->addNotice("Updated account");
        $this->redirectToAction('customer_admin.list');
        return false;
    }
    
    function runDeleteAction()
    {
        $this->requireCustomer();
        $this->customer->delete();
        $this->addNotice("Deleted customer account");
        $this->redirectToAction('customer_admin.list');
        return false;
    }

    function requireCustomer()
    {
        $id = $this->getRequiredParam('id');
        $this->customer = $this->dao->fetch($id);
        if (!$this->customer) {
            throw new Exception("Failed to find customer for id=$id");
        }
    }
    
    function getDao()
    {
        if (!$this->_dao) {
            $this->_dao = new customer_CustomerDAO;
        }
        return $this->_dao;
    }
    
    function preViewFilter($action)
    {
        parent::preViewFilter($action);
        $this->nav_template = "customer/admin/_nav";
    }
}
