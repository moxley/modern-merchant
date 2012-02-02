<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class content_Content extends mvc_Model
{
    public static $types = array('plain', 'html', 'php');
    
    public $id = 0;
    public $name = null;
    public $title = null;
    public $body = null;
    public $type = 'plain';
    public $description = null;
    public $controller = null;
    
    function __construct($array=array())
    {
        $this->populate($array);
    }
    
    function populate($array)
    {
        $this->id = intval(gv($array, 'id',
            gv($array, 'id', $this->id)));
        $this->name = gv($array, 'name', $this->name);
        $this->description = gv($array, 'description', $this->description);
        $this->body = gv($array, 'body', $this->body);
        $this->title = gv($array, 'title', $this->title);
        $type = gv($array, 'type', $this->type);
        if (in_array($type, self::$types)) $this->type = $type;
    }
    
    function toAssoc()
    {
        $array = array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body
        );
        return $array;
    }
    
    function save()
    {
        $dao = new content_ContentDAO;
        if ($this->id) {
            return $dao->update($this);
        }
        else {
            return $dao->add($this);
        }
    }
    
    function renderToOutput($controller=null)
    {
        if ($controller) $this->controller = $controller;
        
        if ($this->type == 'plain') {
            echo nl2br(h($this->body));
        }
        if ($this->type == 'php') {
            eval('?>' . $this->body);
        }
        else {
            echo $this->body;
        }
        
        $this->controller = null;
    }
    
    function __call($func, $args)
    {
        if ($this->controller) {
            return call_user_func_array(array($this->controller, $func), $args);
        } else {
            throw new Exception("Call to undefined method: " . get_class($this) . "#$func()");
        }
    }
}
