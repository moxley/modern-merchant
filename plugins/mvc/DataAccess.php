<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package mvc
 */
class mvc_DataAccess
{
    public $model_class;
    static $table_fields;
    public $_column_defs;
    
    function fetch($mixed)
    {
        $options = array('limit' => 1);
        if (is_array($mixed)) {
            $options = array_merge($options, $mixed);
        }
        else {
            $id = (int) $mixed;
            if (!$id) throw new Exception("Bad or missing id: $mixed");
            $options['where'] = array('id=?', $id);
        }
        $matches = $this->find($options);
        if (!$matches) return null;
        return $matches[0];
    }
    
    function count($options=null)
    {
        if (!$options) $options = array();
        if (gv($options, 'sql')) {
            if (is_array($options['sql'])) {
                $params = $options['sql'];
                $sql = array_shift($params);
            }
            else {
                $params = array();
                $sql = $options['sql'];
            }
        }
        else {
            list($where_clause, $params) = $this->getWhereClause($options);
            $table = gv($options, 'table', $this->getModelTable());
            if (!$table) throw new Exception("No table name given");
            $sql = "SELECT COUNT(*) FROM $table $where_clause LIMIT 1";
        }
        $db = mm_getDatabase();
        $count = $db->getOne($sql, $params);
        return (int) $count;
    }
    
    function find($options=array())
    {
        $order_clause = $this->getOrderClause($options);
        $limit_clause = $this->getLimitClause($options);
        if (gv($options, 'sql')) {
            if (is_array($options['sql'])) {
                $params = $options['sql'];
                $sql = array_shift($params);
            }
            else {
                $params = array();
                $sql = $options['sql'];
            }
        }
        else {
            list($where_clause, $params) = $this->getWhereClause($options);
            list($joins, $join_params) = $this->getJoins($options);
            $params = array_merge($join_params, $params);

            $from = gv($options, 'from', $this->getModelTable());

            $select = gv($options, 'select', "*");
        
            $sql = "SELECT $select FROM $from $joins $where_clause";
        }

        $sql = "$sql $order_clause $limit_clause";
        $db = mm_getDatabase();
        $rows = $db->getAllAssoc($sql, $params);
        $found = array();
        foreach ($rows as $row) {
            $found[] = $this->parseRow($row, $options);
        }
        return $found;
    }
    
    function findBySql($sql, $options=array()) {
        $options['sql'] = $sql;
        return $this->find($options);
    }
    
    function afterValidate()
    {
        // Empty
    }
    
    function save($obj)
    {
        if (isset($obj->id)) {
            return $this->update($obj);
        }
        else {
            return $this->add($obj);
        }
    }
    
    function add($obj)
    {
        $obj->beforeAdd();
        $obj->beforeSave();
        $obj->validate();
        $obj->validateForSave();
        $obj->validateForAdd();
        if ($obj->errors) return false;
        $obj->afterValidate();
        
        $table = $this->getModelTable();
        $columns = array();
        $placeholders = array();
        foreach ($this->getRegularColumns() as $col) {
            $placeholders[] = '?';
            $columns[] = "`$col`";
        }
        $sql = "INSERT INTO $table (" . implode(',', $columns) . ") " . 
            "VALUES (" . implode(',', $placeholders) . ")";
        
        $db = mm_getDatabase();
        $db->execute($sql, $this->rowValues($obj));
        $obj->id = $db->lastInsertId();
        $obj->afterAdd();
        $obj->afterSave();
        return true;
    }

    function update($obj)
    {
        $obj->beforeUpdate();
        $obj->beforeSave();
        $obj->validate();
        $obj->validateForSave();
        $obj->validateForUpdate();
        if ($obj->errors) return false;
        $obj->afterValidate();
        
        $row = $this->rowValues($obj);
        $assignments = array();
        foreach ($this->getRegularColumns() as $col) {
            $assignments[] = "`$col` = ?";
        }
        $assignments = implode(', ', $assignments);
        $table = $this->getModelTable();
        $sql = "UPDATE $table SET $assignments WHERE id=?";
        $db = mm_getDatabase();
        $values = $this->rowValues($obj);
        $values[] = $obj->id;
        $db->execute($sql, $values);
        $obj->afterUpdate();
        $obj->afterSave();
        return true;
    }
    
