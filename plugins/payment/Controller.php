<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class payment_Controller extends admin_Controller
{
    private $dao;
    
    function __construct()
    {
        $this->dao = new payment_PaymentMethodDAO;
    }
    
    function runDefaultAction()
    {
        $this->redirectToAction('payment.list');
        return false;
    }
    
    function runListAction()
    {
        $this->max_results = $this->getMaxResults();
        $this->offset = $this->getOffset();
        
        $this->count = $this->dao->getCount();
        $this->payment_methods = $this->dao->getList(array(
            'limit' => $this->max_results,
            'offset' => $this->offset
        ));
        
        // Get default shipping method
        $this->default_id = mm_getSetting('default_payment_method');
        
        $this->title = "Payment Methods";
    }
    
    function runEditAction()
    {
        $this->requireMethod();
        $this->payment_method->preProcessSettingsForm($this);
        $this->settings_form_html = $this->payment_method->getSettingsFormHtml($this);
        $this->render_started = false;
        
        $this->title = "Edit Payment Method";
    }
    
    function runUpdateAction()
    {
        $this->requireMethod();
        $this->payment_method->property_values = $this->req('payment_method');
        $this->payment_method->postProcessSettingsForm($this);

        if (!$this->payment_method->save()) {
            
            $this->addWarnings($this->payment_method->errors);
            $this->setTemplate('payment/edit');
        }
        else {
            $this->addNotice("Payment method successfully updated.");
            $this->redirectToAction('payment');
            return false;
        }
    }
    
    function runDeactivateAction()
    {
        $this->requireMethod();
        $this->payment_method->active = false;
        $this->payment_method->save();
        $this->redirectToAction('payment');
        return false;
    }
        
    function runActivateAction()
    {
        $this->requireMethod();
        if (!$this->payment_method->activate()) {
            $this->addWarnings($this->payment_method->errors);
        }
        $this->redirectToAction('payment');
        return false;
    }
    
    function runCancelAction()
    {
        $this->addNotice("Action cancelled");
        $this->redirectToAction('payment');
        return false;
    }
    
    function runSetDefaultAction()
    {
        $this->requireMethod();
        mm_setSetting('default_payment_method', $this->payment_method->id);
        $this->redirectToAction('payment');
        return false;
    }
    
    /************************
     **
     ** Utility Methods
     **
     ************************/
    
    function requireMethod()
    {
        $id = $this->getRequiredParam('id');
        $this->payment_method = $this->dao->fetch($id);
        if (!$this->payment_method) {
            throw new Exception("Failed to find payment method for given id");
        }
    }
}
