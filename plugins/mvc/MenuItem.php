<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_MenuItem extends mvc_Model
{
    public $path;
    public $priority = 0;
    public $label;
    public $image;
    public $url;
    public $_action;
    public $registry_id;
    
    public function getChildren() {
        return $this->registry->getSubMenus($this->path);
    }
    
    public function getRegistry() {
        return mvc_HookRegistry::instance($this->registry_id);
    }
    
    public function setAction($action) {
        $this->_action = $action;
        if (!$this->url && $this->_action) {
            $this->url = mm_actionToUri($this->_action);
        }
    }
    
    public function getAction() { return $this->_action; }
}
