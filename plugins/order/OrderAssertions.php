<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class order_OrderAssertions
{
    private $test;
    function __construct($test)
    {
        $this->test = $test;
    }
    
    function assertPopulatedCart($cart)
    {
        $test = $this->test;
        $test->assertNotNull($cart);
        $test->assertEquals('cart_Cart', get_class($cart));
        foreach ($cart->lines as $line) {
            $this->assertPopulatedCartLine($line);
        }
        $test->assertTrue(count($cart->order_values) > 0, "order_values > 0 count");
        $test->assertTrue($cart->order_id > 0, 'order_id should be > 0');
        $test->assertTrue($cart->payment_method_id > 0, 'payment_method_id should be > 0. was: ' . $cart->payment_method_id);
        $test->assertTrue($cart->cust_approved, 'cust_approved should be true');
        $test->assertTrue($cart->payed, 'payed should be true');
        $test->assertTrue($cart->session_id > 0, 'session_id should be > 0');
        $test->assertTrue($cart->creation_date > 0, 'creation_date should be > 0');
        $test->assertTrue(strlen($cart->unique_code) > 0, 'strlen(unique_code) should be > 0');
        $test->assertTrue($cart->complete == true, 'complete should be true');
    }

    function assertPopulatedOrder($order)
    {
        $test = $this->test;
        $test->assertNotNull($order);
        $test->assertTrue($order->date > 0, 'date > 0');
        $test->assertTrue($order->creation_date > 0, 'creation_date should be > 0');
        $test->assertTrue($order->sub_total > 0, 'sub_total should be > 0');
        $test->assertTrue($order->ship_total > 0, 'ship_total should be > 0');
        //$test->assertTrue($order->ship_date > 0, 'ship_date should be > 0');
        $test->assertTrue($order->shipping_method_id > 0, 'shipping_method_id should be > 0');
        $test->assertTrue($order->payment_method_id > 0, 'paymend_method_id should be > 0. was: ' . serialize($order->payment_method_id));
        $test->assertTrue($order->total > 0, 'total should be > 0');
        $test->assertTrue($order->cust_approved, 'cust_approved should be true');
        $test->assertTrue(strlen($order->cust_comments) > 0, 'strlen(cust_comments) should be > 0');
        $test->assertTrue($order->payed, 'payed should be true');
        $test->assertTrue(strlen($order->unique_code) > 0, 'strlen(unique_code) should be > 0');
        $test->assertTrue(strlen($order->session_id) > 0, 'strlen(session_id) should be > 0');
        $test->assertTrue(count($order->lines) > 0, 'count(lines) should be > 0');
        $test->assertTrue($order->checkout_complete, 'checkout_complete should be true');
        $this->assertPopulatedAddress($order->billing_addr);
        $this->assertPopulatedAddress($order->shipping_addr);
        //$this->assertValidPaymentMethod($order->payment_method);
    }
    
    function assertPopulatedAddress($addr)
    {
        $test = $this->test;
        $test->assertNotNull($addr);
        $test->assertTrue(is_object($addr), 'addr should be object');
    }
    
    function assertValidPaymentMethod($method)
    {
        $test = $this->test;
        $test->assertNotNull($method);
        $test->assertTrue(is_object($method));
    }
    
    function assertPopulatedCartLine($line)
    {
        $this->test->assertTrue(strlen($line->sku) > 0, 'strlen(sku) should be > 0. was: ' . $line->sku);
        $this->test->assertTrue($line->price > 0, 'price should be > 0');
    }

}
