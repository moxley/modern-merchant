<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Product Controller
 */
class product_Controller extends admin_Controller
{
    private $image_controller;
    private $dao;
    private $cdao;
    
    function __construct() {
        parent::__construct();
        $this->dao = new product_ProductDAO;
        $this->cdao = new category_CategoryDAO;
        $this->edit = true;
    }
    
    function getRootCategory() {
        $dao = new category_CategoryDAO;
        return $dao->getChildren();
    }
    
    function getListParams() {
        $sess = mm_getSession();
        $this->q = '';
        $this->category_id = $this->req('category_id');
        $this->offset = $this->req('offset', 0);
        $req = $this->request;
        if (!isset($this->category_id)) {
            $req = $sess->get('product.list.req');
            if ($req) {
                $this->category_id = gv($req, 'category_id');
                $this->offset = gv($req, 'offset');
            }
        }
        $sess->set('product.list.req', $req);
    }
    
    function runListAction()
    {
        $this->getListParams();

        $this->max_per_page = 50;
        $this->max_links_per = 10;
        $product_dao = new product_ProductDAO;
        if ($this->category_id) {
            $category_dao = new category_CategoryDAO;
            $this->category = $category_dao->fetch($this->category_id);
            $options = array();
            
            if ($this->req('order') && ($parts = preg_split('/\s+/', $this->req('order'))) && ($order = $parts[0]) && in_array($order, array('sku', 'sortorder', 'name', 'price', 'count'))) {
                if (count($parts) > 1 && strtolower($parts[1]) == 'desc') {
                    $order .= ' desc';
                }
                $options['order'] = $order;
            }
            list($this->products, $this->count) = $product_dao->getListForCategoryId(
                $this->category_id,
                $this->offset, $this->max_per_page,
                $options);
        }
        else {
            $this->category = new category_Category(array('name' => "Not Categorized"));
            list($this->products, $this->count) = $product_dao->getListForNoCategory($this->offset, $this->max_per_page);
        }
        $product_dao->attachMediaToProducts($this->products);
        $extra_params = array('a'=>'product.list');
        if ($this->category_id) $extra_params['category_id'] = $this->category_id;
        $this->results_nav = $this->getResultsNav(
            $this->count,
            $this->offset,
            $this->max_per_page,
            $this->max_links,
            $extra_params);
    }
    
    function sortLink($col, $label) {
      $values = getRequest();
      $values['order'] = $col . ($this->req('order')==$col ? '+desc' : '');
      return '<a href="' . $this->adminBaseUrl() . '?' . makeQueryString($values) . '">' . h($label) . '</a>';
    }
    
    function getSearchParams() {
        $sess = mm_getSession();
        $this->q = $this->req('q');
        $this->offset = $this->req('offset', 0);
        $req = $this->request;
        if (!$this->q) {
            $req = $sess->get('product.list.req');
            if ($req) {
                $this->q = gv($req, 'q');
                $this->offset = gv($req, 'offset');
            }
        }
        $sess->set('product.list.req', $req);
    }
    
    function runSearchAction()
    {
        $this->getSearchParams();
        $this->max_per_page = 50;
        $this->max_links = 10;
        list($this->products, $this->count) = $this->dao->findBySearch($this->q, $this->offset, $this->max_per_page);

        $extra_params = array('a'=>'product.search');
        $extra_params['q'] = $this->q;
        $this->results_nav = $this->getResultsNav(
            $this->count,
            $this->offset,
            $this->max_per_page,
            $this->max_links,
            $extra_params);

        $this->setTemplate('product/list');
    }
    
    function getListOrSearch()
    {
        $sess = mm_getSession();
        $req = $sess->get('product.list.req');
        if (@$req['q']) {
            return 'product.search';
        }
        else {
            return 'product.list';
        }
    }
    
    function runDefaultAction()
    {
        $this->setForward($this->getListOrSearch());
    }
    
    function runEditAction()
    {
        $this->product = $this->requireProduct($this->req('product'));
        $this->title = "Edit Product";
    }
    
    /**
     * Called from templates/edit.php
     */
    function productImageTag($image)
    {
        return $this->imageTag($image->getUrlPath(), array('size' => $width . 'x' . $height, 'border' => 0));
    }
    
    function productImageFileField($image) {
        if ($image->sortorder == -999) {
            return $this->fileField("product_image_template");
        }
        else {
            $index = -1;
            if ($image->id) {
                $index = $image->id;
            }
            return $this->fileField("product[image_uploads][$index]");
        }
    }
    
