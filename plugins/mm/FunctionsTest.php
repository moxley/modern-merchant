<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_FunctionsTest extends PHPUnit_Framework_TestCase
{
    function tearDown()
    {
        $dir = MM_LIB . '/private/mkdirp';
        if (file_exists($dir)) {
            rmdir($dir);
        }
    }
    
    function test_gvInt()
    {
        $expected = 1;
        $values = array(1, '1');
        foreach ($values as $value) {
            $assoc = array('test' => $value);
            $v = gvInt($assoc, 'test');
            $this->assertTrue($v === $expected);
        }
    }
    
    function test_gvInt_null()
    {
        $expected = 1;
        $values = array('', null);
        foreach ($values as $value) {
            $assoc = array('test' => $value);
            $v = gvInt($assoc, 'test');
            $this->assertTrue($v === null);
        }
    }
    
    function testDecodeSession()
    {
        $encoded = 'USERSESS|a:4:{s:8:"hitCount";i:4;s:11:"thisHitTime";i:1133281096;s:4:"data";a:1:{s:7:"orderId";i:13302;}s:11:"lastHitTime";i:1133281083;}';
        $decoded = mm_decodeSession($encoded);
        $this->assertTrue(is_array($decoded), 'should be array');
    }
    
    //function testDecodeSession2()
    //{
    //    $encoded = 'sess_SessionHandler.creation_date|d:1134418547;sess_SessionHandler.session_id|i:0;category_id|s:2:"15";cart|O:10:"cart_Cart":19:{s:5:"lines";a:1:{i:0;O:14:"cart_CartLine":7:{s:2:"id";s:18:"line_439dda8550024";s:3:"sku";s:13:"BE-F-0034M-91";s:11:"description";s:47:"0034 Light Peach Cream Frit #1 #2 (Medium) 2 oz";s:3:"qty";i:1;s:5:"price";d:0.01;s:7:"product";O:15:"product_Product":15:{s:2:"id";i:1250;s:11:"modify_date";d:1132254983;s:15:"modify_username";s:6:"breezy";s:3:"sku";s:13:"BE-F-0034M-91";s:9:"sortorder";i:342;s:4:"name";s:47:"0034 Light Peach Cream Frit #1 #2 (Medium) 2 oz";s:6:"active";b:1;s:11:"description";s:238:"Bullseye makes wonderful frits that are COE90 and 100% tested compatible with their lampworking line. #2 frit is a Medium size. Comes in 2 oz jar. Please note that Bullseye will not work with Moretti or Uroboros, even in small quantities.";s:7:"comment";s:0:"";s:10:"base_price";s:4:"3.50";s:5:"count";i:98;s:6:"images";a:3:{i:0;O:13:"media_Media":13:{s:2:"id";s:3:"687";s:10:"owner_type";s:7:"product";s:8:"owner_id";s:4:"1250";s:9:"mime_type";s:10:"image/jpeg";s:8:"filename";s:11:"1250.1.jpeg";s:17:"media_category_id";s:1:"1";s:4:"name";s:1:"1";s:11:"description";N;s:5:"width";s:3:"120";s:6:"height";s:2:"90";s:6:"values";N;s:5:"errno";N;s:7:"columns";N;}i:1;O:13:"media_Media":13:{s:2:"id";s:3:"688";s:10:"owner_type";s:7:"product";s:8:"owner_id";s:4:"1250";s:9:"mime_type";s:10:"image/jpeg";s:8:"filename";s:11:"1250.2.jpeg";s:17:"media_category_id";s:1:"2";s:4:"name";s:1:"2";s:11:"description";N;s:5:"width";s:3:"640";s:6:"height";s:3:"480";s:6:"values";N;s:5:"errno";N;s:7:"columns";N;}i:2;O:13:"media_Media":13:{s:2:"id";s:3:"689";s:10:"owner_type";s:7:"product";s:8:"owner_id";s:4:"1250";s:9:"mime_type";s:10:"image/jpeg";s:8:"filename";s:11:"1250.3.jpeg";s:17:"media_category_id";s:1:"3";s:4:"name";s:1:"3";s:11:"description";N;s:5:"width";s:3:"640";s:6:"height";s:3:"480";s:6:"values";N;s:5:"errno";N;s:7:"columns";N;}}s:6:"weight";N;s:8:"pricings";a:1:{i:0;O:15:"pricing_Pricing":5:{s:2:"id";i:9;s:5:"value";d:0.01;s:4:"type";s:8:"override";s:4:"name";s:13:"Test Override";s:11:"valid_types";a:3:{i:0;s:8:"multiply";i:1;s:3:"add";i:2;s:8:"override";}}}s:5:"image";r:25;}s:6:"wanted";a:5:{i:0;s:2:"id";i:1;s:5:"price";i:2;s:3:"qty";i:3;s:3:"sku";i:4;s:11:"description";}}}s:9:"ship_calc";N;s:12:"order_values";a:1:{s:8:"s_method";s:1:"5";}s:10:"ship_types";a:1:{i:0;a:5:{s:18:"shipping_method_id";s:1:"5";s:4:"name";s:13:"USPS Priority";s:6:"active";s:1:"1";s:4:"cost";N;s:4:"calc";s:55:"return 3.85 + intval($cart->getSubTotal() / 10) * 0.75;";}}s:8:"order_id";N;s:17:"payment_method_id";N;s:13:"cust_approved";b:0;s:5:"payed";b:0;s:8:"complete";b:0;s:11:"unique_code";s:18:"cart_439dda7431e95";s:10:"session_id";s:32:"4013d8774b851b012bd4c7e50b56210a";s:5:"error";N;s:13:"user_messages";a:0:{}s:10:"order_date";N;s:13:"creation_date";d:1134418548;s:18:"shipping_functions";a:1:{i:5;s:55:"return 3.85 + intval($cart->getSubTotal() / 10) * 0.75;";}s:6:"values";N;s:5:"errno";N;s:7:"columns";N;}user|O:12:"user_User":6:{s:2:"id";N;s:8:"username";N;s:8:"password";N;s:4:"type";N;s:10:"first_name";N;s:9:"last_name";N;}form_completions|a:1:{s:9:"cart_form";b:1;}';
    //    $decoded = mm_decodeSession($encoded);
    //    $this->assertTrue(is_array($decoded), 'should be array');
    //}
    
    function testEncodeDecodeSession()
    {
        $sess = array('a' => 1, 'b' => 2, 'c' => array('x' => '1', 'y' => '2'));
        $encoded = mm_encodeSession($sess);
        $decoded = mm_decodeSession($encoded);
        $this->assertEquals($sess, $decoded);
    }
    
    function testMkDirP()
    {
        $path = MM_LIB . '/private/mkdirp';
        
        $this->assertFalse(file_exists($path), "Path should not exist");
        mkdirp($path);
        $this->assertTrue(file_exists($path), "Path should exist");
    }
    
    function testArrayDetect()
    {
        $array = array(
            (object) array(
                'id' => 1,
                'name' => 'object 1'
            ),
            (object) array(
                'id' => 2,
                'name' => 'object 2'
            )
        );
        $o = eval(array_detect('$array', '$o', '$o->id == 2'));
        $this->assertEquals('object 2', $o->name);
    }
    
}
