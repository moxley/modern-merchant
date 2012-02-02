<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * HTML writer utility that pulls values from model objects
 */
class mvc_ModelHtmlWriterTest extends PHPUnit_Framework_TestCase
{
    private $writer;
    private $controller;
    private $object;
    
    function setUp() {
        $this->object = new mvc_TestGetPublicVarsClass;
        $this->controller = (object) array(
            'name' => 'testname',
            'product' => (object) array(
                'sku' => 'abc123'
            ),
            'cart' => $this->object,
            'assoc' => array('a' => 'a', 'b' => 'b', 'c' => 'c'),
            'registration' => (object) array(
                'user' => (object) array(
                    'active' => true,
                    'username' => 'modern'
                ),
                'notify' => true,
                'blah' => true
            )
        );
        // registration[user][active]
        $this->writer = new mvc_ModelHtmlWriter($this->controller);
    }
    
    function testParseFieldName() {
        $parsed = $this->writer->parseFieldName('name');
        $this->assertEquals(array('name'), $parsed);
    }

    function testParseFieldName2() {
        $parsed = $this->writer->parseFieldName('name[pants]');
        $this->assertEquals(array('name', 'pants'), $parsed);
    }
    
    function testGetPublicVars() {
        $object = (object) array(
            'name' => 'test'
        );
        $object = new mvc_TestGetPublicVarsClass;
        $object->var3 = 'var3';
        
        $vars = mvc_Model::getProperties($object);
        $this->assertEquals(null, @$vars['private_var']);
        $this->assertEquals('var1', @$vars['var1']);
        $this->assertEquals('var2', @$vars['var2']);
        $this->assertEquals('var3', @$vars['var3']);
    }

    function testFindValue() {
        $value = $this->writer->findValue('name');
        $this->assertEquals('testname', $value);
    }

    function testFindValue2() {
        $value = $this->writer->findValue('product[sku]');
        $this->assertEquals('abc123', $value);
    }

    function testFindValue3() {
        $quantities = $this->object->quantities_by_id;
        $this->assertTrue(!empty($quantities));
        $value = $this->writer->findValue('cart[quantitiesById][line_1]');
        $this->assertEquals(12, $value);
    }

    function testTextField() {
        $this->controller->name = 'Modern';
        $out = $this->writer->textField('name');
        $this->assertContains('name="name"', $out);
        $this->assertContains('value="Modern"', $out);
    }
    
    function testTextField2() {
        $out = $this->writer->textField('registration[user][username]');
        $this->assertContains('name="registration[user][username]"', $out);
        $this->assertContains('value="modern"', $out);
    }
    
    function testRadioButton() {
        $out = $this->writer->radioButton('registration[favorite_color]', 'red');
        $this->assertContains('name="registration[favorite_color]"', $out);
        $this->assertContains('value="red"', $out);
    }
    
    function testRadioButton2() {
        $out = $this->writer->radioButton('registration[notify]', true);
        $this->assertContains('name="registration[notify]"', $out);
        $this->assertContains('value="1"', $out);
    }
    
    function testRadioButton3() {
        $cart = new cart_Cart;
        $cart->payment_method_id = 5;
        $this->assertEquals(5, $cart->payment_method_id);
        $this->controller->cart = $cart;
        
        $out = $this->writer->radioButton('cart[payment_method_id]', $cart->payment_method_id);
        $this->assertContains('name="cart[payment_method_id]"', $out);
        $this->assertContains('value="' . $cart->payment_method_id . '"', $out);
        $this->assertContains(' checked', $out);
    }
    
    function testRadioButton4() {
        $this->controller->registration->favorite_color = 'red';
        $this->assertEquals('red', $this->controller->registration->favorite_color);
        $value = $this->writer->findValue('registration[favorite_color]');
        $this->assertEquals('red', $value);
        $out = $this->writer->radioButton('registration[favorite_color]', 'red');
        $this->assertContains(' checked', $out);
    }
    
    function testDefaultValue() {
        $values = array(
            'name' => '   Modern   ',
            'registration' => array(
                'user' => array(
                    'username' => 'modern'
                )
            )
        );
        $model = new mvc_DummyModel($values);
        $out = $this->writer->radioButton('registration[notify]', true, array('default'=>true));
        $this->assertContains('checked', $out, "Should be checked");

        $out = $this->writer->radioButton('registration[notify]', false, array('default'=>true));
        $this->assertNotContains('checked', $out, "Should not be checked");
        
        // Non-existent property
        $out = $this->writer->radioButton('registration[blah]', true, array('default'=>true));
        $this->assertContains('checked', $out, "Should be checked (for non-existent property)");

        $out = $this->writer->radioButton('registration[blah]', false, array('default'=>true));
        $this->assertNotContains('checked', $out, "Should not be checked (for non-existent property)");
        
        // Already-set property
        $model->registration->notify = true;
        $out = $this->writer->radioButton('registration[notify]', true, array('default'=>true));
        $this->assertContains('checked', $out, "Should be checked");
        $model->registration->notify = false;
        $out = $this->writer->radioButton('registration[notify]', false, array('default'=>true));
        $this->assertNotContains('checked', $out, "Should not be checked");
    }
    
    function testDefaultWithoutInstance() {
        $value = $this->writer->findValue('registration[user][active]');
        $this->assertTrue($value);
    }
}
