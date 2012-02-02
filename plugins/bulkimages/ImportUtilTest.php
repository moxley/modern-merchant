<?php
/**
 * @package bulkimages
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class bulkimages_ImportUtilTest extends PHPUnit_Framework_TestCase
{
    private $util;
    
    function setUp()
    {
        $this->util = new bulkimages_ImportUtil;
    }
    
    function testGetSourceFileCount()
    {
        $this->util->setSourcePath(dirname(__FILE__) . '/test/2items');
        $count = $this->util->getSourceFileCount();
        $this->assertEquals(2, $count, "count");
    }
}
