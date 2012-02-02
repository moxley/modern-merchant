<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Represents a temporary file.
 */
class mvc_TempFile
{
    public $file_path;
    
    function __construct($file_path)
    {
        $this->file_path = $file_path;
    }
    
    function moveTo($path)
    {
        if (is_file($path) && !unlink($path)) {
            throw new mm_FileAccessException($path);
        }
        if (!copy($this->file_path, $path)) {
            throw new mm_FileAccessException($path);
        }
        if (!chmod($path, 0644)) {
            throw new mm_FileAccessException($path);
        }
        if (!unlink($this->file_path)) {
            throw new mm_FileAccessException($this->file_path);
        }
        $this->file_path = $path;
    }
}
