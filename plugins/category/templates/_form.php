<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<script type="text/javascript">

category_originalParentId = <?php echo $this->category->parent_id !== null ? $this->category->parent_id : 'null' ?>;
category_placeBefore = <?php echo $this->category->place_before !== null ? $this->category->place_before : 'null' ?>;

function category_onPlaceBeforeChanged() {
    var placeBeforeElm = document.getElementById('category_place_before');
    category_placeBefore = placeBeforeElm.value;
}

function category_onParentChanged() {
    var parentIdElm = document.getElementById('category_parent_id');
    var placeBeforeElm = document.getElementById('category_place_before');

    if (parentIdElm.value == category_originalParentId) {
        // Re-activate the place_before field
        placeBeforeElm.disabled = false;
        placeBeforeElm.value = category_placeBefore;
    }
    else {
        // Deactivate the place_before field
        placeBeforeElm.disabled = true;
        placeBeforeElm.value = '';
    }
}
</script>

<table width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr>
        <td valign="top">
            <form name="form1" method="post" action="<?php ph($this->form_action) ?>" enctype="multipart/form-data">
                <div class="formLiner">
                    <?php echo $this->hiddenFieldTag('id', $this->category->id) ?>
                    <table width="100%" border="0" cellpadding="2" class="dataTable">
                        <?php if ($this->target_action != 'category.add') : ?>
                        <tr>
                            <td class="formRowTitle">ID</td>
                            <td class="formRowValues">
                                <?php ph($this->category->id); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="formRowTitle">Title</td>
                            <td class="formRowValues">
                                <input type="text" name="category[name]" value="<?php ph($this->category->name); ?>" size="40">
                            </td>
                        </tr>
                        <tr> 
                            <td class="itemRowTitle">
                                <div class="formRowTitle">Parent</div>
                                <div class="hint">What parent category does this category belong to?</div>
                            </td>
                            <td class="formRowValues">
                                <select name="category[parent_id]" id="category_parent_id" onchange="category_onParentChanged()">
                                    <option value="0"></option>
<?php
$dao = new category_CategoryDAO;
$categories = $dao->getFlattenedHierarchy();
$parent_id = (int) $this->category->parent_id;
foreach ($categories as $c) {
    print '<option value="' . h($c->id) . '"' . ($parent_id == $c->id ? ' selected' : '') . '>'
        . h(str_repeat('- ', $c->depth) . $c->name)
        . "</option>\n";
}
?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="itemRowTitle">
                                <div class="formRowTitle">Sort order</div>
                                <div class="hint">Which position does this category appear within a list?</div>
                            </td>
                            <td class="formRowValues">
                                <select name="category[place_before]" id="category_place_before" onchange="category_onPlaceBeforeChanged()">
                                    <?php foreach ($this->category->siblings as $c): ?>
                                    <option value="<?php ph($c->id) ?>"<?php echo $c->id == $this->category->place_before ? ' selected' : '' ?>><?php ph($c->name) ?></option>
                                    <?php endforeach ?>
                                    <option value=""<?php echo $this->category->place_before ? '' : ' selected="selected"' ?>>[End]</option>
                                </select>
                                <span class="hint">Appears before the selected category</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="itemRowTitle">
                                <div class="formRowTitle">Set as default category</div>
                                <div class="hint">If a Product List page has no category associated with it,
                                    a default category will be assumed.</div>
                            </td>
                            <td class="formRowValues">
                                <input type="checkbox" name="category[is_default]" value="1" <?php print ($this->category->is_default ? 'checked' : ''); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td class="itemRowTitle">
                                <div class="formRowTitle">Upload image</div>
                                <div class="hint">(Optional)</div>
                            </td>
                            <td class="formRowValues">
                                <?php
                                if ($this->category->image):
                                    $img = $this->category->image;
                                    print "<div>\n" . '<img src="' . h($img->url_path) . '" width="' .
                                            $img->width . '" height="' .
                                            $img->height . '" />' . "</div>\n";
                                ?>
                                    <input type="checkbox" name="category[delete_image]" id="cb1" />
                                    <label for="cb1">Delete image</label>
                                <?php endif ?>
                                <input type="file" name="category[image]" />
                            </td>
                        </tr>
                        <tr>
                            <td class="itemRowTitle">
                                <div class="formRowTitle">Keywords</div>
                                <div style="margin-left: 1em; color: #888;">(Optional) Keywords used to help locate this category within searches. Separate each with a comma.</div>
                            </td>
                            <td class="itemRowValues"> 
                                <?php echo $this->textField("category[keywords]", array('size' => 60)) ?>
                            </td>
                        </tr>
                        <tr> 
                            <td class="itemRowTitle">
                                <div class="formRowTitle">Description</div>
                                <div class="hint">(Optional) Enter a public description for this category</div>
                            </td>
                            <td class="itemRowValues"> 
                                <textarea name="category[description]" cols="50" wrap="virtual" rows="5"><?php ph($this->category->description) ?></textarea>
                            </td>
                        </tr>
                        <tr> 
                            <td class="itemRowTitle">
                                <div class="formRowTitle">Comments</div>
                                <div class="hint">(Optional) Enter any internal notes or comments you would like to keep with this category.</div>
                            </td>
                            <td class="itemRowValues"> 
                                <textarea name="category[comment]" cols="50" wrap="virtual" rows="5"><?php ph($this->category->comment) ?></textarea>
                            </td>
                        </tr>
                        <tr> 
                            <td colspan="2" align="center"> 
                                <input type="submit" name="actions[<?php print $this->target_action ?>]" value="Commit">
                                &nbsp; 
                                <input type="reset" name="" value="Reset">
                                &nbsp; 
                                <input type="submit" name="actions[category.cancel]" value="Cancel">
                                &nbsp; 
                                </td>
                        </tr>
                    </table>
                </div>
            </form>
        </td>
    </tr>
</table>
