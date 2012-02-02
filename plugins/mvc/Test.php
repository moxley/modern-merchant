<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package mvc
 */
class mvc_Test extends PHPUnit_Framework_TestCase
{
    function testGetPropertyType() {
        $model = new mvc_DummyModel;
        $type = $model->getPropertyType('registration');
        $this->assertEquals('mvc_DummyRegistration', $type, 'type');
    }
    
    function testSetValues() {
        $values = array(
            'name' => 'Modern',
            'registration' => array()
        );
        $model = new mvc_DummyModel;
        $model->setPropertyValues($values);
        $this->assertEquals('Modern', $model->name);
        $this->assertType('mvc_DummyRegistration', $model->registration);
    }

    function testSetValues2() {
        $values = array(
            'name' => 'Modern',
            'registration' => array(
                'user' => array(
                    'username' => 'modern'
                )
            )
        );
        $model = new mvc_DummyModel;
        $model->setPropertyValues($values);
        $this->assertEquals('Modern', $model->name);
        $this->assertType('mvc_DummyRegistration', $model->registration);
        $this->assertType('mvc_DummyUser', $model->registration->user);
        $this->assertEquals('modern', $model->registration->user->username);
    }
    
    function testSetPropertyValue() {
        $model = new mvc_DummyModel;
        $model->setPropertyValue('name', 'Modern');
        $this->assertEquals('Modern', $model->name);
    }
    
    function testTrimStrings() {
        $values = array(
            'name' => '   Modern   ',
            'registration' => array(
                'user' => array(
                    'username' => 'modern'
                )
            )
        );
        $model = new mvc_DummyModel($values);
        $this->assertEquals($values['name'], $model->name);
        $this->assertEquals($values['name'], $model->getPropertyValue('name'));
        $model->trimStrings();
        $this->assertEquals(trim($values['name']), $model->name);
    }
    
    function testLcfirst() {
        $str = 'hello';
        $result = lcfirst($str);
        $this->assertEquals('hello', $result);
        $str = "HELLO";
    }
    
    function testGetPropertyMethods() {
        $model = new mvc_DummyWithGet;
        $methods = $model->getPropertyMethods();
        $this->assertTrue(in_array('getId', $methods));
        $this->assertFalse(in_array('getHello', $methods));
        $this->assertEquals(2, count($methods));
    }

    function testGetReadPropertyMethods() {
        $model = new mvc_DummyWithGet;
        $methods = $model->getReadPropertyMethods();
        $this->assertEquals(1, count($methods));
    }
    
    function testGetPropertyValues() {
        $model = new mvc_DummyWithGet;
        $values = $model->getPropertyValues();
        $this->assertEquals(2, count($values));
        $this->assertEquals('dummy', $values['name']);
        $this->assertEquals(10, $values['id']);
    }
    
    function testMethodNameToPropertyName() {
        $method = 'getId';
        $model = new mvc_DummyWithGet;
        $result = $model->methodNameToPropertyName($method);
        $this->assertEquals('id', $result, 'Property name');
    }
    
    function testGetReadProperties() {
        $model = new mvc_DummyWithGet;
        $properties = $model->getReadProperties();
        $this->assertEquals(2, count($properties));
        $this->assertTrue(in_array('name', $properties, true));
        $this->assertTrue(in_array('id', $properties, true));
    }
    
    function testGetPublicVars() {
        $model = new mvc_DummyWithGet;
        $vars = $model->getPublicVars();
        $this->assertEquals(1, count($vars));
        $this->assertEquals('dummy', $vars['name']);
    }

    function testGetWritePropertyMethods() {
        $model = new mvc_DummyWithGet;
        $methods = $model->getWritePropertyMethods();
        $this->assertEquals(1, count($methods));
        $this->assertTrue(in_array('setNothing', $methods, true));
    }

    function testGetWriteProperties() {
        $model = new mvc_DummyWithGet;
        $properties = $model->getWriteProperties();
        $this->assertEquals(2, count($properties));
        $this->assertTrue(in_array('nothing', $properties, true));
        $this->assertTrue(in_array('name', $properties, true));
    }

    function testGetWriteProperties2() {
        $model = new mvc_DummyModel;
        $properties = $model->getWriteProperties();
        $this->assertEquals(2, count($properties));
        $this->assertTrue(in_array('registration', $properties, true));
        $this->assertTrue(in_array('name', $properties, true));
    }
    
    function testVirtualVar() {
        $model = new mvc_DummyWithGet;
        $this->assertEquals(10, $model->getPropertyValue('id'));
        $this->assertEquals(10, $model->id);
    }
    
    function testVirtualVarCallFromInside() {
        $model = new mvc_DummyWithGet;
        $this->assertEquals(10, $model->_getId());
    }
    
    function testSetVirtual() {
        $model = new mvc_DummyWithGet;
        $model->nothing = 'what?';
        $values = $model->values();
        $this->assertEquals('what?', $values['nothing']);
    }

    function testSetVirtual2() {
        $model = new mvc_DummyModel2;
        $model->blah = 'virtual2';
        $values = $model->values();
        $this->assertEquals('virtual2', $values['blah']);
    }
}

