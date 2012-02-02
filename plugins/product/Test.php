<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package product
 */
class product_Test extends PHPUnit_Framework_TestCase
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
    
    public $dao;
    
    function setUp() {
        media_TestHelper::setUpImageStorage($this);
        
        $this->test_images_dir = mm_getConfigValue('filepaths.plugins') . '/media/test';

        foreach (array(1,2) as $i) {
            $this->{"file_upload_$i"} = new mvc_FileUpload;
            $basename = $this->{"image_{$i}_file"};
            $sample_file = $this->test_images_dir . '/' . $basename;
            $uploaded_file = $this->test_dir . '/media/' . $basename;
            copy($sample_file, $uploaded_file);
            $this->{"file_upload_$i"}->file = $uploaded_file;
            $this->{"file_upload_$i"}->mime_type = "image/jpeg";
            $this->{"file_upload_$i"}->size = $this->{"image_{$i}_size"};
            $this->{"file_upload_$i"}->original = $this->{"image_{$i}_file"};
        }
        
        $this->dao = new product_ProductDAO;
    }
    
    function tearDown() {
        media_TestHelper::tearDownImageStorage($this);
    }
    
    function testAdd($options=array()) {
        $this->product = new product_Product;
        $this->product->name = "Test Product";
        $this->product->active = gv($options, 'active', true);
        $this->product->image_uploads[-1] = $this->file_upload_1;
        $user = new user_User;
        $user->username = "testuser"; 
        $this->product->modify_user = $user;
        $this->assertType('user_User', $this->product->modify_user, "\$product->modify_user");
        $this->assertEquals('testuser', $this->product->modify_username, "Username should be 'testuser'");
        $this->assertTrue($this->product->save(), "Failed to save product: " . implode(', ', $this->product->errors));
        
        $this->assertTrue($this->product->id > 0, "product->id is not set");
        $this->assertEquals(gv($options, 'active', true), $this->product->active);
        $images = $this->product->images;
        $this->assertEquals(1, count($images), "Number of product images is incorrect");
        $image = $images[0];
        $this->assertTrue(strlen($image->filename) > 0, "No image filename");
        $this->assertTrue(strlen($image->full_path) > 0, "No image full path");
        $this->assertEquals($this->image_1_size, filesize($image->full_path));

        $this->product = $this->dao->fetch($this->product->id);
        $this->assertEquals('testuser', $this->product->modify_username, 'modify_username does not match');
        $this->assertEquals(gv($options, 'active', true), $this->product->active);

        $this->product->active = !gv($options, 'active', true);
        $this->assertTrue($this->product->save(), "Failed to save product: " . implode(', ', $this->product->errors));
        $this->assertEquals(!gv($options, 'active', true), $this->product->active);
    }

    function testAdd2()
    {
        $this->testAdd(array('active' => false));
    }
    
    function testRemoveImage() {
        $db = mm_getDatabase();
        $this->product = new product_Product;
        $this->product->modify_user = new user_User(array('username' => 'testuser'));
        $this->product->name = "Test Product";
        $this->product->image_uploads = array(-1 => $this->file_upload_1);
        $this->assertTrue($this->product->save(), "Failed to save product: " . implode(', ', $this->product->errors));
        $this->assertEquals(1, $this->getProductImageCount());
        
        $product_values = array('images_to_delete' => array($this->product->images[0]->id => '1'));
        $this->product->property_values = $product_values;
        $this->product->save();
        $this->assertEquals(0, $this->getProductImageCount());
    }
    
    function testAddMultipleImages() {
        $this->product = new product_Product;
        $product_values = array(
            'name' => "Test Product",
            'image_uploads' => array(
                '-1' => $this->file_upload_1,
                '-2' => $this->file_upload_2
            )
        );
        $this->product->modify_user = new user_User(array('username' => 'testuser'));
        $this->product->setPropertyValues($product_values);
        $this->assertTrue($this->product->save(), "Product failed to save: " . implode(', ', $this->product->errors));
        $images = $this->product->images;
        $this->assertEquals(2, count($images), "Number of images");
        $this->assertEquals(0, $images[0]->sortorder, "sortorder for image[0]");
        $this->assertEquals(1, $images[1]->sortorder, "sortorder for image[1]");
        
        $db = mm_getDatabase();
        $row = $db->getOneAssoc("SELECT * FROM mm_media WHERE id=?", array($images[0]->id));
        
        $mdao = new media_MediaDAO;
        $images = $mdao->getListForProductId($this->product->id);
        $this->assertEquals(2, count($images), "Number of images");
        $this->assertEquals($this->product->images[0]->id, $images[0]->id, "image.id");
        $this->assertEquals(0, $images[0]->sortorder, "sortorder for image[0]");
        $this->assertEquals(1, $images[1]->sortorder, "sortorder for image[1]");
    }
    
    function getProductImageCount() {
        $db = mm_getDatabase();
        return (int) $db->getOne("SELECT count(*) FROM mm_media WHERE owner_type=? AND owner_id=?", array('product_Product', $this->product->id));
    }
    
    function testSetPropertyValues()
    {
        $this->product = new product_Product;
        $this->product->property_values = array('description' => 'abc123', 'active' => '1', 'price' => '10.00');
        $this->assertEquals('abc123', $this->product->description);
        $this->assertTrue($this->product->active);
        $this->assertEquals('10.00', $this->product->price);
    }

    function testSetPropertyValues2()
    {
        $this->product = new product_Product;
        $this->product->property_values = array('description' => 'abc123', 'active' => '0', 'price' => '10.00');
        $this->assertEquals('abc123', $this->product->description);
        $this->assertFalse($this->product->active);
        $this->assertEquals('10.00', $this->product->price);
    }
    
    function testSaveDescription() {
        $this->product = new product_Product;
        $this->product->name = "Test Product";
        $this->product->description = "abc123";
        $this->product->price = '10.00';
        $this->product->modify_user = new user_User(array('username' => 'testuser'));
        $this->assertTrue($this->product->save(), "Failed to save product: " . implode(', ', $this->product->errors));
        $this->product = $this->dao->fetch($this->product->id);
        $this->assertEquals('abc123', $this->product->description);
        $this->assertEquals('10.00', $this->product->price);
    }
}