    function delete($obj)
    {
        $obj->beforeDelete();
        $table = $this->getModelTable();
        $sql = "DELETE FROM $table WHERE id=?";
        $db = mm_getDatabase();
        $db->execute($sql, array($obj->id));
        $obj->afterDelete();
        return true;
    }
    
    function deleteAll()
    {
        $table = $this->getModelTable();
        $sql = "DELETE FROM $table";
        $db = mm_getDatabase();
        $db->execute($sql);
    }
    
    function rowValues($obj)
    {
        $values = array();
        foreach ($this->getColumnDefs() as $def) {
            $col = $def->name;
            if (endswith($col, '_on') || endswith($col, '_date')) {
                $values[$col] = $obj->$col ? date('Y-m-d H:i:s', $obj->$col) : NULL;
            }
            else if ($def->type == 'boolean') {
                if ($def->boolean_type == 'TF') {
                    $values[$col] = $obj->$col ? 'T' : 'F';
                }
                else {
                    $values[$col] = $obj->$col ? 1 : 0;
                }
            }
            else {
                $values[$col] = $obj->$col;
            }
        }
        return $values;
    }

    function parseRow($row, $options=array())
    {
        $table = $this->getModelTable();
        $class = $this->getModelClass();
        $model = gv($options, 'object');
        if (!$model) $model = mvc_Model::instance($class);
        foreach ($row as $k=>$v) {
            $def = $this->findColumnDef($k);
            if (!$def) {
                $model->$k = $v;
            }
            else if ($v === null) {
                $model->$k = $v;
            }
            else if ($def->type == 'datetime' || $def->type == 'date') {
                $model->$k = strtotime($v);
            }
            else if ($def->type == 'boolean') {
                $model->$k = $v ? true : false;
            }
            else if ($def->type == 'integer') {
                $model->$k = (int) $v;
            }
            else {
                $model->$k = $v;
            }
        }
        return $model;
    }
    
    function getModelClass()
    {
        if ($this->model_class) {
            return $this->model_class;
        }
        else {
            $dao_class = get_class($this);
            if (preg_match('/^(.*)DAO$/', $dao_class, $match)) {
                return $match[1];
            }
            else {
                return null;
            }
        }
    }
    
    /**
     * Get the short name for the model.
     *
     * Examples:
     *   product_Product: product
     *   blast_List: list
     */
    function getModelName() {
        $class = $this->getModelClass();
        $reflect = new ReflectionClass($class);
        if ($reflect->hasConstant("MODEL_NAME")) {
            return $reflect->getConstant("MODEL_NAME");
        }
        else {
            $parts = explode('_', $class);
            $model_name = array_pop($parts);
            $model_name[0] = strtolower($model_name[0]);
            return $model_name;
        }
    }
    
    /**
     * @deprecated  Use <code>getTable()</code> instead.
     */
    function getModelTable()
    {
        return $this->getTable();
    }
    
    /**
     * Get the database table name for the model.
     */
    function getTable()
    {
        $class = $this->getModelClass();
        $reflect = new ReflectionClass($class);
        if ($reflect->hasConstant("TABLE")) {
            return $reflect->getConstant("TABLE");
        }
        else {
            $model_name = $this->getModelName();
            if ($model_name) {
                return "mm_" . underscore($model_name);
            }
            else {
                return null;
            }
        }
    }
    
    function getOrderClause($options) {
        if ($options && gv($options, 'order')) {
            return "ORDER BY " . $options['order'];
        }
        else {
            return "";
        }
    }
    
    function getLimitClause($options) {
        if ($options && gv($options, 'limit')) {
            $offset = (int) gv($options, 'offset', 0);
            $limit = (int) $options['limit'];
            return "LIMIT $offset, $limit";
        }
        else {
            return "";
        }
    }
    
