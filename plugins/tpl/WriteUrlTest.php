<?php
/**
 * @package tpl
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package tpl
 */
class tpl_WriteUrlTest extends PHPUnit_Framework_TestCase
{
    private $writer;
    
    function setUp()
    {
        $this->writer = new tpl_WriteUrl;
    }
    
    function testGetUrlWithUrlParam()
    {
        $host = 'www.example.com';
        $base = 'https://' . $host;
        $url_param = '/test.php';
        $expected = $base . $url_param;
        mm_setConfigValue('urls.https', $base);
        $params = array(
            'url'    => $url_param,
            'schema' => 'https');
        $this->writer->setSchema('http');
        $this->writer->setHost($host);
        $url = $this->writer->getUrl($params);
        $this->assertEquals($expected, $url);
    }

    function testGetUrlWithNameAndPath()
    {
        $host = 'www.example.com';
        $base_http = 'http://' . $host;
        $base_https = 'https://' . $host;
        $name = 'test';
        $config_key = 'urls.' . $name;
        $config_value = 'test.php';
        mm_setConfigValue($config_key, $config_value);
        $path = '?action=test&value=123';
        $expected = $base_http . '/' . $config_value . $path;

        mm_setConfigValue('urls.http', $base_http);
        $this->assertEquals($base_http, $GLOBALS['MM_CONFIG']['urls.http']);
        $this->assertEquals($base_http, mm_getConfigValue('urls.http'));

        mm_setConfigValue('urls.https', $base_https);
        $this->assertEquals($base_https, mm_getConfigValue('urls.https'));
        
        $params = array(
            'name'   => $name,
            'path'   => $path,
            'schema' => 'http');
        $this->writer->setSchema('https');
        $this->writer->setHost($host);
        $url = $this->writer->getUrl($params);
        $this->assertEquals($expected, $url);
    }
    
    function testUrlFor() {
        $url = $this->writer->urlFor(array('action' => 'media.show'));
        $this->assertTrue(strlen($url) > 0);
        $this->assertEquals(mm_getConfigValue('urls.mm_root') . '?a=media.show', $url);
    }
    
    function testUrlFor2() {
        $url = $this->writer->urlFor(array('action' => 'media.show', 'params' => array('media_id' => 1)));
        $this->assertTrue(strlen($url) > 0);
        $this->assertEquals(mm_getConfigValue('urls.mm_root') . '?a=media.show&media_id=1', $url);
    }
    
    function testWriteUrl() {
        $html = $this->writer->getUrl(array('action'=>'catalog.productDetail', 'sku'=>'abc123'));
        $this->assertEquals(mm_getConfigValue('urls.mm_root') . '?a=catalog.productDetail&sku=abc123', $html);
    }
}
