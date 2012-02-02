<?php
/**
 * @package bulkimages
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class bulkimages_ImportUtil extends mvc_Model
{
    private $source_path;
    
    function getSourceFileCount()
    {
        $source_dir = $this->getSourcePath();
        if (!$source_dir) {
            throw new Exception("Configuration error: 'source path' not set");
        }
        $dir = dir($source_dir);
        $count = 0;
        while( ($entry = $dir->read()) !== FALSE )
        {
            if ($entry{0} == '.') continue;
            $full_path = $source_dir.'/'.$entry;
            if (is_file($full_path)) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Get a list of the files to import. Each file path is relative to <tt>getSourcePath()</tt>.
     * 
     * @return array The list of files
     */
    function getImportList()
    {
        $source_dir = $this->getSourcePath();
        if (!$source_dir) {
            throw new Exception("Configuration error: 'source path' not set");
        }
        $dir = dir($source_dir);
        $list = array();
        while( ($entry = $dir->read()) !== FALSE )
        {
            if ($entry{0} == '.') continue;
            $full_path = $source_dir.'/'.$entry;
            if (is_file($full_path)) $list[] = $entry;
        }
        return $list;
    }
    
    function getSourcePath()
    {
        if ($this->source_path) return $this->source_path;
        $file_util = new mm_PrivateFileUtil('bulkimages/upload');
        return $file_util->getBasePath();
    }
    
    function setSourcePath($path)
    {
        $this->source_path = $path;
    }
    
    function import(&$count)
    {
        $import_dir = $this->getSourcePath();
        $list = $this->getImportList();
        $pdao = new product_ProductDAO;
        $dao = new media_MediaDAO;
        $count = 0;
        foreach ($list as $filename)
        {
            $parts = explode('.', $filename);
            if (count($parts) != 3) {
                $this->addError("Cannot parse filename: $filename");
                continue;
            }
            $sku = $parts[0];
            $product = $pdao->fetchBySku($sku);
            if (!$product) {
                $this->addError("Failed to find product for sku=$match[1]");
                continue;
            }
            $product_id = $product->id;
            $sortorder = $parts[1];
            if ($sortorder) $sortorder--;
            $ext = $parts[2];
            $full_path = $this->getSourcePath() . '/' . $filename;
            
            try {
                if ($media = $dao->fetchForOwner('product', $product_id, $sortorder)) {
                    $media->file_upload = $full_path;
                    if (!$dao->update($media)) {
                        $this->addErrors($dao->errors);
                        continue;
                    }
                }
                else {
                    $media = new media_Media;
                    $media->file_upload = $full_path;
                    $media->sortorder = $sortorder;
                    $media->owner = $product;
                    if (!$dao->add($media)) {
                        $this->addErrors($dao->errors);
                        continue;
                    }
                }
                //unlink($full_path);
            }
            catch (Exception $e) {
                $this->addError("File: $filename: " . $e->getMessage());
                continue;
            }
            
            $count++;
        }
    }
}
