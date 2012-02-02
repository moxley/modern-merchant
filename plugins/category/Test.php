<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package category
 */
class category_Test extends PHPUnit_Framework_TestCase
{
    function setUp() {
        category_Category::deleteAll();
        category_CategoryDAO::$cached_categories = array();
        category_CategoryDAO::$cached_by_url_name = array();
        $this->upload_dir = mm_getConfigValue('filepaths.public') . '/test/uploads';
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }

    function tearDown()
    {
        if (file_exists($this->upload_dir)) {
            rmdirr($this->upload_dir);
        }
    }
    
    function assert($condition, $message="") {
        $this->assertTrue($condition ? true : false, $message);
    }
    
    /**
     * Save a new category and fetch it by ID
     */
    function testSaveFetch() {
        // Create
        $category = new category_Category;
        $category->name = "test category";
        $category->description = "(description) This is a test category";
        $category->comment = "This is a comment";
        $category->sortorder = 1;
        $category->keywords = "red,green,blue";
        $this->assert($category->save(), "Category failed to save");
        $this->assert($category->id, "Should have generated an ID");
        
        // Fetch
        $category = category_Category::fetch($category->id);
        $this->assert($category, "Should have fetched the category");
        $this->assertEquals("test category", $category->name);
        $this->assertEquals("(description) This is a test category", $category->description);
        $this->assertEquals("This is a comment", $category->comment);
        $this->assertEquals("red,green,blue", $category->keywords);
        $this->assertEquals(1, $category->sortorder);
    }
    
    /**
     * Test parent-child relationship
     */
    function testParentChild() {
        $parent = new category_Category;
        $parent->name = "parent";
        $this->assert($parent->save());
        
        $child = new category_Category;
        $child->name = "child";
        if (@$this->assign_parent) {
            $child->parent = $parent;
            $this->assertEquals($parent->id, $child->parent_id);
        } else {
            $child->parent_id = $parent->id;
        }
        $this->assert($child->save());
        
        $child = category_Category::fetch($child->id);
        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertType('category_Category', $child->parent);
    }
    
    // Test setParent($parent), getParent()
    function testSetGetParent() {
        $this->assign_parent = true;
        $this->testParentChild();
    }
    
    function buildImage() {
        $this->test_images_dir = realpath(dirname(__FILE__) . "/../media/test");
        $this->image_file = "kitten1_160x120.jpg";
        copy($this->test_images_dir . '/' . $this->image_file, $this->upload_dir . '/' . $this->image_file);
        $this->image_size = 5691;
        $this->image_mime_type = "image/jpeg";
        $this->file_upload = new mvc_FileUpload;
        $this->file_upload->file = $this->upload_dir . '/' . $this->image_file;
        $this->file_upload->mime_type = $this->image_mime_type;
        $this->file_upload->size = $this->image_size;
        $this->file_upload->original = $this->image_file;
        
        $this->image = new media_Media;
    }
    
    // Test image
    function testImage() {
        $this->buildImage();
        
        $category = new category_Category;
        $category->name = "Test Category";
        if (@$this->use_file_upload) {
            $category->image = $this->file_upload;
        }
        else {
            $category->image = $this->image;
        }
        $this->assert($category->_image, "Should have an image object now");
        $this->assert($category->save(), "Failed to save category");
        $this->assert($category->id);
        $this->assert($category->image_id);
        
        $category = category_Category::fetch($category->id);
        $this->assert($category->image_id);
    }
    
    // Test image using FileUpload
    function testUsingFileUpload() {
        $this->use_file_upload = true;
        $this->testImage();
    }
}
