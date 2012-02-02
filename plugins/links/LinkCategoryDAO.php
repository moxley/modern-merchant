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
class links_LinkCategoryDAO extends mvc_DataAccess {
    function parseRow($row) {
        $category = new links_LinkCategory;
        $category->id = (int) $row['id'];
        $category->name = $row['name'];
        return $category;
    }
}
