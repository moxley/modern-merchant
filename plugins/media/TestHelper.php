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
class media_TestHelper
{
    /**
     * Record existing files in the product images directory.
     */
    static function setUpImageStorage($obj) {
        $obj->test_dir = mm_getConfigValue('filepaths.public') . '/test';
        $obj->samples_dir = dirname(__FILE__) . '/test';
        $obj->media_dir = $obj->test_dir . '/media';
        $obj->uploads_dir = $obj->test_dir . '/uploads';
        $obj->assertTrue(mkdirp($obj->uploads_dir) ? true : false);
        $obj->assertTrue(mkdirp($obj->media_dir) ? true : false);

        $obj->original_media_dir = mm_getConfigValue('filepaths.media');
        mm_setConfigValue('filepaths.media', $obj->media_dir);
    }
    
    /**
     * Remove new files in the product images directory.
     */
    static function tearDownImageStorage($obj) {
        mm_setConfigValue('filepaths.media', $obj->original_media_dir);
        if (file_exists($obj->test_dir)) {
            rmdirr($obj->test_dir);
        }
    }

    function showImagesDirectory() {
        media_TestHelper::showDirectory($this->media_dir);
    }
    
    function showDirectory($dir) {
        $d = dir($dir);
        if (!file_exists($dir)) {
            echo "[DOES NOT EXIST]\n";
        }
        else {
            while (false !== ($entry = $d->read())) {
                echo "  $entry\n";
            }
        }
    }
    
}
