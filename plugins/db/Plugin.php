<?php
/**
 * @package database
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package database
 */
class db_Plugin extends plugin_Base
{
    /**
     * Plugin information.
     */
    function info()
    {
        return array(
            'title'   => 'Database API',
            'version' => '0.1',
            'author'  => 'Moxley Stratton',
            'url'     => 'http://www.modernmerchant.org/',
            'depends' => array());
    }
}
