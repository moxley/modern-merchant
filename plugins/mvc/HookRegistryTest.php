<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_HookRegistryTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->registry = mvc_HookRegistry::instance();
        $this->registry->setMenus(array());
        $this->controller = new mvc_HookRegistryTest_Controller;
        $this->controller->request = array('action' => 'pluginstest.test');
        $this->controller->output = array();
    }

    function testAddMenu() {
        $this->registry->registerMenuItem(array('path'=>'foo', 'label'=>'Foo'));
        $menus = $this->registry->getMenus();
        $this->assertEquals(1, count($menus), "Number of menus should be 1");

        $this->registry->registerMenuItem(array('label'=>'Bar', 'path'=>'foo/bar'));
        $menus = $this->registry->getMenus();
        $this->assertEquals(2, count($menus), "Number of menus should be 1");
    }

    /**
     * @todo fix me
     */
    function _testMenuPriority() {
        $this->registry->registerMenuItem(array('path'=>'menu', 'label' => 'Menu'));
        $this->registry->registerMenuItem(array('path'=>'menu/foo', 'label'=>'Foo'));
        $this->registry->registerMenuItem(array('path'=>'menu/bar', 'label'=>'Bar', 'priority'=> 1));
        $menu = $this->registry->getMenu('menu');
        
        $this->assertEquals(2, count($menu->children), "Number of children");
        $this->assertEquals('menu/bar', $menu->children[0]->path, "First menu should be 'bar'");
    }
    
    function testExtendModel() {
        mvc_Hooks::extendModel('mvc_test_DummyModel', 'mvc_test_DummyModelExtension');
        $model = mvc_Model::instance('mvc_test_DummyModel');
        $this->assertEquals('mvc_test_DummyModelExtension', get_class($model));
        $errors = $model->validate();
        $this->assertEquals(2, count($errors), "There should be two errors");
    }
}

class mvc_HookRegistryTest_Controller extends mvc_Controller
{
    public $test_action_ran = false;
    
    function runTestAction()
    {
        $this->test_action_ran = true;
    }
}
