<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_Model {
    
    protected $_errors;
    protected $_dao;
    
    /**
     * Returns whether a read property exists, and returns its value too
     * 
     * @return mixed  If the property was found, returns an array where element '0' is the value; if the property was not found, return NULL
     */
    static function getPropertyExistsValue($object, $name, $default=null) {
        $exists_value = mvc_Model::getPhpEndorsedPropertyExistsValue($object, $name, $default);
        if ($exists_value) return $exists_value;
        
        $exists_value = mvc_Model::getBeanPropertyExistsValue($object, $name, $default);
        if ($exists_value) return $exists_value;
        
        $exists_value = mvc_Model::getRuntimePropertyExistsValue($object, $name, $default);
        if ($exists_value) return $exists_value;

        return $default;
    }
    
    static function getPhpEndorsedPropertyExistsValue($object, $name, $default=null) {
        // Try as a PHP-endorsed property
        $class = new ReflectionClass(get_class($object));
        try {
            $property = $class->getProperty($name);
            if ($property && $property->isPublic()) {
                return array($property->getValue($object));
            }
        }
        catch (ReflectionException $e) {}
        return $default;
    }
    
    static function getBeanPropertyExistsValue($object, $name, $default=null) {
        $method_name = camelize('get_' . $name);
        $class = new ReflectionClass(get_class($object));
        if (!$class->hasMethod($method_name)) return $default;
        $method = $class->getMethod($method_name);
        if (!$method->isPublic()) return $default;
        $parameters = $method->getParameters();
        if ($parameters) return $default;

        return array($object->$method_name());
    }
    
    static function getRuntimePropertyExistsValue($object, $name, $default=null) {
        $class = new ReflectionClass(get_class($object));
        $object_vars = get_object_vars($object);
        if ($class->hasProperty($name)) return null;
        if (array_key_exists($name, $object_vars)) {
            return array($object->$name);
        }
    }
    
    static function getProperties($object)
    {
        // Get properties defined in class
        $class = new ReflectionClass(get_class($object));
        $properties = $class->getProperties();
        $public_vars = array();
        $non_public_vars = array();
        $all_names = array();
        foreach ($properties as $property) {
            $name = $property->getName();
            $all_names[] = $name;
            if (!$property->isStatic()) {
                if ($property->isPublic()) {
                    $public_vars[$name] = $object->$name;
                }
                else {
                    $non_public_vars[$name] = true;
                }
            }
        }
        
        // Get properties not defined in class
        $object_vars = get_object_vars($object);
        foreach ($object_vars as $k=>$v) {
            if (!array_key_exists($k, $public_vars) && !array_key_exists($k, $non_public_vars)) {
                $public_vars[$k] = $v;
            }
        }

        // Filter out vars that start with '_'
        $vars = $public_vars;
        $public_vars = array();
        foreach ($vars as $k=>$v) {
            if ($k[0] != '_') $public_vars[$k] = $v;
        }
        
        return $public_vars;
    }
    
    function __construct($values=array()) {
        $this->_errors = array();
        if (in_array('created_on', get_class_vars(__CLASS__))) {
            $this->created_on = time();
        }
        if ($values) $this->setPropertyValues($values);
    }
    
    static function instance($model_class) {
        $extensions = mvc_Hooks::getExtensionsForModelClass($model_class);
        if ($extensions) {
            // Get the last registered extension
            $class = $extensions[count($extensions)-1];
        }
        else {
            $class = $model_class;
        }
        return new $class;
    }
    
    function getDatabase() {
        return mm_getDatabase();
    }
    
    function setPropertyValues($values) {
        if (!$values) return;
        if (!is_array($values)) {
            throw new Exception("\$values is not an array");
        }
        $properties = $this->getWriteProperties();
        foreach ($properties as $property) {
            if (array_key_exists($property, $values)) {
                $type = $this->getPropertyType($property);
                if ($values[$property] === null) {
                    $this->$property = null;
                }
                else {
                    if ($type == 'int')    $this->$property = (int) $values[$property];
                    elseif ($type == 'float') $this->$property = (float) $values[$property];
                    elseif ($type == 'string') $this->$property = (string) $values[$property];
                    elseif ($type == 'boolean') $this->$property = (boolean) $values[$property];
                    elseif ($type == 'array') $this->$property = (array) $values[$property];
                    elseif ($type == 'object') $this->$property = (object) $value[$property];
                    elseif ($type) {
                        if (is_object($values[$property])) {
                            eval("\$correct_class = \$values[\$property] instanceof $type;");
                            if (!$correct_class) throw new Exception("Incorrect class for property '$property' in " . get_class($this));
                            $this->$property = $values[$property];
                        }
                        else if (is_array($values[$property])) {
                            $this->$property = new $type;
                            if (method_exists($this->$property, 'setPropertyValues')) {
                                $this->$property->setPropertyValues($values[$property]);
                            }
                        }
                        else {
                            // Just ignore it
                        }
                    }
                    else $this->$property = $values[$property];
                }
            }
        }
    }
    
    static function isObjectType($type) {
        if (!$type) return false;
        $non_object_types = array('int', 'float', 'string', 'boolean', 'array');
        return !in_array($type, $non_object_types);
    }
    
    function getPropertyType($name) {
        $class = new ReflectionClass(get_class($this));

        $method_name = 'get' . ucfirst(camelize($name));
        $look_for_attribute = null;
        
        if ($class->hasMethod($method_name)) {
            $look_for_attribute = "return";
            $method = $class->getMethod($method_name);
            $attributes = mvc_Model::getCommentAttributes($method->getDocComment());
        }
        else if ($class->hasProperty($name)) {
            $look_for_attribute = "var";
            $property = $class->getProperty($name);
            $attributes = mvc_Model::getCommentAttributes($property->getDocComment());
        }
        else {
            return null;
        }

        foreach ($attributes as $attribute) {
            if ($attribute->name == $look_for_attribute) {
                return $attribute->value;
            }
        }

        return null;
    }
    
    function trimStrings() {
        $write_properties = $this->getWriteProperties();
        $read_properties = $this->getReadProperties();
        foreach ($write_properties as $property) {
            if (in_array($property, $read_properties)) {
                $value = $this->getPropertyValue($property);
                if (is_string($value)) {
                    $this->setPropertyValue($property, trim($value));
                }
            }
        }
    }

    /**
     * Get a list of property names for readable properties
     */
    function getReadProperties() {
        $methods = $this->getReadPropertyMethods();
        $method_properties = $this->methodsToProperties($methods);
        $var_properties = $this->getPublicVarNames();
        return array_unique(array_merge($method_properties, $var_properties));
    }
    
    function getPublicVarNames() {
        return array_keys($this->getPublicVars());
    }
    
    function getPublicVars() {
        return mvc_Model::getProperties($this);
    }
    
    function getWriteProperties() {
      //$methods = $this->getWritePropertyMethods();
      //$method_properties = $this->methodsToProperties($methods);
      //$public_vars = $this->getPublicVarNames();
      //return array_unique(array_merge($method_properties, $public_vars));
      $properties = array();
      $methods = get_class_methods(get_class($this));
      foreach ($methods as $m) {
        if(preg_match('/^set[A-Z](.*)$/', $m)) {
          $properties[] = $this->methodNameToPropertyName($m);
        }
      }
      
      foreach(get_class_vars(get_class($this)) as $var=>$value) {
        $properties[] = $var;
      }
      $old_properties = $properties;
      $properties = array();
      foreach ($old_properties as $var) {
        if ($var[0] != '_' && !preg_match('/^property_value.*$/', $var)) $properties[] = $var;
      }
      return $properties;
    }
    
    function methodsToProperties($methods) {
        $properties = array();
        foreach ($methods as $method) {
            $properties[] = $this->methodNameToPropertyName($method);
        }
        return $properties;
    }
    
    function methodNameToPropertyName($method) {
        return underscore(lcfirst(preg_replace('/^(get|set)(.*)$/', '$2', $method)));
    }
    
    function getPropertyValues() {
        $vars = $this->getPublicVars();
        $property_methods = $this->getReadPropertyMethods();
        foreach ($property_methods as $method) {
            $property = $this->methodNameToPropertyName($method);
            if (!in_array($property, $vars)) {
                $vars[$property] = $this->$method();
            }
        }
        return $vars;
    }
    
    function getReadPropertyMethods() {
        return $this->getPropertyMethodsByAccess('get');
    }
    
    function getWritePropertyMethods() {
        return $this->getPropertyMethodsByAccess('set');
    }
    
    function getPropertyMethodsByAccess($access) {
        $methods = $this->getPropertyMethods();
        $bean_methods = array();
        foreach ($methods as $method) {
            if (startswith($method, $access)) $bean_methods[] = $method;
        }
        return $bean_methods;
    }
    
    function getPropertyMethods() {
        // Get methods to skip
        $skip_methods = get_class_methods('mvc_Model');

        $property_methods = array();
        $methods = get_class_methods(get_class($this));
        foreach ($methods as $method_name) {
            if (strlen($method_name) <= 3) continue;
            if (startswith($method_name, 'get')) {
                $type = 'read';
                $parameter_count = 0;
            }
            elseif (startswith($method_name, 'set')) {
                $type = 'write';
                $parameter_count = 1;
            }
            else {
                continue;
            }

            if (in_array($method_name, $skip_methods)) continue;
            $method = new ReflectionMethod(get_class($this), $method_name);
            if (!$method->isPublic()) continue;
            $parameters = $method->getParameters();
            if (count($parameters) == $parameter_count) {
                $property_methods[] = $method_name;
            }
        }
        return $property_methods;
    }
    
    function setPropertyValue($name, $value) {
        $public_vars = $this->getPublicVarNames();
        if (in_array($name, $public_vars)) {
            $this->$name = $value;
        }
        else {
            $method = camelize('set_' . $name);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
            else {
                $this->$name = $value;
            }
        }
    }
    
    function getPropertyValue($name, $default=null) {
        $vars = $this->getPublicVars();
        if (array_key_exists($name, $vars)) return $vars[$name];
        $method = camelize('get_' . ucfirst($name));
        if (method_exists($this, $method)) return $this->$method();
    }
    
    function validate() {
        $this->_errors = array();
        return $this->_errors;
    }

    /**
     * Callback that lets the model to perform validation prior to a Save operation.
     *
     * This method is called after <code>validate()</code> is called.
     */
    function validateForSave() {
        // Empty
    }
    
    /**
     * Callback that lets the model to perform validation prior to an Add operation.
     *
     * This method is called after <code>validate()</code> is called, and before
     * <code>validateForSave()</code>.
     */
    function validateForAdd() {
        // Empty
    }
    
    /**
     * Callback that lets the model to perform validation prior to an Update operation.
     *
     * This method is called after <code>validate()</code> is called, and before
     * <code>validateForSave()</code>.
     */
    function validateForUpdate() {
        // Empty
    }
    
    function isValid() {
        return $this->getIsValid();
    }
    
    function getIsValid() {
        $this->validate();
        return $this->errors ? false : true;
    }
    
    function getValid() {
        return $this->getIsValid();
    }
    
    function afterValidate() {}
    
    function &__get($name) {
        $value = $this->getPropertyValue($name);
        return $value;
    }
    
    function __set($name, $value) {
        $this->setPropertyValue($name, $value);
    }
    
    function isReadProperty($name) {
        $vars = $this->getPublicVarNames();
        if (in_array($name, $vars)) {
            return true;
        }
        $method = 'get' . ucfirst(camelize($name));
        return method_exists($this, $method);
    }
    
    function addError($error) {
        mm_log(get_class($this) . "#addError(" . var_export($error, true) . ")");
        $this->_errors[] = $error;
    }
    
    function addErrors($errors) {
        foreach($errors as $e) $this->addError($e);
    }
    
    function getErrors() {
        if (!isset($this->_errors)) return array();
        return $this->_errors;
    }
    
    function setErrors($errors)
    {
        $this->_errors = $errors;
    }

    static function getCommentAttributes($comment) {
        $lines = preg_split("/\r\n|\n/", $comment);
        $attributes = array();
        foreach ($lines as $line) {
            if (!preg_match('/^\s*\*\s*@((\w+)(\s+(.*)))$/', $line, $match)) continue;
            $attributes[] = (object) array('name'=>$match[2], 'value'=>$match[4]);
        }
        return $attributes;
    }
    
    function __wakeup() {
        $this->_errors = array();
    }
    
    function getDao() {
        if (!$this->_dao) {
            if ($this->_dao_class) {
                $class = $this->_dao_class;
            }
            else {
                $class = get_class($this) . "DAO";
            }
            $this->_dao = new $class;
        }
        return $this->_dao;
    }
    
    function save() {
        return $this->getDao()->save($this);
    }
    
    function delete() {
        return $this->getDao()->delete($this);
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     * See <code>links_Link</code> for an example.
     */
    function getFormFields() {
        $fields = array();
        $classname = get_class($this);
        $model = new $classname;
        $dao = $model->getDao();
        $dao->model_class = get_class($this);
        $db = mm_getDatabase();
        $table = $dao->getModelTable();
        $table_fields = $dao->getColumnDefsFromDatabase();
        $model_name = $dao->getModelName();
        foreach ($table_fields as $row) {
            $field = array(
                'name' => $model_name . '[' . $row['name'] . ']',
                'label' => ucwords(str_replace('_', ' ', $row['name'])));
            if ($row['type'] == 'text') {
                $field['type'] = 'textarea';
            }
            else if ($row['type'] == 'boolean') {
                $field['type'] = 'checkbox';
            }
            else if ($row['type'] == 'datetime') {
                $field['format'] = 'datetime';
            }
            else if ($row['type'] == 'date') {
                $field['format'] = 'date';
            }
            else {
                $field['format'] = 'text';
            }
            $fields[] = $field;
        }
        return new mvc_FormFields($fields);
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     * See <code>links_Link</code> for an example.
     */
    function beforeAdd() {
        // Empty
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     * See <code>links_Link</code> for an example.
     */
    function beforeUpdate() {
        // Empty
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     * See <code>links_Link</code> for an example.
     */
    function beforeSave() {
        // Empty
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     * See <code>links_Link</code> for an example.
     */
    function afterAdd() {
        // Empty
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     * See <code>links_Link</code> for an example.
     */
    function afterUpdate() {
        // Empty
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     * See <code>links_Link</code> for an example.
     */
    function afterSave() {
        // Empty
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     */
    function beforeDelete() {
        // Empty
    }
    
    /**
     * Override this in the subclasses.
     * Be sure to call the parent's version of the method first.
     */
    function afterDelete() {
        // Empty
    }

    /**
     * Find a list of model instances from the database.
     *
     * Example:
     * $products = mvc_Model::find('product_Product', array('where' => 'count > 0'));
     */
    static function find($classname, $options=null) {
        $model = new $classname;
        $dao = $model->getDao();
        $dao->model_class = $classname;
        return $dao->find($options);
    }
    
    static function findColumn($classname, $options=null) {
        if (!isset($options)) $options = array();
        $model = new $classname;
        $dao = $model->getDao();
        $dao->model_class = $classname;
        $results = $dao->find($options);
        $array = array();
        $column = array_delete_at($options, 'property', 'id');
        foreach ($results as $r) {
            $array[] = $r->$column;
        }
        return $array;
    }

    static function findKeyValues($classname, $options=null) {
        if (!isset($options)) $options = array();
        $model = new $classname;
        $dao = $model->getDao();
        $dao->model_class = $classname;
        $results = $dao->find($options);
        $key_name = gv($options, 'key_property', 'id');
        $value_name = gv($options, 'property');
        $assoc = array();
        foreach ($results as $r) {
            $assoc[$r->$key_name] = $r->$value_name;
        }
        return $assoc;
    }
    
    /**
     * Find a single model instance from the database.
     *
     * Examples:
     * $product = mvc_Model::fetch('product_Product', $product_id);
     * $product = mvc_Model::fetch('product_Product', array('where' => array('sku = ?', $sku)));
     */
    static function fetch($classname, $options=null) {
        $model = new $classname;
        $dao = $model->getDao();
        $dao->model_class = $classname;
        return $dao->fetch($options);
    }
    
    function reload() {
        $dao = $this->getDao();
        $dao->model_class = get_class($this);
        $dao->fetch($this->id, array('object' => $this));
    }
    
    /**
     * Fetch the number of records in a given model.
     *
     * Example:
     * $count = mvc_Model::count('product_Product');
     */
    static function count($classname, $options=null) {
        $model = new $classname;
        $dao = $model->getDao();
        $dao->model_class = $classname;
        return $dao->count($options);
    }
}
