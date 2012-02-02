<form method="post" action="<?php ph($this->urlFor(array('a' => 'links_admin.edit'))) ?>" enctype="multipart/form-data">
<div>
    <input type="hidden" name="id" value="<?php ph($this->link->id) ?>" />
    <?php echo $this->formFields($this->link->getFormFields('admin')); ?>
    <?php echo $this->submitButton('Submit') ?>
</div>
</form>
