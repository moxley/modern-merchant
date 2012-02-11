<?php
/**
 * @package media
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
    
/**
 * Media Data Access Object
 * @package media
 */
class media_MediaDAO extends mvc_DataAccess
{
    private static $by_id_cache = array();
    private $cached_database = null;
    private $media_columns = "m.id, m.owner_id, m.owner_type, m.data, m.name, m.description, m.width, m.height, m.filename, m.mime_type, m.sortorder";
    
    function getFullPath($owner_abstract_type, $filename) {
        $path = mm_getConfigValue('filepaths.images.' . $owner_abstract_type);
        if (!$path) {
            throw new mm_IllegalArgumentException("Unknown \$media->owner_abstract_type: " . $owner_abstract_type);
        }
        return $path . DS . $filename;
    }

    /**
     * Save a media object.
     *
     * @param media_Media $media The media object to save
     */
    function save($media)
    {
        if ($media->id) {
            return $this->update($media);
        }
        else {
            return $this->add($media);
        }
    }
    
    /**
     * Add a media domain object and its associated file data.
     *
     * @param media_Media $media
     * @return void
     */
    function add($media)
    {
        // Validation
        if (!$media->valid) {
            $this->addErrors($media->errors);
            return false;
        }
        
        $db = mm_getDatabase();
        $sql = "INSERT INTO mm_media "
            . " (owner_id, owner_type,"
            . " filename,"
            . "  name, description, width, height,"
            . "  mime_type, sortorder)"
            . " VALUES ("
            . intval($media->owner_id)
            . "," . dq($media->owner_type)
            . "," . dq($media->filename)
            . "," . dq($media->name)
            . "," . dq($media->description)
            . "," . intval($media->width)
            . "," . intval($media->height)
            . "," . dq($media->mime_type)
            . "," . intval($media->sortorder)
            . ")";
        $db->execute($sql);
        $media->id = $id = $db->lastInsertId();
        if (!$media->filename) $media->filename = $media->generateFilename();
        if (!$this->moveOrDelete($media)) {
            // Roll back
            $media->delete();
            return false;
        }
        else {
            // Update the record with the generated filename
            $media->filename = $media->generateFilename();
            $db->execute("UPDATE mm_media SET filename=? WHERE id=?", array($media->filename, $media->id));
            return true;
        }
    }
    
