<?php
/**
 * @package test
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class test_QueryBuilder
{
    function buildSqlInsert($row)
    {
        $columns = '';
        $values = '';
        $i = 0;
        foreach ($row as $column => $value) {
            if ($i > 0) {
                $columns .= ',';
                $values .= ',';
            }
            $columns .= $column;
            if ($value === null) {
                $values .= 'NULL';
            }
            else if (is_bool($value)) {
                $values .= ($value ? "'T'" : "'F'");
            }
            else if (!is_string($value) && is_numeric($value)) {
                $values .= $value;
            }
            else {
                $values .= dq($value);
            }
            $i++;
        }
        $sql = "INSERT INTO mm_order ($columns) VALUES ($values)";
        return $sql;
    }
}
