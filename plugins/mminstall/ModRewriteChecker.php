<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mminstall_ModRewriteChecker extends mminstall_Checker
{
    public $rewrites;
    
    function check() {
        $result = new mminstall_CheckerResult("Apache mod_rewrite enabled.<br />" .
            "<span style=\"font-size:80%\">Modern Merchant will function without mod_rewrite, but it is a highly recommended feature. " .
            "Images load considerably faster and friendly URLs are possible with mod_rewrite.</span>");
        $poster = new mm_HttpPoster;
        $url = mm_getConfigValue('urls.http') . '/mod_rewrite_test';
        $http_body = $poster->post($url);
        if (!preg_match('/PASS/', $http_body)) {
            $result->warn('Not functioning');
            mm_setNewConfigValue('rewrites.enabled', '');
        }
        else {
            mm_setNewConfigValue('rewrites.enabled', '1');
        }
        $this->addResult($result);
        return $result->pass;
    }
}
