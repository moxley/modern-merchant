<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package sess
 */
class sess_SessionDAO extends mvc_DataAccess
{
    public $select_columns = array("id", "sid", "creation_date", "modify_date", "data");
    private $data_prefix = "USERSESS|";
    
    function add($sess)
    {
        if (!$sess->sid) {
            throw new mm_IllegalArgumentException("Missing SID");
        }
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $data = $this->formatData($sess->data);
        $sql = sprintf("INSERT INTO mm_session (" .
                $this->getColumnList() .
                ") values (" .
                "%s, %s, %s, %s, %s" .
                ")",
                'NULL',
                $fmt->fString($sess->sid),
                $fmt->fDate($sess->creation_date),
                $fmt->fDate($sess->modify_date),
                $fmt->fString($data));
        $dbh->query($sql);
        $id = $dbh->lastInsertId();
        $sess->id = (int) $id;
        return $sess;
    }
    
    function formatData($data)
    {
        return mm_encodeSession($data);
    }
    
    function fetch($id)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "SELECT " . $this->getColumnList() .
            " FROM mm_session WHERE id=" .
            $fmt->fInt($id);
        return $this->fetchByQuery($sql);
    }
    
    function fetchBySid($sid)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "SELECT " . $this->getColumnList() .
            " FROM mm_session WHERE sid=" .
            $fmt->fString($sid);
        return $this->fetchByQuery($sql);
    }
    
    function fetchByQuery($sql)
    {
        $row = mm_getDatabase()->getOneAssoc($sql);
        if (!$row) return null;
        $sess = $this->parseRow($row);
        return $sess;
    }
    
    function parseRow($row, $options=array())
    {
        $sess = new sess_Session;
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sess->id = $fmt->pInt($row['id']);
        $sess->sid = gv($row, 'sid');
        $sess->creation_date = $fmt->pDate($row['creation_date']);
        $sess->modify_date = $fmt->pDate($row['modify_date']);
        $sess->data = $this->parseData($row['data']);
        return $sess;
    }
    
    function parseData($s_data)
    {
        return mm_decodeSession($s_data);
    }
    
    function save($sess)
    {
        if ($sess->id) {
            return $this->update($sess);
        }
        else {
            return $this->add($sess);
        }
    }
    
    function update($sess)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "UPDATE mm_session set" .
                " modify_date=" . $fmt->fDate($sess->modify_date) .
                ",data=" . $fmt->fString($this->formatData($sess->data)) .
                " where id=" . $fmt->fInt($sess->id);
        $dbh->query($sql);
    }
    
    function deleteBySid($sid)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "DELETE FROM mm_session WHERE sid=" . $fmt->fString($sid);
        $dbh->query($sql);
    }
    
    function deleteExpired($maxlifetime)
    {
        $now = time();
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "DELETE FROM mm_session WHERE modify_date < " .
            $fmt->fDate($now - $maxlifetime);
        $dbh->query($sql);
    }
    
    function deleteAll()
    {
        $dbh = mm_getDatabase();
        $dbh->query('delete from mm_session');
    }
    
    function getColumnList($alias=null)
    {
        $pre = $alias ? $alias . '.' : '';
        $post = $alias ? ' ' . $alias : '';
        return $pre . implode(', ' . $pre , $this->select_columns);
    }
}
