<?php
/**
 * @package db
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class db_Indexes extends mvc_Model
{
    private $_indexes;
    
    function __construct($table)
    {
        $this->table = $table;
    }
    
    function dropForColumn($col)
    {
        $db = mm_getDatabase();
        foreach ($this->indexes as $index) {
            if ($index->column_name == $col) {
                $sql = "ALTER TABLE {$this->table} DROP INDEX {$index->name}";
                $db->execute($sql);
                return 1;
            }
        }
        return 0;
    }
    
    function getIndexes()
    {
        if (!isset($this->_indexes)) {
            $db = mm_getDatabase();
            $rows = $db->getAllAssoc("show index from {$this->table}");
            $this->_indexes = array();
            foreach ($rows as $row) {
                $this->_indexes[] = new db_Index($row);
            }
        }
        return $this->_indexes;
    }
}
