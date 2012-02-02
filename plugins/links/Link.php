<?php
/**
 * @package links
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package links
 */
class links_Link extends mvc_Model {
    public $id;
    public $category_id;
    public $created_on;
    public $_url;
    public $email;
    public $description;
    public $comment;
    public $business_name;
    public $approved;
    public $reciprocal_url;
    public $counter;
    public $_image;
    public $_image_upload;
    public $_category_options;
    public $_send_approval = false;
    
    function getApprovedText() {
        return $this->approved ? "Approved" : "Awaiting";
    }
    
    function validate() {
        if (empty($this->business_name))  $this->addError("Please provide a business name");
        if (empty($this->email))          $this->addError("Please provide an email address");
        if (empty($this->_url))           $this->addError("Please provide a URL you want us to link to");
        if (empty($this->description))    $this->addError("Please provide a description of your site");
        if (empty($this->category_id))    $this->addError("Please select a category for your site");
    }
    
    function validateForAdd() {
        if (empty($this->reciprocal_url)) $this->addError("Please provide the URL of the page on your site where you will link to us");
    }
    
    /**
     * Returns the form fields for submitting a link.
     */
    function getFormFields($option=null) {
        $fields = array(
            array('name' => 'link[business_name]',  'label' => "Business Name",  'required' => true),
            array('name' => 'link[email]',          'label' => "Contact Email",  'required' => true),
            array('name' => 'link[url]',            'label' => "URL",            'required' => true),
            array('name' => 'link[counter]',        'label' => "Clicks",     'type' => 'data'),
            array('name' => 'link[reciprocal_url]', 'label' => "Reciprocal URL", 'required' => true,  'description' => 'Location on your website where link to ' . mm_getConfigValue('urls.http') . '/' . ' has been placed.'),
            array('name' => 'link[image]',          'label' => "Image",          'type' => 'file',    'description' => 'JPG, GIF or PNG images accepted.', 'show_image' => true),
            array('name' => 'link[description]',    'label' => "Description",    'type' => 'textarea', 'required' => true, 'size' => '40x4'),
            array('name' => 'link[category_id]',    'label' => "Category",       'type' => 'select',   'required' => true, 'collection' => $this->getCategoryOptions()),
            array('name' => 'link[comments]',       'label' => "Comments",       'type' => 'textarea', 'size' => '40x4')
        );
        if ($option == 'admin') {
            $fields[] = array('name' => 'link[approval]', 'label' => "Approved", 'type' => 'select', 'collection' => array(0 => 'Not Approved', 1 => 'Approved', 'send_notice' => 'Approve and send notice'));
            foreach ($fields as &$f) {
                if ($f['name'] == 'link[reciprocal_url]') {
                    unset($f['description']);
                    $f['after_field_php'] = '<a href="<?php ph($this->controller->link->reciprocal_url) ?>" target="_new">Check URL</a>';
                }
            }
        }
        if (!$option) {
            $new_fields = array();
            foreach ($fields as $field) {
                if (gv($field, 'name') != 'counter') $new_fields[] = $field;
            }
            $fields = $new_fields;
        }
        return $fields;
    }
    
    /**
     * Set link values from an untrusted user (submitter) who wants to add a link.
     */
    function setSubmitterValues($values) {
        $this->category_id = (int) gv($values, 'category_id');
        $this->url = gv($values, 'url');
        $this->email = gv($values, 'email');
        $this->description = gv($values, 'description');
        $this->comment = gv($values, 'comment');
        $this->image = gv($values, 'image');
        $this->business_name = gv($values, 'business_name');
        $this->reciprocal_url = gv($values, 'reciprocal_url');
    }
    
    function setAdminValues($values) {
        $this->setSubmitterValues($values);
        $approval = gv($values, 'approval');
        if ($approval == 'send_notice') {
            $this->_send_approval = true;
            $this->approved = true;
        }
        else {
            $this->approved = $approval ? true : false;
        }
    }
    
    /**
     * Get an associative array of category names, indexed by category_id.
     */
    function getCategoryOptions() {
        if (!isset($this->_category_options)) {
            $dao = new links_LinkCategoryDAO;
            $categories = $dao->find(array('order' => 'name'));
            $this->_category_options = array();
            foreach ($categories as $c) {
                $this->_category_options[$c->id] = $c->name;
            }
        }
        return $this->_category_options;
    }
    
