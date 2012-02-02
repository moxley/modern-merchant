<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_Hooks
{
    static $listener_registry;
    
    private function __construct() {}
    
    static function notifyListeners($event_name, $param=null)
    {
        mvc_Events::notify($event_name, $param);
    }
    
    public function addListener($event_name, $function)
    {
        mvc_Events::listen($event_name, $function);
    }

    // From HookRegistry

    static function registerAction(mvc_ActionCallback $callback)
    {
        return mvc_HookRegistry::instance()->registerAction($callback);
    }

    static function lookupAction($action_uri)
    {
        return mvc_HookRegistry::instance()->lookupAction($action_uri);
    }
            
    public static function registerController($plugin_name, $object_or_classname, $priority=0)
    {
        $registry = mvc_HookRegistry::instance();
        $registry->registerController($plugin_name, $object_or_classname, $priority);
    }
    
    public static function lookupController($plugin_name, $default=null)
    {
        $registry = mvc_HookRegistry::instance();
        $reg = $registry->lookupController($plugin_name, $default);
        if ($reg) return $reg->controller;
        return null;
    }

    public static function registerView($plugin_name, $object_or_classname, $priority=0)
    {
        $registry = mvc_HookRegistry::instance();
        $registry->registerView($plugin_name, $object_or_classname, $priority);
    }
    
    public static function lookupView($plugin_name, $default=null)
    {
        $registry = mvc_HookRegistry::instance();
        $reg = $registry->lookupView($plugin_name, $default);
        if ($reg) return $reg->view;
        return null;
    }

    public static function pluginInstalled($plugin_name)
    {
        return mm_getSetting("plugins.$plugin_name.installed") ? true : false;
    }
    
    public static function installPlugin($plugin_name)
    {
        $nothing = null;
        self::notifyListeners("plugins.$plugin_name.install", $nothing);
        mm_setSetting("plugins.$plugin_name.installed", true);
    }

    public static function pluginEnabled($plugin_name)
    {
        return mm_getSetting("plugins.$plugin_name.enabled") ? true : false;
    }

    public static function enablePlugin($plugin_name)
    {
        if (!self::pluginInstalled($plugin_name)) {
            throw new mvc_BusinessException("Attempted to enable plugin that isn't installed");
        }
        mm_setSetting("plugins.$plugin_name.enabled", true);
    }

    public static function disablePlugin($plugin_name)
    {
        if (!self::pluginInstalled($plugin_name)) {
            throw new mvc_BusinessException("Attempted to disable plugin that isn't installed");
        }
        mm_setSetting("plugins.$plugin_name.enabled", true);
    }
    
    public static function registerMenu($menu)
    {
        mvc_HookRegistry::instance()->registerMenu($menu);
    }

    public static function registerMenuItem($item)
    {
        mvc_HookRegistry::instance()->registerMenuItem($item);
    }

    public static function getMenus()
    {
        return mvc_HookRegistry::instance()->getMenus();
    }

    public static function getMenu($path)
    {
        return mvc_HookRegistry::instance()->getMenu($path);
    }
    
    static function extendModel($base_class, $extension_class)
    {
        return mvc_HookRegistry::instance()->extendModel($base_class, $extension_class);
    }
    
    static function getExtensionsForModelClass($base_class)
    {
        return mvc_HookRegistry::instance()->getExtensionsForModelClass($base_class);
    }
    
    static function overrideTemplate($original_template, $new_template) {
        return mvc_HookRegistry::instance()->overrideTemplate($original_template, $new_template);
    }
    
    static function getTemplateOverride($original_template) {
        return mvc_HookRegistry::instance()->getTemplateOverride($original_template);
    }
}
