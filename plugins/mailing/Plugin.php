<?php

class mailing_Plugin extends plugin_Base
{
    function info() {
        return array(
            'title'    => "mailing_Plugin",
            'version'  => '0.1',
            'author'   => "Moxley Stratton",
            'url'      => 'http://www.modernmerchant.org/',
            'depends'  => array('content')
        );
    }
    
    function init() {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/mailing', 'action' => 'mailing_admin', 'label' => 'Mass Mailings'));
    }
    
    function install() {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE IF EXISTS mm_mailing_broadcast");
        $db->execute("CREATE TABLE mm_mailing_broadcast (
            id int NOT NULL auto_increment,
            name varchar(255),
            from_addr varchar(255),
            subject varchar(255),
            is_html tinyint(1) NOT NULL default '0',
            cancelled tinyint(1) NOT NULL default '0',
            number_attempted int,
            number_sent int,
            started_on datetime,
            completed_on datetime,
            message text,
            notes text,
            PRIMARY KEY(id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        $db->execute("DROP TABLE IF EXISTS mm_mailing_recipient");
        $db->execute("CREATE TABLE mm_mailing_recipient (
            id int NOT NULL auto_increment,
            name varchar(255),
            email varchar(255),
            created_on datetime,
            customer_id int,
            PRIMARY KEY(id),
            UNIQUE KEY(email)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        $db->execute("DROP TABLE IF EXISTS mm_mailing_list");
        $db->execute("CREATE TABLE mm_mailing_list (
            id int NOT NULL auto_increment,
            name varchar(255),
            is_public tinyint(1) NOT NULL DEFAULT '0',
            created_on datetime,
            PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        $db->execute("DROP TABLE IF EXISTS mm_mailing_subscription");
        $db->execute("CREATE TABLE mm_mailing_subscription (
            recipient_id int NOT NULL,
            list_id int NOT NULL,
            PRIMARY KEY (recipient_id, list_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        $db->execute("DROP TABLE IF EXISTS mm_mailing_broadcast_list");
        $db->execute("CREATE TABLE mm_mailing_broadcast_list (
            list_id int NOT NULL,
            broadcast_id int NOT NULL,
            PRIMARY KEY(list_id, broadcast_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        
        // Text for signup form
        $content = mvc_Model::instance('content_Content');
        $content->name = 'mailing.signup.intro';
        $content->description = "Content on the mailing signup page, just above the form";
        $content->body = '<p>We respect your privacy and ' .
            'consider your email address as confidential; we will never ' .
            'sell, or otherwise give your email address or any other ' .
            'information you provide us.</p>';
        $content->type = 'html';
        if (!$content->save()) {
            $this->addErrors($content->errors);
            return false;
        }
        
        return TRUE;
    }
    
    function uninstall() {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE IF EXISTS mm_mailing_broadcast");
        $db->execute("DROP TABLE IF EXISTS mm_mailing_recipient");
        $db->execute("DROP TABLE IF EXISTS mm_mailing_list");
        $db->execute("DROP TABLE IF EXISTS mm_mailing_subscription");
        $db->execute("DROP TABLE IF EXISTS mm_mailing_broadcast_list");
        $content = mvc_Model::fetch('content_Content', array('where' => "name='mailing.signup.intro'"));
        if ($content) $content->delete();
        return TRUE;
    }
}
