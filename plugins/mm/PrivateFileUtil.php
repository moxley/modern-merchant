<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_PrivateFileUtil extends mm_ManagedFileUtil
{
    public $offset_path;
    
    /**
     * @param string $offset_path  The path offset that the file utility will work from. If
     *     a plugin is using the utility the offset_path will be the plugin's name. Otherwise,
     *     it should probably be 'mm'
     */
    function __construct($offset_path)
    {
        $this->offset_path = $offset_path;
        $base = $this->getBasePath();
        if (!is_dir($base)) $this->makePath($base);
    }
    
    /**
     * Get the base file path.
     */
    function getBasePath()
    {
        $base = mm_getConfigValue('filepaths.private');
        return "$base/{$this->offset_path}";
    }
}
