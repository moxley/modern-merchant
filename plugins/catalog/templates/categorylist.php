<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div style="margin-top: 20px">
  <?php $this->categoryGridBegin(array('parent_id' => mm_getSetting('default_category'))); ?>
  <p class="categoryCell" style="text-align: center">
    <?php if ($category->image->filename): ?>
        <img src="<?php echo $this->writeUrl(array('name' => 'media.categories', 'path' => $category->image->filename)); ?>"/><br />
    <?php endif; ?>
    <a class="header" href="<?php echo $this->writeProductListUrl(array('category_id' => $category->id)); ?>"><?php ph($category->name); ?> :</a><br />
    <?php foreach ($category->children as $subcat): ?>
        <a href="<?php echo $this->writeProductListUrl(array('category_id' => $subcat->id)); ?>"><?php ph($subcat->name); ?></a><br />
    <?php endforeach; ?>
  </p>
  <?php $this->categoryGridEnd(); ?>
</div>
