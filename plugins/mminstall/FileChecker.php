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
class mminstall_FileChecker extends mminstall_Checker {

    function check() {
        // Check PHP version
        $result = new mminstall_CheckerResult("PHP Version >= 5.1");
        $ver = phpversion();
        if (version_compare($ver, "5.1", "<")) {
            $result->fail("Modern Merchant requires PHP version 5.1 or later. You have $ver");
        }
        $this->addResult($result);
        if (!$result->pass) return;
        
        if (!isset($_SESSION['MM_CONFIG']['prereqs'])) {
            $_SESSION['MM_CONFIG']['prereqs'] = array();
        }

        /*
         * Check the SOAP extension
         */
        $result = new mminstall_CheckerResult("PHP SOAP extension");
        mm_log("Checking SoapClient");
        if (class_exists('SoapClient')) {
            $_SESSION['MM_CONFIG']['prereqs']['soap'] = true;
        }
        else {
            $result->warn("The PayPal Website Payments Pro payment method cannot be used without the PHP SOAP extension enabled.");
            $_SESSION['MM_CONFIG']['prereqs']['soap'] = false;
        }
        $this->addResult($result);

        /*
         * Check the SSL extension
         */
        $result = new mminstall_CheckerResult("PHP openssl extension");
        if (!extension_loaded('openssl')) {
            $result->warn("Not installed. None of the payment methods can be used without the PHP openssl extension.");
        }
        $this->addResult($result);
        
        /*
         * Check the cUrl extension
         */
        $result = new mminstall_CheckerResult("PHP cURL extension");
        if (!extension_loaded('curl')) {
            $result->warn("Not installed. By default, the payment methods use the PHP cURL extension. It may be possible to get by with the openssl extension.");
        }
        $this->addResult($result);
        
        // Set MM version
        mm_setNewConfigValue('version', mm_version(true));
        
        // Check file permissions
        $this->checkWritableDir(MM_LIB . "/public");
        $this->checkWritableDir(MM_LIB . "/private");

        if (is_file(MM_CONFIG_FILE)) {
            $this->checkWritableFile(MM_CONFIG_FILE);
        } else {
            $this->checkWritableDir(dirname(MM_CONFIG_FILE));
        }
    }

    function friendlyPath($path) {
        $root = realpath(dirname(__FILE__) . '/../../../');
        return str_replace($root . '/', '', $path);
    }
    
    function checkWritableDir($path) {
        $friendly_path = $this->friendlyPath($path);
        $result = new mminstall_CheckerResult("Write permission to <code>$friendly_path</code>");
        if (!is_dir($path)) $result->fail("Cannot find <code>$friendly_path</code>");
        else if (!mm_is_writable($path)) $result->failWritableDir($friendly_path);
        $this->addResult($result);
        return $result->pass;
    }
    
    function checkWritableFile($path) {
        $friendly_path = $this->friendlyPath($path);
        $result = new mminstall_CheckerResult("Write permission to <code>$friendly_path</code>");
        if (!is_file($path)) $result->fail("Cannot find <code>$friendly_path</code>");
        else if (!mm_is_writable($path)) {
            $result->failWritableFile($friendly_path);
        }
        $this->addResult($result);
        return $result->pass;
    }
    
    function checkReadableFile($path) {
        $friendly_path = $this->friendlyPath($path);
        $result = new mminstall_CheckerResult("Check file path <code>$friendly_path</code>");
        if (!file_exists($path)) {
            $result->fail("Cannot find $friendly_path");
        }
        $this->addResult($result);
        return $result->pass;
    }
    
}
