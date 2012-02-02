<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Managed file access for plugins and the mm framework
 */
abstract class mm_ManagedFileUtil
{
    /**
     * Get the base file path
     */
    abstract function getBasePath();
    
    function getFullPath($relative_file_path)
    {
        return $this->getBasePath() . '/' . $relative_file_path;
    }
    
    function makePath($path)
    {
        if ($path{0} != '/') $full_path = $this->getFullPath($path);
        else $full_path = $path;
        
        if (is_dir($full_path)) return;
        $parent = dirname($full_path);
        if (!is_dir($parent)) {
            $this->makePath($parent);
        }
        if (!@mkdir($full_path)) {
            throw new Exception("Failed to mkdir: $full_path");
        }
        if (!@chmod($full_path, 0777)) {
            throw new Exception("Failed to chmod directory: $full_path");
        }
        
        return $full_path;
    }
}

