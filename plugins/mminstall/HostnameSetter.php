<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package mminstall
 */
class mminstall_HostnameSetter extends mminstall_Checker
{
    function check()
    {
        $mm_root = gv($this->urls, 'mm_root');
        if (!$mm_root) {
            $parsed_uri = parse_url($_SERVER['REQUEST_URI']);
            if (preg_match('#/$#', $parsed_uri['path'])) {
                $mm_root = $parsed_uri['path'];
            } else {
                $mm_root = dirname($parsed_uri['path']);
                if ($mm_root != '/') {
                    $mm_root .= '/';
                }
            }
        }
        mm_setNewConfigValue('urls.mm_root', $mm_root);
        $result = new mminstall_CheckerResult("Set hostnames");
        foreach (array('http', 'https') as $proto) {
            if (empty($this->urls[$proto])) {
                $result->fail("Empty value for urls.$proto");
                $this->addResult($result);
                return;
            }
            mm_setNewConfigValue('urls.' . $proto, $this->urls[$proto]);
        }
        $this->addResult($result);
    }
}
