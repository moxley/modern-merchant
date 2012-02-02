<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class payment_PaymentMethodDAO
{
    function getCount()
    {
        $db = mm_getDatabase();
        return $db->getOne("select count(*) from mm_payment_method");
    }
    
    function getList($options=array())
    {
        $where = gv($options, 'where');
        if ($where && !is_array($where)) $where = array($where);
        $order = gv($options, 'order', 'sortorder');
        $cols = "id, name, active, class, sortorder, public_title, settings";
        
        $db = mm_getDatabase();
        if ($where) {
            $params = $where;
            $where = "WHERE " . array_shift($params);
        }
        else {
            $where = "";
            $params = array();
        }
        $sql = "SELECT $cols " .
            'FROM mm_payment_method ' .
            "$where " .
            "ORDER by $order";
        $rows = $db->getAllAssoc($sql, $params);
        $methods = array();
        foreach ($rows as $row) $methods[] = $this->parseRow($row);
        return $methods;
    }
    
    function getSettingsArray($method)
    {
        $cols = array("id", "name", "active", "class", "sortorder", "public_title", "settings");
        $vars = get_object_vars($method);
        foreach ($vars as $k=>$v) {
            if ($k[0] == '_') {
                unset($vars[$k]);
                $k = substr($k, 1);
                $vars[$k] = $v;
            }
            if (in_array($k, $cols)) {
                unset($vars[$k]);
            }
        }
        return $vars;
    }
    
    function add($method)
    {
        $db = mm_getDatabase();
        $sql = 'INSERT INTO mm_payment_method (name, active, class, sortorder, public_title, settings)' .
            'VALUES (?, ?, ?, ?, ?, ?)';
        $params = array(
            $method->name,
            ($method->active ? 1 : 0),
            $method->class,
            intval($method->sortorder),
            $method->public_title,
            serialize($this->getSettingsArray($method))
        );
        $db->execute($sql, $params);
        $method->id = $db->lastInsertId();
        return $method;
    }
    
    function update($method)
    {
        $db = mm_getDatabase();
        $sql = 'UPDATE mm_payment_method set name=?, active=?, sortorder=?, public_title=?, settings=? WHERE id=?';
        $params = array(
            $method->name, $method->active ? 1 : 0,
            intval($method->sortorder), $method->public_title,
            serialize($this->getSettingsArray($method)),
            intval($method->id)
        );
        $db->execute($sql, $params);
        return $method;
    }
    
    function save($method)
    {
        if (!$method->id) {
            return $this->add($method);
        }
        else {
            return $this->update($method);
        }
    }

    function delete($method)
    {
        $id = $method->id;
        $sql = "delete from mm_payment_method where id=?";
        $db = mm_getDatabase();
        $db->execute($sql, array($id));
    }
    
    function deleteAll()
    {
        $db = mm_getDatabase();
        return $db->execute("delete from mm_payment_method");
    }

    function parseRow($row)
    {
        $class = $row['class'];
        $method = new $class;
        $values = $row['settings'] ? unserialize($row['settings']) : array();
        //$method->setPropertyValues($values);
        $method->class        = $row['class'];
        $method->_name        = $row['name'];
        $method->id           = (int) $row['id'];
        $method->active       = $row['active'] ? true : false;
        $method->sortorder    = (int) $row['sortorder'];
        $method->public_title = $row['public_title'];
        $method->setSettings($row['settings']);
        
        return $method;
    }
    
    function loadMethods()
    {
        $methods = $this->getList();
        $methods_lookup = array();
        foreach ($methods as $method) {
            $methods_lookup[$method->id] = $method;
        }
        return $methods_lookup;
    }

    function getModuleSetting($module)
    {
        global $PAYMENT_METHOD_SETTINGS;
        if ( !isset($PAYMENT_METHOD_SETTINGS) ) $PAYMENT_METHOD_SETTINGS = array();        

        if ($module) $pname = $module;
        else $pname = $this->getName();

        if( !isset($PAYMENT_METHOD_SETTINGS[$pname]) )
        {
            $PAYMENT_METHOD_SETTINGS[$pname] = $this->fetchSettings($pname);
        }
            
        return @$PAYMENT_METHOD_SETTINGS[$pname][$name];
    }
        
    function setModuleSettings($settings, $module)
    {
        global $PAYMENT_METHOD_SETTINGS;
        if( !isset($PAYMENT_METHOD_SETTINGS) ) $PAYMENT_METHOD_SETTINGS = array();        

        $pname = $module->getName();

        if( !isset($PAYMENT_METHOD_SETTINGS[$pname]) )
        {
            $PAYMENT_METHOD_SETTINGS[$pname] = $this->fetchSettings($pname);
        }
            
        $db = mm_getDatabase();
        $method_prefix = PAYMENT_METHOD_SETTING_PREFIX . '.' . $pname . '.';
        foreach( $settings as $key=>$value )
        {
            if( !isset($PAYMENT_METHOD_SETTINGS[$pname][$key]) )
            {
                $sql = 'insert into mm_setting (name,value) values ('.dq($method_prefix.$key).','.dq($value).')';
            }
            else
            {
                $sql = 'update mm_setting set value='.dq($value).' where name='.dq($method_prefix.$key);
            }
            
            $res = $db->query($sql);
            $PAYMENT_METHOD_SETTINGS[$pname][$key] = $value;
        }
    }
        
    function setSetting($name, $value)
    {
        $this->setSettings(array($name=>$value));
    }
        
    function fetchSettings($module)
    {
        global $PAYMENT_METHOD_SETTINGS;

        if (!$module) $module = $this->getName();
        $method_prefix = PAYMENT_METHOD_SETTING_PREFIX . '.' . $module . '.';
        $db = mm_getDatabase();
        $sql = "SELECT * FROM mm_setting WHERE name LIKE '$method_prefix%'";
        $res = $db->query($sql);
        $pname = $module;
        $PAYMENT_METHOD_SETTINGS[$pname] = array();
        while( $record = $res->fetchAssoc() )
        {
            $name = substr($record['name'], strlen($method_prefix));
            $PAYMENT_METHOD_SETTINGS[$pname][$name] = $record['value'];
        }
        $res->free();
            
        return $PAYMENT_METHOD_SETTINGS[$pname];
    }
        
    /**
     * Get an instance of the payment module of the given class name 
     * 
     * @param string The class name of the PaymentMethod object
     * @return object PaymentMethod
     */
    function getModuleByClass($class)
    {
        foreach ($this->getMethods() as $method) {
            if ($method->class == $class) return $method;
        }
        return null;
    }
        
    function getModuleClass($name)
    {
        if( !$name ) return NULL;
        $record = $this->getRecordByName($name);
        if( !$record ) return NULL;
        return $record['class'];            
    }
    
    function getModuleByName($name)
    {
        return $this->fetchByName($name);
    }
    
    function fetchByName($name)
    {
        foreach ($this->getMethods() as $method) {
            if ($method->name == $name) return $method;
        }
        return null;
    }
    
    function fetch($id)
    {
        $db = mm_getDatabase();
        $sql = "SELECT id, name, active, class, sortorder, public_title, settings FROM mm_payment_method WHERE id=?";
        $row = $db->getOneAssoc($sql, array($id));
        if (!$row) return null;
        $pm = $this->parseRow($row);
        return $pm;
    }

    /**
     * @obsolete TODO: remove
     */
    function getModuleById($id)
    {
        return $this->fetch($id);
    }
        
    function &getActiveMethods()
    {
        return array_filter($this->getMethods(), create_function('$m', 'return $m->isActive();'));
    }

    function &getActiveModules()
    {
        return $this->getActiveMethods();
    }

    function getMethods()
    {
        global $PAYMENT_METHODS_BY_ID;
        if (!isset($PAYMENT_METHODS_BY_ID)) {
            $PAYMENT_METHODS_BY_ID = $this->loadMethods();
        }
        return $PAYMENT_METHODS_BY_ID;
    }
    
    function resetMethods()
    {
        global $PAYMENT_METHODS_BY_ID;
        $PAYMENT_METHODS_BY_ID = null;
    }

    /**
     * Plugin hook: Overrides same method in <tt>payment_PaymentMethod</tt>
     */
    function renderPaymentForm($controller)
    {
        $this->controller = $controller;
        $cart = $this->controller->getCart();
        $req = $this->controller->getRequest();
        if (!$cart->payment) {
            $cart->payment = new authnet_Payment;
            
        }
        $cart->payment->setPropertyValues(@$req['cart']['payment']);
        $this->controller->payment_method = $this;
        $this->controller->render('payment/credit_card');
    }
    
}
