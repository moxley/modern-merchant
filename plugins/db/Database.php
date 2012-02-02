<?php
/**
 * @package database
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Database abstraction object.
 * @package database
 */
class db_Database
{
    public $native_conn = null;
    public $type = 'mysql';
    
    public function __construct($config=array())
    {
        $this->connect($config);
    }
    
    public function throwUnsupportedType()
    {
        throw new Exception(sprintf("Unsupported database type, '%s'", $this->type));
    }

    protected function connect($config=array())
    {
        if (!$config) {
            $config_all = mm_dotSepToAssoc($GLOBALS['MM_CONFIG']);
            if (mm_getConfigValue('environment') == 'test') {
                $config = $config_all['database']['test'];
            }
            else {
                $config = $config_all['database'];
            }
        }
        $this->type = $config['type'];
        $this->name = $config['name'];
        if ($this->type == 'mysql') {
            $host_port = $config['host'] . ':' . $config['port'];
            $this->native_conn = @mysql_connect(
                $host_port,
                $config['user'],
                $config['password']
            );
            if (!$this->native_conn) {
                throw new Exception("Failed to connect to mysql database server: " . mysql_error());
            }
            if (!mysql_select_db($config['name'])) {
                throw new Exception("Failed to select database '$config[name]'");
            }
        }
        else if ($this->type == 'mysqli') {
            $this->native_conn = @(new mysqli(
                $config['host'],
                $config['user'],
                $config['password'],
                $config['name'],
                $config['port']
            ));
            if (mysqli_connect_errno()) {
                throw new Exception("Failed to native_connect to database server: " . mysqli_connect_error());
            }
        }
        else {
            $this->throwUnsupportedType();
        }
    }
    
    /**
     * Prepare an SQL statement or query.
     *
     * @param string $sql
     * @return db_PreparedStatement
     */
    public function prepare($sql)
    {
        return new db_PreparedStatement($this, $sql);
    }
    
    /**
     * Perform a query.
     * 
     * @param string $sql The SQL query
     * @param array $params The values to substitute for the placeholders.
     * @return db_PreparedStatement
     * @throws db_DatabaseException
     */
    public function query($sql, $params=null)
    {
        $stmt = new db_PreparedStatement($this, $sql);
        $rs = $stmt->query($params);
        return $rs;
    }
    
    /**
     * Execute an SQL statement.
     *
     * @param string $sql The SQL statement
     * @param array $params The values to substitute for the placeholders.
     * @return db_PreparedStatement
     * @throws db_DatabaseException
     */
    public function execute($sql, $params=null)
    {
        $stmt = new db_PreparedStatement($this, $sql);
        $result = $stmt->execute($params);
        return $result;
    }
    
    /**
     * Get one record as an associative array.
     *
     * @param string $sql The SQL query
     * @param array $params Values to pass into the query's placeholders
     * @return array A record as an associative array
     * @todo Rename this as fetchAssoc()
     */
    public function getOneAssoc($sql, $params=null)
    {
        $stmt = new db_PreparedStatement($this, $sql);
        $rs = $stmt->query($params);
        $assoc = $rs->fetchAssoc();
        $rs->free();
        return $assoc;
    }
    
    /**
     * Get back one column from a query's result set.
     *
     * @param string $sql The SQL query
     * @param array $params Values to pass into the query's placeholders
     * @return array The column
     */
    public function getCol($sql, $params=null)
    {
        $stmt = new db_PreparedStatement($this, $sql);
        $rs = $stmt->query($params);
        $column = array();
        while ($row = $rs->fetchRow()) {
            $column[] = $row[0];
        }
        $rs->free();
        return $column;
    }
    
    /**
     * Get a result set as an array of objects.
     *
     * @param string $sql The SQL query
     * @param array $params Values to pass into the query's placeholders
     * @return array An array of objects
     */
    public function getAllObject($sql, $params=null)
    {
        $all_assoc = $this->getAllAssoc($sql, $params=null);
        $all_objects = array();
        foreach ($all_assoc as $assoc) {
            $all_objects[] = (object) $assoc;
        }
        return $all_objects;
    }
    
    /**
     * Get a result set as an array of associative arrays.
     *
     * @param string $sql The SQL query
     * @param array $params Values to pass into the query's placeholders
     * @return array An array of associative arrays
     */
    public function getAllAssoc($sql, $params=null)
    {
        $stmt = new db_PreparedStatement($this, $sql);
        $rs = $stmt->query($params);
        $all = array();
        while ($row = $rs->fetchAssoc()) {
            $all[] = $row;
        }
        $rs->free();
        return $all;
    }
    
