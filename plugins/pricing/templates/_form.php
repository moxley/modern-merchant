<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
$this->mm_printCategorySelectionsJavascript();
?>

<form method="POST" action="<?php ph($_SERVER['PHP_SELF']) ?>">
    <?php echo $this->hiddenFieldTag('id', $this->pricing->id) ?>
    <table>
        <tr>
            <td>Name: </td>
            <td>
                <?php echo $this->textField('pricing[name]') ?>
            </td>
        </tr>
        <tr>
            <td>Type: </td>
            <td>
                <select name="pricing[type]">
                    <?php echo $this->selectOptions('pricing[type]', $this->pricing->valid_type_options) ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Value: </td>
            <td>
                <?php echo $this->textField('pricing[value]') ?>
            </td>
        </tr>
        <tr>
            <td valign="top">Categories: </td>
            <td>
<?php
    $this->mm_printParentSelect();
?>
    <a href="javascript:CategorySelections.add('pricing[category_ids][]')">Apply to category...</a><br />
    <br />
<?php
    $this->mm_printCategorySelectionsCall('pricing[category_ids][]', $this->pricing->categories);
?>

            </td>
        </tr>
    </table>
    <div style="border: 1px solid #aaa; background-color: #eee; padding: 15px; margin-bottom: 20px">
        <h3 style="margin-top: 0">Examples:</h3>
        <ul>
            <li>10% off Fused Glass:
                <ul>
                    <li>Type: Multiply</li>
                    <li>Value: 0.9</li>
                    <li>Category: Fused Glass</li>
                </ul>
            </li>
            <li>All widgets are $9.99:
                <ul>
                    <li>Type: Override</li>
                    <li>Value: 9.99</li>
                    <li>Category: Widgets</li>
                </ul>
            </li>
        </ul>
    </div>
    <div style="text-align: center">
        <input type="submit" name="actions[<?php ph($this->target_action) ?>]"
            value="Submit" />
    </div>
</form>
