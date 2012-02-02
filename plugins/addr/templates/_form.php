<?php
/**
 * @package addr
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
$fields = array(
    array('name' => 'first_name', 'label' => "First Name"),
    array('name' => 'last_name', 'label' => "Last Name"),
    array('name' => 'salutation', 'label' => "Salutation"),
    array('name' => 'company', 'label' => "Company"),
    array('name' => 'title', 'label' => "Title"),
    array('name' => 'address_1', 'label' => "Address Line 1"),
    array('name' => 'address_2', 'label' => "Address Line 2"),
    array('name' => 'phone_day', 'label' => "Phone - Day"),
    array('name' => 'phone_night', 'label' => "Phone - Evening"),
    array('name' => 'city', 'label' => "City"),
    array('name' => 'state', 'label' => "State"),
    array('name' => 'zip', 'label' => "Zip/Postal Code"),
    array('name' => 'country', 'label' => "Country"),
    array('name' => 'email', 'label' => "Email Address"),
    array('name' => 'fax', 'label' => "Fax Number")
);
foreach ($fields as $k=>$v) {
    $fields[$k] = (object) $v;
}
?>
<?php foreach ($fields as $field): ?>
<div class="row">
    <?php $field_name = $this->field_prefix . "[$field->name]" ?>
    <label for="<?php echo mvc_HtmlWriter::nameToId($field_name); ?>"><?php ph($field->label) ?></label>
    <?php echo $this->textField($field_name) ?>
</div>
<?php endforeach ?>

