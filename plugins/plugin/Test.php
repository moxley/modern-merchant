<?php
/**
 * @package plugin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package plugin
 */
class plugin_Test extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->manager = new plugin_Manager;
        $this->sorter = new plugin_Sorter;
    }
    
    function testGetPlugins()
    {
        $this->plugins = $this->manager->getPlugins();
        $this->assertTrue($this->plugins?true:false, "Errors: " . implode(', ', $this->manager->errors));
        //echo "plugins:\n";
        //foreach ($this->plugins as $plugin) {
        //    echo "  {$plugin->name}\n";
        //}
    }
    
    function testGetPlugins2()
    {
        $plugin_values = array(
            array('name' => 'access'),
            array('name' => 'addr'),
            array('name' => 'admin'),
            array('name' => 'auth'),
            array('name' => 'authnet', 'depends' => array('payment')),
            array('name' => 'bridgetown', 'depends' => array('content', 'catalog')),
            array('name' => 'bulkimages', 'depends' => array('mm')),
            array('name' => 'cart', 'depends' => array('mm')),
            array('name' => 'catalog', 'depends' => array('product', 'category')),
            array('name' => 'category', 'depends' => array('product')),
            array('name' => 'content', 'depends' => array()),
            array('name' => 'customer'),
            array('name' => 'db'),
            array('name' => 'media'),
            array('name' => 'mm', 'depends' => array('db', 'setting', 'sess', 'plugin', 'mvc')),
            array('name' => 'mminstall'),
            array('name' => 'mvc', 'depends' => array('setting')),
            array('name' => 'order'),
            array('name' => 'payment', 'depends' => array('mm')),
            array('name' => 'paypal', 'depends' => array('payment')),
            array('name' => 'paypalwpp', 'depends' => array('payment')),
            array('name' => 'pricing'),
            array('name' => 'product', 'depends' => array('mm')),
            array('name' => 'report'),
            array('name' => 'sess', 'depends' => array('db')),
            array('name' => 'setting', 'depends' => array('db')),
            array('name' => 'shipping'),
            array('name' => 'test', 'depends' => array('mm')),
            array('name' => 'theme', 'depends' => array('mvc')),
            array('name' => 'ups'),
            array('name' => 'user')
        );

        //$plugin_values = array(
        //    array('name' => 'db'),
        //    array('name' => 'mm', 'depends' => array('mvc')),
        //    array('name' => 'mvc', 'depends' => array('setting')),
        //    array('name' => 'setting', 'depends' => array('db'))
        //);
        $plugins = array();
        foreach ($plugin_values as $values) {
            $plugin = new plugin_Base;
            $plugin->name = $values['name'];
            if (gv($values, 'depends')) {
                $plugin->depends = $values['depends'];
            }
            $plugins[] = $plugin;
        }
        
        $plugins = $sorter = new plugin_Sorter($plugins);
        list($heads, $left_overs, $bad_dependencies) = $sorter->makeHierarchy();
        //echo "heads:\n";
        //foreach ($heads as $head) {
        //    echo "  {$head->plugin->name}\n";
        //}
        //echo "left_overs:\n";
        //foreach ($left_overs as $node) {
        //    echo "  {$node->plugin->name}\n";
        //}
        //echo "bad_dependencies:\n";
        //foreach ($bad_dependencies as $node) {
        //    echo "  {$node->plugin->name}\n";
        //}
        //
        $plugins = $sorter->sort();
        //
        //echo "Plugins:\n";
        //foreach ($plugins as $plugin) {
        //    echo "  " . $plugin->name . "\n";
        //}

        $this->assertEquals('db',      $plugins[0]->name, "Wrong order");
        $this->assertEquals('sess',    $plugins[1]->name, "Wrong order");
        $this->assertEquals('setting', $plugins[2]->name, "Wrong order");
        $this->assertEquals('mvc',     $plugins[3]->name, "Wrong order");
        $this->assertEquals('mm',      $plugins[4]->name, "Wrong order");
    }
    
    function testAddToHierarchy1()
    {
        $plugin = new plugin_Base(array('name' => 'head'));
        $head_node = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'a', 'depends' => array('head')));
        $node_a = new plugin_Node($plugin);
        
        $this->assertEquals(0, $head_node->size());
        $head_node->addChild($node_a);
        $this->assertEquals(1, $head_node->size());
    }
    
    function testDependsOn()
    {
        $plugin = new plugin_Base(array('name' => 'head'));
        $head_node = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'a', 'depends' => array('head')));
        $node_a = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'b', 'depends' => array('head')));
        $node_b = new plugin_Node($plugin);
        
        $this->assertTrue($node_a->dependsOn($head_node));
        
        $this->assertFalse($head_node->dependsOn($node_a));

        $this->assertFalse($node_b->dependsOn($node_a));

        $this->assertFalse($node_a->dependsOn($node_b));
    }

    /**
     * Test <tt>addToHierarchy()</tt>.
     * 
     * 1. Add node 'a' to 'head'. 'a' depends on 'head'
     * 2. Add node 'b' to 'head'. 'b' depends on 'a'
     * 
     * 'a' should be child of 'head'
     * 'b' should be child of 'a'
     */
    function testAddToHierarchy2()
    {
        $plugin = new plugin_Base(array('name' => 'head'));
        $head_node = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'a', 'depends' => array('head')));
        $node_a = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'b', 'depends' => array('a')));
        $node_b = new plugin_Node($plugin);
        
        $head_node->addChild($node_a);
        $head_node->addChild($node_b);
        $this->assertEquals(1, $head_node->size());
        $this->assertEquals(1, $head_node->children[0]->size());
        $this->assertEquals('a', $head_node->children[0]->plugin->name);
    }

    /**
     * Test <tt>addToHierarchy()</tt>.
     * 
     * 1. Add node 'a' to 'head'. 'a' depends on 'b'
     * 2. Add node 'b' to 'head'. 'b' depends on 'head'
     * 
     * 'a' should be child of 'b'
     * 'b' should be child of 'head'
     */
    function testAddToHierarchy3()
    {
        $plugin = new plugin_Base(array('name' => 'head'));
        $head_node = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'a', 'depends' => array('b')));
        $node_a = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'b', 'depends' => array('head')));
        $node_b = new plugin_Node($plugin);
        
        $this->assertFalse($head_node->addChild($node_a));
        $this->assertTrue($head_node->addChild($node_b));
        $this->assertTrue($head_node->addChild($node_a));
        $this->assertEquals(1, $head_node->size());
        $this->assertEquals(1, $head_node->children[0]->size());
        $this->assertEquals('b', $head_node->children[0]->plugin->name);
    }

    /**
     * Test <tt>addToHierarchy()</tt>.
     * 
     * 1. Add node 'a' to 'head'. 'a' depends on 'head'
     * 2. Add node 'b' to 'head'. 'b' depends on 'a'
     * 3. Add node 'c' to 'head'. 'c' depends on 'b'
     * 
     * 'a' should be child of 'head'
     * 'b' should be child of 'a'
     * 'c' should be child of 'b'
     */
    function testAddToHierarchy4()
    {
        $plugin = new plugin_Base(array('name' => 'head'));
        $head_node = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'a', 'depends' => array('head')));
        $node_a = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'b', 'depends' => array('a')));
        $node_b = new plugin_Node($plugin);

        $plugin = new plugin_Base(array('name' => 'c', 'depends' => array('b')));
        $node_c = new plugin_Node($plugin);
        
        $this->sorter->addToNode($head_node, $node_a);
        $this->sorter->addToNode($head_node, $node_b);
        $this->sorter->addToNode($head_node, $node_c);
        $this->assertEquals(1, $head_node->size());
        $this->assertEquals(1, $head_node->children[0]->size());
        $this->assertEquals('a', $head_node->children[0]->plugin->name);
        $this->assertEquals('b', $head_node->children[0]->children[0]->plugin->name);
        $this->assertEquals('c', $head_node->children[0]->children[0]->children[0]->plugin->name);
    }
}
