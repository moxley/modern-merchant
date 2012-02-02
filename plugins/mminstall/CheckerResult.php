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
class mminstall_CheckerResult {
    public $title;
    public $error_msg;
    public $pass = true;
    public $warn = false;
    
    function __construct($title) {
        $this->title = $title;
    }
    function setErrorMsg($msg) {
        if (is_array($msg)) {
            $this->error_msg = implode('. ', $msg);
        }
        else {
            $this->error_msg = $msg;
        }
    }
    function fail($msg=null) {
        $this->pass = false;
        if ($msg != null) $this->setErrorMsg($msg);
    }
    function warn($msg=null) {
        $this->pass = true;
        $this->warn = true;
        if ($msg != null) $this->setErrorMsg($msg);
    }
    function failWritableDir($path) {
        $this->fail("Please grant write+execute permissions to the web server for <code>$path</code>. For example: <code>chmod 777 $path</code>");
    }
    function failWritableFile($path) {
        $this->fail("Please grant write permissions to the web server for <code>$path</code>: For example: <code>chmod 666 $path</code>");
    }
    public function __toString() {
        return "title=" . $this->title .", pass=" . ($this->pass ? 'TRUE' : 'FALSE');
    }
}

