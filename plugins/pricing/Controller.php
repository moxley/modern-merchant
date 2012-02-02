<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class pricing_Controller extends admin_Controller
{
    private $dao;
    
    function __construct() 
    {
        parent::__construct();
        $this->dao = new pricing_PricingDAO;
    }

    function runDefaultAction()
    {
        $this->setForward('pricing.list');
    }
    
    function runNewAction()
    {
        $this->pricing = new pricing_Pricing;
        $this->title = "New Pricing";
    }
    
    function runAddAction()
    {
        $this->pricing = new pricing_Pricing;
        $this->pricing->admin_values = $this->req('pricing');
        if (!$this->pricing->save()) {
            $this->addWarnings($this->pricing->errors);
            $this->setTemplate('pricing/new');
        } else {
            $this->addNotice('Pricing added');
            $this->redirectToAction('pricing.edit', array('id'=>$this->pricing->id));
            return false;
        }
    }
    
    function runEditAction()
    {
        $this->pricing = $this->requirePricing();
        $this->title = "Edit Pricing";
    }
    
    function runUpdateAction()
    {
        $this->pricing = $this->requirePricing();
        $this->pricing->admin_values = $this->req('pricing');
        if (!$this->pricing->save()) {
            $this->addWarnings($this->pricing->errors);
            $this->setTemplate('pricing/edit');
        } else {
            $this->addNotice('Pricing updated');
            $this->redirectToAction('pricing');
            return false;
        }
    }
    
    function runListAction()
    {
        $this->pricings = $this->dao->getList(0, 100);
        $this->title = "Pricings";
    }
    
    function runDeleteAction()
    {
        $this->pricing = $this->requirePricing();
        $this->pricing->delete();
        $this->addNotice('Pricing deleted');
        $this->redirectToAction('pricing');
        return false;
    }
    
    /*
     * Utility methods
     */
    
    function requirePricing()
    {
        $id = $this->getRequiredParam('id');
        $pricing = $this->dao->fetch($id);
        if (!$pricing) {
            throw new Exception("Failed to find pricing for id=$id");
        }
        return $pricing;
    }
}
