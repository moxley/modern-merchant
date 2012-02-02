<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * HTML writer utility
 */
class mvc_HtmlWriter
{
    function textAreaTag($name, $content, $options=null) {
        if (!$options) $options = array();
        $ta_options = array(
            'name' => $name
        );
        $size = array_delete_at($options, 'size');
        if ($size) {
            list($cols, $rows) = explode('x', $size);
            $ta_options['cols'] = $cols;
            $ta_options['rows'] = $rows;
        }
        $this->generateId($ta_options);
        $ta_options['cols'] = gv($ta_options, 'cols', 80);
        $ta_options['rows'] = gv($ta_options, 'rows', 10);
        $ta_options = array_merge($ta_options, $options);
        if (!isset($content)) $content = gv($options, 'default');
        return $this->contentTag('textarea', h($content), $ta_options);
    }
    
    function fileFieldTag($name, $options=null) {
        if (!$options) $options = array();
        $field_options = array(
            'type' => 'file',
            'name' => $name
        );
        $field_options = array_merge($field_options, $options);
        $this->addClassToAttributes($field_options, 'file');
        return $this->tagClosed('input', $field_options);
    }
    
    function submitButton($label, $options=null) {
        return $this->submitButtonTag($label, $options);
    }
    
    function submitButtonTag($label, $options=null) {
        if (!$options) $options = array();
        $button_options = array(
            'type' => 'submit'
        );
        $button_options = array_merge($button_options, $options);
        $this->addClassToAttributes($button_options, 'submit');
        return $this->buttonTag($label, $button_options);
    }
    
    function buttonTag($label, $options=null) {
        if (!$options) $options = array();
        $button_options = array(
            'type' => 'button',
            'value' => $label
        );
        $button_options = array_merge($button_options, $options);
        $this->addClassToAttributes($button_options, 'button');
        return $this->tagClosed('input', $button_options);
    }
    
    function imageTag($source, $options=null) {
        if (!$options) $options = array();
        $options['src'] = $source;
        $size = array_delete_at($options, 'size');
        if ($size) {
            list($width, $height) = explode('x', $size);
            $options['width'] = $width;
            $options['height'] = $height;
        }
        $options['alt'] = gv($options, 'alt', gv($options, 'src'));
        return $this->tagClosed('img', $options);
    }
    
    function linkTag($inner_html, $location, $options=null) {
        if (!$options) $options = array();
        $link_options = array();
        if (!is_array($location)) {
            $location = array('href' => $location);
        }
        if ($href = array_delete_at($location, 'href')) {
            $link_options['href'] = $href;
        }
        elseif (($action = array_delete_at($location, 'action')) || ($action = array_delete_at($location, 'a'))) {
            $link_options['href'] = mm_actionToUri($action);
        }
        if ($location) {
            $link_options['href'] = appendParamsToUrl($link_options['href'], $location);
        }
        
        $confirm = array_delete_at($options, 'confirm');
        if ($confirm) {
            $msg = "Are you sure?";
            if (is_string($confirm) && strlen($confirm) > 1) $msg = $confirm;
            $msg = str_replace("'", "\\'", $msg);
            $link_options['onclick'] = "return confirm('$msg')";
        }
        $link_options = array_merge($link_options, $options);
        return $this->tagOpen('a', $link_options) .
            $inner_html .
            "</a>";
    }
    
    function selectFieldTag($name, $inner_html, $attributes=array()) {
        $local_attributes = array(
            'name' => $name
        );
        $this->generateId($local_attributes);
        $attributes = array_merge($local_attributes, $attributes);
        
        return $this->tagOpen('select', $attributes) .
            $inner_html .
            '</select>';
    }
    
    function selectOptionTags($name, $collection, $selected_value) {
        $out = '';
        foreach ($collection as $value=>$label) {
            $selected = $value == $selected_value;
            $out .= $this->tagOpen('option', array('value'=>$value, 'selected'=>$selected)) . h($label) . "</option>\n";
        }
        return $out;
    }
    
    function radioButtonTag($name, $input_value, $checked=false, $attributes=array()) {
        $attributes['type'] = 'radio';
        if (is_bool($input_value)) {
            $input_value = $input_value ? '1' : '0';
        }
        $this->addClassToAttributes($attributes, 'radio');
        return $this->checkBoxTag($name, $input_value, $checked, $attributes);
    }
    
