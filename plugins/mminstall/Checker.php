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
class mminstall_Checker extends mvc_Model {

    var $results = array();
    var $req;
    
    function check() {
    }
    
    function setRequest($req) {
        $this->req = $req;
    }
    
    function addResult($result) {
        $this->results[] = $result;
    }
    
    function isPass() {
        foreach ($this->results as $result) {
            if (!$result->pass) return false;
        }
        return true;
    }
    
}

