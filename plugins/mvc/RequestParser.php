<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_RequestParser extends mvc_Model
{
    public $request = null;
    public $action = null;
    public $action_uri = null;
    public $view = null;
    public $module = null;
    public $params = array();
    public $mvc_direction = 'action';
    
    function __construct($request=array())
    {
        $this->request = $request;
    }
    
    function reset()
    {
        $this->request = null;
        $this->action = null;
        $this->view = null;
        $this->module = null;
        $this->params = array();
        $this->mvc_direction = 'action';
    }
    
    /**
     * Set the request.
     */
    function setRequest(&$request)
    {
        $this->request =& $request;
    }
    
    /**
     * Parse the request
     */
    function parse()
    {
        if (!is_array($this->request))
        {
            throw new Exception("\$this->request is not set or is not an array");
        }
        
        $this->action_address = getAction($this->request);
        if (!$this->action_address) return;
        
        $this->parseAction($this->action_address);
    }
    
    /**
     * Parse the request
     */
    function parseAction($action_address)
    {
        $this->action_address = $action_address;
        $this->reset();
        if (!$action_address) return;
        
        $parts = explode('?', $action_address);
        if (count($parts) > 1) {
            $pairs = explode('&', $parts[1]);
            $this->params = array();
            foreach ($pairs as $keyvalue_str) {
                $keyvalue = explode('=', $keyvalue_str);
                $key = urldecode($keyvalue[0]);
                $value = urldecode($keyvalue[1]);
                $this->params[$key] = $value;
            }
            $action_address = $parts[0];
        }
        
        $parts = explode(':', $action_address);
        $this->mvc_direction = 'action';
        if (count($parts) > 1) {
            $this->mvc_direction = $parts[0];
            $action_address = $parts[1];
        }
        $this->action_uri = $action_address;
        
        $parts = explode('.', $action_address);
        if (count($parts) > 1)
        {
            $this->module = $parts[0];
            $action_or_display = $parts[1];
        }
        else
        {
            $this->module = $parts[0];
            $action_or_display = '';
        }
        if ($this->mvc_direction == 'view' || $this->mvc_direction == 'return') {
            $this->display = $action_or_display;
        }
        else {
            $this->action = $action_or_display;
        }
        
        return;
    }
}
