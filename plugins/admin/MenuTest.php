<?php
/**
 * @package admin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class admin_MenuTest extends PHPUnit_Framework_TestCase
{
    private $child;
    private $parent;
    private $item;
    private $website;

    function setUp() {
        
        // These need to be admin_Menu objects
        $this->child = admin_Menu::normalizeItem(array(
            'path'   => 'menu/child',
            'label'  => 'Manage Products',
            'action' => "product.list"));
        $this->parent = admin_Menu::normalizeItem(array(
            'path'     => 'menu',
            'label'    => 'Products',
            'url'      => "/?action=product.list"));
        $this->website = admin_Menu::normalizeItem(array(
            'path'     => 'menu/website',
            'label'    => 'Web Site',
            'url'      => '/?action=website.default'));
        $this->item = $this->parent;
        
        $this->registry = mvc_HookRegistry::newInstance();
        $this->assertTrue(!empty($this->registry->id), "Registry should have an ID");
        $this->assertType('array', mvc_HookRegistry::$instances, "mvc_HookRegistry::\$instances");
        $this->assertTrue(count(mvc_HookRegistry::$instances) > 0, "Number of registries should be > 0");
        //$this->registry->registerMenuItem($this->child);
        //$this->registry->registerMenuItem($this->parent);
        //$this->registry->registerMenuItem($this->website);
        
        $this->js_child1 = "['','Products','" . mm_getConfigValue('urls.mm_root') . "?a=product.list',null,'Products']";
        $this->js_child2 = "['','Web Site','" . mm_getConfigValue('urls.mm_root') . "?a=website.default',null,'Web Site']";
        $this->js_expect = "['','Manage Products','" . mm_getConfigValue('urls.mm_root') . "?a=product.list',null,'Manage Products',\n\t{$this->js_child1},\n\t{$this->js_child2}]";
        
        $this->registry->registerMenuItem(array(
            'path'   => 'menu',
            'label'  => 'Manage Products',
            'action' => "product.list"));
        $this->registry->registerMenuItem(array(
            'path'     => 'menu/products',
            'label'    => 'Products',
            'url'      => mm_getConfigValue('urls.mm_root') . "?a=product.list",
            'priority' => 0));
        $this->registry->registerMenuItem(array(
            'path'     => 'menu/website',
            'label'    => 'Web Site',
            'url'      => mm_getConfigValue('urls.mm_root') . '?a=website.default',
            'priority' => 0));
    }
    
    function testRegisterMenuItem() {
        $menus = $this->registry->getMenus();
        foreach ($menus as $menu) {
            $this->assertEquals($this->registry->id, $menu->registry_id);
        }
    }

    function testRegisterItems() {
        $menus = $this->registry->getMenus();
        $this->assertEquals('menu', $menus[0]->path);
        $this->assertEquals('menu/products', $menus[1]->path);
        $this->assertEquals('menu/website', $menus[2]->path);
    }
    
    function testGetMenu() {
        $menu = $this->registry->getMenu('menu');
        $this->assertEquals('menu', $menu->path);
        $children = $menu->children;
        $this->assertEquals('menu/products', $children[0]->path);
        $this->assertEquals('menu/website', $children[1]->path);
    }
    
    function testToJS() {
        $menu = $this->registry->getMenu('menu');
        $children = $menu->getChildren();
        $this->assertTrue(count($children) > 0, "Should have children");
        $js = $menu->toJS();
        //echo "js: $js\n";

        $this->assertEquals($this->js_expect, $js, "Rendered Javascript");
    }

}
