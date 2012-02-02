<?php
/**
 * @package database
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class db_DatabaseFormatter
{
    public function __construct($db) {
        $this->db = $db;
    }
    
    function fDate($time)
    {
        if (!$time) return 'NULL';
        return 'from_unixtime(' . intval($time) . ')';
    }
    
    function pDate($val)
    {
        if ($val === null) return null;
        list($date, $time) = explode(' ', $val);
        list($year, $month, $day) = explode('-', $date);
        list($hour, $minute, $second) = explode(':', $time);
        return (double) mktime($hour, $minute, $second, $month, $day, $year);
    }
    
    function fBool($bool)
    {
        return $bool ? "'T'" : "'F'";
    }
    
    function fBoolTF($bool)
    {
        return $bool ? "'T'" : "'F'";
    }
    
    function pBoolTF($value)
    {
        return $value && $value !== 'F';
    }
    
    function fBool01($bool)
    {
        return $bool ? "1" : "0";
    }
    
    function pBool01($value)
    {
        return $value ? true : false;
    }
        
    function fMoney($money)
    {
        if ($money == null) return 'NULL';
        return sprintf('%0.2f', $money);
    }
    
    function pMoney($value)
    {
        if ($value == null) return null;
        return (string) $value;
    }
    
    function fString($string)
    {
        if ($string === null) return 'NULL';
        return "'" . addslashes($string) . "'";
    }
    
    function fSubString($string)
    {
        if ($this->db->type == 'mysql') {
            return mysql_real_escape_string($string, $this->db->native_conn);
        }
        else if ($this->db->type == 'mysqli') {
            return mysqli_real_escape_string($this->db->native_conn, $string);
        }
        else {
            throw new Exception("Unrecognized database type '{$this->db->type}'");
        }
    }
    
    function fInt($int)
    {
        if ($int === null) return 'NULL';
        return (int) $int;
    }
    
    function fIntList($ints)
    {
        return implode(',', array_map(
            create_function('$id', 'return (int) $id;'),
            $ints));
    }
    
    function pInt($val)
    {
        if ($val === null) return null;
        return (int) $val;
    }
    
    function fFloat($float)
    {
        return (float) $float;
    }
    
    function pFloat($val)
    {
        if ($val === null) return null;
        return (float) $val;
    }
}
