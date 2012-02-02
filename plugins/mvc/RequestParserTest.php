<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_RequestParserTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->request = array(
            'action' => 'cart.show'
        );
        $this->parser = new mvc_RequestParser($this->request);
    }
    
    function testGetAction() {
        $action = getAction($this->request);
        $this->assertEquals('cart.show', $action);
    }
    
    function testParse() {
        $this->parser->parse();
        
        $action = $this->parser->action;
        $module = $this->parser->module;
        
        $this->assertEquals('cart', $module);
        $this->assertEquals('show', $action);
    }
    
    function testShortAction() {
        $this->request = array(
            'a' => 'cart.show');
        
        $this->parser->setRequest($this->request);
        $this->parser->parse();
        
        $action = $this->parser->action;
        $module = $this->parser->module;
        
        $this->assertEquals('cart', $module);
        $this->assertEquals('show', $action);
    }
    
    function testOriginalAction() {
        $this->parser->parse();
        $action = $this->parser->action_uri;
        $this->assertEquals('cart.show', $action);
    }
    
    function testOldController() {
        $class_name = 'cart_Controller';
        $controller_name = preg_replace('/^(controller_)(.+)(Controller)$/', '$2', $class_name);
    }
}
