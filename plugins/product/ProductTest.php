<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class product_ProductTest extends PHPUnit_Framework_TestCase
{
    private $gen;
    
    function setUp()
    {
        $this->gen = new order_SampleGenerator;
    }
    
    function testApplyRoundPricing()
    {
        $pricing = $this->gen->makePricing();
        $pricing->type = 'multiply';
        $pricing->value = 1.14515;
        $product = $this->gen->makeProduct();
        $product->price = 3.50;
        $product->addPricing($pricing);
        $this->assertEquals(1, count($product->pricings), "Number of pricings");
        
        $this->assertEquals('4.01', $product->adjusted_price, 'total');
    }
}
