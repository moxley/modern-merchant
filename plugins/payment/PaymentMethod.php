<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

define('PAYMENT_METHOD_SETTING_PREFIX', 'payment_method');
define('PAYMENT_METHOD_NOT_STARTED',    0);
define('PAYMENT_METHOD_PASSED',         1);
define('PAYMENT_METHOD_DECLINED',       2);
define('PAYMENT_METHOD_ERROR',          3);
define('PAYMENT_METHOD_IN_PROGRESS',    4);
define('PAYMENT_METHOD_PENDING',        5);

/**
 */
class payment_PaymentMethod extends mvc_Model
{
    public $id;
    public $active = false;
    public $public_title = "Payment Method";
    public $sortorder = 0;
    public $class;

    private $user_message;
    private $result_id = PAYMENT_METHOD_NOT_STARTED;
    private $dao;
    public $_settings;
    public $_name;
    private $_is_payed = false;
    
    static function defaultMethodId()
    {
        return mm_getSetting('default_payment_method');
    }
    
    function __construct($values=null)
    {
        parent::__construct($values);
        $this->class = get_class($this);
        $this->order = 0;
        $this->_settings = new stdClass;
        $this->dao = new payment_PaymentMethodDAO;
    }
    
    function getDao() {
        if (!$this->dao) {
            $this->dao = new payment_PaymentMethod;
        }
        return $this->dao;
    }

    function getuser_message()
    {
        return $this->user_message;
    }

    function getUserMessage()
    {
        return $this->user_message;
    }

    function setUserMessage($msg)
    {
        $this->user_message = $msg;
    }
    
    function processPayment(&$controller, &$cart)
    {
        return;
    }
        
    function getName()
    {
        return $this->_name ? $this->_name : $this->_class;
    }
    
    function setName($name)
    {
        $this->_name = $name;
    }
    
    /**
     * Render the payment method's form fields for the checkout's payment page.
     * 
     * <code>
     *   $this->controller = $controller;
     *   $this->controller->payment_method = $this;
     *   $this->controller->render('payment/credit_card');
     * </code>
     */
    function renderPaymentForm($controller)
    {
        $this->controller = $controller;
        $this->controller->payment_method = $this;
        $this->controller->render('payment/credit_card');
    }

    function getMonthOptions()
    {
        $months = array(1=>'January', 'February', 'March', 'April',
            'May', 'June', 'July', 'August',
            'September', 'October', 'November', 'December');
        $collection = array();    
        foreach ($months as $value=>$month) {
            $title = sprintf("%02d - %s", $value, $months[$value]);
            $collection[$value] = $title;
        }
        return $collection;
    }
    
    function getYearOptions()
    {
        $thisYear = date('Y');
        $collection = array();
        for($i=0; $i<10; $i++) {
            $value = $thisYear+$i;
            $title = $value;
            $collection[$value] = $title;
        }
        return $collection;
    }
    
    function &getUserFormHtml($controller)
    {
    }
        
    function preProcessUserform(&$form, $controller)
    {
        return;
    }
    
    /**
     * Validate the payment data
     */
    function validate()
    {
        parent::validate();
        if (!$this->public_title) {
            $this->addError("Please specify a Public Title for this payment method");
        }
        return $this->errors;
    }
    
    function validatePayment()
    {
        $this->validate();
        if (!$this->payment) {
            $this->addError("No payment details specified");
        }
        else if (is_object($this->payment) && method_exists($this->payment, 'validate')) {
            $this->addErrors($this->payment->validate());
        }
        return $this->errors;
    }
    
    function preProcessSettingsForm($controller)
    {
        return;
    }
        
    function postProcessSettingsForm($controller)
    {
        return;
    }
    
    function getSettingsFormHtml($controller)
    {
        ob_start();
        $controller->render($this->name . '/edit');
        return ob_get_clean();
    }
    
    function getVerifyHtml($controller, $input)
    {
        return $this->public_title;
    }

    /**
     * Start the processing of the payment.
     *
     * @return int
     * 
     * 0 = Processing started sucessfully
     * 1 = An error occured while trying to start payment processing
     */
    function process($cart)
    {
        $this->addError(get_class($this) . "::process() method is not implemented");
        return false;
    }
        
    /**
     * Payment result.
     *
     * @return int
     *
     * 0 = No payment processing has been started
     * 1 = Payment accepted
     * 2 = Payment rejected
     * 3 = Error
     * 4 = Processing is still in progress
     * 5 = Pending
     */
    function result()
    {
        return $this->result_id;
    }

    function setResult($result)
    {
        $this->result_id = $result;
    }
        
    /**
     * Handle user's return from external payment site.
     *
     * Override this method to handle situations where the user is
     * returning from an external payment web site. Some services
     * have a feature that lets the originating web site to check the
     * status of the payment, and this method could perform that check.
     */
    function returnFromPayment($controller, $input)
    {
    }

    /**
     * Indicates that this payment method plugin uses synchronous
     * processing (while the customer waits for a response),
     * as opposed to asynchronous processing that happens independently of
     * the customer's page requests.
     *
     * This is a plugin hook that may be overriden.
     */
    function synchronousProcessing()
    {
        return TRUE;
    }

    function isActive()
    {
        return $this->active ? true : false;
    }

    function getSortOrder()
    {
        return $this->order;
    }

    function install()
    {
        return $this->save();
    }

    function uninstall()
    {
        $this->delete($this);
    }
    
    function newPayment($values=array())
    {
        return new payment_CreditCardPayment($values);
    }
    
    function save()
    {
        if (!$this->is_valid) return false;
        $dao = new payment_PaymentMethodDAO;
         $dao->save($this);
        return true;
    }
    
    static function deleteAll()
    {
        $dao = new payment_PaymentMethodDAO;
        $dao->deleteAll();
    }
    
    function delete()
    {
        $this->dao->delete($this);
    }
    
    function setSettings($settings)
    {
        if (is_string($settings)) {
            $settings = unserialize($settings);
        }
        
        if (is_array($settings)) {
            //$this->_settings = new mvc_Model;
            //foreach ($settings as $k=>$v) $this->_settings->$k = $v;
            foreach ($settings as $k=>$v) $this->$k = $v;
        }
        //else {
        //    $this->_settings = $settings;
        //}
    }
    
    function getSettings()
    {
        if (!$this->_settings) {
            $this->_settings = new mvc_Model;
        }
        return $this->_settings;
    }
    
    function getSetting($name, $default=null)
    {
        return gv($this->_settings, $name, $default);
    }
    
    function getIsDefault()
    {
        return $this->id && mm_getSetting('default_payment_method') == $this->id;
    }
    
    function getIsPayed()
    {
        return $this->_is_payed;
    }
    
    function setIsPayed($is_payed)
    {
        $this->_is_payed = $is_payed;
    }
    
    function getPayment()
    {
        if ($this->cart) {
            return $this->cart->payment;
        }
    }
    
    function __toString()
    {
        $attrs = array(
            'id',
            'active',
            'public_title',
            'sortorder',
            'class',
            'user_message',
            'result_id',
            'name',
            'is_payed');
        $str = '';
        foreach ($attrs as $name) {
            $str .= "$name: {$this->$name}\n";
        }
        return $str;
    }
    
    public function activate()
    {
        $this->active = true;
        return $this->save();
    }
    
    public static function maskCCNumber($cc_number)
    {
        $last4 = substr($cc_number, -4);
        return str_repeat('x', strlen($cc_number)-4) . $last4;
    }
}
