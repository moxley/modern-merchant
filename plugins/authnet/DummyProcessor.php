<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class authnet_DummyProcessor extends mvc_Model
{
    public $_blah;
    
    function setBlah($val) {
        $this->_blah = $val;
    }
}
