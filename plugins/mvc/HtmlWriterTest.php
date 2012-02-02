<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Test the HTML writer utility
 */
class mvc_HtmlWriterTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->writer = new mvc_HtmlWriter;
    }
    
    function testAttributes() {
        $attributes = array(
            'name' => 'hello',
            'checked' => false,
            'selected' => true
        );
        $out = $this->writer->tagAttributes($attributes);
        $this->assertEquals(' name="hello" selected', $out);
    }
}

