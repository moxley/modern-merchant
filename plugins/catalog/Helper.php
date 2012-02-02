<?php
class catalog_Helper
{
    public $controller;
    
    function __construct($controller)
    {
        $this->controller = $controller;
    }
    
    function showProducts($category_id)
    {
        if ($this->getProducts($category_id) !== false) {
            $this->catalogController->render('catalog/products');
        }
    }
    
    function getProducts($category_id, $max_results=null)
    {
        if (!$category_id) {
            echo "<p>Missing \$category_id parameter in call to getProducts()</p>";
            return false;
        }
        $this->catalogController = new catalog_Controller;

        $this->catalogController->offset = (int) $this->catalogController->req('offset');
        $this->catalogController->max_results = isset($max_results) ? $max_results : mm_getSetting('plugins.catalog.products_per_page', 20);
        $this->catalogController->max_links = 10;

        $cdao = new category_CategoryDAO;
        $this->catalogController->category = $cdao->fetch($category_id);
        if (!$this->catalogController->category) {
            echo "<p>Category '$category_id' not found</p>\n";
            return false;
        }
        
        $pdao = new product_ProductDAO;
        list($this->catalogController->products, $this->catalogController->count) = $pdao->getDescendantProducts(
            $this->catalogController->category->id,
            $this->catalogController->offset,
            $this->catalogController->max_results,
            array('where' => '(available_on IS NULL OR available_on <= NOW()) AND active > 0'));
        
        $extra_params = array('a' => 'catalog.products', 'category_id' => $this->catalogController->category->id);
        $this->catalogController->results_nav = $this->catalogController->getResultsNav(
            $this->catalogController->count,
            $this->catalogController->offset, 
            $this->catalogController->max_results,
            $this->catalogController->max_links,
            $extra_params);

        return $this->catalogController->products;
    }
}
