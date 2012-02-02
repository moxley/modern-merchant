<?php
/**
 * @package plugin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Administrator MVC Controller for plugins.
 * @package plugin
 */
class plugin_admin_Controller extends admin_Controller
{
    private $manager;
    
    function getManager()
    {
        if (!$this->manager) {
            $this->manager = new plugin_Manager;
        }
        return $this->manager;
    }
    
    function runDefaultAction()
    {
        $this->runListAction();
        $this->setTemplate('plugin/admin/list');
    }
    
    function runListAction()
    {
        $this->plugins = $this->getManager()->getPlugins();
        $this->title = "Plugins";
    }
    
    function runEditAction()
    {
        $this->requirePlugin();
        $this->title = "Plugin Settings for '{$this->plugin->name}'";
        $this->nav_template = "plugin/admin/_nav";
    }

    function runUpdateAction()
    {
        $this->requirePlugin();
        $this->plugin->admin_values = $this->req('plugin');
        $this->plugin->save();
        $name = $this->plugin->name;
        $this->addNotice("Updated plugin '$name'");
        $this->redirectToAction('plugin_admin.list');
        return false;
    }
    
    function runInstallAction()
    {
        $this->requirePlugin();
        if (!$this->getManager()->install($this->plugin)) {
            $this->addWarning("Failed to install plugin");
        }
        else {
            $this->addNotice("Installed plugin '{$this->plugin->name}'");
        }
        
        $this->redirectToAction('plugin_admin.list');
        return false;
    }

    function runUninstallAction()
    {
        $this->requirePlugin();
        
        if (!$this->getManager()->uninstall($this->plugin)) {
            $this->addWarning("Failed to install plugin");
        }
        else {
            $this->addNotice("Uninstalled plugin '{$this->plugin->name}'");
        }
        $this->redirectToAction('plugin_admin.list');
        return false;
    }
    
    function requirePlugin()
    {
        $name = $this->getRequiredParam('name');
        $this->plugin = $this->getManager()->getPluginForName($name);
        if (!$this->plugin) {
            throw new Exception("Unrecognized plugin '$name'");
        }
        return $this->plugin;
    }
}
