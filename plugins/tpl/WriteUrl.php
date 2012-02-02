<?php
/**
 * @package tpl
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Helper functions to be called from inside templates.
 */
class tpl_WriteUrl
{
    public $controller;
    
    function __construct($controller=null)
    {
        $this->controller = $controller;
    }
    
    function writeUrl($params)
    {
        $type = gv($params, 'type');
        $method = "get" . ucfirst($type) . "Url";
        if ($type && method_exists($this, $method)) {
            ph($this->$method($params));
        }
        else {
            ph($this->getUrl($params));
        }
    }
    
    function writeProductDetailUrl($params)
    {
        ph($this->getProductDetailUrl($params));
    }
    
    function writeProductListUrl($params)
    {
        ph($this->getProductListUrl($params));
    }
    
    function writeProductDetailImages($params)
    {
        $product = $params['product'];
        if (!$product) {
            print "No product supplied to {writeProductDetailImages}";
            return;
        }
        $first = '';
        $lines = array();
        foreach ($product->images as $image) {
            $url = $image->url_path;
            $w = $image->width;
            $h = $image->height;
            $line = sprintf('<img src="%s" width="%d" height="%d" />' . "\n",
                $url, $w, $h);
            if ($image->sortorder == 0) {
                $first = $line;
            }
            else {
                $lines[] = $line;
            }
        }
        // If the only image is the thumbnail,
        // just print that. Otherwise, print
        // the detail images
        if (!$lines && $first) {
            print $first;
        }
        else {
            foreach($lines as $line) {
                print $line;
            }
        }
    }
    
    function getController()
    {
        return $this->controller;
    }
    
    function getConfig()
    {
        return mm_getConfig();
    }
    
    function getAddToCartUrl($params)
    {
        $sku = gv($params, 'sku');
        $url = mm_getConfigValue('urls.cart.add');
        if (getSchema() != 'https')
        {
            $url = mm_getConfigValue('urls.https') . $url;
        }
        $url = appendQueryToUrl($url, "sku=".urlencode($sku));
        return $url;
    }
    
    function getShowCartUrl($params)
    {
        $url = mm_getConfigValue('urls.cart.show');
        if (getSchema() != 'https')
        {
            $url = mm_getConfigValue('urls.https') . $url;
        }
        return $url;
    }
    
    function getUpdateCartUrl($params)
    {
        $url = mm_getConfigValue('urls.cart.update');
        if (getSchema() != 'https')
        {
            $url = mm_getConfigValue('urls.https') . $url;
        }
        return $url;
    }

    function getProductDetailUrl($params)
    {
        $sku = gv($params, 'sku');
        $url = mm_getConfigValue('urls.catalog.product_detail');
        $url = appendQueryToUrl($url, "sku=".$sku);
        if (getSchema() != 'http')
        {
            $url = mm_getConfigValue('urls.http') . $url;
        }
        return $url;
    }
    
    function getProductListUrl($params)
    {
        $id = gv($params, 'category_id');
        $url = mm_getConfigValue('urls.catalog.product_list');
        $url = appendQueryToUrl($url, "category_id=".h($id));
        if (getSchema() != 'http')
        {
            $url = mm_getConfigValue('urls.http') . $url;
        }
        return $url;
    }

    function getShowPageUrl($params)
    {
        $config = $this->getConfig();
        $template = gv($params, 'template');
        
        $secure = strtolower(gv($params, 'secure'));
        $proto = gv($params, 'proto');

        if ($secure || $proto)
        {
            if ($secure=='true' && getSchema() != 'https')
            {
                $url = mm_getConfigValue('urls.https') . $url;
            }
            if ($proto != getSchema())
            {
                $url = mm_getConfigValue('urls.'.$proto) . $url;
            }
        }
        else if (getSchema() != 'http')
        {
            $url = mm_getConfigValue('urls.http') . $url;
        }
        
        $url = mm_getConfigValue('urls.pages.script');
        $url = appendQueryToUrl($url, "template=".h($template));
        return $url;
    }
    
