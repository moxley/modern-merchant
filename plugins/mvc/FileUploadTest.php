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
class mvc_FileUploadTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->files = array(
            'product' => array(
                'tmp_name' => array(
                    'images' => array(
                        '1' => '/tmp/product_images_1',
                        '2' => '/tmp/product_images_2'
                    ),
                    'image' => '/tmp/product_image',
                    'media' => array(
                        'images' => array(
                            '1' => '/tmp/product_media_images_1',
                            '2' => '/tmp/product_media_images_2'
                        )
                    )
                ),
                'name' => array(
                    'images' => array(
                        '1' => 'product_images_1.jpg',
                        '2' => 'product_images_2.jpg'
                    ),
                    'image' => 'product_image.jpg',
                    'media' => array(
                        'images' => array(
                            '1' => 'product_media_images_1.jpg',
                            '2' => 'product_media_images_2.jpg'
                        )
                    )
                ),
                'type' => array(
                    'images' => array(
                        '1' => 'image/jpeg',
                        '2' => 'image/jpeg'
                    ),
                    'image' => 'image/jpeg',
                    'media' => array(
                        'images' => array(
                            '1' => 'image/jpeg',
                            '2' => 'image/jpeg'
                        )
                    )
                ),
                'size' => array(
                    'images' => array(
                        '1' => 1000,
                        '2' => 1000
                    ),
                    'image' => 1000,
                    'media' => array(
                        'images' => array(
                            '1' => 1000,
                            '2' => 1000
                        )
                    )
                ),
                'error' => array(
                    'images' => array(
                        '1' => 0,
                        '2' => 0
                    ),
                    'image' => 0,
                    'media' => array(
                        'images' => array(
                            '1' => 0,
                            '2' => 0
                        )
                    )
                ),
            ),
            'image' => array(
                'tmp_name' => '/tmp/image',
                'name' => 'image.jpg',
                'size' => 1000,
                'type' => 'image/jpeg',
                'error' => 0
            )
        );
    }
    
    function testUploadParsing() {
        $converted = mvc_FileUpload::convertFiles($this->files);
        $this->assertTrue(is_array($converted));
        
        $this->assertType('mvc_FileUpload', $converted['product']['images']['1']);
        $this->assertEquals('/tmp/product_images_1', $converted['product']['images']['1']->file);
        $this->assertEquals('product_images_1.jpg', $converted['product']['images']['1']->original);
        
        $this->assertType('mvc_FileUpload', $converted['product']['images']['2']);
        $this->assertEquals('/tmp/product_images_2', $converted['product']['images']['2']->file);
        $this->assertEquals('product_images_2.jpg', $converted['product']['images']['2']->original);
        
        $this->assertType('mvc_FileUpload', $converted['image']);
        $this->assertEquals('/tmp/image', $converted['image']->file);
        $this->assertEquals('image.jpg', $converted['image']->original);
        $this->assertEquals('image/jpeg', $converted['image']->mime_type);
        $this->assertEquals(1000, $converted['image']->size);
        $this->assertEquals(0, $converted['image']->error);

        $this->assertType('mvc_FileUpload', $converted['product']['image']);
        $this->assertEquals('/tmp/product_image', $converted['product']['image']->file);
        $this->assertEquals('product_image.jpg', $converted['product']['image']->original);

        $this->assertType('mvc_FileUpload', $converted['product']['media']['images']['1']);
        $this->assertEquals('/tmp/product_media_images_1', $converted['product']['media']['images']['1']->file);
        $this->assertEquals('product_media_images_1.jpg', $converted['product']['media']['images']['1']->original);
        
        $this->assertType('mvc_FileUpload', $converted['product']['media']['images']['2']);
        $this->assertEquals('/tmp/product_media_images_2', $converted['product']['media']['images']['2']->file);
        $this->assertEquals('product_media_images_2.jpg', $converted['product']['media']['images']['2']->original);
    }
}
