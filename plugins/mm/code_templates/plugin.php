<?php
/**
 * Class generation template
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

echo <<<CLASS_EOF
<?php

class $class extends plugin_Base
{
    function info() {
        return array(
            'title'    => "$class",
            'version'  => '0.1',
            'author'   => "Anonymous",
            'url'      => 'http://www.example.com/',
            'depends'  => array('db', 'sess')
        );
    }
    
    function init() {
        // Executed upon each request
    }
    
    function install() {
        return TRUE;
    }
    
    function uninstall() {
        return TRUE;
    }
}

CLASS_EOF;