    function getMessages($params=array(), $engine=null)
    {
        if (!$engine) $engine = $this->engine;
        $messages = array(
            'errors' => $this->controller->getErrors(true),
            'warnings' => $this->controller->getWarnings(true),
            'notices' => $this->controller->getNotices(true)
        );
        $engine->assign($messages);
    }
    
    function getCartActionUrl($params)
    {
        $config = $this->getConfig();
        $action = gv($params, 'action');
        $url = mm_getConfigValue('urls.cart.script');
        if (getSchema() != 'https')
        {
            $url = mm_getConfigValue('urls.https') . $url;
        }
        $url = appendQueryToUrl($url, "action=".$action);
        return $url;
    }
    
    function getHttpUrl($params)
    {
        $uri = gv($params, 'path', '/');
        if (getSchema() != 'http')
        {
            $uri = mm_getConfigValue('urls.http') . $uri;
        }
        return $uri;
    }
    
    function getHttpsUrl($params)
    {
        $uri = gv($params, 'path', '/');
        if (getSchema() != 'https')
        {
            $config =& $this->getConfig();
            $uri = mm_getConfigValue('urls.https') . $uri;
        }
        
        return $uri;
    }
    
    function getHomeUrl($params)
    {
        $params['type'] = 'user.home';
        return $this->getUrl($params);
    }
    
    function writeImageUrl($params)
    {
        ph($this->getImageUrl($params));
    }
    
    function getCatLink($params)
    {
        $controller =& $this->getController();
        $request =& $controller->getRequest();
        $reqCatId = gv($request, 'category_id');
        $href = $this->getProductListUrl($params);
        $id = gv($params, 'category_id');
        $label = gv($params, 'label');
        // TODO: Make css class names configurable
        if ($reqCatId == $id) {
            return sprintf('<span class="catNavItemOn">%s</span>', h($label));
        }
        else {
            return sprintf('<a href="%s" class="catNavItemOff">%s</a>', h($href), h($label));
        }
    }
    
    function writeCatLink($params)
    {
        print $this->getCatLink($params);
    }
    
    function writeLink($params)
    {
        echo $this->link_to(array_delete_at($params, 'label'), $params);
    }
    
    function urlFor($options)
    {
        $url = '';
        $action = array_delete_at($options, 'action');
        if (!$action) $action = array_delete_at($options, 'a');
        $type = array_delete_at($options, 'type');
        if ($action) {
            $url = mm_actionToUri($action);
        }
        elseif ($type) {
            $method = 'get' . ucfirst($type) . 'Link';
            if (method_exists($this, $method)) {
                ph($this->$method($options));
                return;
            }
            $method = "get" . ucfirst($type) . "Url";
            $url = $this->$method($options);
        }
        $params = array_delete_at($options, 'params');
        $url = appendParamsToUrl($url, $params);
        $url = appendParamsToUrl($url, $options);
        return $url;
    }
    
    function urlForImage($image, $options=array())
    {
        return $image->getUrlPath();
    }
    
    function productThumb($product)
    {
        $out = '';
        $image = @$product->images[0];
        if (!$image) {
            $out .= '<img src="/mm/themes/default/images/blank-product.gif" width="80" height="80"';
        }
        else {
            $out .= '<img';
            $out .= ' src="' . h($this->urlForImage($image)) . '"';
            $out .= ' width="' . h($image->width) . '"';
            $out .= ' height="' . h($image->height) . '"';
        }
        $out .= ' border="1"';
        $out .= ' alt="Item ' . h($product->sku) . '" />';
        return $out;
    }
    
    function linkTo($label, $params)
    {
        $url = $this->urlFor($params);
        $attrs = $this->getLinkAttrs($params);
        if ($attrs) $attrs = ' ' . $attrs;
        return sprintf('<a href="%s"%s>%s</a>', h($url), $attrs, h($label));
    }
    
