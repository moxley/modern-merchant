<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_Events
{
    private $listeners = array();
    private static $instance;
    
    private function __construct() {}

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new mvc_Events;
        }
        return self::$instance;
    }
    
    static function listen($event_name, $function)
    {
        self::instance()->addListener($event_name, $function);
    }
    
    static function notify($event_name, &$param)
    {
        self::instance()->notifyListeners($event_name, $param);
    }
    
    public function addListener($event_name, $function)
    {
        $this->listeners[$event_name][] = $function;
    }
    
    public function notifyListeners($event_name, &$param)
    {
        if (array_key_exists($event_name, $this->listeners)) {
            foreach ($this->listeners[$event_name] as $function) {
                call_user_func($function, $param);
            }
        }
    }
}
