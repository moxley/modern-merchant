<?php
/**
 * @package access
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package access
 */
class access_Access extends mvc_Model
{
    public $id;
    public $name;
    public $title;
    
    static function fetchByName($name)
    {
        $dao = new access_AccessDAO;
        return $dao->fetch(array('where' => array('name=?', $name)));
    }
}
