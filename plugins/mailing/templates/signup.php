<form method="post" action="<?php ph($this->urlFor(array('a' => 'mailing.signup'))) ?>">
    <?php $this->dbContent('mailing.signup.intro') ?>
    <?php echo $this->formFields($this->recipient->getFormFields('signup')) ?>
    <?php echo $this->submitButton('Submit') ?>
</form>
