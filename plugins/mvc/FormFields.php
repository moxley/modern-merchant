<?php

class mvc_FormFields implements ArrayAccess, Iterator
{
    public $_fields;
    
    function __construct($fields=array()) {
        if (!empty($fields) && !isset($fields[0]) || !is_array($fields)) {
            throw new Exception("Incorrect argument for mvc_FormFields::__construct(\$fields). Needed an array of arrays.");
        }
        $this->_fields = $fields;
    }
    
    function matchNames($names) {
        $fields = array();
        foreach ($names as $name) {
            $field = $this->offsetGet($name);
            if ($field) $fields[] = $field;
        }
        return new mvc_FormFields($fields);
    }
    
    /* ArrayAccess methods */
    
    function offsetUnset($user_index) {
        $index = $this->realIndex($user_index);
        $new_fields = array();
        foreach ($this->_fields as $i=>$f) {
            if ($index != $i) $new_fields[] = $f;
        }
        $this->_fields = $new_fields;
    }
    
    function realIndex($index) {
        if (is_numeric($index)) {
            return $index;
        }
        foreach ($this->_fields as $i=>$f) {
            if ($f['name'] == $index) return $i;
        }
        return count($this->_fields);
    }
    
    function offsetSet($user_index, $value) {
        $index = $this->realIndex($user_index);
        if (is_string($user_index)) {
            $value['name'] = $user_index;
        }
        $this->_fields[$index] = $value;
    }
    
    function offsetGet($index) {
        $index = $this->realIndex($index);
        return gv($this->_fields, $index);
    }
    
    function &itemAtName($name) {
        $index = $this->realIndex($name);
        return $this->_fields[$index];
    }
    
    function &itemAtIndex($index) {
        return $this->_fields[$index];
    }
    
    function offsetExists($index) {
        $index = $this->realIndex($index);
        return array_key_exists($index, $this->_fields);
    }
    
    /* Iterator methods */
    
    function rewind() {
        reset($this->_fields);
    }
    
    function current() {
        return current($this->_fields);
    }
    
    function key() {
        return key($this->_fields);
    }
    
    function next() {
        return next($this->_fields);
    }
    
    function valid() {
        return $this->current() !== false;
    }
}
