<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Helper class encapsulating a category record, its level within the heirarchy, 
 * and its parent
 */
class category_CatInHierarchy extends category_Category
{
    public $children = array();
    public $parent = null;
    public $level = 0;
    
    function add($child)
    {
        $child->setLevel($this->level + 1);
        $this->children[] = $child;
    }
    
    function setLevel($level)
    {
        $this->level = $level;
        foreach ($this->children as $child) {
            $child->setLevel($level + 1);
        }
    }
}
