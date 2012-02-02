<?php
/**
 * @package contact
 * @copyright (C) 2007 AlchemyWest
 * @copyright (C) 2007 Modern Merchant
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
?>
<div class="contact">
    <form method="post" action="<?php ph($this->urlFor(array('a' => 'contact'))); ?>">
        <p>Fields marked with an asterisk (<span class="form_required">*</span>) are required.</p>

        <?php echo $this->formFields($this->contact->getFormFields()) ?>

        <?php echo $this->submitButton("Send"); ?>
    </form>
</div>
