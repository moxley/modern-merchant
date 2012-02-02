<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_MenuHookTest extends PHPUnit_Framework_TestCase
{
    private $reg;

    function setUp() {
        $this->reg = mvc_HookRegistry::newInstance();
    }

    function testSimpleMenu() {
        $products_menu = array(
            'path' => 'products',
            'label' => 'Products',
            'action' => 'product.list'
        );
        $this->reg->registerMenuItem($products_menu);
        $expected = admin_Menu::normalizeItem($products_menu);
        $expected->registry_id = $this->reg->id;
        $actual = $this->reg->getMenu('products');
        $this->assertEquals($expected, $actual);
    }

    function testWebsiteMenu() {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website', 'label' => 'Website', 'action' => 'website.settings'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin',         'label' => 'Admin'));
        $menu = mvc_Hooks::getMenu('admin/website');
        $out = $menu->toJS($menu);
        $this->assertContains('=website.settings', $out);
    }
    
    function setUpSimple() {
        $this->reg->registerMenuItem(array('path' => 'admin/website/plugin1', 'label' => 'Plugin 2'));
        $this->reg->registerMenuItem(array('path' => 'admin/config/plugin2',  'label' => 'Plugin 1'));
        $this->reg->registerMenuItem(array('path' => 'admin/website',         'label' => 'Website'));
        $this->reg->registerMenuItem(array('path' => 'admin',                 'label' => 'Admin'));
        $this->reg->registerMenuItem(array('path' => 'admin/config',          'label' => 'Configuration', 'priority' => -1));
    }
    
    function testRootMenus() {
        $this->setUpSimple();
        $this->matches = $this->reg->getRootMenus();
        $this->assertRootMenus();
    }
    
    function testRootMenusViaSubMenus() {
        $this->setUpSimple();
        $this->matches = $this->reg->getSubMenus();
        $this->assertRootMenus();
    }
    
    function assertRootMenus() {
        $this->assertType('array', $this->matches);
        $this->assertEquals(1, count($this->matches), "root menus count");
        $this->assertEquals('admin', $this->matches[0]->path, "root menu path");
    }
    
    /**
     * @todo fix me
     */
    function _testSubMenus() {
        $this->setUpSimple();
        $this->matches = $this->reg->getSubMenus('admin');
        $this->assertAdminMenus();
    }
    
    function assertAdminMenus() {
        $this->assertType('array', $this->matches);
        $this->assertEquals(2, count($this->matches), "admin menus count");
        $parts = explode('/', $this->matches[0]->path);
        $this->assertEquals('admin', $parts[0], "First part of first menu");
        $this->assertEquals('admin/website', $this->matches[0]->path, "First menu path");
        $this->assertEquals('admin/config', $this->matches[1]->path, "Second menu path");
    }
}
