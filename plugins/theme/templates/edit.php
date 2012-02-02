<?php
/**
 * @package theme
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

$ini_array = $this->ini_array;
$title = h($ini_array['title']);
$author = h($ini_array['author']);
$url = h($ini_array['url']);
$version = h($ini_array['version']);

echo <<<END_INFO
<b>title:</b> $title<br />
<b>author:</b> $author<br />
<b>url:</b> <a href="$url" target="pluginurl">$url</a><br />
<b>version:</b> $version<br />
END_INFO;
?>
<br />
<input type="button" onclick="window.location='<?php ph(mm_getConfigValue('urls.mm_root')) ?>?a=theme.list'" value="OK" />