    function productThumbnailTag($image, $options=array())
    {
        $no_cache = array_delete_at($options, 'no_cache');
        $url = $image->url_path;
        if ($no_cache) $url = appendQueryToUrl($url, 't=' . time());
        $size = mm_getSetting('thumbnail.size', '40x40');
        list($width, $height) = explode('x', $size);
        if ($image->width > $width || $image->height > $height) {
            if ($image->width > $image->height) {
                $height = round($image->height * $width / $image->width);
            }
            else {
                $width = round($image->width * $height / $image->height);
            }
        }
        
        $options = array_merge($options, array('width' => $width, 'height' => $height, 'border' => 0));
        $options = array_merge($options, array('class' => 'image-hover'));
        return $this->imageTag($url, $options);
    }
    
    function runUpdateAction()
    {
        $this->requireProduct();
        $this->product->setPropertyValues($this->req('product'));
        $this->product->modify_user = mm_getUser();
        if (!$this->product->save()) {
            $this->addWarnings($this->product->errors);
            $this->setTemplate('product/edit');
        } else {
            $this->addNotice("Product successfully updated.");
            $this->redirectToAction($this->getListOrSearch());
            return false;
        }
    }
    
    function runNewAction()
    {
        $this->product = new product_Product($this->req('product'));
        if ($category_id = $this->req('category_id')) {
            $cdao = new category_CategoryDAO;
            $this->product->addCategory($cdao->fetch($category_id));
        }
        $user = mm_getUser();
        $this->product->modify_username = $user->username;
        $this->title = "New Product";
    }
    
    function runAddAction()
    {
        $this->product = new product_Product($this->req('product'));
        $this->product->modify_user = mm_getUser();
        if (!$this->product->save()) {
            $this->addWarnings($this->product->errors);
            $this->setReturnAction('product.new');
        }
        else {
            $this->addNotice("Added new product (sku={$this->product->sku})");
            if ($this->req('return')) {
                $this->redirectToAction('product.edit', array('id'=>$this->product->id));
                return false;
            }
            $this->redirectToAction('product');
            return false;
        }
    }
    
    function runDeleteAction()
    {
        $id = $this->getRequiredParam('id');
        $dao = new product_ProductDAO;
        $dao->deleteByIds(array($id));
        $this->addNotice("Product successfully deleted.");
        $this->redirectToAction('product');
        return false;
    }
    
    function runUpdateMultipleAction()
    {
        $products = $this->req('products');
        $dbh = mm_getDatabase();
        if (!$products)
        {
            $this->addWarning("No products selected");
            $this->redirectToAction('product');
            return false;
        }
        $sess = $this->getSession();
        $user = mm_getUser();
        list($updated, $deleted) = $this->dao->updateMultiple($products, $user);
        if ($updated) {
            $this->addNotice("$updated products successfully updated");
        }
        if ($deleted) {
            $this->addNotice("$deleted products successfully deleted");
        }
        
        if ($this->req('return')) {
            $this->redirect($this->req('return'));
            return false;
        }
        else {
            $this->redirectToAction('product');
            return false;
        }
    }
    
    function runCancelAction()
    {
        $this->addNotice("Action cancelled");
        $this->redirectToAction('product');
        return false;
    }
        
    /*
     ******************************
     *
     *    Image Management
     *
     ******************************
     */

    function getImageController()
    {
        if( isset($this->image_controller) ) return $this->image_controller;
        $this->image_controller = new media_Controller;
        return $this->image_controller;
    }            
    
    function getImagesOut($product_id=null)
    {
        $dao = new media_MediaDAO;
        return $dao->getListForProductId($product_id);
    }
        
    /**
     * 
     * @param $input array
     * @return mixed  Returns boolean 'TRUE' if success. Throws exception on error.
     */
    function updateImages(&$input)
    {
        $image_controller = $this->getImageController();
        
        // Delete Images
        $image_count = mm_getSetting('images_per_product', 3);
        for($i=1; $i <= $image_count; $i++)
        {
            $name_for_deletion = $image_controller->imageDeleteName($i);
            if (isset($input[$name_for_deletion]))
            {
                $image_controller->deleteImage($input['id'], $i);
            }
        }

        // Insert / Replace Images
        for($i=1; $i <= $image_count; $i++)
        {
            $name = $image_controller->imageName($i);
            if ( isset($_FILES[$name]) && $_FILES[$name]['tmp_name'])
            {
                $this->updateImage($input, $i);
            }
        }

        return TRUE;
    }

    function requireProduct() {
        $id = $this->req('id');
        if ($id) {
            $this->product = $this->dao->fetch($id);
        }
        else if ($sku = $this->req('sku')) {
            $this->product = $this->dao->fetchBySku($sku);
        }
        else {
            throw new Exception("Missing required parameter, 'id' or 'sku'");
        }
        
        if (!$this->product) {
            throw new Exception("Failed to find product");
        }
        return $this->product;
    }

    function preViewFilter() {
        parent::preViewFilter();
        $this->addJavascriptInclude(mm_getConfigValue('urls.plugins') . '/product/product.js');
        $this->addJavascriptInclude(mm_getConfigValue('urls.plugins') . '/product/clickdrag.js');
    }
}
