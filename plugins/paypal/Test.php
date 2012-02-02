<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypal_Test extends PHPUnit_Framework_TestCase
{
    function testValid() {
        $paypal = new paypal_PayPal;
        $paypal->public_title = "This is PayPal";
        $this->assertEquals("This is PayPal", $paypal->public_title);
        $this->assertTrue($paypal->is_valid, implode(', ', $paypal->errors));
    }
}
