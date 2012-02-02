<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package mminstall
 */
class mminstall_AdminAdder extends mminstall_Checker
{
    function check()
    {
        $result = new mminstall_CheckerResult("Valid username");
        $username = trim($this->admin['username']);
        $len = strlen($username); 
        if ($len < 4 || $len > 15) {
            $result->fail("Needs to be between 4 and 15 characters long");
        }
        $this->addResult($result);
        
        $result = new mminstall_CheckerResult("Valid password");
        if ($this->admin['new_password']) {
            $this->admin['password'] = $this->admin['new_password'];
            $this->admin['new_password'] = null;
        }
        $password = trim($this->admin['password']);
        $len = strlen($password);
        if ($len < 4 || $len > 15) {
            $result->fail("Needs to be between 4 and 15 characters long");
        }
        $this->addResult($result);
        if (!$this->isPass()) return;

        $result = new mminstall_CheckerResult("Add user");
        $this->admin['confirm_password'] = $this->admin['password'];
        $user = new user_User($this->admin);
        $user->setAsAdmin();
        if (!$user->save()) {
            $result->fail(implode(', ', $user->errors));
        }
        $this->addResult($result);
        if (!$this->isPass()) return;
    }
}

