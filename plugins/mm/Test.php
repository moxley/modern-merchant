<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package mm
 */
class mm_Test extends PHPUnit_Framework_TestCase
{
    function testAdmin() {
        $user_dao = new user_UserDAO();
        $user_dao->deleteAll();

        $user = new user_User;
        $user->username = 'test';
        $user->password = 'password';
        $user->setAsAdmin();
        $this->assertTrue($user->save(), "Failed to save user: " .
            implode(', ', $user->errors));

        $active_user = mm_getUser();
        $this->assertTrue(empty($active_user), "'user' should not be in session");
        
        // Send "login" action
        $_GET['a'] = 'auth.login';
        $_POST['login'] = array(
            'username' => $user->username,
            'password' => $user->password);
        
        $_SERVER['REQUEST_METHOD'] = "POST";
        $this->sendAdmin();
        $this->assertForward('redirect', 'product.list');
        
        $active_user = mm_getUser();
        $this->assertTrue(is_object($active_user), "'user' should be in session");
        $this->assertEquals($user->username, $active_user->username, "Should match username");

        $_GET['a'] = 'product.list';
        $this->sendAdmin();
        $this->assertForward('view', 'product.list');
        
        $_GET = array('a' => 'product.list', 'category_id' => 6);
        $this->sendAdmin();
        $this->assertForward('view', 'product.list');

        $_GET = array('a' => 'product.list', 'category_id' => 6);
        $this->sendAdmin();
        $this->assertForward('view', 'product.list');
    }
 
    function sendAdmin($params=null, $options=array()) {
        if ($params) $_REQUEST = $params;
        $buffer = isset($options['buffer']) ? $options['buffer'] : true;
        $this->forward = $this->runRequest($buffer);
        return $this->forward;
    }
    
    function assertForward($command, $action_uri) {
        $this->assertEquals($command, $this->forward->command, "Unexpected command");
        $this->assertEquals($action_uri,  $this->forward->uri, "Unexpected action");
    }
    
    function runRequest($buffer=true) {
        global $MM_SESSION;
        
        mm_getSession();
        $this->assertTrue(isset($MM_SESSION), "Session should be set");
        
        if (!$buffer) {
            $result = mvc_Controller::runRequest();
        }
        else {
            ob_start();
            $result = mvc_Controller::runRequest();
            $contents = ob_get_contents();
            ob_end_clean();
        }
        
        $sess_dao = new sess_SessionDAO;
        $sess_dao->update($MM_SESSION);
        
        return $result;
    }
}
