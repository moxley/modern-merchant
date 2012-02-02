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
class mvc_FileUpload extends mvc_Model {
    
    public $mime_type;
    public $size;
    public $error;
    public $file;
    public $original;
    public $keep_source = false;
    
    function __construct($php_file_upload=null)
    {
        if ($php_file_upload) {
            $this->setPhpFileUpload($php_file_upload);
        }
    }
    
    function setPhpFileUpload($php_file_upload)
    {
        $this->mime_type = $php_file_upload['type'];
        $this->size      = $php_file_upload['size'];
        $this->error     = $php_file_upload['error'];
        $this->file      = $php_file_upload['tmp_name'];
        $this->original  = $php_file_upload['name'];
    }
    
    static function getUploads() {
        $uploads = array();
        foreach ($_FILES as $name => $info) {
            if (!$info['tmp_name']) {
                $uploads[$name] = null;
                continue;
            }
            $upload = new mvc_FileUpload($info['tmp_name']);
            $upload->type = $info['type'];
            $upload->size = $info['size'];
            $upload->error = $info['error'];
            $uploads[$name] = $upload;
        }
        return $uploads;
    }
    
    static function getUpload($name) {
        $uploads = self::getUploads();
        return gv($uploads, $name, null);
    }
    
    /**
     * @deprecated 0.04 - Jan 2, 2006 - Use <tt>moveTo()</tt> instead.
     */
    function saveTo($path) {
        return $this->moveTo($path);
    }
    
    /**
     * Move a file upload.
     * 
     * If the file is not an uploaded file (as identified by is_uploaded_file()),
     * this function does not delete the source file.
     */
    function moveTo($path) {
        // Create parent directories
        if (!file_exists($path)) {
            $dir = dirname($path);
            mm_log("Creating directory \"$dir\"");
            if (!mkdirp($dir)) {
                mm_log("Failed to create directory");
                $this->addError("Failed to create directory");
                return false;
            }
        }
        if (is_uploaded_file($this->file)) {
            mm_log("Moving uploaded file \"{$this->file}\" to \"$path\"");
            if (!move_uploaded_file($this->file, $path)) {
                mm_log("Failed to move uploaded file");
                $this->addError("Failed to move uploaded file");
                return false;
            }
            mm_log("Successfully moved uploaded file");
        }
        else {
            mm_log("Copying \"{$this->file}\" to \"$path\"");
            if (!copy($this->file, $path)) {
                mm_log("Failed to copy file");
                $this->addError("Failed to copy file");
                return false;
            }
            mm_log("Successfully copied file");
            if (!$this->keep_source && is_writable(dirname($this->file))) {
                if (strpos($this->file, 'plugins') !== false) {
                    throw new Exception("Attempt to delete file from the plugins directory tree");
                }
                if (unlink($this->file)) {
                    mm_log("Successfully deleted source $this->file");
                }
            }
        }
        $this->file_path = $path;
        try {
            if (get_current_user() == fileowner(dirname($path))) {
                $r = @chmod(dirname($path), 0777);
                if (!$r) {
                    mm_log("Failed chmod('" . dirname($path) . "', 0777)");
                }
            }

            if (get_current_user() == fileowner($path)) {
                if (!chmod($path, 0666)) {
                    mm_log("Failed chmod($path, 0666)");
                    $this->addError("Failed to chmod file");
                    return false;
                }
            }
            mm_log("Successfully chmoded directory and file");
        }
        catch(Exception $e) {
            mm_log("Exception thrown during call to chmod(): " . $e->getMessage());
        }
        return true;
    }
    
    function getIsEmpty() {
        return !$this->file;
    }
    
    /* Utility functions */
    
    /**
     * Converts file uploads from PHP's array-based format to an array of
     * <code>mvc_FileUpload</code> objects.
     * 
     * This method is called during MM's bootup sequence (see <code>getRequest()</code>
     * function in tools.php), so that incoming file uploads are more homogenous with other
     * kinds of form values.
     */
    static function convertFiles($files) {
        $converted = array();
        
        foreach ($files as $first_name => $file_info) {
            $names = array($first_name);
            if (!is_array($file_info['tmp_name'])) {
                self::convertFile($file_info, $names, $converted);
            }
            else {
                self::convertFilesSub($file_info, $names, $level=0, $converted);
            }
        }
        
        return $converted;
    }
    
    /**
     * Called by <code>convertFiles()</code>.
     */
    static function convertFile($file_info, $names, &$converted) {
        $first_name = array_shift($names);
        $single_info = array();
        foreach ($file_info as $key=>$v) {
            $single_info[$key] = self::array_get($file_info[$key], $names);
        }
        $upload = new mvc_FileUpload($single_info);
        self::array_set($converted, array_merge(array($first_name), $names), $upload);
    }
    
    /**
     * Called by <code>convertFiles()</code>.
     */
    static function convertFilesSub($file_info, $names, $level, &$converted) {
        $file_info_names = array_merge(array('tmp_name'), array_slice($names, 1, $level));
        $file_info_value = self::array_get($file_info, $file_info_names);
        
        foreach ($file_info_value as $k => $v) {
            if (!is_array($v)) {
                self::convertFile($file_info, array_merge($names, array($k)), $converted);
            }
            else {
                self::convertFilesSub($file_info, array_merge($names, array($k)), $level+1, $converted);
            }
        }
    }

    /**
     * Get a value from a multi-dimensional array using an array of keys.
     *
     * The <code>$keys</code> parameter is an array of names. The position of each
     * name corresponds with the dimension of <code>$array</code> that the name indexes
     * within that dimension. Make sense? Here's an example:
     *
     * $array = array('key_0' => array('key_1' => 'value'));
     * $keys = array('key_0', 'key_1');
     * $result = mvc_FileUpload::array_get($array, $keys);
     * // Returns 'value'
     */
    static function array_get($array, $keys) {
        $value = $array;
        foreach ($keys as $name) {
            $value = $value[$name];
        }
        return $value;
    }
    
    static function array_set(&$array, $names, $value) {
        $a =& $array;
        foreach ($names as $name) {
            if (!array_key_exists($name, $a)) {
                $a[$name] = array();
            }
            $a =& $a[$name];
        }
        $a = $value;
    }
}
