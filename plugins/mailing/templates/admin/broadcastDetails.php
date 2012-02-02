<form method="post" action="<?php ph($this->urlFor(array('a' => 'mailing_admin.broadcastDetails', 'id' => $this->broadcast->id))) ?>">
    <?php echo $this->formFields($this->broadcast->getFormFields('details')) ?>
    <?php echo $this->submitButton("Save") ?>
</form>
