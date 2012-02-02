<form method="post" action="<?php ph($this->urlFor(array('a' => 'mailing_admin.addRecipient'))) ?>">
    <?php echo $this->formFields($this->recipient->getFormFields()) ?>
    <?php echo $this->submitButton('Add') ?>
</form>
