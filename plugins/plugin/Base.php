<?php
/**
 * @package plugin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * The <tt>Plugin</tt> base class.
 * @package plugin
 */
class plugin_Base extends mvc_Model
{
    static $kernel_names = array('db', 'setting', 'sess');
    
    public $_info;
    public $_name;
    public $dependents_count = 0;
    
    private $_depends;
    public $_settings;
    
    function __construct($values=null)
    {
        if (!$values) $values = array();
        $this->_info = array(
            'title'   => '',
            'version' => '',
            'author'  => '',
            'url'     => '',
            'depends' => array()
        );
        $info = array_delete_at($values, 'info');
        if ($info) {
            $this->_info = array_merge($this->_info, $info);
        }
        $name = array_delete_at($values, 'name');
        if ($name) $this->_name = $name;
        $depends = array_delete_at($values, 'depends', $this->_info['depends']);
        $this->_info['depends'] = $depends;
        parent::__construct($values);
    }
    
    function info()
    {
        return $this->_info;
    }
    
    function getInfo()
    {
        return $this->info();
    }
    
    function getName()
    {
        if ($this->_name) return $this->_name;
        $parts = explode('_', get_class($this));
        return $parts[0];
    }
    
    function getTitle()
    {
        return gv($this->getInfo(), 'title');
    }
    
    function getVersion()
    {
        return gv($this->getInfo(), 'version');
    }

    function getAuthor()
    {
        return gv($this->getInfo(), 'author');
    }
    
    function getUrl()
    {
        return gv($this->getInfo(), 'url');
    }
    
    function getDepends()
    {
        if (!isset($this->_depends)) {
            $info = $this->getInfo();
            $depends = gv($info, 'depends');
            if (!is_array($depends)) {
                if (isset($depends)) {
                    $depends = array($depends);
                }
                else {
                    $depends = array();
                }
            }
            $this->_depends = $depends;
        }
        return $this->_depends;
    }
    
    function addDepend($name)
    {
        if (!isset($this->_depends)) {
            $this->getDepends();
        }
        $this->_depends[] = $name;
        return $this;
    }
    
    function getInstalled()
    {
        return mm_getSetting("plugins.$this->name.installed");
    }

    function getActive()
    {
        return $this->installed && mm_getSetting("plugins.$this->name.active");
    }

    function getPriority()
    {
        return mm_getSetting("plugins.$this->name.priority", 0);
    }
    
    function setAdminValues($values)
    {
        $settings_names = $this->getInputSettingsNames();
        $this->_settings = array();
        foreach ($settings_names as $name) {
            if (array_key_exists($name, $values)) {
                $this->_settings[$name] = gv($values, $name);
            }
        }
    }
    
    function loadSettings()
    {
        $settings_names = array_merge($this->getReadOnlySettingsNames(), $this->getInputSettingsNames());
        foreach ($settings_names as $name) {
            $this->$name = mm_getSetting("plugins.$this->name.$name");
        }
    }
    
    function save() {
        if (isset($this->_settings)) {
            foreach ($this->_settings as $name=>$value) {
                mm_setSetting("plugins.$this->name.$name", $value);
            }
            unset($this->_settings);
        }
    }

    /**
     * Overridden by subclasses, this method allows plugin to perform actions for each request.
     */
    function init()
    {
        // Empty
    }
    
    /**
     * Overridden by subclasses, this method allows plugin to perform actions in
     * response to being installed.
     *
     * @return boolean True if tasks were performed without error
     */
    function install()
    {
        return true;
    }
    
    /**
     * Overridden by subclasses, this method allows plugin to perform actions in
     * response to being uninstalled.
     *
     * @return boolean True if tasks were performed without error
     */
    function uninstall()
    {
        return true;
    }
    
    function managedUpgrade()
    {
        $name = $this->name;
        $file_version = $this->getVersion();
        
        // If this is a kernel plugin, and Modern Merchant version is < 0.6.0,
        // calculate the old version
        $is_kernel = in_array($this->name, plugin_Base::$kernel_names);
        
        // Is previous version less than 0.6.0?
        $previous_version = $GLOBALS['MM_CONFIG_OLD']['version'];
        $version_pattern = '/^(\d+\.\d+\.\d+)([a-z]+\d+)?$/';
        preg_match($version_pattern, $previous_version, $match);
        $previous_version = $match[1];
        $previous_suffix = @$match[2];
        preg_match($version_pattern, $GLOBALS['MM_CONFIG']['version'], $match);
        $current_version = $match[1];
        $current_suffix = @$match[2];
        $is_pre_06 = mm_compare_versions($previous_version, '0.6.0') < 0;
        
        if ($is_kernel && $is_pre_06) {
            $db_version = "0.0";
        }
        else {
            $db_version = mm_getSetting("plugins.$name.version", null, $force=true);
            if (!$db_version) $db_version = '0.0';
        }
        
        $result = true;
        $version_i = mm_next_version($db_version);
        while ($result && mm_compare_versions($version_i, $file_version) <= 0) {
            $method = "upgrade_to_" . str_replace('.', '_', $version_i);
            mm_log("Check for upgrade handle: $method");
            if (method_exists($this, $method)) {
                $result = $this->$method();
            }
            if (!$is_kernel) {
                mm_setSetting("plugins.$name.version", $version_i, $force=true);
            }
            $version_i = mm_next_version($version_i);
        }
        return $result;
        
    }
    
    function getFormFields() {
        $fields = array(
            array('name' => 'plugin[title]',    'type' => 'data',     'label' => 'Title'),
            array('name' => 'plugin[author]',   'type' => 'data',     'label' => 'Author'),
            array('name' => 'plugin[url]',      'type' => 'link',     'label' => 'Home Page')
        );
        $name = $this->getName();
        $extra_names = $this->getInputSettingsNames();
        $settings_dao = new setting_SettingDAO;
        $all_settings = $settings_dao->getCachedList();
        foreach ($all_settings as $setting) {
            if (preg_match('/^plugins\.([^\.]+)\.(.+)$/', $setting->name, $match) && $match[1] == $this->name && in_array($match[2], $extra_names)) {
                $name = $match[2];
                $field = array('name' => "plugin[$name]", 'label' => ucfirst(str_replace('_', ' ', $name)));
                if ($setting->type == 'boolean') {
                    $field['type'] = 'checkbox';
                }
                if ($setting->description) {
                    $field['description'] = $setting->description;
                }
                $fields[] = $field;
            }
        }
        
        return $fields;
    }
    
    function getReadOnlySettingsNames() {
        return array('installed', 'version');
    }
    
    function getInputSettingsNames() {
        $name = $this->name;
        $settings = mm_getSettingsAsAssoc("plugins.$name");
        $skip_names = $this->getReadOnlySettingsNames();
        $names = array();
        foreach ($settings as $name=>$value) {
            if (in_array($name, $skip_names)) continue;
            $names[] = $name;
        }
        return $names;
    }
    
    function isInstallable()
    {
        return true;
    }
}
