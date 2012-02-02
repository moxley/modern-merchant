<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class pricing_PricingDAOTest extends PHPUnit_Framework_TestCase
{
    private $dao;
    private $gen;
    private $cat_dao;
    
    function setUp()
    {
        $this->dao = new pricing_PricingDAO;
        $this->gen = new order_SampleGenerator;
        $this->dao->deleteAll();
        
        $this->cat_dao = new category_CategoryDAO;
        $this->cat_dao->deleteAll();
    }
    
    function testAdd()
    {
        $this->assertEquals(0, $this->dao->getCount());
        $pricing = $this->gen->makePricing();
        $this->dao->add($pricing);
        $this->assertTrue($pricing->id > 0, 'id should be > 0');
        $this->assertEquals(1, $this->dao->getCount());
    }
    
    function testFetch()
    {
        $pricing = $this->gen->makePricing();
        $pricing->name = ' test ';
        $this->dao->add($pricing);
        $fetched = $this->dao->fetch($pricing->id);
        $this->assertEquals($pricing, $fetched);
    }
    
    function testUpdate()
    {
        $pricing = $this->gen->makePricing();
        $this->dao->add($pricing);
        $pricing->name = 'new name';
        $this->dao->update($pricing);
        $fetched = $this->dao->fetch($pricing->id);
        $this->assertEquals($pricing, $fetched);
    }
    
    function testGetCategories()
    {
        $pricing = $this->gen->makePricing();
        $this->dao->add($pricing);
        
        $category = $this->gen->makeCategory();
        $category->name = trim($category->name);
        $cat_dao = new category_CategoryDAO;
        $cat_dao->add($category);
        
        $this->dao->addCategoriesToPricing($pricing, array($category));
        
        $categories = $this->dao->getCategories($pricing);
        $this->assertEquals(1, count($categories), 'number of categories');
        $this->assertEquals($category->id, $categories[0]->id, "category_id doesn't match");
    }
    
    function testAddPricingToCategories()
    {
        $this->dao->deleteAll();
        $this->assertEquals(0, $this->dao->getPricingCategoryCount());
        $pricing = $this->gen->makePricing();
        $this->dao->add($pricing);
        $cat_dao = new category_CategoryDAO;
        
        $categories = array();
        $cat_count = 3;
        
        for ($i=0; $i < $cat_count; $i++) {
            $category = $this->gen->makeCategory();
            $cat_dao->add($category);
            $categories[] = $category;
        }

        $this->dao->addCategoriesToPricing($pricing, $categories);
        $this->assertEquals($cat_count, $this->dao->getPricingCategoryCount());
    }
    
    function testDelete()
    {
        $pricing = $this->gen->makePricing();
        $this->dao->add($pricing);
        $this->assertEquals(1, $this->dao->getCount());
        $this->dao->delete($pricing);
        $this->assertEquals(0, $this->dao->getCount());
    }
    
    function testDeleteCategoryPricings()
    {
        $pricing = $this->gen->makePricing();
        $this->dao->add($pricing);
        $category = $this->gen->makeCategory();
        $this->cat_dao->add($category);
        $category1 = $this->gen->makeCategory();
        $this->cat_dao->add($category1);
        $this->dao->addCategoriesToPricing($pricing, array($category, $category1));
        $this->assertEquals(2, $this->dao->getPricingCategoryCount(),
            'product-category count');
        $this->dao->deleteCategoryIdsFromPricing(
            $pricing, array($category->id, $category1->id));
        $this->assertEquals(0, $this->dao->getPricingCategoryCount(),
            'product-category count');
    }
    
    function testMakeProductIdToPricingLookup()
    {
        $products = array();
        $prod_count = 2;
        $pricing_count = 2;
        $cat_count = 2;
        $prod_dao = new product_ProductDAO;
        $cat_dao = new category_CategoryDAO;
        $product_ids = array();
        $pricings = array();
        
        // Make 2 products
        for ($i=0; $i < $prod_count; $i++) {
            $product = $this->gen->makeProduct();
            $prod_dao->add($product);
            $products[] = $product;
            $product_ids[] = $product->id;
            
            // Make 2 categories
            for ($j=0; $j < $cat_count; $j++) {
                $category = $this->gen->makeCategory();
                $cat_dao->add($category);
                $prod_dao->addProductToCategories($product, array($category));
                
                // Make 2 pricings
                for ($x = 0; $x < $pricing_count; $x++) {
                    $pricing = $this->gen->makePricing();
                    $pricings[] = $pricing;
                    $this->dao->add($pricing);
                    $this->dao->addCategoriesToPricing($pricing, array($category));
                }
            }
        }
        
        $lookup = $this->dao->makeProductIdToPricingLookup($product_ids);
        $this->assertEquals($prod_count, count($lookup), 'number of products');
        $fetched_pricings = $lookup[$product_ids[0]];
        $this->assertEquals(count($pricings) / count($products), count($fetched_pricings),
            'number of pricings');
        $this->assertEquals($pricings[0], $fetched_pricings[0], 'pricing');
    }
    
    function testMakeProductIdToPricingLookup_PricingSharedByCategories()
    {
        $products = array();
        $prod_count = 2;
        $pricing_count = 2;
        $cat_count = 2;
        $prod_dao = new product_ProductDAO;
        $cat_dao = new category_CategoryDAO;
        $product_ids = array();
        $pricings = array();
        
        // Make 2 products
        for ($i=0; $i < $prod_count; $i++) {
            $product = $this->gen->makeProduct();
            $prod_dao->add($product);
            $products[] = $product;
            $product_ids[] = $product->id;
            
            // Pricing to be shared by categories
            $pricing = $this->gen->makePricing();
            $pricings[] = $pricing;
            $this->dao->add($pricing);

            // Make 2 categories
            for ($j=0; $j < $cat_count; $j++) {
                $category = $this->gen->makeCategory();
                $cat_dao->add($category);
                $prod_dao->addProductToCategories($product, array($category));
                $this->dao->addCategoriesToPricing($pricing, array($category));
            }
        }
        
        $lookup = $this->dao->makeProductIdToPricingLookup($product_ids);
        $this->assertEquals($prod_count, count($lookup), 'number of products');
        $fetched_pricings = $lookup[$product_ids[0]];
        $this->assertEquals(count($pricings) / count($products), count($fetched_pricings),
            'number of pricings');
        $this->assertEquals($pricings[0], $fetched_pricings[0], 'pricing');
    }
    
}
