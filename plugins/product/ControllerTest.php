<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class product_ControllerTest extends PHPUnit_Framework_TestCase
{
    private $controller;
    private $dao;
    private $gen;
    
    function setUp()
    {
        $this->controller = new product_Controller;
        $this->dao = new product_ProductDAO;
        $this->gen = new order_SampleGenerator;
    }
    
    function testEditAction()
    {
        $product = $this->gen->makeProduct();
        $this->assertTrue($product->price > 0, 'price should be > 0');
        $this->dao->add($product);
        $this->controller->setRequestValue('id', $product->id);
        $this->controller->runEditAction();
        $this->assertTrue(isset($this->controller->product), 'product should be set');
        $this->assertTrue(isset($this->controller->product->price), 'price should be set');
        $this->assertTrue($this->controller->product->price > 0, 'price should be > 0');
    }
}
