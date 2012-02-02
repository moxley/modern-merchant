<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package authnet
 */
class authnet_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Authorize.net AIM',
            'version' => '0.2',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('mm', 'cart', 'payment'));
    }
    
    function init()
    {
        // Set AUTHNET_DEBUG_STUB to TRUE if you want to try out the
        // Authorize.net checkout without hitting the Authorize.net
        // payment gateway server.
        // Otherwise, set to FALSE
        define('AUTHNET_DEBUG_STUB', mm_getSetting('checkout.payment.debug_mode'));
    }

    function install()
    {
        $authnet = new authnet_AuthNet;
        return $authnet->install();
    }

    function uninstall()
    {
        $authnet = new authnet_AuthNet;
        $authnet->uninstall();
    }
    
    function upgrade_to_0_2()
    {
        $this->public_title  = "Credit Card";
        $this->active        = true;
        $this->account_id    = "authnetuser";
        $this->test_mode     = 'hard_test';
        $this->tran_key      = 'abc123xyzHHEELLLLOO';
        $this->email_receipt = false;
        $this->trans_type    = "AUTH_CAPTURE";
        
        $pdao = new payment_PaymentMethodDAO;
        $authnet = $pdao->fetchByName('authnet');
        
        $authnet->public_title = mm_getSetting('payment_method.authnet.title', "Credit Card");
        $authnet->account_id = mm_getSetting('payment_method.authnet.account_id');
        $authnet->test_mode = mm_getSetting('payment_method.authnet.test_mode') ? 'soft_test' : 'live';
        $authnet->tran_key = mm_getSetting('payment_method.authnet.password');
        $authnet->email_receipt = mm_getSetting('payment_method.authnet.email_receipt') ? true : false;
        $authnet->trans_type = mm_getSetting('payment_method.authnet.trans_type');

        if (!$authnet->save()) {
            $this->addErrors($authnet->errors);
            return false;
        }
        
        mm_removeSetting('payment_method.authnet.title');
        mm_removeSetting('payment_method.authnet.enable');
        mm_removeSetting('payment_method.authnet.account_id');
        mm_removeSetting('payment_method.authnet.test_mode');
        mm_removeSetting('payment_method.authnet.password');
        mm_removeSetting('payment_method.authnet.email_receipt');
        mm_removeSetting('payment_method.authnet.trans_type');
        mm_removeSetting('payment_method.authnet.account_email');
        mm_removeSetting('payment_method.authnet.currency');
        
        return true;
    }
}
