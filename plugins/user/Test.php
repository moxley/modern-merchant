<?php
/**
 * @package user
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package user
 */
class user_Test extends PHPUnit_Framework_TestCase
{
    function testCreate() {
        $dao = new user_UserDAO;
        $dao->deleteAll();
        $adao = new access_AccessDAO;
        $adao->deleteAll();
        $access_defs = array(
            array('name' => 'admin.read', "read-only"),
            array('name' => 'admin.write', "Modern Merchant Manager Interface"),
            array('name' => 'user.management', "User Management")
        );
        foreach ($access_defs as $def) {
            $access = new access_Access($def);
            $access->save();
        }

        $this->user = new user_User;
        $this->user->username = 'test';
        $this->user->password = 'password';
        $this->user->access_names = array('admin.write');
        $accesses = $this->user->accesses;
        $this->assertTrue(is_array($accesses), "Should be an array");
        $this->assertEquals(1, count($accesses), "Number of accesses should be 1");
        $this->assertTrue($this->user->save(), "Failed to save user");
    }
}
