<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mminstall_Upgrader extends mminstall_Checker {

    function check() {
        $this->checkUpgrade();
        $this->checkDeleteCompiledTemplates();
    }
    
    function checkUpgrade() {
        $result = new mminstall_CheckerResult("Upgrade the database");
        try {
            require_once('mm/conf/config.php');
            mm_require_once("mminstall/upgrade.php");
        } catch (Exception $e) {
            $result->fail("Exception thrown: " . $e->getMessage());
        }
        $this->addResult($result);
    }
    
    function checkDeleteCompiledTemplates() {
        global $MM_CONFIG;
        $result = new mminstall_CheckerResult("Delete compiled templates");
        $dir = $MM_CONFIG['filepaths.ext'] . '/smarty/templates_c';
        $out = system("rm -rf $dir/* 2>&1", $error_no);
        if ($error_no) {
            $result->fail("Failed to remove compiled templates (error_no=$error_no): $out");
        }
        $this->addResult($result);
    }
}