    /**
     * Get the first column of the first row of a query.
     *
     * @param string $sql The SQL query
     * @param array $params Values to pass into the query's placeholders
     * @return mixed The column of the first row
     */
    public function getOne($sql, $params=null)
    {
        $stmt = new db_PreparedStatement($this, $sql);
        $rs = $stmt->query($params);
        if (!$rs) return null;
        $row = $rs->fetchRow();
        $rs->free();
        return $row[0];
    }
    
    public function limitQuery($sql, $offset, $limit, $params=null)
    {
        $stmt = new db_PreparedStatement($this, $sql);
        $sql = $sql . " LIMIT " . intval($offset) . "," . intval($limit);
        $rs = $stmt->query($params);
        return $rs;
    }
    
    public function lastInsertId()
    {
        if ($this->type == 'mysql') {
            return mysql_insert_id($this->native_conn);
        }
        else if ($this->type == 'mysqli') {
            return $this->native_conn->insert_id;
        }
        else {
            $this->throwUnsupportedType();
        }
    }
    
    public function affectedRows()
    {
        if ($this->type == 'mysql') {
            return mysql_affected_rows($this->native_conn);
        }
        else if ($this->type == 'mysqli') {
            return $this->native_conn->affected_rows;
        }
        else {
            $this->throwUnsupportedType();
        }
    }
        
    public function getFormatter()
    {
        return new db_DatabaseFormatter($this);
    }
}

class db_PreparedStatement
{
    private $sql;
    public $params;
    
    public $db;
    public $native_result;
    public $start;
    public $time;
    
    public function __construct($db, $sql)
    {
        $this->db = $db;
        $this->sql = $sql;
        $this->params = array();
    }
    
    public function query($params=null)
    {
        if ($params !== null) $this->params = $params;
        $this->params = (array) $this->params;
        if ($this->db->type == 'mysql') {
            $sql = $this->interpolate($this->sql, $this->params);
            $this->start = microtime(true);
            $this->native_result = @mysql_query($sql, $this->db->native_conn);
            $this->time = microtime(true) - $this->start;
            if (mm_getConfigValue('log.sql')) $this->logSql($sql);
            if (!$this->native_result) {
                throw new db_DatabaseException("Failed on query: " . mysql_error($this->db->native_conn));
            }
            return new db_ResultSet($this);
        }
        if ($this->db->type == 'mysqli') {
            $sql = $this->interpolate($this->sql, $this->params);
            $this->start = microtime(true);
            $this->native_result = @$this->db->native_conn->query($sql);
            $this->time = microtime(true) - $this->start;
            if (mm_getConfigValue('log.sql')) $this->logSql($sql);
            if (!$this->native_result) {
                throw new db_DatabaseException("Failed on query: " . $this->db->native_conn->error);
            }
            return new db_ResultSet($this);
        }
        else {
            $this->db->throwUnsupportedType();
        }
    }

    /**
     * @param array $params The values to substitute for the placeholders.
     * @return db_PreparedStatement
     * @throws db_DatabaseException
     */
    public function execute($params=null)
    {
        if ($params !== null) $this->params = $params;
        $this->params = (array) $this->params;
        $parsed_sql = $this->sql;
        if ($this->db->type == 'mysql') {
            $sql = $this->interpolate($this->sql, $this->params);
            $this->start = microtime(true);
            $this->native_result = @mysql_query($sql, $this->db->native_conn);
            $this->time = microtime(true) - $this->start;
            if (mm_getConfigValue('log.sql')) $this->logSql($sql);
            if (!$this->native_result) {
                throw new db_DatabaseException(
                    "Failed on sql statement ($sql): " . mysql_error($this->db->native_conn));
            }
            return $this;
        }
        else if ($this->db->type == 'mysqli') {
            $sql = $this->interpolate($this->sql, $this->params);
            $this->start = microtime(true);
            $this->native_result = $this->db->native_conn->query($sql);
            $this->time = microtime(true) - $this->start;
            if (mm_getConfigValue('log.sql')) $this->logSql($sql);
            if (!$this->native_result) {
                throw new db_DatabaseException(
                    "Failed on sql statement ($sql): " . $this->db->native_conn->error);
            }
            return $this;
        }
        else {
            $this->db->throwUnsupportedType();
        }
    }
    
