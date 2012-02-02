<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class order_OrderDAOTest extends PHPUnit_Framework_TestCase
{
    private $generator;
    private $dao;
    
    function setUp()
    {
        $dbh = mm_getDatabase();
        $dbh->query('delete from mm_order');
        $this->dao = new order_OrderDAO;
        $this->generator = new order_SampleGenerator;
    }
    
    function tearDown()
    {
        $dbh = mm_getDatabase();
        $dbh->query('delete from mm_order');
    }
    
    function testAddFetch()
    {
        $order = $this->generator->makeOrder();
        $username = 'modern';
        $order->modify_username = $username;
        $this->assertTrue((boolean) $order->save(), "Failed to save order: " . implode(', ', $order->errors));
        $this->assertTrue($order->id > 0, "Failed to assign order ID");
        
        $actual = $this->dao->fetch($order->id);
        $this->assertNotNull($actual, "Failed to fetch order for id = $order->id");
        $this->assertOrderEquals($order, $actual);
        $this->assertEquals($username, $actual->modify_username);
    }
    
    function assertOrderEquals($expected, $actual) {
        $simple_attributes = array('id', 'creation_date', 'modify_username',
            'ship_total', 'ship_date', 'shipping_method_id', 'payment_method_id',
            'customer_id', 'tracking', 'unique_code', 'cart_id', 'sid',
            'cust_comments', 'notes', 'cust_approved', 'checkout_complete',
            'previous_customer', 'order_date', 'session_id', 'sub_total', 'total');
        $complex_attributes = array('lines', 'billing_addr', 'shipping_addr');
        foreach ($simple_attributes as $name) {
            $this->assertEquals($expected->$name, $actual->$name, "attribute: " . $name . " was supposed to be " . $expected->$name);
        }
    }
}
