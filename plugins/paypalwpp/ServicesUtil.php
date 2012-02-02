<?php
/**
 * @package paypalwpp
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypalwpp_ServicesUtil
{
    private $profile;
    private $caller;
    private static $instance;
    const timeout = 12;

    private function __construct() {}

    public static function getCaller()
    {
        if (!isset(self::$instance)) self::$instance = new paypalwpp_ServicesUtil;
        return self::$instance->_getCaller();
    }
    
    public function _getCaller()
    {
        if (!$this->profile) {
            include_once 'Services/PayPal/Profile/Handler/Array.php'; 
            include_once 'Services/PayPal/Profile/API.php'; 

            $environment = mm_getSetting('payment_method.paypalwpp.environment');
            $apiusername = mm_getSetting('payment_method.paypalwpp.apiusername.' . $environment);
            $apipassword = mm_getSetting('payment_method.paypalwpp.apipassword.' . $environment);
            $subject     = mm_getSetting('payment_method.paypalwpp.subject');
        
            $certfile = dirname(__FILE__) . "/certs/cert_key_pem.txt";

            $handler = ProfileHandler_Array::getInstance(
                array(
                    'username'        => $apiusername,
                    'certificateFile' => $certfile,
                    'subject'         => $subject,
                    'environment'     => $environment));

            $id = $handler->generateID();

            $this->profile = APIProfile::getInstance($id, $handler);
            $this->profile->setAPIPassword($apipassword);
        }
        
        $this->caller = Services_PayPal::getCallerServices($this->profile);
        if (Services_PayPal::isError($this->caller)) {
            throw new mm_InfrastructureException(
                "Failed to get PayPal WPP caller: " . $this->caller->getMessage());
        }
        $this->caller->setOpt('timeout', self::timeout);

        return $this->caller;
    }

}
