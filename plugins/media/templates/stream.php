<?php
/**
 * @package media
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
// Outputs an image

header("Content-Type: ".$output['record']['mime_type']);
print $output['record']['image_data'];
