<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Product domain object
 */
class product_Product extends mvc_Model
{
    /**
     * @var int
     */
    public $id;
    
    /**
     * @var int
     */
    public $created_on;
    
    /**
     * @var int
     */
    public $modify_date;

    public $_modify_user;

    public $modify_username;
    
    public $sku;
    
    /**
     * @var boolean
     */
    public $sku_same_as_id = true;
    
    /**
     * @var int
     */
    public $sortorder = null;
    
    public $name;
    
    /**
     * @var boolean
     */
    public $active = false;
    
    public $description;
    
    public $comment;
    
    public $price;
    
    public $count = null;
    
    public $weight;
    
    public $keywords = "";
    
    /**
     * @var int Unix timestamp
     */
    public $_available_on;
    
    public $_images;
    //public $images_to_save = array();
    public $images_to_delete = array();
    public $image_uploads = array();
    
    /**
     * @var array
     */
    public $_pricings = null;
    
    public $_categories = null;
    
    function __construct($values=array())
    {
        $this->created_on = $this->available_on = $this->modify_date = time();
        parent::__construct($values);
    }
    
    function getModifyUser()
    {
        return $this->_modify_user;
    }
    
    function setModifyUser($user)
    {
        if ($user) {
            if (!is_object($user)) {
                throw new Exception("Trying to set modify_user with a non-object");
            }
            $this->_modify_user = $user;
            $this->modify_username = $user->username;
        }
        else {
            $this->_modify_user = null;
            $this->modify_username = null;
        }
    }
    
    function setAvailableOn($date)
    {
        if (is_numeric($date)) {
            $this->_available_on = $date;
        }
        elseif ($date) {
            $this->_available_on = strtotime($date);
        }
        else {
            $this->_available_on = null;
        }
    }
    
    function getAvailableOn()
    {
        return $this->_available_on;
    }
    
    function getAdjustedPrice()
    {
        if (!$this->pricings) return $this->price;
        $adds = array();
        $multiplications = array();
        $overrides = array();
        foreach ($this->pricings as $pricing) {
            switch ($pricing->type) {
            case 'multiply':
                $multiplications[] = $pricing;
                break;
            case 'add':
                $adds[] = $pricing;
                break;
            case 'override':
                $overrides[] = $pricing;
                break;
            }
        }
        $price = $this->price;
        foreach ($overrides as $pricing) {
            $price = $pricing->apply($price);
        }
        foreach ($multiplications as $pricing) {
            $price = $pricing->apply($price);
        }
        foreach ($adds as $pricing) {
            $price = $pricing->apply($price);
        }
        return number_format($price, 2);
    }
    
    function getPercentOff() {
        if ($this->price == 0) return 0;
        return round(100 * (($this->price - $this->adjusted_price) / $this->price));
    }
    
    /**
     * @obsolete
     */
    function getIsAvailable() {
        return $this->active
            && (!is_numeric($this->count) || $this->count > 0)
            && (!$this->available_on || $this->available_on <= time())
            && ($this->adjusted_price > 0);
    }
    
    function getIsVisible() {
        return $this->active && (!$this->available_on || $this->available_on <= time());
    }
    
    function getIsForSale() {
        return $this->is_visible
            && (!is_numeric($this->count) || $this->count > 0)
            && $this->price > 0;
    }
    
    function updateCount() {
        $db = mm_getDatabase();
        $sql = "UPDATE mm_product SET count=? WHERE id=?";
        $db->execute($sql, array($this->count, $this->id));
        return true;
    }
    
    function save() {
        if (!$this->is_valid) {
            return false;
        }

        // Get highest sort order
        if ($this->sortorder === '' || $this->sortorder === null) {
            $sql = "select max(sortorder) from mm_product";
            $highest = $db->getOne($sql);
            $this->sortorder = $highest + 1;
        }

        $dao = new product_ProductDAO;
        $this->modify_date = time();
        $dao->save($this);
        if (!$this->sku) {
            $this->sku = $this->id;
            $dao->save($this);
        }
        
        // Add categories
        $this->saveProductCategories();
        
        // Delete images
        $this->deleteMarkedImages();
        
        // Save images
        if (!$this->saveImages()) {
            //$this->addError("Failed to save image");
            return false;
        }
        
        return true;
    }
    
