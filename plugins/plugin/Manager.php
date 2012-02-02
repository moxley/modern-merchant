<?php
/**
 * @package plugin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * The plugin manager.
 * @package plugin
 */
class plugin_Manager extends mvc_Model
{
    /**
     * Returns an array of plugin objects, sorted by dependency, then alpha.
     * 
     * @return array An array of plugins
     */
    function getPlugins()
    {
        $names = $this->getPluginDirs();
        
        // Build a plugin list for the purpose of sorting
        $plugins = array();
        $lookup = array();
        foreach ($names as $name) {
            $plugin = $this->getPluginForName($name);
            $lookup[$name] = $plugin;
            $plugins[] = $plugin;
        }
        
        // Sort the plugins
        //$plugins = array_reverse($plugins);
        //usort($plugins, array($this, 'comparePlugins'));
        //$plugins = array_reverse($plugins);
        $sorter = new plugin_Sorter($plugins);
        $plugins = $sorter->sort();
        
        return $plugins;
    }
    
    /**
     * Get the plugin object for a given name.
     *
     * If the plugin doesn't have a Plugin object, create one.
     */
    function getPluginForName($plugin)
    {
        $class = $plugin . '_Plugin';
        $base = mm_getConfigValue('filepaths.plugins');
        if (!file_exists("$base/$plugin")) {
            return null;
        }
        if (!file_exists("$base/$plugin/Plugin.php")) {
            $obj = new plugin_Base;
            $obj->_name = $plugin;
            return $obj;
        }
        $obj = new $class;
        $obj->loadSettings();
        return $obj;
    }
    
    /**
     * Get an array of all plugin directories, sorted by alpha.
     * @return array  An array of plugin names
     */
    function getPluginDirs()
    {
        $plugins_path = mm_getConfigValue('filepaths.plugins');
        if (!$plugins_path) {
            throw new Exception("'filepaths.plugins' configuration value is not defined");
        }
        $skip_pattern = '^\.';
        $skip_items = array('CVS', 'classes', 'compat_0_00', 'hook', 'smarty');
        $dirs = array();
        $dir = dir($plugins_path);
        while (false !== ($entry = $dir->read())) {
            if (preg_match("/$skip_pattern/", $entry)) continue;
            if (in_array($entry, $skip_items)) continue;
            if (!is_dir("$plugins_path/$entry")) continue;
            $dirs[] = $entry;
        }
        $dir->close();
         sort($dirs);
        return $dirs;
    }
    
    function getInstalledSettingName($name)
    {
        return "plugins.$name.installed";
    }
    
    function getActiveSettingName($name)
    {
        return "plugins.$name.active";
    }

    function markAsInstalled($plugin)
    {
        if (is_object($plugin)) $plugin = $plugin->name;
        $setting_name = "plugins.$plugin.installed";
        $sdao = new setting_SettingDAO;
        $sdao->set($setting_name, true);
        return true;
    }

    function markAsActive($plugin)
    {
        if (is_object($plugin)) $plugin = $plugin->name;
        $setting_name = "plugins.$plugin.active";
        $sdao = new setting_SettingDAO;
        $sdao->set($setting_name, true);
        return true;
    }
    
    function markAsInstalledIfNotDefined($plugin)
    {
        return $this->markIfNotDefined($plugin, "installed");
    }
    
    function markAsActiveIfNotDefined($plugin)
    {
        return $this->markIfNotDefined($plugin, "active");
    }
    
    function markIfNotDefined($plugin, $mark_as)
    {
        if (is_object($plugin)) $plugin = $plugin->name;
        $sdao = new setting_SettingDAO;
        $all_settings = $sdao->getAllAssoc();
        $setting_name = "plugins.$plugin.$mark_as";
         if (!array_key_exists($setting_name, $all_settings)) {
            $sdao->set($setting_name, true);
        }
        return true;
    }
    
    function install($plugin)
    {
        if (!is_object($plugin)) $plugin = $this->getPluginForName($plugin);
        $name = $plugin->name;
        $installed_plugins = $this->getInstalledPlugins();
        $installed_names = array();
        foreach ($installed_plugins as $p) {
            $installed_names[] = $p->name;
        }
        if (in_array($plugin->name, $installed_names)) throw new Exception("Plugin '$name' is already installed");

        // Check plugin dependencies
        foreach ($plugin->depends as $dep) {
            if (!in_array($dep, $installed_names)) {
                throw new Exception("Plugin '$name' requires plugin '$dep', which isn't installed");
            }
        }
        
        if (!$plugin->install()) {
            throw new Exception("Failed to install plugin");
        }
        $this->markAsInstalled($plugin);
        $this->markAsActive($plugin);
        $version = $plugin->getVersion();
        mm_setSetting("plugins.$name.version", isset($version) ? $version : '0.0');

        return $plugin;
    }
    
    function installRaw($plugin)
    {
        try {
            if (method_exists($plugin, 'install')) {
                if (!$plugin->install()) {
                    $this->addErrors($plugin->errors);
                    return false;
                }
            }
            return true;
        }
        catch (Exception $e) {
            $this->addError("Plugin '$plugin->name' failed to install: " . $e->getMessage());
            return false;
        }
    }
    
    function upgradeRaw($plugin)
    {
        if (!$plugin->managedUpgrade()) {
            $this->addErrors($plugin->errors);
            return false;
        }
        return true;
    }
    
    function upgrade($plugin)
    {
        if (!$plugin->managedUpgrade()) {
            $this->addErrors($plugin->errors);
            return false;
        }
        else {
            $this->markIfNotDefined($plugin, "installed");
            $this->markIfNotDefined($plugin, "active");
            return true;
        }
    }
    
    function uninstall($plugin)
    {
        if (!is_object($plugin)) $plugin = $this->getPluginForName($plugin);
        if (!$plugin) {
            throw new Exception("Bad parameter for plugin_Manager::uninstall(\$plugin)");
        }
        $name = $plugin->name;
        $setting_name = "plugins.$name.installed";
        if (!mm_getSetting($setting_name)) {
            throw new Exception("Plugin '$name' is not installed");
        }
        $plugin->uninstall();
        mm_setSetting($setting_name, false);
        mm_setSetting("plugins.$name.active", false);
        return $plugin;
    }
    
    function getActivePlugins()
    {
        return $this->getPluginsWithStatus('active');
    }

    function getInstalledPlugins()
    {
        return $this->getPluginsWithStatus('installed');
    }
    
    function getPluginsWithStatus($kind)
    {
        $settings = mm_getSettingsAsAssoc();
        $active_plugins = array();
        foreach ($settings as $name => $value) {
            if (preg_match('/^plugins\.([^.]*)\.([^.]*)$/', $name, $match) && $match[2] == $kind) {
                if ($value) {
                    $plugin = $this->getPluginForName($match[1]);
                    if ($plugin) {
                        $active_plugins[] = $plugin;
                    }
                }
            }
        }
        return $active_plugins;
    }
    
    function initializePlugins()
    {
        global $MM_CONFIG;
        $plugins = $this->getActivePlugins();
        $plugin_names = array();
        foreach ($plugins as $plugin) {
            $plugin->init();
            $plugin_names[] = $plugin->name;
        }
        $MM_CONFIG['plugin_names'] = $plugin_names;
        return true;
    }
}
