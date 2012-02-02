<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class payment_PaymentMethodDAOTest extends PHPUnit_Framework_TestCase
{
    private $method_number = 0;
    
    function setUp() {
        $this->dao = new payment_PaymentMethodDAO;
    }
    
    function createMethod() {
        $this->method_number++;
        
        $method = new payment_PaymentMethod;
        $method->title = "Test Method $this->method_number";
        $method->active = true;
        $method->sortorder = $this->method_number;
        $method->class = "payment_PaymentMethod";
        $method->name = "test_method$this->method_number";
        $this->dao->save($method);
    }
    
    function testGetList() {
        // Delete existing payment methods
        $this->dao->deleteAll();
        
        // Assert total count is 0
        $this->assertEquals(0, $this->dao->getCount());
        
        // Create a payment method
        $this->createMethod();
        
        // Assert total count is now 1
        $this->assertEquals(1, $this->dao->getCount());

        $list = $this->dao->getList();
        $this->assertEquals(1, count($list));
    }
}
