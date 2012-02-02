<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class category_Category extends mvc_Model
{
    public $id = null;
    public $_name = null;
    public $_url_name = null;
    public $_parent = null;
    public $_parent_id = 0;
    public $lft;
    public $rgt;
    public $depth;
    public $description = null;
    public $_image_id = null;
    public $_image = null;
    public $delete_image = null;
    public $_image_to_delete = null;
    public $comment = null;
    public $sortorder = 0;
    public $keywords = null;
    public $_children = null;
    public $siblings = null;
    public $place_before = null;

    private $_is_default = false;
    
    static function fetch($id, $options=array()) {
        $dao = new category_CategoryDAO;
        return $dao->fetch($id, $options);
    }
    
    static function deleteAll() {
        $dao = new category_CategoryDAO;
        return $dao->deleteAll();
    }
    
    public function add($child) {
        if (!isset($this->children)) $this->children = array();
        $this->children[] = $child;
    }
    
    public function validate() {
        if ($this->parent_id < 0 && $this->id !== 0) {
            $this->addError("Please select a parent category");
        }
        
        if (!$this->_name) {
            $this->addError("Please provide a name for the category");
        }
        return $this->errors;
    }
    
    function save() {
        if (!$this->isValid()) false;

        $dao = new category_CategoryDAO;
        if ($this->delete_image) {
            if ($this->_previous_image) {
                $this->_image_to_delete = $this->_previous_image;
            }
            else {
                $this->_image_to_delete = $this->image;
                $this->_image_id = null;
                $this->_image = null;
            }
            $this->delete_image = false;
        }
        
        if (!parent::save()) {
            return false;
        }
        else {
            if (isset($this->_is_default)) {
                $current_default_id = mm_getSetting('catalog.default_category');
                if ($this->_is_default && $current_default_id != $this->id) {
                    mm_setSetting('catalog.default_category', $this->id);
                }
            }
            if ($this->_image_to_delete) {
                $this->_image_to_delete->delete();
                $this->_image_to_delete = null;
            }
            if ($this->_image) {
                $this->saveImage();
            }
        }
        return true;
    }
    
    protected function saveImage() {
        $this->image->owner = $this;
        if (!$this->image->save()) {
            $this->addErrors($this->image->errors);
            return false;
        }
        
        if ($this->_image_id != $this->image->id) {
            $this->_image_id = $this->image->id;
            $db = mm_getDatabase();
            $db->execute("UPDATE mm_category SET image_id=? WHERE id=?", array($this->_image_id, $this->id));
        }
        return true;
    }
    
    function setIsDefault($default) {
        $this->_is_default = $default;
    }
    
    function getIsDefault() {
        if ($this->id && $this->id == mm_getSetting('catalog.default_category')) {
            $this->_is_default = true;
        }
        return $this->_is_default;
    }
    
    function setImageId($id) {
        if ($this->_image_id == $id) return;
        $this->_image_id = $id;
        $this->_image = null;
    }
    
    function getImageId() {
        return $this->_image_id;
    }
    
    function getImage() {
        if (!$this->_image) {
            if ($this->image_id) {
                $mdao = new media_MediaDAO;
                $this->_image = $mdao->fetch($this->image_id);
            }
        }
        return $this->_image;
    }
    
    function setImage($image) {
        if ($image) {
            if ($image instanceof mvc_FileUpload) {
                if ($image->file) {
                    $new_image = new media_Media;
                    $new_image->file_upload = $image;
                    $new_image->owner = $this;
                    $new_image_id = null;
                }
            }
            else if (is_object($image)) {
                $new_image = $image;
                $new_image->owner = $this;
            }
            
            if (isset($new_image)) {
                if ($this->_image_id && $this->_image_id != $new_image->id) {
                    $this->delete_image = true;
                    $this->_previous_image = $this->image;
                }
                $this->_image = $new_image;
                $this->_image_id = $new_image->id;
            }
        }
        else {
            $this->_image = null;
            $this->_image_id = null;
        }
    }
    
    function delete() {
        $dao = new category_CategoryDAO;
        $dao->delete($this);
    }
    
    function getParentId() {
        return $this->_parent_id;
    }
    
    function setParentId($parent_id) {
        $this->_parent = null;
        $this->_parent_id = $parent_id;
    }
    
    function setParent($parent) {
        $this->_parent = $parent;
        $this->_parent_id = $parent->id;
    }
    
    function getParent() {
        if (!$this->_parent) {
            if ($this->_parent_id) {
                $this->_parent = category_Category::fetch($this->_parent_id, array('use_cache' => true));
            }
        }
        return $this->_parent;
    }
    
    /**
     * Get all ancestors, oldest to newest.
     */
    function getAncestry() {
        $ancestors = array();
        $c = $this;
        while ($c->parent_id) {
            $c = $c->parent;
            $ancestors[] = $c;
        }
        return array_reverse($ancestors);
    }
    
    function getName() {
        return $this->_name;
    }
    
    function setName($name) {
        $this->_name = $name;
        $this->_url_name = self::generateUrlName($name);
    }
    
    function getUrlName() {
        return $this->_url_name;
    }
    
    function setUrlName($url_name) {
        throw new Exception("url_name is read-only. Cannot be set.");
    }
    
    static function generateUrlName($name) {
        // str.gsub('&', 'and').gsub(/[^a-zA-Z0-9\-_]/, '_').gsub(/__+/, '_').gsub(/^_+|_+$/, '')
        $url_name = preg_replace('/&/', ' and ', $name);
        $url_name = preg_replace('/[^a-z0-9_-]/i', '_', $url_name);
        $url_name = preg_replace('/_+/', '_', $url_name);
        return preg_replace('/^_+|_+$/', '', $url_name);
    }
    
    function getChildren() {
        if (!isset($this->_children)) {
            $dao = new category_CategoryDAO;
            $this->_children = $dao->getChildren($this->id);
        }
        return $this->_children;
    }
}
