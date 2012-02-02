<?php
/**
 * @package cache
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package cache
 */
class cache_admin_Controller extends admin_Controller
{
    function runClearPageAction() {
        $cache_dir = mm_getConfigValue('filepaths.public') . '/cache';
        rmdirr($cache_dir . '/');
    }
}
