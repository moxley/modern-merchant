[
    <?php echo $this->linkTag('Lists', $this->urlFor(array('a' => 'mailing_admin.lists'))) ?>
    | <?php echo $this->linkTag('Create New Mailing List', $this->urlFor(array('a' => 'mailing_admin.addList'))) ?>
    | <?php echo $this->linkTag('Recipients', $this->urlFor(array('a' => 'mailing_admin.recipients'))) ?>
    | <?php echo $this->linkTag('Add Recipient', $this->urlFor(array('a' => 'mailing_admin.addRecipient'))) ?>
    | <?php echo $this->linkTag('Broadcasts', $this->urlFor(array('a' => 'mailing_admin.broadcasts'))) ?>
    | <?php echo $this->linkTag('Send Broadcast', $this->urlFor(array('a' => 'mailing_admin.sendBroadcast'))) ?>

]
