<?php
/**
 * @package auth
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Provides authentication and login functions for other controllers
 */
class auth_Controller extends admin_Controller
{
    function beforeAction()
    {
        return true;
    }
    
    function runDefaultAction()
    {
        $this->setForward('auth.prompt');
    }

    /**
     * Prompt for a username and password
     */
    function runPromptAction()
    {
        // Often the calling controller will want to transition
        // back to a state within itself after the authentication is done
        $this->transition = $this->req('transition');

        $this->login = new user_User;
        
        if (MM_DEMO_MODE)
        {
            $this->login->username = 'demo';
            $this->login->password = 'demo';
        }
        
        $this->site = "";
        $this->title = "Login";
        $this->logged_in = false;
    }
    
    function runLogoutAction()
    {
        $user = mm_getUser();
        $user->logout();
        $this->redirectToAction('auth.prompt');
        return false;
    }
    
    /**
     * Authenticate the user.
     */
    function runLoginAction()
    {
        mm_log("Top of runLoginAction()");
        $this->login = new user_User($this->req('login'));
        
        // Log the user in
        if (!($user = $this->login->login())) {
            $this->addWarning("Login failed. Check your username or password and try again.");
            $this->setReturnAction('auth.prompt');
            return;
        }
        
        $this->addNotice("Hi {$user->username}!");
        if( $this->transition = $this->req('transition')) {
            mm_log("Redirecting to transition: ", $this->transition);
            $this->redirectToAction($this->transition);
            return false;
        } else {
            $sess = mm_getSession();
            $action = $sess->get('after_login_action');
            mm_log("after_login_action: ", $action);
            if (!$action) {
                $action = mm_getConfigValue('actions.admin_default');
            }
            else {
                $sess->set('after_login_action', null);
            }
            $this->redirectToAction($action);
            return false;
        }
    }
}