    function checkBoxTag($name, $value='1', $checked=false, $attributes=array()) {
        $local_attributes = array(
            'type'    => 'checkbox',
            'name'    => $name,
            'checked' => $checked,
            'value'   => $value
        );
        $attributes = array_merge($local_attributes, $attributes);
        if (!array_key_exists('id', $attributes)) {
            $attributes['id'] = mvc_HtmlWriter::nameToId($attributes['name'] . '[' . $attributes['value'] . ']');
        }
        $out = '';
        if ($attributes['type'] == 'checkbox' && gv($attributes, 'with_hidden', true)) {
            $out .= $this->hiddenFieldTag($attributes['name']);
        }
        $this->addClassToAttributes($attributes, 'checkbox');
        $out .= $this->tagClosed('input', $attributes);
        return $out;
    }
    
    function nameToId($name) {
        $search = array(']', '[', '.', '__');
        $replace = array('', '_', '_', '_');
        return str_replace($search, $replace, $name);
    }
    
    function passwordFieldTag($name, $value=null, $attributes=null) {
        $attributes['type'] = 'password';
        $this->addClassToAttributes($attributes, 'password');
        return $this->textFieldTag($name, $value, $attributes);
    }
    
    function hiddenFieldTag($name, $value=null, $attributes=null) {
        $attributes['type'] = 'hidden';
        $attributes['input_class'] = 'hidden';
        return $this->textFieldTag($name, $value, $attributes);
    }
    
    function generateId(&$attributes) {
        if (!array_key_exists('id', $attributes)) {
            $attributes['id'] = mvc_HtmlWriter::nameToId($attributes['name']);
        }
        return $attributes;
    }
    
    function textFieldTag($name, $value=null, $attributes=null) {
        if (!$attributes) $attributes = array();
        if ($filter = array_delete_at($attributes, 'filter')) {
            $value = $filter($value);
        }
        
        $local_attributes = array(
            'type'  => 'text',
            'name'  => $name,
            'value' => $value
        );
        if (gv($attributes, 'type') != 'hidden') {
            $this->generateId($local_attributes);
        }
        $attributes = array_merge($local_attributes, $attributes);
        $this->addClassToAttributes($attributes, array_delete_at($attributes, 'input_class', 'text'));
        $value = gv($attributes, 'value');
        if (!isset($value)) $value = gv($attributes, 'default');
        $value = $this->convertValueForDisplay($value, $attributes);
        $attributes['value'] = $value;
        return $this->tagClosed('input', $attributes);
    }
    
    function tagClosed($tag, $attributes=array()) {
        return '<' . $tag . $this->tagAttributes($attributes) . ' />';
    }
    
    function tagOpen($tag, $attributes=array()) {
        return '<' . $tag . $this->tagAttributes($attributes) . '>';
    }
    
    function contentTag($tag, $inner_html, $attributes=null) {
        if (!$attributes) $attributes = array();
        return $this->tagOpen($tag, $attributes) . $inner_html . "</$tag>";
    }
    
    function tagAttributes($attributes) {
        if (!$attributes) $attributes = array();
        $out = '';
        foreach ($attributes as $name=>$value) {
            if (is_bool($value)) {
                if ($value) {
                    $out .= ' ' . h($name);
                }
            }
            else {
                $out .= ' ' . h($name) . '="' . h($value) . '"';
            }
        }
        return $out;
    }

    function addClassToAttributes(&$attributes, $class) {
        $existing_class = gv($attributes, 'class', '');
        $classes = array();
        if ($existing_class) {
            $classes = array_map('trim', explode(' ', $existing_class));
        }
        if (!in_array($class, $classes)) $classes[] = $class;
        $attributes['class'] = implode(' ', $classes);
    }
    
    function labelFor($name, $inner_html, $user_options=array()) {
        $options = array(
            'for' => mvc_HtmlWriter::nameToId($name)
        );
        $options = array_merge($options, $user_options);
        return $this->contentTag('label', $inner_html, $options);
    }
    
    function convertValueForDisplay($value, &$options) {
        $format = array_delete_at($options, 'format');
        if (is_bool($value)) $value = $value ? 'Yes' : 'No';
        else if ($format == 'date') {
            $value = mm_date($value);
        }
        else if ($format == 'datetime') {
            $value = mm_datetime($value);
        }
        else if ($format == 'price') {
            $value = mm_price($value);
        }
        return $value;
    }
}