    function getWhereClause($options) {
        if ($options && $where = gv($options, 'where')) {
            if (is_array($where)) {
                $sql = array_shift($where);
                $params = $where;
                $where = $sql;
            }
            else {
                $params = array();
            }
            return array("WHERE $where", $params);
        }
        else {
            return array("", array());
        }
    }
    
    function getJoins($options) {
        if ($joins = gv($options, 'joins')) {
            if (!is_array($joins)) {
                return array($joins, array());
            }
            else {
                $sql = array_shift($joins);
                $params = $joins;
                return array($sql, $params);
            }
        }
        else {
            return array("", array());
        }
    }

    function getRegularColumns() {
        $defs = $this->getColumnDefs();
        $cols = array();
        foreach ($defs as $def) {
            $cols[] = $def->name;
        }
        return $cols;
    }
    
    function getColumnDefs() {
        if (!isset($this->_column_defs)) {
            if (!isset($this->regular_columns)) {
                $this->_column_defs = array();
                foreach ($this->getColumnDefsFromDatabase() as $def) {
                    $this->_column_defs[] = (object) $def;
                }
            }
            else {
                $this->_column_defs = array();
                foreach ($this->regular_columns as $def) {
                    if (is_array($def)) {
                        $name = array_shift($def);
                        $type = gv($def, 'type', 'string');
                    }
                    else {
                        $name = $def;
                        $type = 'string';
                    }
                    $this->_column_defs[] = (object) array('name' => $name, 'type' => $type);
                }
            }
        }
        return $this->_column_defs;
    }
    
    function getColumnDefsFromDatabase() {
        $db = mm_getDatabase();
        $table = $this->getModelTable();

        if (isset(self::$table_fields) && gv(self::$table_fields, $table)) {
            $fields = self::$table_fields[$table];
        }
        else {
            $fields = $db->getAllAssoc("SHOW FIELDS FROM $table");
            if (!isset(self::$table_fields)) self::$table_fields = array();
            self::$table_fields[$table] = $fields;
        }
        $defs = array();
        foreach ($fields as $def) {
            if ($def['Key'] == 'PRI') continue;
            $defs[] = $this->parseDatabaseFieldDef($def);
        }
        return $defs;
    }
    
    function parseDatabaseFieldDef($def) {
        $parsed_def = array();
        $parsed_def['name'] = $def['Field'];
        $parsed_def['type'] = null;
        if (preg_match('/^enum/', $def['Type'])) {
            if ($def['Type'] == "enum('T','F')") {
                $parsed_def['type'] = 'boolean';
                $parsed_def['boolean_type'] = 'TF';
            }
            else {
                $parsed_def['type'] = 'string';
            }
        }
        else {
            preg_match('/^([a-z]+)(\(\d+(,(\d+))?\))?$/', $def['Type'], $match);
            switch ($match[1]) {
            case 'int':
                $parsed_def['type'] = 'integer';
                break;
            case 'tinyint':
                $parsed_def['type'] = 'boolean';
                $parsed_def['boolean_type'] = '01';
                break;
            case 'datetime':
                $parsed_def['type'] = 'datetime';
                break;
            case 'date':
                $parsed_def['type'] = 'date';
                break;
            case 'varchar':
                $parsed_def['type'] = 'string';
                break;
            case 'text':
                $parsed_def['type'] = 'text';
                break;
            case 'decimal':
                $parsed_def['type'] = 'decimal';
                break;
            case 'float':
                $parsed_def['type'] = 'float';
                break;
            }
        }
        return $parsed_def;
    }
    
    function findColumnDef($name) {
        if ($name == 'id') {
            return (object) array(
                'name' => 'id',
                'type' => 'integer'
            );
        }
        $defs = $this->getColumnDefs();
        foreach ($defs as $def) {
            if ($def->name == $name) return $def;
        }
        return null;
    }
}
