<?php
/**
 * @package mminstall
 */

/**
 * The CLI installer class.
 * 
 * @package mminstall
 */
class mminstall_CliInstaller
{
    public $_config_file;

    /**
     * Run the installer.
     */
    function install()
    {
        $config = $this->getConfig();
        echo "Installing with the following configuration:\n";
        var_export($config);
        echo "\n";

        $try = array(
                     'files',
                     'hostnames',
                     'database_settings',
                     'plugins',
                     'config_file',
                     array('name' => 'settings', 'fresh_install_only' => true),
                     array('name' => 'admin_user', 'fresh_install_only' => true));
        try {
            foreach ($try as $section) {
                if (!is_array($section)) $section = array('name' => $section);
                if (!gv($config, 'upgrade') || !gv($section, 'fresh_install_only')) {
                    $method = camelize('do_' . $section['name']);
                    $this->$method();
                    echo "PASS\n";
                }
            }
        }
        catch (mm_Exception $e) {
            echo "FAILED: " . $e->getMessage() . "\n";
            foreach ($this->checker->results as $result) {
                if (!$result->pass) {
                    echo "  ", $result->error_msg, "\n";
                }
            }
        }
        catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }

    function doFiles()
    {
        $this->sectionHeader("Checking files");
        $this->checker = new mminstall_FileChecker;
        $this->checker->check();
        if ($this->checker->isPass()) {
            throw new mm_Exception("File checker failed");
        }
    }

    function doHostnames()
    {
        $this->sectionHeader("Setting hostnames");
        $this->checker = new mminstall_HostnameSetter;
        $config = $this->getConfig();
        $this->checker->urls = array(
            'http' => $config['urls.http'],
            'https' => $config['urls.https'],
            'mm_root' => $config['urls.mm_root']);
        $this->checker->check();
        if (!$this->checker->isPass()) {
            throw new mm_Exception("Setting hostnames failed");
        }
        $this->hostname_checker = $this->checker;
    }

    function doDatabaseSettings()
    {
        $this->sectionHeader("Checking database");
        $this->checker = $_SESSION['checker'];
        $this->checker = new mminstall_DatabaseChecker;
        $config = $this->getConfig();
        $database = array();
        foreach ($config as $k => $v) {
            if (preg_match('/^database\.(.*)$/', $k, $matches)) {
                $database[$matches[1]] = $v;
            }
        }
        $this->checker->database = $database;
        $this->checker->check();
        if (!$this->checker->isPass()) {
            throw new mm_Exception("Failed database settings");
        }
        $this->database_checker = $this->checker;
    }

    function doPlugins()
    {
        $this->sectionHeader("Installing plugins");
        $this->checker = new mminstall_PluginInstaller;
        $this->config = $this->getConfig();
        $this->checker->upgrade = gv($config, 'upgrade');
        $this->checker->check();
        if (!$this->checker->isPass()) {
            throw new mm_Exception("Failed to install plugins");
        }
        $this->plugins_checker = $this->checker;
    }


    function doConfigFile()
    {
        $this->sectionHeader("Creating configuration file");
        $config = $this->getConfig();
        $this->checker = new mminstall_ConfigWriter;
        $this->checker->urls = $this->hostname_checker->urls;
        $this->checker->database = $this->database_checker->database;
        //$this->checker->rewrites = $_SESSION['installer.rewrites'];
        $this->checker->debug_mode = gv($config, 'debug_mode', false);
        $this->checker->check();
        if (!$this->checker->isPass()) {
            throw new mm_Exception("Failed to create configuration file");
        }
    }

    function doSettings() {
        $this->sectionHeader("Settings");
        $config = $this->getConfig();
        $this->checker = new mminstall_SettingsChecker;
        $this->checker->setValues($config);
        $this->checker->check();
        if (!$this->checker->isPass()) {
            throw new mm_Exception("Failed to set settings");
        }
    }
    
    function doAdminUser()
    {
        $this->sectionHeader("Admin user");
        $config = $this->getConfig();
        $this->checker = new mminstall_AdminAdder;
        $admin = array(
                        'username' => gv($config, 'admin.username'),
                        'password' => gv($config, 'admin.password'));
        $this->checker = new mminstall_AdminAdder();
        $this->checker->admin = $admin;
        $this->checker->database = $this->database_checker->database;
        $this->checker->check();
        if (!$this->checker->isPass()) {
            throw new mm_Exception("Failed to add admin user");
        }
    }

    function sectionHeader($str)
    {
        printf('%-\'.30s ', $str);
    }

    function getConfig()
    {
        if (!isset($this->_config)) {
            $this->_config = $this->fetchConfig();
        }
        return $this->_config;
    }

    /**
     * Fetch the configuration array.
     *
     * @return array
     */
    function fetchConfig()
    {
        if (!file_exists($this->getConfigFile())) {
            throw new mm_Exception("CLI Installer: Failed to find configuration file: " . $this->getConfigFile());
        }
        return parse_ini_file($this->getConfigFile());
    }

    /**
     * Get the config file path.
     * @return string
     */
    function getConfigFile()
    {
        if (!isset($this->_config_file)) {
            $files = array();
            $files[] = MM_LIB . '/conf/config_install.ini';
            $files[] = $_SERVER['HOME'] . '/.modernmerchant_config_install.ini';
            foreach ($files as $file) {
                if (file_exists($file)) {
                    echo "Found file: $file\n";
                    $this->_config_file = $file;
                    break;
                }
            }
            if (!$this->_config_file) {
                throw new mm_Exception("Failed to find configuration file in the default locations: " . implode(', ', $files));
            }
        }
        return $this->_config_file;
    }

    /**
     * Set the config file path.
     * @param string $path
     */
    function setConfigFile($path)
    {
        $this->_config_file = $path;
    }
}

