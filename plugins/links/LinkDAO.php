<?php
/**
 * @package links
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package links
 */
class links_LinkDAO extends mvc_DataAccess {
    function parseRow($row) {
        $fmt = mm_getDatabase()->getFormatter();
        $link = mvc_Model::instance('links_Link');
        $link->id = (int) $row['id'];
        $link->category_id = (int) $row['category_id'];
        $link->created_on = $fmt->pDate($row['created_on']);
        $link->_url = $row['url'];
        $link->email = $row['email'];
        $link->description = $row['description'];
        $link->comment = $row['comment'];
        $link->business_name = $row['business_name'];
        $link->approved = $row['approved'] ? true : false;
        $link->reciprocal_url = $row['reciprocal_url'];
        $link->counter = (int) $row['counter'];
        return $link;
    }
    
    function attachCategories($links) {
        $category_ids = array();
        $links_by_category_id = array();
        foreach ($links as $l) {
            $category_ids[] = $l->category_id;
            if (!isset($links_by_category_id[$l->category_id])) {
                $links_by_category_id[$l->category_id] = array();
            }
            $links_by_category_id[$l->category_id][] = $l;
        }
        $dao = new links_LinkCategoryDAO;
        $categories = $dao->find(array('where' => array("id IN (?)", $category_ids)));
        foreach ($categories as $c) {
            foreach ($links_by_category_id[$c->id] as $link) {
                $link->category = $c;
            }
        }
    }

    function attachImages($links) {
        // This keeps the link model from trying to fetch an image that doesn't exist
        foreach ($links as $l) $l->_image = false;
        
        $links_lookup = array();
        foreach ($links as $l) $links_lookup[$l->id] = $l;
        $link_ids = array();
        foreach ($links as $l) $link_ids[] = $l->id;
        $dao = new media_MediaDAO;
        $images = $dao->find(array('where' => array("owner_type='links_Link' AND owner_id IN (?)", $link_ids)));
        foreach ($images as $img) {
            $link = $links_lookup[$img->owner_id];
            $link->_image = $img;
        }
    }
    
    function find($options) {
        $attach_images = array_delete_at($options, 'attach_images');
        $attach_categories = array_delete_at($options, 'attach_categories');
        $links = parent::find($options);
        if ($attach_images) $this->attachImages($links);
        if ($attach_categories) $this->attachCategories($links);
        return $links;
    }
    
}
