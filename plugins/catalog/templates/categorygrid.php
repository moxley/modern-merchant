<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
if (!isset($this->categories)) {
  $dao = new category_CategoryDAO;
  $this->categories = $dao->getChildren(mm_getSetting('catalog.root_category'));
}
?>

<div style="margin-top: 20px">
  <?php foreach ($this->categories as $category): ?>
  <p class="categoryCell" style="text-align: center">
    <?php if($category->image && $category->image->filename): ?><img src="<?php $this->writeUrl(array('name'=>'media.categories', 'path'=>$category->image->filename)); ?>"/><br /><?php endif; ?>
    <a class="header" href="<?php $this->writeProductListUrl(array('category_id'=>$category->id)); ?>"><?php ph($category->name); ?> :</a><br />
    <?php foreach($this->categories as $subcat): ?>
        <a href="<?php $this->writeProductListUrl(array('category_id'=>$subcat->id)); ?>"><?php ph($subcat->name); ?></a><br />
    <?php endforeach; ?>
  </p>
  <?php endforeach ?>
</div>
