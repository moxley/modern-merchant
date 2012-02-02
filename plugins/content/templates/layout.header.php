<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div class="title">
  <h1><a href="<?php $this->writeUrl(array('name'=>'site.home', 'schema'=>'http')) ?>"><?php ph(mm_getSetting("site.name")); ?></a></h1>
    <?php echo $this->editLink() ?>
</div>
<form class="search" action="">
  <?php echo $this->hiddenFieldTag('a', 'catalog.search'); ?>
  <?php echo $this->textField('q'); ?>
  <?php echo $this->submitButton('Search'); ?>
</form>
<div class="nav">
  <?php $this->dbContent('layout.header.nav') ?>
</div>
