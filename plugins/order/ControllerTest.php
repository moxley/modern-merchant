<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package order
 */
class order_ControllerTest extends PHPUnit_Framework_TestCase
{
    private $order_assoc;
    private $order;
    private $new_id = 13316;
    private $lines_assoc;
    
    private $new_line_id = 'new_line';
    private $line_id = 'line_438c15d483a5a';
    private $line_id2 = 'line_438c15d49039c';
    private $line_assoc;
    private $line_assoc2;
    private $blank_line_assoc;
    private $controller;
    
    function __construct($name=null)
    {
        parent::__construct($name);
    }
    
    function setUp()
    {
        new admin_Controller;
        $this->controller = new order_Controller;
        $gen = new order_SampleGenerator;
        
        $this->blank_line_assoc = array(
            'sku' => '',
            'price' => '20.00',
            'qty' => '2',
            'description' => 'Blank product');
            
        $this->line_assoc = array(
            'sku' => '1234',
            'price' => '20.00',
            'qty' => '2',
            'description' => 'Product 1234');

        $this->line_assoc2 = array(
            'sku' => '444',
            'price' => '14.00',
            'qty' => '1',
            'description' => 'new product');
            
        $this->lines_assoc = array(
            $this->line_id => $this->line_assoc,
            $this->line_id2 => $this->blank_line_assoc,
            $this->new_line_id => $this->line_assoc2);
            
        $this->shipping_addr_assoc = array(
            'first_name' => 'Moxley',
            'last_name' => 'Stratton',
            'company' => 'Moxley Data Systems',
            'address1' => '3606 NE 9th Ave.',
            'address2' => 'Suite 100',
            'city' => 'Portland',
            'state' => 'OR',
            'zip' => '97212',
            'email' => 'moxley@moxleydata.com',
            'country' => 'US',
            'phone_day' => '503-281-6109',
            'phone_night' => '503-493-7364'
        );
        
        $this->billing_addr_assoc = array(
            'first_name' => 'Moxley',
            'last_name' => 'Stratton',
            'company' => 'Moxley Data Systems',
            'address1' => '3606 NE 9th Ave.',
            'address2' => 'Suite 100',
            'city' => 'Portland',
            'state' => 'OR',
            'zip' => '97212',
            'email' => 'moxley@moxleydata.com',
            'country' => 'US',
            'phone_day' => '503-281-6109',
            'phone_night' => '503-493-7364'
        );
        
        $this->order_assoc = array(
            'id' => (string) $this->new_id,
            'lines' => $this->lines_assoc,
            'date' => '4/2/1972',
            'ship_date' => '',
            'shipping_method_id' => '1',
            'tracking' => '',
            'payment_method_id' => '1',
            'cust_approved' => 'T',
            'payed' => 'T',
            'ship_total' => '3.85',
            'shipping_addr' => $this->shipping_addr_assoc,
            'billing_addr' => $this->billing_addr_assoc,
            'comments' => '***THIS IS A TEST!!!***',
            'notes' => ''
        );
        
        $this->order = new order_Order($this->order_assoc);
        $this->order->save();
        $this->assertTrue($this->order->id > 0, "Should have created an order ID");
    }
    
    function testLineObjToOutput()
    {
        $line = new cart_CartLine;
        $line->id = 'ajsliej342';
        $line->sku = '1234';
        $line->qty = 1;
        $line->price = 30.00;
        $line->description = 'This is a test product';
        $line_assoc = (array) $line;
        $this->assertValidLineHash($line_assoc);
    }
    
    function testRunEditAction()
    {
        $GLOBALS['MM_SESSION'] = new sess_MockSession;
        $request = array('id' => $this->order->id);
        $this->controller->setRequest($request);
        $this->controller->runEditAction();

        $this->assertTrue(isset($this->controller->order), 'order should be set');
        $order = $this->controller->order;
        $this->assertValidOrderHash($order);
    }
    
    function testParseCartLine()
    {
        return;
        $line = $this->controller->parseCartLine($this->line_id, $this->line_assoc);
        $this->assertTrue($line instanceof cart_CartLine, 'should be cart_CartLine');
    }
    
    function testParseBlankCartLine()
    {
        return;
        $line = $this->controller->parseCartLine($this->line_id, $this->blank_line_assoc);
        $this->assertNull($line, 'Line should be null');
    }
    
    function testParseNewLine()
    {
        return;
        $line = $this->controller->parseCartLine(
            $this->controller->new_line, $this->line_assoc);
        $this->assertNotNull($line->id, 'id should have been created');
    }
    
    function testParseOrderForm()
    {
        return;
        $order = $this->controller->parseOrder($this->order_assoc);
        $this->assertNotNull($order);
        $assert = new order_OrderAssertions($this);
        $assert->assertPopulatedOrder($order);
    }
    
    function assertValidOrderHash($order)
    {
        return;
        $this->assertTrue(isset($order['billing_addr']), 'billing_addr should be set');
        $this->assertTrue(is_array($order['payment_method']), 'payment_method should be an array');
        $this->assertTrue(strlen($order['payment_method']['title']) > 0, 'strlen(payment_method[title]) should > 0');
        foreach ($order['lines'] as $line) {
            $this->assertValidLineHash($line);
        }
    }
    
    function assertValidLineHash($line)
    {
        $this->assertTrue(isset($line['sku']), 'sku should be set');
        $this->assertTrue(isset($line['id']), 'id should be set');
        $this->assertTrue(isset($line['price']), 'price should be set');
        $this->assertTrue(isset($line['qty']), 'qty should be set');
        $this->assertTrue(isset($line['description']), 'description should be set');
    }
}
