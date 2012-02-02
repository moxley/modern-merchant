<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * HTML writer utility that pulls values from model objects
 */
class mvc_ModelHtmlWriter
{
    private $writer;
    private $controller;
    
    function __construct($controller) {
        $this->controller = $controller;
        $this->writer = new mvc_HtmlWriter;
    }
    
    function textArea($name, $options=null) {
        $value = $this->findValue($name);
        return $this->writer->textAreaTag($name, $value, $options);
    }
    
    function selectField($name, $collection, $attributes=array()) {
        return $this->writer->selectFieldTag($name, '<option value="">-- Select --</option>' . $this->selectOptions($name, $collection), $attributes);
    }
    
    function selectOptions($name, $collection) {
        $selected_value = $this->findValue($name);
        return $this->writer->selectOptionTags($name, $collection, $selected_value);
    }
    
    function radioButton($name, $tag_value='1', $attributes=array()) {
        $group_value = $this->findValue($name);
        $checked = $group_value == $tag_value;
        return $this->writer->radioButtonTag($name, $tag_value, $checked, $attributes);
    }

    function checkBox($name, $tag_value='1', $attributes=array()) {
        $group_value = $this->findValue($name);
        if (is_array($group_value)) {
            $checked = in_array($tag_value, $group_value);
        }
        else {
            $checked = $group_value == $tag_value;
        }
        return $this->writer->checkBoxTag($name, $tag_value, $checked, $attributes);
    }

    function passwordField($name, $attributes=null) {
        if (!$attributes) $attributes = array();
         return $this->writer->passwordFieldTag($name, null, $attributes);
        return $out;
    }
    
    function fileField($name, $attributes=null) {
        if (!$attributes) $attributes = array();
         return $this->writer->fileFieldTag($name, $attributes);
        return $out;
    }
    
    function hiddenField($name, $attributes=null) {
        if (!$attributes) $attributes = array();
        $value = $this->findValue($name);
         return $this->writer->hiddenFieldTag($name, $value, $attributes);
        return $out;
    }
    
    function textField($name, $attributes=null) {
        if (!$attributes) $attributes = array();
        $value = $this->findValue($name);
        if (is_object($value)) {
            mm_log("value is an object: " . get_class($value));
        }
        return $this->writer->textFieldTag($name, $value, $attributes);
    }
    
    function getControllerValue($name, $default=null) {
        $vars = get_object_vars($this->controller);
        if (array_key_exists($name, $vars)) {
            return $vars[$name];
        }
        return $default;
    }
    
    function findValue($name) {
        $names = $this->parseFieldName($name);
        $object = $this;
        $name = array_shift($names);
        $value = $this->getControllerValue($name);
        if (!$value) return null;
        $object = $value;
        
        foreach ($names as $n) {
            if (is_array($object)) {
                $vars = $object;
                $value = gv($vars, $n);
                if (!isset($value)) return null;
                $object = $value;
            }
            else if (!is_object($object)) {
                return null;
            }
            else {
                $exists_value = mvc_Model::getPropertyExistsValue($object, $n);
                if (!$exists_value) return null;

                // So, let's get its value
                $value = $exists_value[0];

                // If the property value is non-null, that will be our next object
                if (isset($value)) {
                    $object = $value;
                }
                // Value is null.
                // Check to see if it's supposed to be an object,
                // and if it is, try to create an instance of that type,
                // and that will be our next object
                else if (method_exists($object, 'getPropertyType')) {
                    $type = $object->getPropertyType($n);
                    if (mvc_Model::isObjectType($type) && $type != 'object') {
                        $object = new $type;
                    }
                    else {
                        return null;
                    }
                }
                else {
                    return null;
                }
            }
            
        }
        return $object;
    }

