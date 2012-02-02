<?php
/**
 * @package admin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

$script = $this->adminBaseUrl();

if( isset($output["results_nav"]) )
{
    if( isset($output["results_nav"]["previous"]) )
    {
        $link = $output["results_nav"]["previous"];
?>
<a href="<?php ph($script) ?>?<?php print $link["params"] ?>">previous</a>&nbsp;
<?php
    }
    if( isset($output["results_nav"]["numbered"]) )
    {
        foreach( $output["results_nav"]["numbered"] as $link )
        {
            if( $link["current_page"] )
            {
?>
<?php ph($link["page_number"]) ?>&nbsp;
<?php
            }
            else
            {
?>
<a href="<?php ph($script) ?>?<?php print $link["params"] ?>"><?php ph($link["page_number"]) ?></a>&nbsp;
<?php
            }
        }
    }
    if( isset($output["results_nav"]["next"]) )
    {
        $link = $output["results_nav"]["next"];
?>
<a href="<?php ph($script) ?>?<?php print $link["params"] ?>">next</a>&nbsp;
<?php
    }
}
