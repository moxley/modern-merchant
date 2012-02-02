<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
$script = $_SERVER['REQUEST_URI'];
//$script = preg_replace('/\?.*$/', '', $script);

if ($this->results_nav) {
    if ($this->results_nav->previous) {
        $link = $this->results_nav->previous;
?>
<a href="<?php ph(appendParamsToUrl($script, $link["params"])) ?>" class="item prev">previous</a>&nbsp;
<?php
    }
    if ($this->results_nav->numbered) {
        foreach ($this->results_nav->numbered as $link) {
            if ($link["current_page"]) {
?>
<span class="item num current"><?php ph($link["page_number"]) ?></span>&nbsp;
<?php
            }
            else {
?>
<a href="<?php ph(appendParamsToUrl($script, $link["params"])) ?>" class="item num"><?php ph($link["page_number"]) ?></a>&nbsp;
<?php
            }
        }
    }
    if ($this->results_nav->next) {
        $link = $this->results_nav->next;
?>
<a href="<?php ph(appendParamsToUrl($script, $link["params"])) ?>" class="item next">next</a>&nbsp;
<?php
    }
}
