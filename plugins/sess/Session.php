<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class sess_Session extends mvc_Model
{
    /*
        session_id    int NOT NULL auto_increment,
          sid           varchar(50) default NULL,
          creation_date datetime default NULL,
          data          blob,
    */
    public $id;
    public $sid;
    public $creation_date;
    public $modify_date;
    public $data;
    
    function __construct($values=array())
    {
        $this->data = array();
        $this->creation_date = mm_time();
        $this->modify_date = mm_time();
        $this->sid = uniqid('sess_');
        parent::__construct($values);
    }
    
    function get($key, $default=null)
    {
        return gv($this->data, $key, $default);
    }
    
    function &getRef($key)
    {
        return $this->data[$key];
    }
    
    function set($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    function unsetVar($key)
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
    }
    
    function setRef($key, &$value)
    {
        $this->data[$key] = &$value;
    }
}