    function sortImagesToSave() {
        // Set sortorder
        $saved_images = array(); // New images, but existing record
        $unsaved_images = array(); // New images, no existing record
        // 1. Sort the saved images
        // images_to_save are new images, but possibly existing record
        foreach ($this->images_to_save as $image) {
            if ($image->id) {
                $saved_images[] = $image;
            }
            else {
                $unsaved_images[] = $image;
            }
        }
        usort($saved_images, create_function('$a,$b', 'if ($a->sortorder == $b->sortorder) return 0; return ($a->sortorder < $b->sortorder) ? -1 : 1;'));
        for ($i=0; $i < count($saved_images); $i++) {
            $saved_images[$i]->sortorder = $i;
        }

        $high_sortorder = -1;
        foreach ($this->images as $image) {
            $high_sortorder = $image->sortorder;
        }

        // 2. Append the unsaved images
        $unsaved_images = array_reverse($unsaved_images);
        usort($unsaved_images, create_function('$a,$b', 'if ($a->sortorder == $b->sortorder) return 0; return ($a->sortorder > $b->sortorder) ? -1 : 1;'));
        $unsaved_images = array_reverse($unsaved_images);
        for ($i=0; $i < count($unsaved_images); $i++) {
            $unsaved_images[$i]->sortorder = $high_sortorder + 1 + $i;
        }
        
        $this->images_to_save = array_merge($saved_images, $unsaved_images);
    }
    
    function saveImages() {
        $images = $this->images;
        $images_to_save = array();
        
        // Loop through the existing images
        foreach ($images as $image) {
            // Ignore ones marked for deletion
            if (gv($this->images_to_delete, $image->id)) {
                continue;
            }
            
            // Find new file upload
            if (($upload = gv($this->image_uploads, $image->id)) && !$upload->is_empty) {
                $image->file_upload = $upload;
            }
            $images_to_save[] = $image;
        }
        
        // Loop through image uploads to find new images
        foreach ($this->image_uploads as $index=>$upload) {
            if ($index < 0 && !$upload->is_empty) {
                $images_to_save[] = new media_Media(array('file_upload' => $upload, 'owner_type' => 'product_Product', 'owner_id' => $this->id));
            }
        }
        $this->image_uploads = array();
        
        // Save each image, (re)setting its sortorder
        $this->_images = array();
        foreach ($images_to_save as $i => $image) {
            $image->sortorder = $i;
            if (!$image->save()) {
                $this->addErrors($image->errors);
            }
            else {
                $this->_images[] = $image;
            }
        }
        
        if ($this->errors) return false;
        
        return true;
    }
    
    function _saveImages() {
        $this->sortImagesToSave();

        foreach ($this->images_to_save as $image) {
            $image->owner = $this;
            if (!$image->save()) {
                $this->addErrors($image->errors);
                return false;
            }
        }
        $this->_images = $this->images_to_save;
        $this->images_to_save = array();
        return true;
    }
    
    function deleteMarkedImages() {
        if (!isset($this->images_to_delete)) return true;
        foreach ($this->images_to_delete as $id=>$delete) {
            if ($delete && ($image = $this->getImageForId($id))) {
                if (!$image->delete()) {
                    $this->addErrors($image->errors);
                }
            }
        }
        return $this->errors ? false : true;
    }
    
    function _setImageUploads($uploads) {
        $this->images_to_save = array();
        $this->_images = $this->getImages();
        
        foreach ($uploads as $index=>$upload) {
            if ($upload && $upload->file) {
                if ($index > 0) {
                    // Update image
                    $existing = eval(array_detect('$this->_images', '$im', '$im->id == $index'));
                    if (!$existing) {
                        $image = new media_Media(array('file_upload' => $upload));
                    }
                    else {
                        $image = $existing;
                        $image->file_upload = $upload;
                    }
                    $this->images_to_save[] = $image;
                }
                else {
                    // Add image
                    $image = new media_Media;
                    $image->file_upload = $upload;
                    $this->images_to_save[] = $image;
                }
            }
        }
        
        $this->sortImagesToSave();
    }
    
