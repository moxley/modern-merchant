<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package category
 */
class catalog_CatalogControllerTest extends PHPUnit_Framework_TestCase
{
    private $controller;
    private $gen;
    private $cat_dao;
    private $prod_dao;
    
    function setUp()
    {
        $this->controller = new catalog_Controller;
        $this->cat_dao = new category_CategoryDAO;
        $this->prod_dao = new product_ProductDAO;
        $this->gen = new order_SampleGenerator;
    }
    
    function testRunProductsAction()
    {
        $category = $this->gen->makeCategory();
        $this->assertTrue($category->save(), "Failed to save category: " . implode(', ', $category->errors));
        $product = $this->gen->makeProduct();
        $this->prod_dao->add($product);

        $media = $this->gen->makeMedia();
        $media->owner = $product;
        $media_dao = new media_MediaDAO;
        $media_dao->add($media);

        $this->prod_dao->addProductToCategories($product, array($category));
        $request = array('category_id' => $category->id);
        $this->controller->setRequest($request);
        ob_start();
        $this->controller->runProductsAction();
        ob_end_clean();
    }

    function testRunProductDetailAction()
    {
        $category = $this->gen->makeCategory();
        $this->cat_dao->add($category);

        $product = $this->gen->makeProduct();
        $this->prod_dao->add($product);

        $media = $this->gen->makeMedia();
        $media->owner = $product;
        $media_dao = new media_MediaDAO;
        $media_dao->add($media);

        $this->prod_dao->addProductToCategories($product, array($category));
        $request = array('sku' => $product->sku);
        $this->controller->setRequest($request);
        $this->controller->runProductDetailAction();
    }
}
