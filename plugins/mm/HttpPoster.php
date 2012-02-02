<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package mm
 */
class mm_HttpPoster
{
    CONST TIMEOUT = 30;
    public $url;
    public $path;
    public $host;
    public $port;
    public $sock_scheme;
    public $sock_url;
    public $http_scheme;
    public $error_message;
    
    /**
     * The response.
     * Consists of a <code>'header'</code> and <code>'body'</code>.
     * @var array
     */
    public $response;
    
    /**
     * Options.
     * These are passed in by the caller during instantiation.
     * @var array
     */
    public $opts;

    /**
     * @param array $opts<pre>
     *$opts = array(
     *    'proxy_host' => '127.0.0.1', // IP address or hostname
     *    'proxy_port' => 80
     *);</pre>
     */
    function __construct($opts=array())
    {
        $this->opts = $opts;
    }
    
    /**
     * Used internally to get an option value.
     *
     * @param string $key The option name or key.
     * @param mixed $default A default value to use if the option key does not exist.
     */
    function getOpt($key, $default=null)
    {
        return array_key_exists($key, $this->opts) ? $this->opts[$key] : $default;
    }
    
    /**
     * Used internally to parse a URL.
     *
     * @param string $url The URL.
     */
    function parseUrl($url)
    {
        $info = array();
        $https = 'https://';
        $http = 'http://';
        if (strpos($url, $https) === 0) {
            $url = substr($url, strlen($https));
            $info['http_scheme'] = 'https';
            $info['sock_scheme'] = 'ssl';
            $info['port'] = 443;
        }
        else if (startswith($url, $http)) {
            $url = substr($url, strlen($http));
            $info['http_scheme'] = 'http';
            $info['sock_scheme'] = '';
            $info['port'] = 80;
        }
        
        if (!$url) {
            $this->error_message = "Empty URL";
            trigger_error($this->error_message, E_USER_WARNING);
            return false;
        }
        if (!preg_match('|^([^/]*)(/.*)?$|', $url, $matches)) {
            $this->error_message = "Bad URL: $url";
            trigger_error($this->error_message, E_USER_WARNING);
            return false;
        }
        $info['host'] = $matches[1];
        $info['path'] = $matches[2];
        
        if ($info['sock_scheme'] == 'ssl') {
            $info['sock_url'] = $info['sock_scheme'] . "://" . $info['host'];
        }
        else {
            $info['sock_url'] = $info['host'];
        }
        return $info;
    }
    
    /**
     * Execute the post.
     *
     * @param string $url
     * @param string $query
     * @return string The response body
     */
    function post($url, $query='', $opts=array())
    {
        $method = mm_getConfigValue('http_poster.method');
        if ($method == 'curl') {
            return $this->postWithCurl($url, $query, $opts);
        }
        else {
            return $this->postWithPHP($url, $query, $opts);
        }
    }
    
    /**
     * Execute the post using PHP's stream functionality.
     *
     * @param string $url
     * @param string $query
     * @return string The response body
     */
    function postWithPHP($url, $valuesOrData)
    {
        if (is_array($valuesOrData)) {
            $data = mm_makeQueryString($valuesOrData);
        }
        else {
            $data = $valuesOrData;
        }
        $this->response = array();
        $urlInfo = $this->parseUrl($url);
        
        if ($this->getOpt('proxy_host')) {
            $sock = fsockopen(
                $this->getOpt('proxy_host'),
                $this->getOpt('proxy_port'),
                $errno,
                $errstr,
                $this->getTimeout());
        }
        else {
            $sock = fsockopen(
                $urlInfo['sock_url'],
                $urlInfo['port'],
                $errno,
                $errstr,
                $this->getTimeout());
        }
        if (!$sock) {
            $this->error_message = "Failed to open socket";
            trigger_error($this->error_message, E_USER_WARNING);
            return false;
        }
        
        if ($this->getOpt('proxy_host')) {
            fwrite($sock, "POST $url HTTP/1.0\r\n");
            $host = $this->getOpt('proxy_host');
            fwrite($sock, "Host: $host\r\n");
        }
        else {
            fwrite($sock, "POST {$urlInfo['path']} HTTP/1.0\r\n");
            fwrite($sock, "Host: {$urlInfo['host']}\r\n");
        }
        fwrite($sock, "Content-type: application/x-www-form-urlencoded\r\n");
        fwrite($sock, "Content-length: " . strlen($data) . "\r\n");
        fwrite($sock, "Accept: */*\r\n");
        fwrite($sock, "\r\n");
        fwrite($sock, "$data\r\n");
        fwrite($sock, "\r\n");
        
        $headers = "";
        while ($str = trim(fgets($sock, 4096))) {
            $headers .= $str . "\n";
        }
        $this->response['headers'] = $headers;
        
        $body = "";
        while (!feof($sock) && false !== ($char = fgetc($sock))) {
            $body .= $char;
        }
        fclose($sock);
        
        $this->response['body'] = $body;
        
        return $body;
    }
    
    /**
     * Execute the post using the CURL extension.
     *
     * @param string $url
     * @param string $query
     * @return string The response body
     */
    function postWithCurl($url, $valuesOrData='')
    {
        if (is_array($valuesOrData)) {
            $data = mm_makeQueryString($valuesOrData);
        }
        else {
            $data = $valuesOrData;
        }

        $urlInfo = $this->parseUrl($url);
        $this->response = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());
        if ($this->getOpt('proxy_host')) {
            $proxy_scheme = $urlInfo['http_scheme'];
            $proxy_host = $this->getOpt('proxy_host');
            $proxy_port = $this->getOpt('proxy_port');
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY,"$proxy_scheme://$proxy_host:$proxy_port");
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            $this->error_message = $error;
            trigger_error($this->error_message, E_USER_WARNING);
            return false;
        }
        $this->response['body'] = $result;
        curl_close($ch);
        return $this->response['body'];
    }
    
    function getTimeout()
    {
        if (array_key_exists('timeout', $this->opts)) {
            return $this->opts['timeout'];
        }
        else {
            return self::TIMEOUT;
        }
    }
}
