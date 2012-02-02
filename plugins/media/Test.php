<?php
/**
 * @package media
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package media
 */
class media_Test extends PHPUnit_Framework_TestCase
{
    public $image_1_file = "kitten1_160x120.jpg";
    public $image_1_size = 5691;
    public $image_1_mime_type = "image/jpeg";
    public $image_1_width = 160;
    public $image_1_height = 120;
    
    public $image_2_file = "kitten2_160x120.jpg";
    public $image_2_size = 4824;
    public $image_2_mime_type = "image/jpeg";
    public $image_2_width = 160;
    public $image_2_height = 120;
    
    function setUp()
    {
        media_TestHelper::setUpImageStorage($this);
        
        // Create product
        $this->product = new product_Product;
        $this->product->name = "Test Product";
        $this->product->modify_username = 'testuser';
        $this->product->save();
        $this->assertTrue($this->product->id > 0, "Missing product->id");
        
        // File upload
        foreach (array(1, 2) as $n) {
            $basename = $this->{'image_' . $n . '_file'};
            $var = "file_upload_$n";
            copy($this->samples_dir . '/' . $basename,
                 $this->uploads_dir . '/' . $basename);
            $this->$var = new mvc_FileUpload;
            $this->$var->file = $this->uploads_dir . '/' . $basename;
            $this->$var->mime_type = "image/jpeg";
            $this->$var->size = $this->image_1_size;
            $this->$var->original = $this->image_1_file;
        }

        // Create image
        $this->image = new media_Media;
        $this->image->owner = $this->product;
        $this->image->file_upload = $this->file_upload_1;
        $this->assertTrue($this->image->owner_id > 0, "Missing owner_id");
        $this->assertEquals('product', $this->image->owner_abstract_type);
        $this->assertEquals('mm_product', $this->image->owner_table);
        $this->assertEquals($this->image_1_width, $this->image->width);
        $this->assertEquals($this->image_1_height, $this->image->height);
    }
    
    function tearDown()
    {
        media_TestHelper::tearDownImageStorage($this);
    }
    
    function assertImage()
    {
        $this->assertTrue($this->image ? true : false, "Failed to find image for id='{$this->image->id}'");
        $this->assertTrue($this->image->owner_id > 0, "Missing owner_id");
        $this->assertEquals($this->product->id, $this->image->owner_id, "Owner ID of image");
        $this->assertEquals('product', $this->image->owner_abstract_type);
        $this->assertEquals('mm_product', $this->image->owner_table);
        $this->assertEquals('product_Product', $this->image->owner_type, "Owner type of image");
        
        //$this->assertEquals($this->image_1_size, strlen($this->image->data), "Size of stored image");
        $this->assertEquals("product.{$this->image->owner_id}.{$this->image->id}.jpg", $this->image->filename, "Image filename");
        $this->assertEquals(0, strlen($this->image->data), "Size of stored image");
        
        $this->assertEquals($this->image_1_mime_type, $this->image->mime_type, "Mime type of image");
        $this->assertEquals($this->image_1_width, $this->image->width);
        $this->assertEquals($this->image_1_height, $this->image->height);
        
        $this->assertTrue(file_exists($this->image->full_path), "File doesn't exist");
        $this->assertContains($this->image->filename, $this->image->url_path, "URL path");
    }
    
    function testPaths()
    {
        $this->image->id = 1;
        $this->image->filename = "test_move_upload.jpg";
        //echo "file: " . $this->file_upload_1->file . "\n";
        //echo "base_path: " . $this->image->base_path . "\n";
        //echo "full_path: " . $this->image->full_path . "\n";
        //echo "id: " . $this->image->id . "\n";
        $full = explode('/', str_replace('\\', '/', $this->image->full_path));
        $this->assertEquals("test_move_upload.jpg", array_pop($full));
        $this->assertEquals("0", array_pop($full));
        $this->assertEquals("1", array_pop($full));
        //media_TestHelper::showImagesDirectory();
    }
    
    function testMoveUpload()
    {
        $this->image->id = 1;
        $this->image->filename = "test_move_upload.jpg";
        $this->assertTrue($this->image->file_upload->moveTo($this->image->full_path) ? true : false, implode(', ', $this->image->file_upload->errors));
        $this->assertTrue(file_exists($this->image->full_path));
        //media_TestHelper::showImagesDirectory();
    }
    
    function testAddImage()
    {
        // Store it
        $this->assertTrue($this->image->save(), "Model Error: " . implode(', ', $this->image->errors));
        
        // Check stored image
        $this->image = $this->image->dao->fetch($this->image->id);
        $this->assertImage();
        
        //echo "full_path: " . $this->image->full_path;
    }
    
    function testUpdateImage()
    {
        // Store it
        $this->assertTrue($this->image->save(), "Model Error: " . implode(', ', $this->image->errors));
        
        // Check stored image
        $this->image = $this->image->dao->fetch($this->image->id);

        $this->image->file_upload = $this->file_upload_2;
        $this->image->save();
        clearstatcache();
        $this->assertEquals($this->image_2_size, filesize($this->image->full_path));
        $this->image = $this->image->dao->fetch($this->image->id);
        clearstatcache();
        $this->assertEquals($this->image_2_size, filesize($this->image->full_path));
    }
}
