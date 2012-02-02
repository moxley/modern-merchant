<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package paypal
 */
class paypal_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'PayPal IPN',
            'version' => '0.2',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('mm', 'payment', 'cart'));
    }
    
    function init()
    {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/paypal', 'action'=>'paypal.transactions', 'label' => 'Paypal IPN Transactions'));
        
        // Set PAYPAL_DEBUG_STUB to TRUE if you want to try out the
        // PayPal checkout without making a real PayPal payment
        // Otherwise, set to FALSE
        define('PAYPAL_DEBUG_STUB', mm_getSetting('checkout.payment.debug_mode'));
    
        if (isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], '=Return+To+Merchant') !== FALSE) {
            mm_log("Redirecting to thank you page");
            redirect(mm_getConfigValue('urls.cart.thank_you'));
            exit;
        }
    }

    function install()
    {
        $queries[] = "DROP TABLE IF EXISTS `mm_paypal_ipn_trans`";
        $queries[] = "CREATE TABLE `mm_paypal_ipn_trans` (" .
            "id int NOT NULL auto_increment," .
            "creation_date       datetime default NULL," .
            "postdata            text," .
            "txn_id              varchar(30) default NULL," .
            "status              enum('Canceled_Reversal','Completed','Denied','Failed','Pending','Refunded','Reversed') default NULL," .
            "cart_id             int default null," .
            "order_id            int default NULL," .
            "sid                 varchar(127) default NULL," .
            "unique_code         varchar(30) default NULL," .
            "PRIMARY KEY  (`id`)," .
            "UNIQUE KEY `txn_id` (`txn_id`)," .
            "KEY (sid)," .
            "KEY (cart_id)" .
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $dbh = mm_getDatabase();
        foreach ($queries as $query) {
            $dbh->query($query);
        }
        
        $paypal = new paypal_PayPal;
        $newly_installed = $paypal->install();
        return $newly_installed;
    }

    function uninstall()
    {
        $paypal = new paypal_PayPal;
        $paypal->uninstall();
    }

    function upgrade_to_0_2() {
        $pdao = new payment_PaymentMethodDAO;
        $paypal = $pdao->fetchByName('paypal');
        $db = mm_getDatabase();
        $paypal->public_title = mm_getSetting('payment_method.paypal.title', "PayPal");
        $paypal->account_email = mm_getSetting('payment_method.paypal.account_email');
        $paypal->currency = mm_getSetting('payment_method.paypal.currency');
        $paypal->webscr = mm_getSetting('payment_method.paypal.webscr');
        $paypal->ipn_notify = mm_getSetting('payment_method.paypal.ipn_notify');
        if (!$paypal->save()) {
            $this->addErrors($paypal->errors);
            return false;
        }
        mm_removeSetting('payment_method.paypal.title');
        mm_removeSetting('payment_method.paypal.account_email');
        mm_removeSetting('payment_method.paypal.currency');
        mm_removeSetting('payment_method.paypal.webscr');
        mm_removeSetting('payment_method.paypal.ipn_notify');
        
        $db = mm_getDatabase();
        $db->execute("DROP TABLE mm_paypal_ipn_trans_seq");
        $db->execute("ALTER TABLE mm_paypal_ipn_trans CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_paypal_ipn_trans CHANGE paypal_ipn_trans_id id int NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_paypal_ipn_trans CHANGE cart_id unique_code varchar(30) DEFAULT NULL");
        $db->execute("ALTER TABLE mm_paypal_ipn_trans ADD cart_id int DEFAULT NULL AFTER status");
        $db->execute("ALTER TABLE mm_paypal_ipn_trans ADD INDEX cart_id (cart_id)");
        $db->execute("ALTER TABLE mm_paypal_ipn_trans DROP session_id");
        return true;
    }
}
