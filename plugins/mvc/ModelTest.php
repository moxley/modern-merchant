<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * 
 */
class mvc_ModelTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->object = new mvc_TestGetPublicVarsClass;
        $this->object->runtime = 'runtime';
    }
    
    function testGetPropertyExistsValue() {
        $property = mvc_Model::getPropertyExistsValue($this->object, 'private_var');
        $this->assertNull($property);

        $property = mvc_Model::getPropertyExistsValue($this->object, 'var1');
        $this->assertTrue(is_array($property));
        $this->assertEquals(1, count($property));
        $this->assertEquals('var1', $property[0]);

        $property = mvc_Model::getPropertyExistsValue($this->object, 'var2');
        $this->assertTrue(is_array($property));
        $this->assertEquals(1, count($property));
        $this->assertEquals('var2', $property[0]);
        
        $property = mvc_Model::getPropertyExistsValue($this->object, 'quantitiesById');
        $this->assertTrue(is_array($property));
        $this->assertEquals(1, count($property));
        $this->assertTrue(is_array($property[0]));
        $this->assertTrue(!empty($property[0]));

        $property = mvc_Model::getPropertyExistsValue($this->object, 'privateValue');
        $this->assertNull($property);

        $property = mvc_Model::getPropertyExistsValue($this->object, 'valueWithArgs');
        $this->assertNull($property);

        $property = mvc_Model::getPropertyExistsValue($this->object, 'runtime');
        $this->assertTrue(is_array($property));
        $this->assertEquals(1, count($property));
        $this->assertEquals('runtime', $property[0]);
    }
}

