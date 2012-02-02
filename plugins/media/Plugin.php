<?php
/**
 * @package media
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package media
 */
class media_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Media Management',
            'version' => '0.2',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('setting'));
    }

    function install()
    {
        $db = mm_getDatabase();
        
        $queries[] = "DROP TABLE IF EXISTS `mm_media`";
        $queries[] = "CREATE TABLE `mm_media` (
          `id`                int NOT NULL auto_increment,
          `owner_id`          int default NULL,
          `owner_type`        varchar(30) default NULL,
          `name`              varchar(40) default NULL,
          `description`       text,
          `width`             int default NULL,
          `height`            int default NULL,
          `filename`          varchar(40) default NULL,
          `mime_type`         varchar(40) default NULL,
          `data`              mediumblob,
          sortorder           integer not null default '0',
          PRIMARY KEY (`id`),
          INDEX (owner_id, owner_type)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        
        foreach ($queries as $sql) {
            $db->execute($sql);
        }

        mm_setSetting('thumbnail.size', '40x40');

        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE mm_media_seq");

        $db->execute("ALTER TABLE mm_media CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_media CHANGE media_id id INT NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_media CHANGE image_data data mediumblob");
        $db->execute("ALTER TABLE mm_media MODIFY product_id integer default null");
        
        // Change product_id, category_id to owner_id, owner_type
        $db->execute("ALTER TABLE mm_media ADD owner_id int AFTER product_id");
        $db->execute("ALTER TABLE mm_media ADD owner_type varchar(30) AFTER owner_id");
        $db->execute("UPDATE mm_media SET owner_id=product_id, owner_type='product_Product' WHERE product_id > 0");
        $db->execute("UPDATE mm_media SET owner_id=category_id, owner_type='category_Category' WHERE category_id > 0");
        $db->execute("ALTER TABLE mm_media DROP product_id");
        $db->execute("ALTER TABLE mm_media DROP category_id");
        
        // Add sortorder column
        try {
            $db->execute("ALTER TABLE mm_media ADD sortorder integer not null default '0'");
        }
        catch (Exception $e) {}

        // Populate sortorder with values from media_category_id
        $db->execute("UPDATE mm_media SET sortorder = media_category_id - 1 WHERE media_category_id > 0");
        $db->execute("ALTER TABLE mm_media DROP media_category_id");
        
        // Move images
        $dao = new media_MediaDAO;
        $images = $dao->getAll();
        foreach ($images as $image) {
            $source = $GLOBALS['MM_CONFIG_OLD']['filepaths.images.' . $image->owner_abstract_type];
            $source_file = $source . '/' . $image->filename;
            $image->filename = $image->generateFilename();
            $dest_file = $image->full_path;
            if (!mkdirp(dirname($dest_file))) {
                $this->addError("Failed to create directory " . dirname($dest_file));
                return false;
            }
            if (!file_exists($source_file)) continue;
            if (!copy($source_file, $dest_file)) {
                $this->addError("Failed to copy file ($source_file) to " . dirname($dest_file));
                return false;
            }
            unlink($source_file);
            $image->save();
        }
        
        mm_setSetting('thumbnail.size', '80x80');
        
        return true;
    }
    
    function upgrade_to_0_2() {
        $db = mm_getDatabase();
        return $db->execute("ALTER TABLE mm_media ADD INDEX (owner_id, owner_type)");
    }
}
