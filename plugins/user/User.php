<?php
/**
 * @package user
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package user
 */
class user_User extends mvc_Model
{
    public $id;
    public $username;
    public $password;
    public $new_password;
    public $confirm_password;
    public $first_name;
    public $last_name;
    public $email;
    
    public $_accesses;
    
    static function fetchByUsername($username)
    {
        $dao = new user_UserDAO;
        return $dao->fetch(array('where' => array('username=?', $username)));
    }
    
    function isAdmin()
    {
        return $this->hasAccess('admin.write');
    }
    
    function validateLogin()
    {
        $errors = array();
        if (!$this->username) {
            $errors[] = "Please provide your username";
        }
        else if (!$this->password) {
            $errors[] = "Please provide your password";
        }
        return $errors;
    }
    
    function login() {
        if ($errors = $this->validateLogin()) {
            return false;
        }

        if (!($user = $this->dao->login($this))) {
            $this->addError("Login failed. Please check your username and password, and try again.");
            return null;
        }
        else {
            mm_setUser($user);
            mvc_Hooks::notifyListeners('user.login', $user);
            return $user;
        }
    }
    
    function logout() {
        mm_setUser(null);
        mvc_Hooks::notifyListeners('user.logout', $this);
    }
    
    /**
     * Find out if user has access to a particular named zone
     */
    function hasAccess($name) {
        foreach ($this->accesses as $access) {
            if ($access->name == $name) return true;
        }
        return false;
    }
    
    function setAccessNames($names) {
        $dao = new access_AccessDAO;
        $new_accesses = $dao->find(array('where' => array('name IN (?)', $names)));
        
        // Build new lookup
        $new_accesses_by_name = array();
        foreach ($new_accesses as $access) {
            $new_accesses_by_name[$access->name] = $access;
        }
        
        // Build old lookup
        $old_accesses_by_name = array();
        foreach ($this->accesses as $access) {
            $old_accesses_by_name[$access->name] = $access;
        }
        
        // Collect IDs to delete
        $this->access_ids_to_delete = array();
        foreach ($old_accesses_by_name as $name => $access) {
            if (!gv($new_accesses_by_name, $name)) {
                $this->access_ids_to_delete[] = $access->id;
            }
        }
        
        // Collect IDs to add
        $this->access_ids_to_add = array();
        foreach ($new_accesses_by_name as $name => $access) {
            if (!gv($old_accesses_by_name, $name)) {
                $this->access_ids_to_add[] = $access->id;
            }
        }
        
        // Set
        $this->_accesses = $new_accesses;
    }
    
    function getAccessNames() {
        $names = array();
        foreach ($this->accesses as $access) {
            $names[] = $access->name;
        }
        return $names;
    }
    
    function getAccesses() {
        if (!isset($this->_accesses)) {
            if ($this->id) {
                $this->_accesses = mvc_Model::find('access_Access', array('joins' => array('INNER JOIN mm_user_access ua ON mm_access.id = ua.access_id AND ua.user_id=?', $this->id)));
            } else {
                $this->_accesses = array();
            }
        }
        return $this->_accesses;
    }
    
    function setAdminValues($values=array()) {
        $this->username = gv($values, 'username');
        if ($password = gv($values, 'new_password')) {
            $this->new_password = $password;
            $this->confirm_password = gv($values, 'confirm_password');
        }
        $this->first_name = gv($values, 'first_name');
        $this->last_name = gv($values, 'last_name');
        $this->access_names = gv($values, 'access_names');
    }
    
    function setOwnerValues($values=array()) {
        $this->username = gv($values, 'username');
        if ($password = gv($values, 'new_password')) {
            $this->new_password = $password;
            $this->confirm_password = gv($values, 'confirm_password');
        }
        $this->first_name = gv($values, 'first_name');
        $this->last_name = gv($values, 'last_name');
    }
    
    function validate() {
        if (!$this->username) {
            $this->addError("Please provide a username");
        }
        return $this->errors;
    }
    
    function validateForUpdate() {
        if ($this->new_password && $this->new_password != $this->confirm_password) {
            $this->addError("Password and confirmation do not match.");
        }
        if ($this->errors) return;
        
        $duplicate = $this->dao->fetchByDuplicateUsername($this);
        if ($duplicate) {
            $this->addError("Duplicate username=" . $this->username);
        }
    }
    
    function validateForAdd() {
        if (!$this->password) {
            if (!$this->new_password) {
                $this->addError("Please provide a password");
            }
            else if ($this->new_password && $this->new_password != $this->confirm_password) {
                $this->addError("Password and confirmation do not match.");
            }
        }
        if ($this->errors) return;

        $duplicate = $this->dao->fetchByUsername($this->username);
        if ($duplicate) {
            $this->addError("Duplicate username=" . $this->username);
        }
    }
    
    function afterValidate() {
        if ($this->new_password) {
            $this->password = $this->new_password;
        }
    }
    
    function setAsCustomer() {
        $this->access_names = array('customer');
    }
    
    function setAsAdmin() {
        $this->access_names = array('admin.read', 'admin.write', 'user.management', 'customer');
    }

    function save() {
        // Check for duplication username
        if (!$this->id && $this->username) {
            $user = $this->dao->fetch(array('where' => array('username=?', $this->username)));
            if ($user) {
                $this->addError("Username '{$this->username}' already taken");
                return false;
            }
        }
        
        if (!parent::save()) {
            return false;
        }
        else {
            // Add/Delete accesses
            if (isset($this->access_ids_to_delete)) {
                $db = mm_getDatabase();

                // Delete
                if (count($this->access_ids_to_delete) > 0) {
                    $db->execute("DELETE FROM mm_user_access WHERE user_id=? AND access_id IN (?)", array($this->id, $this->access_ids_to_delete));
                }
                unset($this->access_ids_to_delete);

                // Add
                if (count($this->access_ids_to_add) > 0) {
                    foreach ($this->access_ids_to_add as $access_id) {
                        $db->execute("INSERT INTO mm_user_access (user_id, access_id) VALUES (?, ?)", array($this->id, $access_id));
                    }
                }
                unset($this->access_ids_to_add);
            }
            return true;
        }
    }
}