    /**
     * Move a file upload to a permanent location.
     *
     * @return boolean Returns true on success
     */
    function moveOrDelete($media)
    {
        // Move uploaded file to permanent location
        if ($media->file_upload) {
            if (is_string($media->file_upload)) {
                mm_log("Copying: \$media->file_upload: $media->file_upload, \$media->full_path: $media->full_path");
                
                $dest_dir = dirname($media->full_path);
                if (!file_exists($dest_dir)) {
                    mm_log("Creating directory $dest_dir");
                    if (!mkdirp($dest_dir)) {
                        $this->addError("Failed to create directory");
                        return false;
                    }
                }
                
                if (!copy($media->file_upload, $media->full_path)) {
                    $this->addErrors("Failed to copy media file from $media->file_upload to $media->full_path");
                    return false;
                }
                unlink($media->file_upload);
            }
            else {
                if (!$media->file_upload->moveTo($media->full_path)) {
                    $media->addErrors($media->file_upload->errors);
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Update a persistent media domain object and its associated file data.
     * 
     * @param media_Media    $media
     * @return void
     */
    function update($media)
    {
        if (!$media->valid) {
            $this->addErrors($media->errors);
            return false;
        }
        
        $path = mm_getConfigValue('filepaths.media');
        if (!$path) {
            throw new mm_IllegalArgumentException("No path defined for filepaths.media");
        }
        $new_filename = $media->generateFilename();
        if ($media->filename != $new_filename) {
            $this->deleteFile($media);
            $media->filename = $new_filename;
        }
        
        $dbh = mm_getDatabase();
        $sql = "UPDATE mm_media "
            . " SET name=" . dq($media->name)
            . ", description=" . dq($media->description)
            . ", width=" . intval($media->width)
            . ", height=" . intval($media->height)
            . ", filename=" . dq($media->filename)
            . ", mime_type=" . dq($media->mime_type)
            . ", sortorder=" . intval($media->sortorder)
            . " WHERE id=" . intval($media->id);
        $dbh->query($sql);
        
        $res = $this->moveOrDelete($media);
        if (!$res) {
            return false;
        }
            
        //$this->addToCache($media);
        
        return true;
    }
    
    //function fetch($id) {
    //    $media = gv(self::$by_id_cache, $id);
    //    if ($media) return $media;
    //    $sql = "select " . $this->media_columns . " from mm_media m where m.id=" . intval($id);
    //    $row = mm_getDatabase()->getOneAssoc($sql);
    //    if (!$row) {
    //        return null;
    //    }
    //    $media = $this->parseRow($row);
    //    //$this->addToCache($media);
    //        
    //    return $media;
    //}
    
    function fetchByFilename($filename)
    {
        $sql = "select " . $this->media_columns . " from mm_media m where m.filename=" . dq($filename);
        $res = mm_getDatabase()->query($sql);
        $row = $res->fetchAssoc();
        if (!$row) return null;
        $media = new media_Media;
        $this->parseRow($row, $media);
        $this->addToCache($media);
        return $media;
    }
        
    function getProductFileData($media)
    {
        $base = mm_getConfigValue('filepaths.images.product');
        return file_get_contents($base . '/' . $media->filename);
    }

    function addToCache(media_Media $media) {
        self::$by_id_cache[$media->id] = $media;
    }
    
    function parseRow($row, $options=null) {
        if (!$row) {
            return null;
        }

        if (!isset($options)) {
            $options = array();
        }
        else if (is_object($options)) {
            $options = array('model' => $options);
        }
        
        if (!($media = gv($options, 'object'))) {
            $media = new media_Media;
        }
        
        $media->id          = $row['id'];
        $media->_owner_id   = $row['owner_id'];
        $media->_owner_type = $row['owner_type'];
        $media->name        = $row['name'];
        $media->description = $row['description'];
        $media->width       = $row['width'];
        $media->height      = $row['height'];
        $media->_filename   = $row['filename'];
        $media->mime_type   = $row['mime_type'];
        $media->data        = $row['data'];
        $media->sortorder   = (int) $row['sortorder'];
        if (!$media->_filename) $media->_filename = $media->generateFilename();
        
        return $media;
    }
    
    function delete($media) {
        if (!$media->id) {
            throw new mm_IllegalArgumentException("Cannot delete image with an empty id");
        }
        $sql = "delete from mm_media where id=?";
        $dbh = mm_getDatabase();
        $stmt = $dbh->prepare($sql);
        $stmt->execute(array($media->id));
        $this->deleteFile($media);
        $this->deleteFromCache($media->id);
        
        $dbh->execute("UPDATE mm_media SET sortorder = sortorder - 1 WHERE owner_type = ? AND owner_id = ? AND sortorder > ?", array($media->owner_type, $media->owner_id, $media->sortorder));
    }
    
    function fetchForOwner($owner_abstract_type, $owner_id, $sortorder) {
        $m = $this->fetchFromCacheForOwner($owner_abstract_type, $owner_id, $sortorder);
        if ($m) return $m;
        $sql = "select " . $this->media_columns . " from mm_media m where"
            . " owner_id=" . intval($owner_id)
            . " and owner_type=" . ($owner_abstract_type == "category" ? "'category_Category'" : "'product_Product'")
            . " and m.sortorder=" . intval($sortorder);
        $row = mm_getDatabase()->getOneAssoc($sql);
        if (!$row) return null;
        $m = $this->parseRow($row);
        $this->addToCache($m);
        return $m;
    }
    
    function fetchFromCacheForOwner($owner_abstract_type, $owner_id, $sortorder) {
        foreach (self::$by_id_cache as $id => $m) {
            if ($m->owner_abstract_type != $owner_abstract_type) continue;
            if ($m->owner_id != $owner_id) continue;
            if ($m->sortorder != $sortorder) continue;
            return $m;
        }
        return null;
    }
    
    function deleteFile($media) {
        $path = $media->getFullPath();
        if (!$path) return;
        if (!file_exists($path)) return;
        if (!unlink($path)) {
            throw new mm_FileAccessException($path);
        }
        mm_log("Deleted $path");
        return true;
    }
    
    protected function deleteFromCache($id)
    {
        if (array_key_exists($id, self::$by_id_cache)) {
            unset(self::$by_id_cache[$id]);
        }
    }
    
    function deleteRecordForId($id) {
        $sql = "delete from mm_media where id=" . intval($id);
        $res = mm_getDatabase()->query($sql);
        $this->deleteFromCache($id);
    }
    
    function getListForProductId($id) {
        return $this->getListForProductIds(array($id));
    }
    
    function getListForProductIds($ids) {
        $dbh = mm_getDatabase();
        $clean_ids = cleanIntList($ids);
        $query = "SELECT " . $this->media_columns . " FROM mm_media m" .
            " WHERE m.owner_type = 'product_Product'" .
            " AND m.owner_id in ($clean_ids)" . 
            " ORDER BY owner_id, sortorder";
        return $this->fetchAll($dbh->query($query));
    }
    
    function fetchAll($rs) {
        $media_array = array();
        while ($row = $rs->fetchAssoc()) {
            $media_array[] = $this->parseRow($row);
        }
        $rs->free();
        return $media_array;
    }
    
    function getAll() {
        $query = "select " . $this->media_columns . " from mm_media m";
        $db = mm_getDatabase();
        return $this->fetchAll($db->query($query));
    }
}
