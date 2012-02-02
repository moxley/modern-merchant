<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_Xml
{
    function toAssoc($element)
    {
        $assoc = array();
        $nodes = $element->childNodes;
        for ($i=0; $i < $nodes->length; $i++) {
            $node = $nodes->item($i);
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $key = $node->nodeName;
                $children = $node->childNodes;
                $value = null;
                if ($children->length > 0) {
                    $child_node= $children->item(0);
                    if ($child_node->nodeType != XML_TEXT_NODE) continue;
                    $value = $children->item(0)->nodeValue;
                }
                $assoc[$key] = $value;
            }
        }
        return $assoc;
    }
}
