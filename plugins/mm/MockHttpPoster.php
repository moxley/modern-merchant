<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * A Mock HttpPoster for testing.
 */
class mm_MockHttpPoster extends mm_HttpPoster
{
    public $query;
    public $throw_exception = false;
    
    /**
     * Execute the mock post.
     *
     * At the end of this call, the <code>$url</code> property will be set to the
     * <code>$url</code> parameter value, and the <code>$query</code> property will be set
     * to the <code>$query</code> parameter value.
     *
     * @param string $url
     * @param string $query
     * @return string The response body
     */
    function post($url, $query='')
    {
        $this->url = $url;
        $this->query = $query;
        if ($this->throw_exception) {
            throw new Exception("Dummy exception from " . __CLASS__);
        }
        return $this->body;
    }
}
