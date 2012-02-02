<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<style type="text/css">
form label {
    display: block;
    font-weight: bold;
}
div.row {
    margin-bottom: 10px;
}
</style>

<div class="row">
    <label>Name</label>
    <div>
        <?php echo $this->textField('content[name]') ?>
    </div>
</div>

<div class="row">
    <label>Link HTML</label>
    <div>
        <?php printf('<code style="margin-left: 15px;color:#000000;background:#ffffff;"><span style="color:#a65700; ">&lt;</span><span style="color:#800000; font-weight:bold; ">a href=</span><span style="color:#0000e6; ">&quot;?a=content.show&amp;amp;page=<span id="html_code_page_name">%s</span></span>&quot;</span><span style="color:#a65700; ">&gt;</span><span id="html_code_link_label">%s</span><span style="color:#a65700; ">&lt;/</span><span style="color:#800000; font-weight:bold; ">a</span><span style="color:#a65700; ">&gt;</span></code>',
            h($this->content->name), h($this->content->description));?>
    </div>
    <div>
        Use the above HTML to link to a page with this content.
    </div>
</div>

<div class="row">
    <label>Title</label>
    <?php echo $this->textField('content[title]', array('size' => 60)) ?>
</div>

<div class="row">
    <label>Description</label>
    <?php echo $this->textField('content[description]', array('size' => 60)) ?>
</div>

<div class="row">
    <label>Content Type</label>
    <select name="content[type]">
        <?php echo $this->selectOptions('content[type]', $this->getContentTypeOptions()) ?>
    </select>
</div>

<div class="row">
    <label>Content Body</label>
    <?php echo $this->textArea('content[body]', array('size' => '60x30')) ?>
</div>