    function getLinkAttrs($params)
    {
        $attrs = '';
        $class = gv($params, 'class');
        if ($class) $attrs .= sprintf(' class="%s"', h($class));
        $style = gv($params, 'style');
        if ($style) $attrs .= sprintf(' style="%s"', h($style));
        return $attrs;
    }
    
    function getUrl($params)
    {
        $url = array_delete_at($params, 'url');
        $action = array_delete_at($params, 'a', array_delete_at($params, 'action'));

        if ($action) {
            $url = mm_actionToUri($action);
        }
        else if (!$url) {
            $path = array_delete_at($params, 'path');
            $name = array_delete_at($params, 'name');
            if (!$name) return null;
            $base = mm_getConfigValue('urls.' . $name);
            if (!$base) return null;
        
            if ($path) {
                if ($path{0} == '?') {
                    $url = $base . $path;
                }
                else {
                    $url = $base . '/' . $path;
                }
            }
            else $url = $base;
        }
        
        $schema = array_delete_at($params, 'schema');
        $absolute = array_delete_at($params, 'absolute');
        
        $http_base = mm_getConfigValue('urls.http');
        $https_base = mm_getConfigValue('urls.https');
        $schema_host = $this->getSchemaAndHost();
        
        if ($schema == 'http' && ($absolute || $schema_host != $http_base)) {
            $base = mm_getConfigValue('urls.http');
            if ($url[0] != '/') $url = '/' . $url;
            $url = $base . $url;
        }
        else if($schema == 'https' && ($absolute || $schema_host != $https_base)) {
            $base = mm_getConfigValue('urls.https');
            if ($url[0] != '/') $url = '/' . $url;
            $url = $base . $url;
        }
        return appendParamsToUrl($url, $params);
    }
    
    function getSchemaAndHost() {
        $schema = $this->getSchema();
        if (!$schema) return null;
        $host = $this->getHost();
        if (!$host) return null;
        return "$schema://$host";
    }
    
    function getHost()
    {
        if (!isset($this->host)) {
            $this->host = gv($_SERVER, 'HTTP_HOST');
        }
        return $this->host;
    }
    
    function setHost($host)
    {
        $this->host = $host;
    }
    
    function getSchema() {
        if (!isset($this->schema)) {
            $this->schema = getSchema();
        }
        
        return $this->schema;
    }
    
    function setSchema($schema) {
        $this->schema = $schema;
    }
    
    function writeSetting($params)
    {
        $controller =& $this->getController();
        $name = gv($params, 'name');
        if (!$name) return null;
        $value = mm_getSetting($name);
        ph($value);
    }
    
    function writeContent($params)
    {
        if (is_string($params)) {
            $name = $params;
        }
        else {
            $name = gv($params, 'name');
        }
        if (!$name) {
            return $this->raiseError('No name specified in writeContent()');
        }
        $da = new content_ContentDAO;
        $content = $da->fetchByName($name);
        if (!$content) return;
        $content->renderToOutput($this->getController());
    }
    
    function paginate($params=array(), $engine=null)
    {
        $controller = $this->getController();
        if (!$engine) $engine = $controller->getTemplateEngine();
        
        $path = mm_getConfigValue('templates.shared');
        $controller->render($path . '/resultsNav');
    }
    
    function loginLogoutLink($params=null) {
        if (!$params) $params = array();
        $login_label = gv($params, 'login', 'Login');
        $logout_label = gv($params, 'logout', 'Logout');
        $user = mm_getUser();
        if ($user) {
            $label = $logout_label;
            $location = $this->getUrl(array('a' => 'user.logout', 'schema' => 'http'));
        }
        else {
            $label = $login_label;
            $location = $this->getUrl(array('a' => 'user.login', 'schema' => 'https'));
        }
        return $this->controller->linkTag($label, $location);
    }
}
