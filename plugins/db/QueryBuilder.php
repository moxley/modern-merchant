<?php
/**
 * @package database
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

class db_QueryBuilder
{
    var $tables = array();
    var $columns = array();
    var $ands = array();
    var $legal_columns = array();
    var $default_limit = 1000;
    var $limit = 0;
    var $offset = 0;
    var $order_by = array();
    
    /**
     * Example: <code>addTable('Product');</code>
     */
    function addTable($table)
    {
        $this->tables[] = $table;
    }

    /**
     * Example: <code>addColumn('SKU');</code>
     */        
    function addColumn($column)
    {
        $this->columns[] = $column;
    }
    
    /**
     * Example: <code>addAndCondition('categorylink.CategoryID=category.CategoryID');</code>
     */
    function addAndCondition($condition)
    {
        $this->ands[] = $condition;
    }
    
    /**
     * Example: <code>addorder_by('item.Name')</code>
     */
    function addOrderBy($order_by, $direction) {
        if ($direction == 0) return;
        if ($direction < 0) $direction = -1;
        if ($direction > 0) $direction = 1;
        $this->order_by[] = array($order_by, $direction);
    }
    
    /**
     * Example: <code>legalizeColumn('SKU');</code>
     */
    function legalizeColumn($column)
    {
        $this->legal_columns[] = $column;
    }
    
    /**
     * Example: <code>$isValidColumn = addCheckedAndCondition($column, '=', "'ABC001'");</code>
     */
    function addCheckedAndCondition($column, $operator, $rval)
    {
        if (!in_array($column, $this->legal_columns)) return false;
        $this->addAndCondition($column . $operator . $rval);
        return true;
    }
    
    function setLimit($limit)
    {
        $this->limit = intval($limit);
    }
    
    function setOffset($offset)
    {
        $this->offset = $offset;
    }
    
    /**
     * Example: <code>$sql = $builder->build();</code>
     */
    function build()
    {
        $sql = "SELECT ";
        $sql .= implode(', ', $this->columns);
        $sql .= " FROM ";
        $sql .= implode(', ', $this->tables);
        if ($this->ands)
        {
            $sql .= " WHERE ";
            $sql .= implode(' AND ', $this->ands);
        }
        if ($this->order_by)
        {
            $sql .= ' ORDER BY';
            $count = 0;
            foreach ($this->order_by as $array)
            {
                list($order_by, $direction) = $array;
                if ($count > 0) $sql .= ',';
                $sql .= ' ' . $order_by;
                if ($direction < 0) $sql .= ' DESC';
                $count++;
            }
        }
        if ($this->limit || $this->offset)
        {
            $sql .= ' LIMIT ';
            if ($this->offset)
            {
                $sql .= intval($this->offset);
                $sql .= ',';
            }
            $limit = intval($this->limit);
            $sql .= ($limit ? $limit : $this->default_limit);
        }
        
        return $sql;
    }
    
    function buildCount()
    {
        $sql = "SELECT count(*) FROM ";
        $sql .= implode(', ', $this->tables);
        if ($this->ands)
        {
            $sql .= " WHERE ";
            $sql .= implode(' AND ', $this->ands);
        }
        
        return $sql;
    }
}
