<?php
/**
 * @package addr
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package addr
 */
class addr_Test extends PHPUnit_Framework_TestCase
{
    function testEmpty() {
        $addr = new addr_Address;
        $this->assertTrue($addr->is_empty);
        $addr->first_name = "Test";
        $this->assertFalse($addr->is_empty);
    }
}
