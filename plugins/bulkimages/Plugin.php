<?php
/**
 * @package bulkimages
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package bulkimages
 */
class bulkimages_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => "Bulk Image Upload",
            'version' => '0.2',
            'author'  => "Moxley Stratton",
            'url'     => "http://www.modernmerchant.org",
            'depends' => array('mm'));
    }
}
