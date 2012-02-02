<?php
/**
 * @package media
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package media
 */
class media_Media extends mvc_Model {
    public $id;
    
    public $_owner;
    public $_owner_id;
    public $_owner_type;
    
    public $mime_type;
    public $_filename;
    
    public $name = null;
    
    public $description = null;
    
    public $width = 0;
    
    public $height = 0;
    
    public $data;
    
    public $sortorder = 0;
    
    private $_file_upload = null;
    
    function getOwnerTable() {    
        if (!$this->owner) {
            $class = $this->owner_type . "DAO";
            $dao = new $class;
        }
        else {
            $dao = $this->owner->dao;
        }
        return $dao->getTable();
    }
    
    function getOwnerAbstractType() {
        if (!$this->owner_type) return null;
        $parts = explode('_', $this->owner_type);
        $name = array_pop($parts);
        return underscore($name);
    }
    
    function setOwner($owner) {
        if ($owner) {
            $this->_owner = $owner;
            $this->_owner_id = $this->owner->id;
            $this->_owner_type = get_class($this->owner);
        }
        else {
            $this->_owner = null;
            $this->_owner_id = null;
            $this->_owner_type = null;
        }
    }
    
    function getOwner() {
        if (!$this->_owner && $this->_owner_id && $this->_owner_type) {
            $dao_class = $this->_owner_type . "DAO";
            $dao = new $dao_class;
            $this->_owner = $dao->fetch($this->_owner_id);
        }
        return $this->_owner;
    }
    
    function getOwnerId() {
        return $this->_owner_id;
    }
    
    function setOwnerId($id) {
        $this->_owner_id = $id;
    }
    
    function getOwnerType() {
        return $this->_owner_type;
    }
    
    function setOwnerType($type) {
        $this->_owner_type = $type;
    }
    
    function getBasePath() {
        return mm_getConfigValue('filepaths.media');
    }
    
    function generateFilename() {
        return "{$this->owner_abstract_type}.{$this->owner_id}.{$this->id}.{$this->ext}";
    }
    
    function getFilename() {
        return $this->_filename;
    }
    
    function setFilename($filename) {
        $this->_filename = $filename;
    }
    
    function getFullPath() {
        if (!$this->filename) return null;
        $base = $this->getBasePath();
        if (!$base) return null;
        return $base . DS . str_replace('/', DS, $this->path_offset) . DS . $this->filename;
    }
    
    function getUrlPath() {
        return self::calculatePath($this->id, $this->filename);
    }
    
    function getPathOffset()
    {
        return self::calculatePathOffset($this->id);
    }
    
    static function calculatePathOffset($id)
    {
        $p0 = $id % 10;
        $p1 = intval($id / 10) % 10;
        return "$p0/$p1";
    }
    
    static function calculatePath($id, $filename)
    {
        return mm_getConfigValue("urls.media") . self::calculatePathOffset($id) . '/' . $filename;
    }
    
    static function calculateFilePath($id, $filename)
    {
        return mm_getConfigValue("filepaths.media") . '/' . self::calculatePathOffset($id) . '/' . $filename;
    }
    
    static function renderById($id) {
        // Image directory management
        $public_dir = MM_LIB . '/public';
        $media_dir = $public_dir . '/media';
        $cached = null;

        // Check for file
        if (is_dir($media_dir)) {
            $files = glob($media_dir . '/' . intval($id) . '.*');
            foreach ($files as $file) {
                $file = basename($file);
                if (preg_match('/^\d+\.([a-z]+)$/', $file, $match)) {
                    $cached = $match[0];
                    $ext = $match[1];
                }
            }
        }
        
        if ($cached) {
            // Get mime type
            $mime_type = self::extToMime($ext);
            
            // Send content type
            header("Content-Type: $mime_type");
            
            // Send file
            $file = $media_dir . '/' . $cached;
            readfile($file);
        }
        else {
            $media = self::find($id);
            $media->render();
            flush();
            $media->cache();
        }
    }
    
    function render() {
        header("Content-Type: $this->mime_type");
        echo $this->data;
    }
    
    function cache() {
        $public_dir = MM_LIB . '/public';
        $media_dir = $public_dir . '/media';
        if (!is_dir($media_dir)) {
            mkdir($media_dir);
        }
        $file = $media_dir . '/' . $this->id . '.' . self::mimeToExt($this->mime_type);
        file_put_contents($file, $this->data);
    }
    
    static function extToMime($ext) {
        $lookup = array(
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png'
        );
        return $lookup[$ext];
    }
    
    static function mimeToExt($mime) {
        if (!$mime) return null;
        $lookup = array(
            'image/jpeg' => 'jpg',
            'image/gif'  => 'gif',
            'image/png'  => 'png'
        );
        return $lookup[$mime];
    }
    
    function getExt() {
        if (!$this->mime_type) {
            return '';
        }
        else {
            return media_Media::mimeToExt($this->mime_type);
        }
    }
    
    function setFileUpload($file_upload) {
        if (!is_string($file_upload)) {
            if ($file_upload->error) {
                $this->addError($this->getErrorMessage($file_upload->error));
                return false;
            }
            $this->name = $file_upload->original;
            $this->image_size_info = getimagesize($file_upload->file);
        }
        else {
            $this->name = basename($file_upload);
            $this->image_size_info = getimagesize($file_upload);
        }
        $this->_file_upload = $file_upload;
    }
    
    function setImageSizeInfo($size_info)
    {
        $this->width = $size_info[0];
        $this->height = $size_info[1];
        $this->mime_type = $size_info['mime'];
    }
    
    function getFileUpload() {
        return $this->_file_upload;
    }
    /**
     * @todo Rename to reflect the fact that it is a file upload error message.
     */
    function getErrorMessage($code) {
        if ($code == UPLOAD_ERR_OK) {
            return "";
        }
        else if ($code == UPLOAD_ERR_INI_SIZE) {
            return "The uploaded file exceeds the maximum file size allowed (by PHP).";
        }
        else if ($code == UPLOAD_ERR_FORM_SIZE) {
            return "The uploaded file exceeds the maximum file size allowed (by the form's MAX_FILE_SIZE value).";
        }
        else if ($code == UPLOAD_ERR_PARTIAL) {
            return "The uploaded file was only partially uploaded.";
        }
        else if ($code == UPLOAD_ERR_NO_FILE) {
            return "No file was uploaded.";
        }
        else if ($code == UPLOAD_ERR_NO_TMP_DIR) {
            return "Missing a temporary folder.";
        }
        else if ($code == UPLOAD_ERR_CANT_WRITE) {
            return "Failed to write file to disk.";
        }
        else if ($code == UPLOAD_ERR_EXTENSION) {
            return "File upload stopped by extension.";
        }
    }
    
    function validate()
    {
        if (!$this->owner_id) {
            $this->addError("Missing owner_id");
        }
        if (!$this->owner_type) {
            $this->addError("Missing owner_type");
        }
    }
    
    function getWidthHeight() {
        return $this->width . 'x' . $this->height;
    }
    
}