    function _setImagesToDelete($deletions) {
        $this->images_to_delete = array();
        $this->_images = $this->getImages();
        foreach ($deletions as $id=>$delete) {
            if ($delete) {
                $existing = eval(array_detect('$this->_images', '$im', '$im->id == $id'));
                $this->images_to_delete[] = $existing;
            }
        }
    }
    
    function getImages()
    {
        if (!isset($this->_images)) {
            $mdao = new media_MediaDAO;
            $this->_images = $mdao->getListForProductId($this->id);
        }
        return $this->_images;
    }
    
    function getImageForId($id)
    {
        foreach ($this->images as $image) {
            if ($image->id == $id) {
                return $image;
            }
        }
        return null;
    }
    
    function saveProductCategories()
    {
        if ($this->id) {
            return $this->updateProductCategories();
        }
        else {
            return $this->addProductCategories();
        }
    }
    
    function addCategory($category)
    {
        if (!isset($this->_categories)) $this->_categories = array();
        $this->_categories[] = $category;
    }
    
    function addProductCategories()
    {
        $db = mm_getDatabase();
        foreach ($this->categories as $category) {
            $query = "INSERT INTO mm_product_category (product_id, category_id) values (?,?)";
            $db->execute($query, array($this->id, $category->id));
        }
    }

    function updateProductCategories()
    {
        $db = mm_getDatabase();
        $db->execute("DELETE FROM mm_product_category WHERE product_id=?", array($this->id));
        $this->addProductCategories();
    }
    
    function validate() {
        if (!$this->sku && !$this->sku_same_as_id) {
            $this->addError("Please specify a SKU");
        }
        if (!$this->name) {
            $this->addError("Please specify a product Title");
        }
        if (!$this->modify_username) {
            $this->addError("Please specify a user who modifies this product");
        }
        if ($this->count === '') {
            $this->count = null;
        }
    }
    
    function validateForSave() {
        $db = mm_getDatabase();
        $params = array($this->sku, $this->id);
        $count = $db->getOne("select count(*) from mm_product where sku=? and id != ?", $params);
        if ($count) {
            $this->addError("Product with given SKU already exists");
        }
    }
    
    function getCategories() {
        if (!$this->_categories) {
            $cdao = new category_CategoryDAO;
            $this->_categories = $cdao->getListForProductId($this->id);
        }
        return $this->_categories;
    }
    
    function setCategoryIds($category_ids) {
        // Map ids to category objects
        
        $filtered = array();
        foreach ($category_ids as $id) {
            if ($id > 0) $filtered[] = intval($id);
        }
        $ids = array_unique($filtered);
        $dao = new category_CategoryDAO;
        $this->_categories = array();
        foreach ($ids as $id) {
            $cat = $dao->fetch($id);
            if ($cat) {
                $this->_categories[] = $cat;
            }
        }
    }
    
    function getCategoryIds() {
        // Map category objects to ids
        $ids = array();
        foreach ($this->getCategories() as $cat) $ids[] = $cat->id;
        return $ids;
    }
    
    function getPricings() {
        if (!isset($this->_pricings)) {
            $pdao = new pricing_PricingDAO;
            $this->_pricings = $pdao->getPricingsForProduct($this);
        }
        return $this->_pricings;
    }
    
    function addPricing($pricing) {
        if (!isset($this->_pricings)) $this->_pricings = array();
        $this->_pricings[] = $pricing;
    }
    
    function setPricings($pricings) {
        $this->_pricings = $pricings;
    }
    
    function setImages($images) {
      $this->_images = $images;
    }

    function getThumbnail() {
        $images = $this->getImages();
        if ($images) {
            return $images[0];
        }
        else {
            return null;
        }
    }
    
    static function generateUrlName($name) {
        return category_Category::generateUrlName($name);
    }
}
