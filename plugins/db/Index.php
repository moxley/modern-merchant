<?php
/**
 * @package db
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class db_Index extends mvc_Model
{
    function __construct($values)
    {
        parent::__construct($values);
        $this->table = gv($values, 'Table', $this->table);
        $this->non_unique = (boolean) gv($values, 'Non_unique', $this->non_unique);
        $this->name = gv($values, 'Key_name', $this->name);
        $this->seq_in_index = gv($values, 'Seq_in_index', $this->seq_in_index);
        $this->column_name = gv($values, 'Column_name', $this->column_name);
        $this->collation = gv($values, 'Collation', $this->collation);
        $this->cardinality = gv($values, 'Cardinality', $this->cardinality);
        $this->sub_part = gv($values, 'Sub_part', $this->sub_part);
        $this->packed = gv($values, 'Packed', $this->packed);
        $this->is_null = gv($values, 'Null', $this->is_null);
        $this->type = gv($values, 'Index_type', $this->type);
        $this->comment = gv($values, 'Comment', $this->comment);
    }
}
