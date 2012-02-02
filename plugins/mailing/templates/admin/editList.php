<form method="post" action="<?php ph($this->urlFor(array('a' => 'mailing_admin.editList', 'id' => $this->list->id))) ?>">
    <?php echo $this->formFields($this->list->getFormFields()) ?>
    <?php echo $this->submitButton("Update") ?>
</form>
