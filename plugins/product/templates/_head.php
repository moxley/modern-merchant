<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<style type="text/css">
    .fakeTextInput { background: #ffffff; font-size: 10pt; border: solid 1px #000000; width: 100px; text-align: center; padding: 5; text-decoration: none; margin-top: 2px; margin-bottom: 1px; }
    .categoryItem, .linkCategoryButton { font-size: 10pt; border: solid 1px #000000; width: 150px; padding: 5; text-decoration: none; margin-top: 2px; margin-bottom: 1px; }
    .categoryItem { text-align: left; background: #ffffff; width: 330px; }
    .linkCategoryButton { background: #EEF5FF; text-align: center; }
</style>
<?php $this->mm_printCategorySelectionsJavascript(); ?>

<table class="layout">
  <tr>
    <td class="layout">
      <?php
          if ($this->product->sku) :
              $conf = $this->getConfig();
              $url = $conf->get('urls.catalog.product_detail');
      ?>
      <a href="<?php ph($url.'&sku='.$this->product->sku) ?>">See customer's view of this product</a>
      <?php
          endif;
      ?>
    </td>
    <td align="right" class="layout">
        <?php $this->render('product/_search'); ?>
    </td>
  </tr>
</table>
