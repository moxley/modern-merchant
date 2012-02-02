<?php
/**
 * @package plugin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package plugin
 */
class plugin_Sorter
{
    public $plugins;
    public $heads;
    
    function __construct($plugins=array())
    {
        $this->plugins = $plugins;
    }
    
    function getHeadAndRemainders($plugins)
    {
        $plugins_to_add = array();
        if (count($plugins) == 1) {
            return array($plugins, array());
        }
        
        // Get ones without dependencies
        $with_dependencies = array();
        $without_dependencies = array();
        $kernel_plus_mm = array_merge(plugin_Base::$kernel_names, array('mm'));
        foreach ($plugins as $plugin) {
            if (!$plugin->depends && in_array($plugin->name, $kernel_plus_mm)) {
                $without_dependencies[] = $plugin;
            }
            else {
                if ($plugin->name != 'mm' && !in_array('mm', $plugin->depends)) {
                    $plugin->addDepend('mm');
                }
                $with_dependencies[] = $plugin;
                $plugins_to_add[] = $plugin;
            }
        }
        
        // Get ones without dependents
        $heads = array();
        foreach ($without_dependencies as $plugin) {
            if ($this->hasDependents($plugin, $with_dependencies)) {
                $heads[] = $plugin;
            }
            else {
                $plugins_to_add[] = $plugin;
            }
        }
        return array($heads, $plugins_to_add);
    }
    
    /**
     * Get the plugins that should have top priority.
     */
    function getHeadPlugins($plugins)
    {
        list($heads, $remainders) = $this->getHeadAndRemainders($plugins);
        return $heads;
    }

    function hasDependents($plugin, $plugins)
    {
        foreach ($plugins as $p) {
            if ($p->depends && in_array($plugin->name, $p->depends)) return true;
        }
        return false;
    }
    
    function addToNode($node, $child)
    {
        if ($child->dependsOn($node)) {
            $node->addChild($child);
            return true;
        }
        else {
            foreach ($node->children as $c) {
                if ($this->addToNode($c, $child)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Incomplete
     */
    function makeHierarchy($plugins=null)
    {
        if ($plugins) $this->plugins = $plugins;
        if (!$this->plugins) return null;
        
        list($head_plugins, $plugins_to_add) = $this->getHeadAndRemainders($this->plugins);
        
        if (!$head_plugins) {
            return array(array(), $this->plugins, array());
        }
        else if (count($this->plugins) == 1) {
            return array(array(new plugin_Node($this->plugins[0])), array(), array());
        }
        else {
            $this->heads = $this->wrapInNode($head_plugins);
            $nodes = $this->wrapInNode($plugins_to_add);
            $remaining_nodes = array();
            foreach ($this->heads as $head) {
                do {
                    $remaining_nodes = array();
                    foreach ($nodes as $node) {
                        if (!$this->addToNode($head, $node)) {
                            $remaining_nodes[] = $node;
                        }
                    }
                    $nodes = $remaining_nodes;
                    $count = count($remaining_nodes);
                    if (isset($remaining_nodes_count) && $count == $remaining_nodes_count) {
                        // No more nodes can be added
                        break;
                    }
                    $remaining_nodes_count = $count;
                }
                while ($remaining_nodes_count);
            }
            return array($this->heads, $remaining_nodes, array());
        }
    }

    function wrapInNode($plugins)
    {
        if (!$plugins) return null;
        if (!is_array($plugins)) {
            $plugins = array($plugins);
        }
        $nodes = array();
        foreach ($plugins as $p) {
            $nodes[] = new plugin_Node($p);
        }
        return $nodes;
    }
    
    function unWrapFromNode($nodes)
    {
        $plugins = array();
        foreach ($nodes as $node) {
            $plugins[] = $node->plugin;
        }
        return $plugins;
    }
    
    function sort()
    {
        $sorted = array();
        list($heads, $leftovers, $conflicts) = $this->makeHierarchy($this->plugins);
        $in_hierarchy = $this->collectFromTop($heads);
        if ($in_hierarchy) {
            $sorted = array_merge($sorted, $in_hierarchy);
        }
        if ($leftovers) {
            $sorted = array_merge($sorted, $this->unWrapFromNode($leftovers));
        }
        return $sorted;
    }

    function collectFromTop($nodes)
    {
        $sorted = array();
        foreach ($nodes as $node) {
            $sorted[] = $node->plugin;
        }
        
        foreach ($nodes as $node) {
             $sub_sorted = $this->collectFromTop($node->children);
            if ($sub_sorted) {
                $sorted = array_merge($sorted, $sub_sorted);
            }
        }
        return $sorted;
    }
}
