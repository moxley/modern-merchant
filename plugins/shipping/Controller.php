<?php
/**
 * @package shipping
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class shipping_Controller extends admin_Controller
{
    private $dao;
    
    function __construct()
    {
        parent::__construct();
        $this->edit = true;
        $this->dao = new shipping_ShippingMethodDAO;
    }
    
    function runListAction()
    {
        $this->count = $this->dao->getCount();
        $this->offset = $this->getOffset();
        $this->max_results = $this->getMaxResults();
        $options = array(
            'offset' => $this->offset,
            'limit' => $this->max_results,
            'order' => 'sortorder');
        $this->shipping_methods = $this->dao->find($options);
        
        $params['action'] = "shipping.list";

        $this->results_nav = $this->getResultsNav(
            $this->count,
            $this->offset,
            $this->max_results,
            $this->max_links,
            $params
        );
        $this->search_script = "shipping";
        
        $this->title = "Shipping Methods";
    }
    
    function requireShippingMethod()
    {
        $id = $this->getRequiredParam('id');
        $shipping_method = $this->dao->fetch($id);
        if (!$shipping_method) {
            throw new Exception("Failed to find shipping method for the given id");
        }
        return $shipping_method;
    }
    
    function runEditAction()
    {
        $this->shipping_method = $this->requireShippingMethod();
        $this->target_action = "shipping.update";
        $this->title = "Edit Shipping Method";
    }
    
    function runNewAction()
    {
        $this->shipping_method = new shipping_ShippingMethod;
        $this->target_action = 'shipping.add';
        $this->title = "New Shipping Method";
    }
        
    function runUpdateAction()
    {
        $this->shipping_method = $this->requireShippingMethod();
        $this->shipping_method->setPropertyValues($this->req('shipping_method'));
        $this->shipping_method->save();
        $this->addNotice("Shipping method successfully updated.");
        $this->redirectToAction('shipping');
        return false;
    }
    
    function runDefaultAction() {
        $this->setForward('shipping.list');
    }
    
    function runDeactivateAction()
    {
        $this->shipping_method = $this->requireShippingMethod();
        $this->shipping_method->deactivate();
        $this->addNotice("Deactivated shipping method");

        $this->redirectToAction('shipping');
        return false;
    }

    function runActivateAction()
    {
        $this->shipping_method = $this->requireShippingMethod();
        $this->shipping_method->activate();
        $this->addNotice("Activated shipping method");

        $this->redirectToAction('shipping');
        return false;
    }
    
    function runAddAction()
    {
        $this->shipping_method = new shipping_ShippingMethod($this->req('shipping_method'));
        $this->shipping_method->save();
        $this->addNotice("Method successfully added");
        $this->redirectToAction("shipping.edit", array('id'=>$this->shipping_method->id));
        return false;
    }
    
    function runDeleteAction()
    {
        $this->shipping_method = $this->requireShippingMethod();
        $this->shipping_method->delete();
        $this->addNotice("ShippingMethod successfully deleted.");
        $this->redirectToAction('shipping');
        return false;
    }
    
    function runCancelAction()
    {
        $this->addNotice("Action cancelled");
        $this->redirectToAction('shipping');
        return false;
    }
    
    function runSetDefaultAction()
    {
        $this->shipping_method = $this->requireShippingMethod();
        $this->shipping_method->is_default = true;
        $this->shipping_method->save();
        $this->redirectToAction('shipping');
        return false;
    }
    
    function preViewFilter($action)
    {
        parent::preViewFilter($action);
        $this->nav_template = "shipping/_nav";
    }
}
