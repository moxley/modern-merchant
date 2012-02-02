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
class mminstall_Controller extends mvc_Controller
{
    function getLayoutTemplate() {
        return MM_LIB . '/plugins/mminstall/templates/layout.php';
    }
    
    function runDefaultAction() {
        $_SESSION['MM_CONFIG'] = $GLOBALS['MM_CONFIG'];
        $this->setForward('mminstall.files');
    }
    
    function runFilesAction() {
        $this->checker = new mminstall_FileChecker;
        $this->checker->check();
        if ($this->checker->isPass()) {
            $this->setForward('mminstall.hostnames');
            return;
        }
        $this->setTemplate('mminstall/results');
    }
    
    function runHostnamesAction() {
        $default_urls = array(
            'http' => mm_getConfigValue('urls.http') ? mm_getConfigValue('urls.http') : 'http://' . $_SERVER['HTTP_HOST'],
            'https' => mm_getConfigValue('urls.https') ? mm_getConfigValue('urls.https') : 'https://' . $_SERVER['HTTP_HOST']);
        $this->urls = $this->req('urls', $default_urls);
        if ($this->is_post) {
            $this->checker = new mminstall_HostnameSetter;
            $this->checker->urls = $this->urls;
            $this->checker->check();
            $_SESSION['checker'] = $this->checker;
            if (!$this->checker->isPass()) {
                unset($_SESSION['installer.urls']);
                $this->redirectToAction('mminstall.hostnames');
                return false;
            }
            else {
                $_SESSION['installer.urls'] = $this->urls;
                $this->redirectToAction('mminstall.databaseSettings');
                return false;
            }
        }
    }
    
    //function runModRewriteAction() {
    //    $this->checker = $_SESSION['checker'];
    //    if ($this->is_post) {
    //        $this->checker = new mminstall_ModRewriteChecker;
    //        $this->checker->check();
    //        $_SESSION['checker'] = $this->checker;
    //        if (!$this->checker->isPass()) {
    //            $this->redirectToAction('mminstall.modRewrite');
    //            return false;
    //        }
    //        else {
    //            $this->redirectToAction('mminstall.databaseSettings');
    //            return false;
    //        }
    //    }
    //}
    
    function runDatabaseSettingsAction() {
        $this->checker = $_SESSION['checker'];
        $db_config_defaults = array(
            'name'     => mm_getConfigValue('database.name') ? mm_getConfigValue('database.name') : '',
            'host'     => mm_getConfigValue('database.host') ? mm_getConfigValue('database.host') : 'localhost',
            'user'     => mm_getConfigValue('database.user') ? mm_getConfigValue('database.user') : '',
            'password' => mm_getConfigValue('database.password') ? mm_getConfigValue('database.password') : '',
            'port'     => mm_getConfigValue('database.port') ? mm_getConfigValue('database.port') : 3306
        );
        $this->database = $this->req('database', $db_config_defaults);
        if ($this->is_post) {
            $this->checker = new mminstall_DatabaseChecker;
            $this->checker->database = $this->req('database');
            $this->checker->check();
            $_SESSION['checker'] = $this->checker;
            if ($this->checker->isPass()) {
                $this->redirectToAction('mminstall.installPlugins');
                return false;
            }
            else {
                $this->redirectToAction('mminstall.databaseSettings');
                return false;
            }
        }
    }
    
    function runInstallPluginsAction() {
        $this->checker = $_SESSION['checker'];
        $this->plugin_checker = new mminstall_PluginInstaller;
        if ($this->is_post) {
            $this->checker = new mminstall_PluginInstaller;
            $this->checker->upgrade = $this->req('upgrade') ? true : false;
            mm_setNewConfigValue('is_upgrade', $this->checker->upgrade);
            $this->checker->check();
            $_SESSION['checker'] = $this->checker;
            if ($this->checker->isPass()) {
                $this->redirectToAction('mminstall.configuration');
                return false;
            }
            else {
                $this->redirectToAction('mminstall.installPlugins');
                return false;
            }
        }
    }
    
    function runConfigurationAction() {
        $this->checker = $_SESSION['checker'];
        if ($this->is_post) {
            $this->checker = new mminstall_ConfigWriter;
            $this->checker->urls = $_SESSION['installer.urls'];
            $this->checker->database = $_SESSION['installer.database'];
            $this->checker->rewrites = $_SESSION['installer.rewrites'];
            $this->checker->debug_mode = $this->req('debug_mode') ? true : false;
            $this->checker->check();
            $_SESSION['checker'] = $this->checker;
            if ($this->checker->isPass()) {
                $this->redirectToAction('mminstall.settings');
                return false;
            }
            else {
                $this->redirectToAction('mminstall.configuration');
                return false;
            }
        }
    }
    
    function runSettingsAction() {
        if (mm_getConfigValue('is_upgrade')) {
            $this->redirectToAction('mminstall.complete');
            return false;
        }
        else {
            $this->checker = $_SESSION['checker'];
            $this->settings = new mminstall_SettingsChecker;
            if ($this->is_post) {
                $this->checker = new mminstall_SettingsChecker($this->req('settings'));
                $_SESSION['checker'] = $this->checker;
                $this->checker->check();
                if ($this->checker->isPass()) {
                    $this->redirectToAction('mminstall.adminUser');
                    return false;
                }
                else {
                    $this->redirectToAction('mminstall.settings');
                    return false;
                }
            }
        }
    }
    
    function runAdminUserAction() {
        if (mm_getConfigValue('is_upgrade')) {
            $this->redirectToAction('mminstall.complete');
            return false;
        }
        else {
            $this->checker = $_SESSION['checker'];
            $this->has_existing = false;
            $this->admin = $this->req('admin', array('username' => 'admin', 'new_password' => ''));
            if ($this->is_post) {
                $this->checker = new mminstall_AdminAdder();
                $this->checker->admin = $this->admin;
                $this->checker->database = $_SESSION['installer.database'];
                $this->checker->check();
                $_SESSION['checker'] = $this->checker;
                if ($this->checker->isPass()) {
                    $this->redirectToAction('mminstall.complete');
                    return false;
                }
                else {
                    $this->redirectToAction('mminstall.adminUser');
                    return false;
                }
            }
        }
    }
    
    function runCompleteAction() {
        $this->checker = $_SESSION['checker'];
    }
}
