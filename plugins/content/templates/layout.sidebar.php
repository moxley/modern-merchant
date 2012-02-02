<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div class="row row-first">
  <?php $user = mm_getUser() ?>
  <?php if ($user): ?>
  <h3>Hello, <?php ph($user->username) ?></h3>
  <?php else: ?>
  <?php echo $this->linkTag('login', '?a=user.login') ?>
  <?php endif ?>
</div>
<div class="row">
  <h3>Products</h3>
  <div class="row-body">
    <?php $this->getHelper('category')->writeCategories(array('schema'=>'http')); ?>
  </div>
</div>
<div class="row">
  <h3>Your Cart</h3>
  <div class="row-body">
    <?php $this->render('cart/smallcart'); ?>
  </div>
</div>
<?php echo $this->editLink() ?>
