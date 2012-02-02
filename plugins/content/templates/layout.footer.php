<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div style="text-align: center">
  <div class="footerLinks">
    <a href="<?php $this->writeUrl(array('name'=>'site.home')); ?>">Home</a>
    - <a href="<?php $this->writeUrl(array('name'=>'cart.show', 'schema'=>'https')); ?>">Your Cart</a>
  </div>
</div>
<div class="powered-by">Powered By
  <a href="http://www.modernmerchant.org/">Modern Merchant</a>
</div>
<?php echo $this->editLink() ?>
