<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

?>
<form method="post" action="?a=content.update">
    <?php echo $this->hiddenFieldTag('id', $this->content->id) ?>
    <?php $this->render('content/_form'); ?>
    
    <input type="submit" value="Update Content" />
</form>
