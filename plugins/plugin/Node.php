<?php
/**
 * @package plugin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class plugin_Node extends mvc_Model
{
    static $all;
    public $plugin;
    public $children;
    
    function __construct($plugin=null, $children=array()) {
        if (!isset(self::$all)) {
            self::$all = array();
        }
        self::$all[] = $this;
        $this->plugin = $plugin;
        $this->children = $children;
    }
    
    function getName() {
        return $this->plugin->name;
    }
    
    function getParent() {
        foreach (self::$all as $node) {
            if ($node->name == $this->name) continue;
            foreach ($node->children as $child) {
                if ($child->name == $this->name) return $node;
            }
        }
        return null;
    }
    
    function dependsOn($node) {
        if (in_array($node->plugin->name, $this->plugin->depends)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Attempt to add child to this hierarchy.
     *
     * @return boolean True if child was added.
     */
    function addChild($node) {
        if ($node->plugin->name == $this->plugin->name) return false;

        // Is node too high in hierachy?
        // If so, add it to the appropriate child
        // Is a child too high in the hierarchy?
        // If so, remove it from this, and add it to node
        foreach ($this->children as $i=>$child) {
            if ($child->dependsOn($node)) {
                // Remove child from this, and add it to node
                $this->children[$i] = $node;
                $node->addChild($child);
                return true;
            }
            else {
                if ($child->addChild($node)) {
                    return true;
                }
            }
        }
        if ($node->dependsOn($this)) {
            $this->children[] = $node;
            return true;
        }
        return false;
    }
    
    function size() {
        return count($this->children);
    }
}

