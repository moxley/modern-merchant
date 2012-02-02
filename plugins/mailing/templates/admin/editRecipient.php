<form method="post" action="<?php ph($this->urlFor(array('a' => 'mailing_admin.editRecipient', 'id' => $this->recipient->id))) ?>">
    <?php echo $this->formFields($this->recipient->getFormFields()) ?>
    <?php echo $this->submitButton('Update') ?>
</form>
