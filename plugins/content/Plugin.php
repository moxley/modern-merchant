<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package content
 */
class content_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Content Management',
            'version' => '0.3',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array());
    }

    function install()
    {
        $db = mm_getDatabase();
        $drop = "DROP TABLE IF EXISTS mm_content";
        $db->execute($drop);
        
        $create = "CREATE TABLE mm_content (
          id int(11) NOT NULL auto_increment,
          name varchar(40) default NULL,
          description varchar(255) default NULL,
          body text,
          type varchar(20) default 'plain',
          title varchar(255) default NULL,
          sortorder integer not null default '0',
          PRIMARY KEY  (id),
          UNIQUE KEY name (name),
          INDEX (sortorder)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);

        return $this->installPresetContent();
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE mm_content_seq");
        $db->execute("ALTER TABLE mm_content CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_content CHANGE content_id id INT NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_content MODIFY type varchar(20) default 'plain'");
        $db->execute("ALTER TABLE mm_content ADD title varchar(255) DEFAULT NULL AFTER type");
        
        return $this->installPresetContent(array('exclude' => array('home')));
    }


    function installPresetContent($options=array())
    {
        $meta = array(
            array(
                'name' => 'home',
                'description' => "Home Page",
                'title' => mm_getSetting('site.name') . ": Welcome",
                'type' => 'php'),
            array(
                'name' => 'layout.sidebar',
                'description' => "Page sidebar of the web site",
                'type' => 'php'),
            array(
                'name' => 'layout.header',
                'description' => "Page header of the web site",
                'type' => 'php'),
            array(
                'name' => 'layout.header.nav',
                'description' => "Navigation tabs in the page header",
                'type' => 'php'),
            array(
                'name' => 'layout.footer',
                'description' => "Page footer of the web site",
                'type' => 'php'),
            //array(
            //    'name' => 'catalog.product.2column',
            //    'description' => "2-Column Product List",
            //    'type' => 'php'),
            //array(
            //    'name' => 'catalog.product.detail',
            //    'description' => "Product Detail",
            //    'type' => 'php')
        );

        $da = new content_ContentDAO;

        try {
            foreach ($meta as $tpl_info) {
                if (gv($options, 'exclude') && in_array($tpl_info['name'], gv($options, 'exclude'))) continue;
                $content = new content_Content;
                $content->name = $tpl_info['name'];
                $content->description = $tpl_info['description'];
                $content->type = $tpl_info['type'];
                $content->title = $tpl_info['title'];
                $file = $tpl_info['name'] . '.' . $tpl_info['type'];
                $content->body = file_get_contents(dirname(__FILE__) . '/templates/' . $file);
                $da->add($content);
            }
            return true;
        } catch (Exception $e) {
            echo "<pre>";
            echo $e->getTraceAsString();
            echo "</pre>\n";
            throw $e;
        }
    }
    
    function upgrade_to_0_2()
    {
        $db = mm_getDatabase();
        $db->execute("ALTER TABLE mm_content MODIFY name varchar(255) default NULL");
        return true;
    }
    
    function upgrade_to_0_3()
    {
        $db = mm_getDatabase();
        $rs = $db->query("SELECT id, body FROM mm_content");
        while ($row = $rs->fetchAssoc()) {
            $row['body'] = str_replace('$this->writeCategories', '$this->getHelper(\'category\')->writeCategories', $row['body']);
            $db->execute("UPDATE mm_content SET body=? WHERE id=?", array($row['body'], $row['id']));
        }
        return true;        
    }
}
