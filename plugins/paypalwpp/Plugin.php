<?php
/**
 * @package paypalwpp
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package
 */
class paypalwpp_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'PayPal Website Payments Pro',
            'version' => '0.2',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('mm', 'payment', 'cart'));
    }
    
    function isInstallable()
    {
        return $_SESSION['MM_CONFIG']['prereqs']['soap'];
    }

    function install()
    {
        // Add payment methods to database
        $direct = new paypalwpp_DirectMethod;
        if (!$direct->install()) {
            $this->addErrors($direct->errors);
            return false;
        }
        
        $express = new paypalwpp_ExpressMethod;
        if (!$express->install()) {
            $this->addErrors($express->errors);
            return false;
        }
        
        return true;
    }

    function uninstall()
    {
        // Remove payment methods from database
        $direct = new paypalwpp_DirectMethod;
        $direct->uninstall();
        $express = new paypalwpp_ExpressMethod;
        $express->uninstall();
        $base = new paypalwpp_BaseMethod;
        $base->uninstall();
    }

    function init()
    {
        $dao = new payment_PaymentMethodDAO;
        $express_method = $dao->fetchByName('paypalwpp_express');
        if ($express_method->active) {
            mvc_Controller::registerController('cart',      'paypalwpp_ExpressCartController');
        }
    }
    
    function upgrade_to_0_1() {
        $db = mm_getDatabase();
        $db->execute("ALTER TABLE mm_payment_method MODIFY class varchar(255)");
        $row = $db->getOneAssoc("select * from mm_payment_method where name LIKE 'paypalwpp%'");
        if (!$row) {
            $db->execute("INSERT INTO mm_payment_method (name, active, class, sortorder, public_title) VALUES ('paypalwpp_express', 1, 'paypalwpp_ExpressMethod', 0, 'PayPal')");
            $db->execute("INSERT INTO mm_payment_method (name, active, class, sortorder, public_title) VALUES ('paypalwpp_direct', 1, 'paypalwpp_DirectMethod', 0, 'Credit Card')");
            $dao = new payment_PaymentMethodDAO();
            $dao->resetMethods();
        }

        mm_setSetting("payment_method.paypalwpp_express.currency_id", "USD");
        mm_setSetting("payment_method.paypalwpp_express.title", "PayPal");
        mm_setSetting("payment_method.paypalwpp_express.environment", "sandbox");
        mm_setSetting("payment_method.paypalwpp_express.api_username_Sandbox", null);
        mm_setSetting("payment_method.paypalwpp_express.api_password_Sandbox", null);
        mm_setSetting("payment_method.paypalwpp_express.api_signature_Sandbox", null);
        mm_setSetting("payment_method.paypalwpp_express.api_certificate_Sandbox", null);
        
        mm_setSetting("payment_method.paypalwpp_direct.currency_id", "USD");
        mm_setSetting("payment_method.paypalwpp_direct.title", "PayPal");
        mm_setSetting("payment_method.paypalwpp_direct.environment", "sandbox");
        mm_setSetting("payment_method.paypalwpp_direct.api_username_Live", null);
        mm_setSetting("payment_method.paypalwpp_direct.api_password_Live", null);
        mm_setSetting("payment_method.paypalwpp_direct.api_signature_Live", null);
        mm_setSetting("payment_method.paypalwpp_direct.api_certificate_Live", null);
        
        return true;
    }
    
    function upgrade_to_0_2() {
        // TODO: Rename properties:
        //   apiusername_Sandbox to apiusername_sandbox
        //   apiusername_Live to apiusername_live
        //   apipassword_Live to apipassword_live
        //   certificate_Sandbox to certificate_sandbox
        
        $methods = array(array('name' => 'paypalwpp_express', 'title' => "PayPal"),
            array('name' => 'paypalwpp_direct', 'title' => 'Credit Card'));
        foreach ($methods as $m) {
            $name = $m['name'];
            $default_title = $m['title'];
            $pdao = new payment_PaymentMethodDAO;
            $method = $pdao->fetchByName($name);
            $method->public_title = mm_getSetting("payment_method.$name.title", $default_title);
            mm_removeSetting("payment_method.$name.title");
            
            $method->environment = strtolower(mm_getSetting("payment_method.$name.environment", "sandbox"));
            mm_removeSetting("payment_method.$name.environment");
            $method->currency_id = mm_getSetting("payment_method.$name.currency_id", 'USD');
            mm_removeSetting("payment_method.$name.currency_id");

            foreach (array('sandbox', 'live') as $environment) {
                $method->$environment->api_username = mm_getSetting("payment_method.$name.api_username_" . ucfirst($environment));
                mm_removeSetting("payment_method.$name.api_username_" . ucfirst($environment));
                $method->$environment->api_password = mm_getSetting("payment_method.$name.api_password_" . ucfirst($environment));
                mm_removeSetting("payment_method.$name.api_password_" . ucfirst($environment));
                $method->$environment->api_signature = mm_getSetting("payment_method.$name.api_signature_" . ucfirst($environment));
                mm_removeSetting("payment_method.$name.api_signature_" . ucfirst($environment));
                $method->$environment->api_certificate = mm_getSetting("payment_method.$name.api_certificate_" . ucfirst($environment));
                mm_removeSetting("payment_method.$name.api_certificate_" . ucfirst($environment));
            }

            if (!$method->save()) {
                $this->addErrors($method->errors);
                return false;
            }
        }
        return true;
    }
}