    function parseFieldName($name) {
        $parsed = array();
        if (!preg_match('/^([^\[]*)(.*)$/', $name, $matches)) {
            return $parsed;
        }
        $parsed[] = $matches[1];
        $remainder = $matches[2];
        $remainder = preg_replace('/^(.*)\[\]$/', '$1', $remainder);

        do {
            $r = preg_match('/^\[([^\]]*)\](.*)$/', $remainder, $matches);
            if ($r) {
                $parsed[] = $matches[1];
                $remainder = $matches[2];
            }
        } while ($r);

        return $parsed;
    }
    
    function formItem($name, $options) {
        $out = "";
        if (gv($options, 'type') != 'hidden') $out .= "<div class=\"form_item\">\n";
        if (gv($options, 'label')) {
            $label_html = h(gv($options, 'label'));
            if (gv($options, 'required')) {
                $label_html .= ' <span class="form_required" title="This field is required">*</span>';
            }
            $label_options = array();
            if (gv($options, 'type') == 'checkbox') {
                $label_options = array('for' => mvc_HtmlWriter::nameToId($name) . '_' . gv($options, 'value', '1'));
            }
            $out .= '    ' . $this->writer->labelFor($name, $label_html, $label_options) . "\n";
        }
        
        $before_field_php = array_delete_at($options, 'before_field_php');
        if ($before_field_php) {
            ob_start();
            eval('?> ' . $before_field_php);
            $out .= ob_get_clean() . "\n";
        }

        $after_field_php = array_delete_at($options, 'after_field_php');
        $description = gv($options, 'description');
        
        if (gv($options, 'type') == 'data') {
            $value = $this->findValue($name);
            $value = $this->writer->convertValueForDisplay($value, $options);
            $out .= '    <span class="data">' . h($value) . '</span>' . "\n";
        }
        else if (gv($options, 'type') == 'link') {
            $out .= sprintf('    <a href="%s">%s</a>' . "\n", h($this->findValue($name)), h($this->findValue($name)));
        }
        else if (gv($options, 'type') == 'checkbox') {
            $out .= $this->checkBox($name, array_delete_at($options, 'checked_value', '1'), $options);
        }
        else if (gv($options, 'type') == 'checkbox_group') {
            $collection = array_delete_at($options, 'collection', array());
            foreach ($collection as $key=>$value) {
                $out .= "<div class=\"select_item\">\n";
                $out .= $this->checkBox($name . '[]', $key, array('with_hidden' => false)) . "\n";
                $out .= $this->writer->labelFor($name . '_' . $key, h($value)) . "\n";
                $out .= "</div>\n";
            }
        }
        else if (gv($options, 'type') == 'file') {
            if (array_delete_at($options, 'show_image') && ($img = $this->findValue($name))) {
                $out .= '    ' . sprintf('<img src="%s" width="%s" height="%s" alt="%s" />', h($img->url_path), $img->width, $img->height, h($img->name)) . "\n";
            }
            $out .= '    ' . $this->fileField($name, $options) . "\n";
        }
        else if (gv($options, 'type') == 'textarea') {
            $out .= '    ' . $this->textArea($name, $options) . "\n";
        }
        else if (gv($options, 'type') == 'select') {
            $collection = array_delete_at($options, 'collection');
            $out .= '    ' . $this->selectField($name, $collection, $options) . "\n";
        }
        else if (gv($options, 'type') == 'html') {
            $out .= gv($options, 'content');
        }
        else if (gv($options, 'type') == 'hidden') {
            $out .= '    ' . $this->hiddenField($name, $options) . "\n";
        }
        else {
            $out .= '    ' . $this->textField($name, $options) . "\n";
        }
        
        if ($after_field_php) {
            ob_start();
            eval('?> ' . $after_field_php);
            $out .= ob_get_clean() . "\n";
        }
        
        if ($description) {
            $out .= '    <div class="description">' . h($description) . "</div>\n";
        }
        if (gv($options, 'type') != 'hidden') $out .= "</div>\n";
        return $out;
    }
    
    function formFields($fields) {
        $out = "";
        foreach ($fields as $field) {
            $name = array_delete_at($field, 'name');
            $out .= $this->formItem($name, $field);
        }
        return $out;
    }
}
