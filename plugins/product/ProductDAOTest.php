<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package product
 */
class product_ProductDAOTest extends PHPUnit_Framework_TestCase
{
    private $dao;
    private $gen;
    private $cat_dao;
    
    function setUp()
    {
        $this->gen = new order_SampleGenerator;
        $this->dao = new product_ProductDAO;
        $this->dao->deleteAll();
        $this->cat_dao = new category_CategoryDAO;
        $this->cat_dao->deleteAll();
    }
    
    function testAdd()
    {
        $this->assertEquals(0, $this->dao->getCount());
        $product = $this->gen->makeProduct();
        $this->dao->add($product);
        $this->assertEquals(1, $this->dao->getCount());
    }
    
    function testFetch()
    {
        $product = $this->gen->makeProduct();
        $this->dao->add($product);
        $fetched = $this->dao->fetch($product->id);
        $product->_modify_user = null;
        $this->assertEquals($product, $fetched);
    }
    
    function testAddProductToCategories()
    {
        $product = $this->gen->makeProduct();
        $this->dao->add($product);
        
        $category = $this->gen->makeCategory();
        $cat_dao = new category_CategoryDAO;
        $cat_dao->add($category);

        list($products, $count) = $this->dao->getListForCategory($category, 0, 5);
        $this->assertEquals(0, count($products), 'number of products in category');
        $this->assertEquals(0, $count, 'number of products in category');

        $this->dao->addProductToCategories($product, array($category));
        list($products, $count) = $this->dao->getListForCategory($category, 0, 5);
        $this->assertEquals(1, count($products), 'number of products in category');
        $this->assertEquals(1, $count, 'number of products in category');
    }
    
    function testGetDescendantProducts()
    {
        $product = $this->gen->makeProduct();
        $this->dao->add($product);
        $category = $root_category = $this->gen->makeCategory();
        $this->cat_dao->add($category);
        $this->dao->addProductToCategories($product, array($category));
        $category = $this->gen->makeCategory();
        $category->parent_id = $root_category->id;
        $this->cat_dao->add($category);
        $product = $this->gen->makeProduct();
        $this->dao->add($product);
        $this->dao->addProductToCategories($product, array($category));
        
        list($products, $count) = $this->dao->getDescendantProducts($root_category->id, 0, 100);
        $this->assertEquals(2, count($products), 'number of products');
        $this->assertEquals(2, $count, 'number of products');
    }
    
    function testFindBySearch()
    {
        $product_match = $this->gen->makeProduct();
        $product_match->name = "This is a test name";
        $this->dao->add($product_match);
        $product_no_match = $this->gen->makeProduct();
        $this->dao->add($product_no_match);
        
         list($products, $count) = $this->dao->findBySearch('test', 0, 10);
        $this->assertEquals(1, $count, "Result count");
        $this->assertEquals(1, count($products), "Number in list");
    }    
}
