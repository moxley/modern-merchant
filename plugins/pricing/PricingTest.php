<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class pricing_PricingTest extends PHPUnit_Framework_TestCase
{
    function testTruth() {
        $product = new product_Product;
        $pricings = $product->pricings;
        $this->assertType('array', $pricings);
    }
}
