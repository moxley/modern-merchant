<form method="post" action="<?php ph($this->urlFor(array('a' => 'mailing_admin.sendBroadcast'))) ?>">
    <?php echo $this->formFields($this->broadcast->getFormFields()) ?>
    <p>Once you press the 'Send' button, you may navigate away from the page before it is finished loading.</p>
    <?php echo $this->submitButton("Send") ?>
</form>
