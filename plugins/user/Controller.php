<?php
/**
 * @package user
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package user
 */
class user_Controller extends admin_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->dao = new user_UserDAO;
    }
    
    function isAuthorized($action)
    {
        $user = mm_getUser();
        if (in_array($action, array('login', 'logout', 'editSelf'))) {
            return true;
        }
        else if (!parent::isAuthorized($action)) {
            return false;
        } else {
            return $user->hasAccess('user.management');
        }
    }
    
    function runDefaultAction()
    {
        $this->setForward('user.list');
    }
    
    function runLoginAction()
    {
        $this->title = "Login";
        
        $this->theme_type = 'public';
        if (!$this->is_post) {
            $this->login = new user_User;
        }
        else {
            $this->login = new user_User($this->req('login'));
            // Log the user in
            if (!($user = $this->login->login())) {
                $this->addWarnings($this->login->errors);
                return;
            }

            $this->addNotice("Hi {$user->username}!");
            if ($this->transition = $this->req('transition')) {
                $this->redirectToAction($this->transition);
                return false;
            } elseif ($user->isAdmin()) {
                $this->redirect(mm_getConfigValue('urls.admin.script'));
                return false;
            } else {
                $this->redirect('/');
                return false;
            }
        }
    }
    
    function runLogoutAction()
    {
        $this->theme_type = 'public';
        $user = mm_getUser();
        if ($user) $user->logout();
        $this->addNotice("You are now logged out");
        $this->redirect(mm_getConfigValue('urls.mm_root'));
        return false;
    }
    
    function runListAction()
    {
        $dao = new user_UserDAO;
        $this->users = $dao->find(array(
            'select' => 'DISTINCT mm_user.*',
            'from'   => "mm_user, mm_user_access AS ua, mm_access AS a",
            'where'  => array('ua.user_id = mm_user.id AND ua.access_id = a.id AND a.name <> ?', 'customer'),
            'limit'  => $this->max_per_page));
        $this->title = "Manager Users";
    }
    
    function runEditAction()
    {
        $this->user = $this->requireUser();
        $this->target_action = 'user.update';
        $this->title = "Edit User '{$this->user->username}'";
    }

    function runDeleteAction()
    {
        $this->user = $this->requireUser();
        $this->user->delete();
        $this->addNotice("User deleted");
        $this->redirectToAction('user');
        return false;
    }
    
    function runUpdateAction()
    {
        $this->user = $this->requireUser();
        $this->user->admin_values = $this->req('user');
        if (!$this->user->save()) {
            $this->addWarnings($this->user->errors);
            $this->setTemplate('user/edit');
        } else {
            $this->addNotice("Updated user account");
            $this->redirectToAction('user.edit', array('id' => $this->user->id));
            return false;
        }
    }
        
    function runNewAction()
    {
        $this->user = new user_User;
        $this->target_action = 'user.add';
        $this->title = "Create New User";
    }
    
    function runAddAction()
    {
        $this->user = new user_User($this->req('user'));
        if (!$this->user->save()) {
            $this->addWarnings($this->user->errors);
            $this->setReturnAction('user.new');
        } else {
            $this->addNotice("Added new user");
            $this->redirectToAction('user.edit', array('id'=>$this->user->id));
            return false;
        }
    }
    
    function runEditSelfAction()
    {
        $this->user = mm_getUser();
        if ($this->is_post) {
            $this->user->owner_values = $this->req('user');
            $this->user->save();
            $this->addNotice('Updated your user account');
        }
        $this->title = "Edit your account";
    }
    
    function requireUser()
    {
        $id = $this->getRequiredParam('id');
        $user = $this->dao->fetch($id);
        if (!$user) {
            throw new Exception("Failed to find user for id=$id");
        }
        return $user;
    }
    
    function preViewFilter($action)
    {
        parent::preViewFilter($action);
        $this->nav_template = "user/_nav";
    }
}