    /**
     * Submit a new link to the approval stage.
     */
    function submit() {
        if (!$this->save()) return false;
        $this->sendNotification();
        return true;
    }
    
    /**
     * Sends an email notification to 'sales'.
     * 
     * Called by <code>submit()</code>.
     */
    function sendNotification() {
        $cat_titles = $this->getCategoryOptions();
        $links_admin_url = mm_actionToUri('links_admin');
        $category = $cat_titles[$this->category_id];
        $body = "Hello,

A new link is ready for your approval. Please visit $links_admin_url to examine and approve the link. Details follow:

Business Name:  {$this->business_name}
Contact Email:  {$this->email}
URL:            {$this->url}
Reciprocal URL: {$this->reciprocal_url}
Category:       $category
Description---
{$this->description}
---
Comment---
{$this->comment}
---
";
        $to = mm_getSetting('sales.notify');
        $subject = mm_getSetting('site.name') . ': Link Add Request';
        $from = mm_getSetting('site.noreply');
        $reply_to = $this->email;
        return mm_mail($to, $subject, $body, "From: $from\r\n" . "Reply-To: $reply_to\r\n");
    }
    
    function sendApprovalNotice() {
        $site_name = mm_getSetting('site.name');
        $body = <<<EOF
Hello,

Thank you for submitting your link to $site_name. We have approved your link, and it now appears on the $site_name web site.

Thank you!

$site_name
EOF;

        $to = $this->email;
        $subject = 'Your link has been approved';
        $from = mm_getSetting('site.noreply');
        $reply_to = $this->email;
        return mm_mail($to, $subject, $body, "From: $from\r\n" . "Reply-To: $reply_to\r\n");
    }

    /**
     * Callback used by the data model base.
     */
    function beforeAdd() {
        $this->created_on = time();
        if (!isset($this->approved)) $this->approved = false;
        $this->counter = 0;
        $this->resizeImageOnAdd();
    }
    
