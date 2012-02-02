<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Content data access.
 * @package content
 */
class content_ContentDAO extends mvc_DataAccess
{
    function getList($offset, $limit)
    {
        $dbh = mm_getDatabase();
        $res = $dbh->limitQuery('SELECT * FROM mm_content ORDER BY name', $offset, $limit);
        $records = array();
        while ( ($record = $res->fetchAssoc()) != null ) {
            $items[] = $this->parseRow($record);
        }
        $res->free();
        
        return $items;
    }
    
    function parseRow($row, $options=array())
    {
        $content = new content_Content;
        $content->id = $row['id'];
        $content->name = $row['name'];
        $content->description = $row['description'];
        $content->title = $row['title'];
        $content->body = $row['body'];
        $content->type = $row['type'];
        $content->sortorder = intval($row['sortorder']);
        return $content;
    }
    
    function add($content)
    {
        $dbh = mm_getDatabase();
        $c = $content;
        $values = array($c->name, $c->description, $c->type, $c->title, $c->body);
        $sql = 'insert into mm_content (name, description, type, title, body) values (?, ?, ?, ?, ?)';
        $stmt = $dbh->execute($sql, $values);
        $content->id = $dbh->lastInsertId();
        return $content;
    }
    
    function update($content)
    {
        $dbh = mm_getDatabase();
        $id = intval($content->id);
        if (!$id) {
            return new mm_IllegalArgumentException("Blank \$content->id");
        }
        $sql = 'update mm_content set name=?, description=?, type=?, title=?, body=? where id=?';
        $c = $content;
        $values = array($c->name, $c->description, $c->type, $c->title, $c->body, $c->id);
        $res = $dbh->execute($sql, $values);
        return $content;
    }
        
    function fetch($id)
    {
        $dbh = mm_getDatabase();
        $record = $dbh->getOneAssoc('select * from mm_content where id=?', array($id));
        if (!$record) return null;
        return new content_Content($record);
    }

    function delete($spec)
    {
        if (is_numeric($spec)) {
            $dbh = mm_getDatabase();
            $content = $this->fetch($spec);
            $dbh->execute('delete from mm_content where id=?', array($spec));
            return true;
        }
        else {
            return parent::delete($spec);
        }
    }
    
    function fetchByName($name)
    {
        $dbh = mm_getDatabase();
        $record = $dbh->getOneAssoc('select * from mm_content where name=?', array($name));
        if (!$record) return null;
        return $this->parseRow($record);
    }
}
