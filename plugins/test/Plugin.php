<?php
/**
 * @package test
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class test_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Unit Testing',
            'version' => '0.1',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('mm'));
    }
}
