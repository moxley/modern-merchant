<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_ActionCallback
{
    public $action_uri;
    public $callback;
    public $priority;
    
    function __construct($action_uri, $callback, $priority=0)
    {
        $this->action_uri = $action_uri;
        $this->callback = $callback;
        $this->priority = $priority;
    }
}