    /**
     * Resizes the link's image before it is added.
     * Called by <code>beforeAdd()</code>.
     */
    protected function resizeImageOnAdd() {
        if ($this->_image_upload && !$this->_image_upload->file) return true;
        
        // Errors generated by addError() will be picked up during validation phase.
        $gd_loaded = extension_loaded('gd');
        if ($this->_image_upload && !$gd_loaded) mm_log("Cannot resize image: GD library is not installed");
        
        if ($this->_image_upload && $gd_loaded) {
            // Move and resize image to a temporary file, keeping the
            // _image_upload object.
            $upload = $this->_image_upload;
            
            // Validate image type
            $tmp_file = $upload->file;
            list($w, $h, $img_type, $attr) = getimagesize($tmp_file);
            if (!in_array($img_type, array(1, 2, 3))) {
                $this->addError("Image must be of type JPEG, GIF or PNG.");
                return;
            }

            // Convert GIFs to PNGs
            $res_newimg = null;
            $res_oldimg = null;
            switch ($img_type)
            {
                case 2: // do jpegs
                    $res_oldimg = imagecreatefromjpeg($tmp_file);
                    $file_ext = '.jpg';
                    break;
                case 3: // do png's
                    $res_oldimg = imagecreatefrompng($tmp_file);
                    $file_ext = '.png';
                    break;
                case 1:
                    $gd_info = gd_info();
                    if ($gd_info['GIF Create Support']) {
                        $res_oldimg = imagecreatefromgif($tmp_file);
                        $file_ext = '.gif';
                    }
                    else {
                        // convert gif to png
                        require mm_getConfigValue('filepaths.plugins') . '/media/gifutil.php';
                        //exec($GLOBALS['gif2png_path'] . "/gif2png -t $tmp_file $tmp_file.png");
                        $gif = gif_loadFile($tmp_file);
                        $tmp_file = $tmp_file . '.png';
                        $file_ext = '.png';
                        gif_outputAsPNG($gif, $tmp_file);
                        $res_oldimg = imagecreatefrompng($tmp_file);
                    }
                    break;
            }

            $max_w = 150;
            $max_h = 75;
            $resize = '';
            if ($w > $max_w) $resize = 'w';
            if ($h > $max_h && ($h-$max_h) > ($w-$max_w)) $resize = 'h';
            if ($resize) {
                if ($resize == 'w') {
                    $newW = $max_w;
                    $newH = $h * $max_w / $w;
                }
                else {
                    $newH = $max_h;
                    $newW = $w * $max_h / $h;
                }
                $newH = intval($newH);
                $newW = intval($newW);
                mm_log("New dim: {$newW}x{$newH}");
                $res_newimg = imagecreate($newW, $newH);
                imagecopyresized($res_newimg, $res_oldimg, 0, 0, 0, 0,
                        $newW, $newH, $w, $h);
            } else {
                $res_newimg = imagecreate($w, $h);
                imagecopy($res_newimg, $res_oldimg, 0, 0, 0, 0, $w, $h);
            }

            // if business names are similar, save new files for each
            $filename = strtolower(preg_replace('/[^A-Za-z]/', 
                    '', $this->business_name));
            $filename = substr($filename, 0, 10);
            $links_dir = mm_getConfigValue('filepaths.public') . "/links/tmp_images";
            mkdirp($links_dir);
            if (file_exists($links_dir . "/$filename" . $file_ext)) {
                $i = 0;
                while (file_exists($links_dir ."/$filename$i" . $file_ext)) {
                    $i++;
                }
                $filename = $filename . $i;
            }
            $filename .= $file_ext;

            // Save the image file
            $filepath = $links_dir . '/' . $filename;
            if ($file_ext == '.png') {
                imagepng($res_newimg, $filepath);
                $upload->mime_type = 'image/png';
            }
            else if ($file_ext == '.gif') {
                imagegif($res_newimg, $filepath);
                $upload->mime_type = 'image/gif';
            }
            else {
                imagejpeg($res_newimg, $filepath, 75);
                $upload->mime_type = 'image/jpeg';
            }
            $upload->name = $filename;
            $upload->file = $filepath;
            $upload->size = filesize($filepath);
        }
    }
    
    /**
     * Callback used by the data model base.
     */
    function afterAdd() {
        if ($this->_image_upload) {
            if ($this->_image_upload->file) {
                $this->_image = mvc_Model::instance('media_Media');
                $this->_image->file_upload = $this->_image_upload;
                $this->_image->owner_type = 'links_Link';
                $this->_image->owner_id = $this->id;
                if (!$this->_image->save()) {
                    $this->addErrors($this->_image->errors);
                }
            }
            unset($this->_image_upload);
        }
    }
    
    /**
     * Callback used by the data model base.
     */
    function afterUpdate() {
        if ($this->_image_upload) {
            if (!$this->getImage()) {
                $this->_image = mvc_Model::instance('media_Media');
                $this->_image->owner_type = 'links_Link';
                $this->_image->owner_id = $this->id;
            }
            $this->_image->file_upload = $this->_image_upload;
            unset($this->_image_upload);
            if (!$this->_image->save()) {
                $this->addErrors($this->_image->errors);
            }
        }
        if ($this->_send_approval) {
            $this->sendApprovalNotice();
            $this->_send_approval = false;
        }
    }
    
    /**
     * Callback used by the data model base.
     */
    function beforeDelete() {
        if ($this->image) $this->image->delete();
    }
    
    function getImage() {
        if (!isset($this->_image) && $this->id) {
            $dao = new media_MediaDAO;
            $this->_image = $dao->fetch(array('where' => array("owner_type='links_Link' AND owner_id=?", $this->id), 'order' => 'id DESC'));
            if (!$this->_image) $this->_image = false;
        }
        return $this->_image;
    }
    
    function setImage($image) {
        if ($image instanceof mvc_FileUpload) {
            $this->_image_upload = $image;
        }
        else {
            $this->_image = $image;
        }
    }
    
    function getUrl() {
        return $this->_url;
    }
    
    function setUrl($url) {
        $this->_url = mm_fixUrl($url);
    }
    
    function getApproval() {
        return $this->approved ? '1' : '0';
    }
    
}
