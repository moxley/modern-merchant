<form method="post" action="<?php ph($this->urlFor(array('a' => 'mailing_admin.addList'))) ?>">
    <?php echo $this->formFields($this->list->getFormFields()) ?>
    <?php echo $this->submitButton("Add") ?>
</form>
