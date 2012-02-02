<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Controller object responsible for returning lists of products.
 */
class catalog_Controller extends mvc_PublicController
{
    public $max_links = 10;
    public $max_results = 10;
    public $dao;
    public $cdao;
    
    function __construct($args=null)
    {
        parent::__construct($args);
        $this->dao = new product_ProductDAO;
        $this->cdao = new category_CategoryDAO;
    }
    
    /*
     ***************************
     * Action definitions
     ***************************
     */
    
    function runDefaultAction()
    {
        $name = 'actions.catalog.default';
        $action = mm_getSetting($name,
                mm_getConfigValue($name, 'products'));
        $this->setForward($action);
    }
    
    function runProductsAction()
    {
        $this->offset = (int) $this->req('offset');
        $this->max_results = mm_getSetting('plugins.catalog.products_per_page', 20);
        $this->max_links = 10;
        
        // Product category
        $category_id = $this->req('category_id');
        if (!$category_id) $category_id = mm_getSetting('catalog.default_category');
        if (!$category_id) {
            throw new Exception("No category specified, and no default category found");
        }
        $this->category = $this->cdao->fetch($category_id);
        
        // Titles
        $this->title = "Products > " . $this->category->name;
        if (!$this->category) {
            throw new Exception("No category found for id=$category_id");
        }

        list($this->products, $this->count) = $this->dao->getDescendantProducts($this->category->id,
            $this->offset, $this->max_results,
            array('where' => '(available_on IS NULL OR available_on <= NOW()) AND active > 0'));
        
        $extra_params = array('a' => 'catalog.products', 'category_id' => $this->category->id);
        $this->results_nav = $this->getResultsNav(
            $this->count,
            $this->offset, 
            $this->max_results,
            $this->max_links,
            $extra_params);
        $this->setTemplate(mm_getSetting('templates.catalog.product.list'));
    }
    
    function runSearchAction()
    {
        $this->q = $this->req('q');
        $this->offset = (int) $this->req('offset');
        $this->max_results = mm_getSetting('plugins.catalog.products_per_page', 20);
        $this->max_links = 10;

        $category_id = $this->req('category_id');
        if ($category_id) {
            $cdao = new category_CategoryDAO;
            $this->category = $cdao->fetch($category_id);
        }

        $result = $this->dao->findBySearch(
            $this->q,
            $this->offset,
            $this->max_results,
            array('where' => '(available_on IS NULL OR available_on <= NOW()) AND active > 0',
                'category_id' => $category_id)
        );
        $this->products = $result[0];
        $this->count = $result[1];
        $extra_params = array('a' => 'catalog.search', 'q' => $this->q);
        $this->results_nav = $this->getResultsNav(
              $this->count,
              $this->offset, 
              $this->max_results,
              $this->max_links,
              $extra_params
              );
        $this->render(mm_getSetting('templates.catalog.product.list'));
        $this->setTemplate(false);
    }

    function runCategoryGridAction()
    {
    }
    
    function runCategoriesAction()
    {
        $name = 'catalog.root_category';
        $id = $this->req('id',
            mm_getSetting($name,
                mm_getConfigValue($name,
                    0)));
        $categories = $this->cdao->getChildren($id);
        $this->categories = $categories;

        $this->render(mm_getSetting('templates.catalog.category.list'));
        $this->setTemplate(false);
    }
    
    function runProductDetailAction()
    {
        $id = $this->req('id');
        $sku = $this->req('sku');
        if (!$id && !$sku)
        {
            $this->addWarning("An id or sku is required for this page.");
            $this->setForward('catalog.error');
            return;
        }
        
        if ($id) {
            $this->product = $this->dao->fetch($id);
            if (!$this->product) {
                $this->addWarning("No product found for id=$id");
            }
        }
        else {
            $this->product = $this->dao->fetchBySku($sku);
            if (!$this->product) {
                $this->addWarning("No product found for sku=$sku");
            }
        }
        
        if ($this->getWarnings()) {
            $this->setForward('error');
            return false;
        }

        $this->title = $this->product->name;
    }

    function categoryGridBegin($params=array()) {

    }

    function categoryGridEnd() {

    }
    
}
