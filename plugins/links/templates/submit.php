<?php
/**
 * "Submit a Link" form.
 *
 * @package links
 * @copyright (C) 2004 - 2008 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<form method="POST" id="addLink" action="<?php ph($this->urlFor(array('a' => 'links.submit'))) ?>" enctype="multipart/form-data">
<div>
    <h1>Add your link and graphic to our web site!</h1>
    
    <h3>Guidelines:</h3>

    <ul>
        <li>Before submitting your link, we require that you add a link to <?php ph(mm_getConfigValue('urls.http') . mm_getConfigValue('urls.site.home')) ?> from your 
            website. We will check from time to time to verify that your site 
            links to <?php ph(mm_getConfigValue('urls.http') . mm_getConfigValue('urls.site.home')) ?></li>
        <li>Logos may be in JPEG, GIF, or PNG format.</li>
        <li>All logos will be automatically reduced to fit within a 150x75 
            pixel box.</li>
        <li>Please convert transparencies to a specific color before uploading.</li>
        <li>Animated GIFs will not work. Please submit a static image only.</li>
        <li>Fields marked with an asterisk (<span class="form_required">*</span>) are required.</li>
    </ul>
    
    <p>Back to <a href="<?php ph($this->urlFor(array('a' => 'links'))) ?>">Links</a></p>

    <?php $this->render("mm/shared/messages"); ?>
    
    <?php echo $this->formFields($this->link->getFormFields()); ?>

    <?php echo $this->submitButton('Submit') ?>

    <p>Back to <a href="<?php ph($this->urlFor(array('a' => 'links'))) ?>">Links</a></p>
</div>
</form>
