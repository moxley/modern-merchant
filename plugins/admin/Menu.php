<?php
/**
 * @package admin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class admin_Menu extends mvc_MenuItem
{
    public function toJS()
    {
        return admin_Menu::itemToJS($this);
    }
    
    public static function itemToJS($item)
    {
        $item = self::normalizeItem($item);
        $args = array();
        $args[] = "'{$item->image}'";
        $args[] = "'{$item->label}'";
        $args[] = "'{$item->url}'";
        $args[] = "null";
        $args[] = "'{$item->label}'";

        $child_args = array();
        foreach ($item->children as $child) {
            $child = self::normalizeItem($child);
            $child_args[] = "\t" . self::itemToJS($child);
        }
        if ($child_args) $args[] = "\n" . implode(",\n", $child_args);
        
        $js = '[' . implode(',', $args) . "]";
        return $js;
    }

    public static function normalizeItem($item=null)
    {
        if (is_object($item)) {
            return $item;
        }
        else {
            return new admin_Menu($item);
        }
    }
}