    protected function logSql($sql)
    {
        mm_log($this->db->type . ": " . $sql . "\nSQL execution time: " . $this->time . " seconds");
    }
    
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
    
    public function numRows()
    {
        if ($this->db->type == 'mysql') {
            return mysql_num_rows($this->native_result);
        }
        else if ($this->db->type == 'mysqli') {
            return $this->native_result->num_rows;
        }
        else {
            $this->db->throwUnsupportedType();
        }
    }
    
    public function free()
    {
        if ($this->db->type == 'mysql') {
            if (!$this->native_result) {
                throw new Exception("free(): Illegal operation: A result is required");
            }
            if (!mysql_free_result($this->native_result)) {
                throw new Exception("Not a valid result");
            }
        }
        else if ($this->db->type == 'mysqli') {
            $this->native_result->free();
        }
        else {
            $this->db->throwUnsupportedType();
        }
    }
    
    public function getChar()
    {
        return $this->sql[$this->parse_i];
    }
    
    public function skipChar()
    {
        return $this->sql[$this->parse_i++];
    }
    
    public function skipQuotedString()
    {
        $str = $quote_char = $this->skipChar();
        while ($this->parse_i < strlen($this->sql)) {
            $c = $this->getChar();
            if ($c == $quote_char) {
                $str .= $this->skipChar();
                return $str;
            }
            else if ($c == '\\') {
                $str .= $this->skipChar();
                $str .= $this->skipChar();
            }
            else {
                $str .= $this->skipChar();
            }
        }
        return $str;
    }
    
    public function escape($v)
    {
        if ($this->db->type == 'mysql') {
            return mysql_real_escape_string($v);
        }
        else if ($this->db->type == 'mysqli') {
            return $this->db->native_conn->real_escape_string($v);
        }
        else {
            $this->throwUnsupportedType();
        }
    }
    
    public function interpolate()
    {
        $new_sql = '';
        if (!is_array($this->params)) {
            throw new Exception("interpolate(\$sql, \$params): \$params should be an array");
        }
        $tmp_params = $this->params;
        for ($this->parse_i=0; $this->parse_i < strlen($this->sql);) {
            $c = $this->getChar();
            if ($c == "'" || $c == '"') {
                $new_sql .= $this->skipQuotedString();
            }
            else if ($c == '?') {
                $v = array_shift($tmp_params);
                if (!is_array($v)) {
                    $new_sql .= $this->formatSingle($v);
                }
                else {
                    if (array_key_exists('type', $v)) {
                        $type = $v['type'];
                        if ($type == 'datetime') {
                            $date = array_shift($v);
                            $new_sql .= $this->db->getFormatter()->fDate($date);
                        }
                        else {
                            $new_sql .= $this->formatSingle(null);
                        }
                    }
                    else {
                        foreach (array_values($v) as $i=>$value) {
                            if ($i > 0) $new_sql .= ',';
                            $new_sql .= $this->formatSingle($value);
                        }
                    }
                }
                $this->skipChar();
            }
            else {
                $new_sql .= $this->skipChar();
            }
        }
        
        return $new_sql;
    }
    
    function formatSingle($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        else if (is_null($value)) {
            return "NULL";
        }
        else {
            return "'" . $this->escape($value) . "'";
        }
    }
}

class db_ResultSet
{
    
    private $statement;
    
    public function __construct($statement)
    {
        $this->statement = $statement;
    }
    
    public function fetchAssoc()
    {
        if ($this->statement->db->type == 'mysql') {
            return mysql_fetch_assoc($this->statement->native_result);
        }
        else if ($this->statement->db->type == 'mysqli') {
            return $this->statement->native_result->fetch_assoc();
        }
        else {
          $this->statement->db->throwUnsupportedType();
        }
    }
    
    public function fetchArray()
    {
        if ($this->statement->db->type == 'mysql') {
            return mysql_fetch_array($this->statement->native_result);
        }
        else if ($this->statement->db->type == 'mysqli') {
            return $this->statement->native_result->fetch_array();
        }
        else {
            $this->statement->db->throwUnsupportedType();
        }
    }
    
    public function fetchRow()
    {
        return $this->fetchArray();
    }
    
    public function free()
    {
        return $this->statement->free();
    }
    
    public function numRows()
    {
        return $this->statement->numRows();
    }
}
