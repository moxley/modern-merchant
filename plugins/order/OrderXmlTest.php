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
class order_OrderXmlTest extends PHPUnit_Framework_TestCase
{
    private $generator;
    private $line_nodes;
    private $addr_nodes;
    private $order_xml;
    
    function setUp()
    {
        $this->generator = new order_SampleGenerator;
        $this->addr_nodes = array(
            'first_name', 'last_name', 'email', 'address_1', 'address_2',
            'city', 'state', 'zip', 'country', 'phone_day', 'phone_night',
            'company');
        $this->line_nodes = array(
            'sku', 'price', 'qty');
        $this->order_xml = new order_OrderXml;
    }
    
    function testFormatLine()
    {
        $line = $this->generator->makeCartLine();
        $xml = $this->order_xml->formatLine($line);
        $expected = $this->order_xml->formatLine($line);
        $this->assertEquals($expected, $xml, "generated XML");
        return;
    }
    
    function testParseLine()
    {
        $this->line = $this->generator->makeCartLine();
        $this->assertTrue($this->line->product->sku ? true : false, "Product should have a SKU");
        $this->assertTrue($this->line->product->price > 0, "Price should be > 0");
        $this->assertTrue($this->line->price > 0, "Price should be > 0");
        $xml = $this->order_xml->formatLine($this->line);
        $doc = new DOMDocument;
        $doc->loadXML($xml);
        $line_node = $doc->getElementsByTagName('line')->item(0);
        $actual = $this->order_xml->parseLineNode($line_node);
        $this->assertLineEquals($this->line, $actual);
    }
    
    function testParseBilling()
    {
        $billing = $this->generator->makeAddress();
        $xml = $this->order_xml->formatAddress('billing_addr', $billing);
        //print "xml=$xml\n";
        $doc = new DOMDocument;
        $doc->loadXML($xml);
        $node = $doc->getElementsByTagName('billing_addr')->item(0);
        $actual = $this->order_xml->parseAddressNode($node);
        $this->assertNotNull($actual);
        $this->assertAddressEqual($billing, $actual);
        $doc = new DOMDocument;
        $doc->loadXML($xml);
        $dao = new order_OrderDAO;
    }
    
    function testPayed()
    {
        $order = new order_Order;
        $vars = get_object_vars($order);
        $order->payed = true;
        $this->assertTrue($order->payed);
    }
    
    function testParseOrder()
    {
        $order = $this->generator->makeOrder();
        $order->payed = true;
        $this->assertTrue($order->payed);
        $xml = $this->order_xml->formatOrder($order);
        //print "xml=$xml\n";
        $doc = new DOMDocument;
        $doc->loadXML($xml);
        $elements = $doc->getElementsByTagName('order');
        $this->assertEquals(1, $elements->length, 'number of <order> elements');
        $node = $elements->item(0);
        $this->assertNotNull($node);
        $actual = $this->order_xml->parseOrderNode($node);
        $this->assertOrderEquals($order, $actual, 'Order');
        
        $this->assertNotNull($actual->billing_addr);
        $this->assertAddressEqual($order->billing_addr,
            $actual->billing_addr, 'billing first_name');
        $this->assertTrue($order->payed, "Should be marked as payed");
    }
    
    function assertOrderEquals($expected, $actual)
    {
        $this->assertAddressEqual($expected->billing_addr, $actual->billing_addr, 'billing addr');
        $this->assertAddressEqual($expected->shipping_addr, $actual->shipping_addr, 'shipping addr');
        $this->assertLinesEquals($expected->lines, $actual->lines, 'lines');
        //var_export($actual);
        $attribs = array('date', 'creation_date',
            'modify_username', 'sub_total', 'ship_total',
            'ship_date', 'payment_method_id', 'tracking',
            'total', 'cust_approved', 'payed', 'unique_code',
            'session_id', 'cust_comments', 'notes', 'shipping_method_id',
            'previous_customer', 'date');
        foreach ($attribs as $attrib) {
            $this->assertEquals($expected->$attrib, $actual->$attrib, $attrib);
        }
    }
    
    function assertLinesEquals($expected, $actual)
    {
        $this->assertEquals(count($expected), count($actual), 'line count');
        for ($i=0; $i < count($expected); $i++) {
            $this->assertLineEquals($expected[$i], $actual[$i]);
        }
    }
    
    function assertLineEquals($expected, $actual)
    {
        $this->assertEquals($expected->id,          $actual->id,          'parsed line id');
        $this->assertEquals($expected->sku,         $actual->sku,         'parsed line sku');
        $this->assertEquals($expected->description, $actual->description, 'parsed line description');
        $this->assertEquals($expected->qty,         $actual->qty,         'parsed line qty');
        $this->assertEquals($expected->price,       $actual->price,       'parsed line price');
    }
    
    function assertAddressEqual($expected, $actual)
    {
        $check = $this->addr_nodes;
        $check[] = 'id';
        foreach ($check as $attr) {
            $this->assertEquals($expected->$attr, $actual->$attr, $attr);
        }
    }

}
