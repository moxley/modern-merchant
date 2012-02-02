<div class="categories">
    <?php foreach ($this->categories as $i=>$category): ?>
    <?php if ($i) echo "&nbsp;|&nbsp;" ?>
    <a href="<?php ph($this->urlFor(array('a' => 'links.browse', 'link_category_id' => $category->id))) ?>" class="<?php echo ($this->category && $category->id == $this->category->id) ? 'selected' : "" ?>"><?php ph($category->name)?></a>
    <?php endforeach ?>
</div>
