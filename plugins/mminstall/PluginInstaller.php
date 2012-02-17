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
class mminstall_PluginInstaller extends mminstall_Checker {
    
    public $upgrade = null;
    
    function isUpgrade() {
        if (is_null($this->upgrade)) {
            return !empty($GLOBALS['MM_CONFIG_OLD']);
        } else {
            return $this->upgrade;
        }
    }
    
    function check() {
        global $install_queries;
        $this->results = array();
        
        // Connect to database
        $result = new mminstall_CheckerResult("Connect to database");
        try {
            $this->db = new db_Database;
        }
        catch (Exception $e) {
            $result->fail($e->getMessage());
        }
        $this->addResult($result);
        if (!$result->pass) return;
        
        // Check plugin dependencies
        $this->manager = new plugin_Manager;
        $result = new mminstall_CheckerResult("Check plugin dependencies");
        $this->plugins = $this->manager->getPlugins();
        if (!$this->plugins) {
            $this->addResult($result);
            $result->fail($this->manager->errors);
            return;
        }
        else {
            $this->addResult($result);
        }
        
        // Remove plugins that don't want to be auto-installed
        $new_plugins = array();
        foreach ($this->plugins as $plugin) {
            if (!(method_exists($plugin, 'info') && ($info = $plugin->info()) && !gv($info, 'auto_install', true))) {
                $new_plugins[] = $plugin;
            }
        }
        $this->plugins = $new_plugins;

        $names_ordered = array();
        foreach ($this->plugins as $plugin) {
            $names_ordered[] = $plugin->name;
        }
        mm_log("plugins, ordered:\n  " . implode("\n  ", $names_ordered));
        
        /* Install or upgrade kernel plugins */
        
        // Separate kernel plugins and non-kernel plugins
        $kernel_plugins = array();
        $non_kernel_plugins = array();
        foreach ($this->plugins as $plugin) {
            if (in_array($plugin->name, plugin_Base::$kernel_names)) {
                $kernel_plugins[] = $plugin;
            }
            else {
                $non_kernel_plugins[] = $plugin;
            }
        }
        
        if ($this->upgrade && $GLOBALS['MM_CONFIG_OLD']['version'] == '0.6.2a1') {
            $this->fixVersion062a1();
        }
        
        mm_log("Installing/Upgrading kernel plugins (count: " . count($kernel_plugins) . ")");
        $db = mm_getDatabase();
        foreach ($kernel_plugins as $plugin) {
            if ($this->upgrade && $db->getOne("SELECT value FROM mm_setting WHERE name=?", array('plugins.' . $plugin->name . '.installed'))) {
                if (!$this->upgradePlugin($plugin)) {
                    throw new Exception("Failed to upgrade kernel plugin '$plugin->name'");
                    return false;
                }
            }
            else {
                if (!$this->installPlugin($plugin)) {
                    throw new Exception("Failed to install kernel plugin '$plugin->name'");
                    return false;
                }
            }
        }
        
        mm_log("Setting 'kernel.active' to TRUE");
        mm_setNewConfigValue('kernel.active', true);
        $GLOBALS['MM_SETTING_DAO_ASSOC'] = null;
        
        // Mark the kernel plugins
        // TODO: wrap in an installer result
        foreach ($kernel_plugins as $plugin) {
            $this->markPlugin($plugin);
        }
        
        /*
         * Install remaining plugins
         */
        $db = mm_getDatabase();
        foreach ($non_kernel_plugins as $plugin) {
            $installed = $db->getOne("SELECT value FROM mm_setting WHERE name=?", array('plugins.' . $plugin->name . '.installed'));
            if ($plugin->isInstallable()) {
                $result = false;
                $upgrade = false;
                if ($this->upgrade && $installed) {
                    $upgrade = true;
                }
                if ($upgrade) {
                    $result = $this->upgradePlugin($plugin, true);
                }
                else {
                    $result = $this->installPlugin($plugin, true);
                }
                if ($result) {
                    $this->markPlugin($plugin);
                }
            }
            else if ($installed) {
                $this->markPlugin($plugin, false);
            }
        }
    }
    
