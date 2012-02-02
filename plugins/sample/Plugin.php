<?php
/**
 * @package sample
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Sample plugin.
 * @package sample
 */
class sample_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => 'Sample Plugin',
            'version' => '0.1',
            'author'  => 'Your Name',
            'url'     => 'http://www.example.com/',
            'depends' => array('mm'));
    }

    /**
     * Called automatically for each request if the plugin is active.
     */
    function init()
    {
    }

    /**
     * Called automatically when Modern Merchant is installed, and when user
     * requests that the plugin be installed.
     */
    function install()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE IF EXISTS mm_sample");
        $db->execute("
        CREATE TABLE mm_sample (
            id int not null auto_increment,
            name varchar(255) not null,
            comment text,
            PRIMARY KEY (id),
            UNIQUE KEY (name)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
        ");
        $db->execute("INSERT INTO mm_sample (name, comment) VALUES (?,?)", array("morning", "It's too early"));
        $db->execute("INSERT INTO mm_sample (name, comment) VALUES (?,?)", array("coffee", "Double Soy Latte"));
        $db->execute("INSERT INTO mm_sample (name, comment) VALUES (?,?)", array("wifi", "WiFi Required"));
        return true;
    }

    /**
     * Called automatically when user requests that the plugin to be uninstalled.
     */
    function uninstall()
    {
        $db->execute("DROP TABLE IF EXISTS mm_sample");
        return true;
    }
}
