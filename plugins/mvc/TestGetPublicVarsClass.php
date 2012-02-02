<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_TestGetPublicVarsClass extends mvc_Model {
    private $private_var = 'private_var';
    public $var1 = 'var1';
    var $var2 = 'var2';
    function getQuantitiesById() {
        return array('line_1' => 12);
    }
    private function getPrivateValue() {
        return 10;
    }
    public function getValueWithArgs($arg1) {
        return 20;
    }
}