    function markPlugin($plugin, $installed=true) {
        mm_log("markPlugin($plugin->name)");
        mm_setSetting("plugins.{$plugin->name}.installed", $installed);
        mm_setSetting("plugins.{$plugin->name}.version", $plugin->getVersion());
        mm_setSetting("plugins.{$plugin->name}.active", $installed);
    }
    
    function installPlugin($plugin, $failOnError=true) {
        mm_log("installPlugin($plugin->name)");
        if (!isset($this->installed_plugins)) $this->installed_plugins = array();
        $result = new mminstall_CheckerResult("Install plugin: {$plugin->name}");
        try {
            if (!$this->manager->installRaw($plugin)) {
                if ($failOnError) {
                    $result->fail($this->manager->errors);
                    $this->addResult($result);
                    return false;
                } else {
                    $result->warn($this->manager->errors);
                    $this->addResult($result);
                    return true;
                }
            }
            else {
                $this->installed_plugins[] = $plugin;
                $this->addResult($result);
            }
            return true;
        }
        catch (Exception $e) {
            $result->fail("Exception: " . $e->getMessage());
            $this->addResult($result);
            return false;
        }
    }
    
    function install() {
        // Install the plugins
        $installed_plugins = array();
        foreach ($this->plugins as $plugin) {
            $this->installPlugin($plugin);
        }
    
        // Mark plugins as installed and active
        $result = new mminstall_CheckerResult("Mark plugins as installed");
        foreach ($installed_plugins as $plugin) {
            if (!$this->manager->markAsInstalled($plugin)) {
                $result->fail("Failed to mark plugin '$plugin->name' as installed");
                $this->addResult($result);
                return false;
            }
            if (!$this->manager->markAsActive($plugin)) {
                $result->fail("Failed to mark plugin '$plugin->name' as active");
                $this->addResult($result);
                return false;
            }
            mm_setSetting("plugins.{$plugin->name}.version", $plugin->getVersion());
        }
        $this->addResult($result);
        
        return true;
    }
    
    function upgradePlugin($plugin, $failOnError=true) {
        mm_log("upgradePlugin($plugin->name)");
        if (!isset($this->upgraded_plugins)) $this->upgraded_plugins = array();
        $result = new mminstall_CheckerResult("Upgrade plugin: {$plugin->name}");
        try {
            if (!$this->manager->upgradeRaw($plugin)) {
                if ($failOnError) {
                    $result->fail($this->manager->errors);
                    $this->addResult($result);
                    return;
                } else {
                    $result->warn($this->manager->errors);
                    $this->addResult($result);
                    return;                    
                }
            }
            else {
                $this->upgraded_plugins[] = $plugin;
                $this->addResult($result);
            }
            return true;
        }
        catch (Exception $e) {
            mm_log("Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $result->fail("Exception: " . $e->getMessage());
            $this->addResult($result);
            return false;
        }
    }
    
    /**
     * Upgrade the plugins
     */
    function upgrade() {
        foreach ($this->plugins as $plugin) {
            if (!$this->upgradePlugin($plugin)) return false;
        }
        return true;
    }
    
    function fixVersion062a1()
    {
        $versions = array(
            'access' => '0.2',
            'addr' => '0.1',
            'admin' => '0.1',
            'authnet' => '0.2',
            'bulkimages' => '0.2',
            'cart' => '0.2',
            'catalog' => '0.1',
            'category' => '0.4',
            'contact' => '0.1',
            'content' => '0.1',
            'customer' => '0.1',
            'db' => '0.1',
            'mailing' => '0.1',
            'media' => '0.2',
            'mm' => '0.3',
            'order' => '0.1',
            'payment' => '0.1',
            'paypal' => '0.2',
            'paypalwpp' => '0.2',
            'pricing' => '0.1',
            'product' => '0.2',
            'sample' => '0.1',
            'sess' => '0.1',
            'setting' => '0.1',
            'shipping' => '0.1',
            'test' => '0.1',
            'theme' => '0.1',
            'user' => '0.2');
        $db = mm_getDatabase();
        foreach ($versions as $name=>$version) {
            $db->execute("UPDATE mm_setting SET value=? WHERE name=?", array(
                $version, 'plugins.' . $name . '.version'));
        }
    }
}
