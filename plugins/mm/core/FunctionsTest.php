<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_core_FunctionsTest extends PHPUnit_Framework_TestCase
{
    function testDecodeSession()
    {
        $original_serialized = 'a:6:{s:11:"request_log";a:2:{i:0;O:8:"stdClass":3:{s:3:"uri";s:13:"/mm/admin.php";s:4:"time";i:1172462956;s:2:"ip";s:9:"127.0.0.1";}i:1;O:8:"stdClass":3:{s:3:"uri";s:46:"/mmadmin.php?action=Product.list&category_id=3";s:4:"time";i:1172462967;s:2:"ip";s:9:"127.0.0.1";}}s:33:"sess_SessionHandler.creation_date";d:1172462956;s:30:"sess_SessionHandler.session_id";i:0;s:4:"user";O:12:"user_User":6:{s:2:"id";s:1:"1";s:8:"username";s:5:"admin";s:8:"password";s:5:"admin";s:4:"type";s:1:"2";s:10:"first_name";N;s:9:"last_name";N;}s:15:"messages.notice";O:13:"mvc_Messages":1:{s:8:"messages";a:0:{}}s:43:"controller_productcontroller::runlistaction";a:7:{s:11:"product.sku";N;s:12:"product.name";N;s:19:"product.description";N;s:17:"product.sortorder";N;s:11:"category_id";s:1:"3";s:6:"search";N;s:6:"offset";N;}}';
        $original = unserialize($original_serialized);
        $this->assertTrue(is_array($original), "Should be an array");
        $encoded = mm_encodeSession($original);
        $decoded = mm_decodeSession($encoded);
        $this->assertTrue(is_array($decoded), "Should be an array");
    }

    function testDocCommentAttributes() {
        $class = new ReflectionClass('mm_core_DummyClass');
        $property = $class->getProperty('dummy');
        $attributes = mm_getCommentAttributes($property->getDocComment());
        $this->assertEquals(1, count($attributes));
        $this->assertEquals('var', $attributes[0]->name);
        $this->assertEquals('string', $attributes[0]->value);
    }
    
    function testCamelize() {
        $word = "hello_world";
        $result = camelize($word);
        $this->assertEquals('helloWorld', $result);
        
        $word = "hello_world_";
        $result = camelize($word);
        $this->assertEquals('helloWorld', $result);

        $word = "_hello_world";
        $result = camelize($word);
        $this->assertEquals('HelloWorld', $result);
    }
}
