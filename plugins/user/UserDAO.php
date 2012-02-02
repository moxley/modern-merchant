<?php
/**
 * @package user
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class user_UserDAO extends mvc_DataAccess
{
    public $regular_columns = array('username', 'password', 'first_name', 'last_name', 'email');
    
    function login($user)
    {
        if ($errors = $user->validateLogin()) {
            return false;
        }
        return $this->fetch(array(
            'where' => array('username=? AND password=?', $user->username, $user->password)));
    }
    
    function fetchByQuery($sql, $obj=null)
    {
        $dbh = mm_getDatabase();
        $row = $dbh->getOneAssoc($sql);
        if (!$row) return null;
        return $this->parseRow($row, $obj);
    }
    
    function deleteById($id)
    {
        $dbh = mm_getDatabase();
        $sql = "delete from {$this->table} where {$this->primary_key}=".intval($id);
        $dbh->query($sql);
        return true;
    }
    
    function fetchByUsername($username)
    {
        $matches = $this->find(array('where' => array("username=?", $username)));
        if (!$matches) return null;
        return $matches[0];
    }
    
    function fetchByDuplicateUsername($user)
    {
        if (!$user->id) {
            $conditions = array("username=?", $user->username);
        }
        else {
            $conditions = array("username=? AND id != ?", $user->username, $user->id);
        }
        $matches = $this->find(array('where' => $conditions));
        if (!$matches) return null;
        return $matches[0];
    }
    
    function getList($offset, $limit)
    {
        return $this->find(array('offset' => $offset, 'limit' => $limit));
    }
}
