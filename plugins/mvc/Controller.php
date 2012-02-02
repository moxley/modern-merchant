<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * MVC Controller Base class
 */
class mvc_Controller
{
    /**
     * The request parameters.
     * @var array
     */
    public $request;
    
    /**
     * The output stream as a string.
     * @var string
     */
    public $_content = '';
    
    /**
     * Object that renders templates.
     * @var object
     */
    protected $_template_engine;

    /**
     * The current action.
     * @var string
     */
    public $action;
    
    /**
     * HTML tags that should be added to the HTML document's HEAD tag.
     * @var array
     * @todo Move this
     */
    public $head_content = array();
    
    /**
     * For paginated result sets, the maximum number of records per page.
     * @var int
     * @todo Move this
     */
    public $max_per_page = 30;
    
    /**
     * View helpers that will be available globally.
     * @var array
     * @todo Move this
     */
    public $view_helpers = array();
    
    /**
     * Core view helpers.
     * @var array
     */
    protected $_core_view_helpers;
    
    /**
     * Cached, named helpers.
     * @var array
     */
    protected $_cached_helpers = array();
    
    /**
     * Overridden path to template.
     * @var string
     */
    public $layout;

    /**
     * The controller name.
     *
     * This is usually the same as the module name.
     *
     * @var string
     */
    protected $controller_name;
    
    /**
     * Forwarding action.
     * @var string
     */
    protected $forward;
    
    /**
     * Path to automatically-rendered template.
     *
     * Set with setTemplate().
     *
     * If the default automatically-rendered template is to be different
     * than the default one, then this property should be set with a
     * unique one.
     *
     * @var string
     */
    protected $template;
    
    /**
     * Return action.
     * @var string
     */
    protected $returnAction;
    
    static $controller_registrations = array();
    
    protected $contentByName = array();
    
    function __construct() {}
    
    static function runRequest()
    {
        try {
            $request = getRequest();
            // Parse the request in order to determine the Controller and
            // Controller action
            $parser = new mvc_RequestParser($request);
            $res = $parser->parse();

            foreach ($parser->params as $key=>$value) {
                $request[$key] = $value;
            }

            $action = $parser->action;
            $controller_name = $parser->module;

            if (!$parser->module) {
                $default_action = mm_getConfigValue('default_action');
                if ($default_action) {
                    $parser->parseAction($default_action);
                    $controller_name = $parser->module;
                    $action = $parser->action;
                    foreach ($parser->params as $k=>$v) {
                        $request[$k] = $v;
                    }
                }
                else {
                    $parser->module = $controller_name = mm_getConfigValue('controllers.default');
                    if (!$parser->module) {
                        throw new Exception("No default controller found (check 'controllers.default' configuration value)");
                    }
                    $parser->action = $action = 'default';
                    $parser->action_uri = $parser->module . '.' . $parser->action;
                }
            }

            if (!$controller_name) {
                throw new Exception("Malformed action address: No controller component in '" . $parser->action_address . "'");
            }

            if (!$action) $action = 'default';

            /*
             * Run the action.
             */
            //return $controller->runActionRequest($action);
            self::doActionLoop($controller_name, $action, $request);
        }
        catch (Exception $e) {
            error_log("Exception caught in " . __CLASS__ . "::" . __FUNCTION__ . ': ' . $e->getMessage());
            error_log("\n" . mm_exceptionToString($e));
            echo "<html><head><title>Error</title></head><body>\n";
            echo "<h1>" . $e->getMessage() . "</h1>";
            echo "<pre>";
            ph($e->getTraceAsString());
            echo "</pre>";
            echo "</body></html>\n";
        }
    }
    
    /**
     * Run the controller.
     *
     * @param $controller_name string
     * @param $action string
     * @param $request array
     */
    static function doActionLoop($controller_name, $action, $request)
    {
        $maxIterations = 5;
        $i = 0;
        while (true) {
            
            if (isset($controller)) $old_controller = $controller;
            $controller = self::getControllerByModuleName($controller_name);
            if (isset($old_controller) && get_class($old_controller) == get_class($controller)) {
                $controller = $old_controller;
            } else {
                $controller->setRequest($request);
                $controller->controller_name = $controller_name;
            }
            
            $exception = null;
            ob_start();
            try {
                /*
                 * Run the action.
                 */
                $result = $controller->runAction($action);
                if ($result === false) {
                    ob_end_flush();
                    return false;
                }
            } catch (Exception $exception) {
                triggerErrorFromException($exception, E_USER_WARNING);
                //trigger_error($exception->getMessage() . "\n"
                //    . $exception->getTraceAsString(),
                //    E_USER_WARNING);
                $result = $controller->runAction('error', false);
            }
            $action = $controller->action; // Controller may override what the action name is
            $content = ob_get_clean();
            $controller->addContent($content);
            if ($exception && !$controller->errors) {
                $controller->addError("There was an error.");
            }
            
            if ($controller->forward) {
                /*
                 * Forward to another action
                 */
                $actionParts = explode('.', $controller->forward);
                $controller->forward = null;
                $controller_name = $actionParts[0];
                $action = $actionParts[1];
            }
            else if ($controller->returnAction) {
                /*
                 * Render the view, but as a return action
                 */
                $controller->renderAction($controller->returnAction);
                $controller->returnAction = null;
                return false;
            }
            else {
                /*
                 * Render the view
                 */
                if ($controller->template === false) {
                    $controller->renderLayout();
                } else {
                    $controller->preViewFilter($controller->action);
                    $controller->renderAction($controller->action);
                }
                return false;
            }
            
            $i++;
            if ($i > $maxIterations) {
                throw new Exception("Too many chained actions. Perhaps a loop?");
            }
            else if ($i == $maxIterations) {
                $action = 'error';
                trigger_error("Too many chained actions. Perhaps a loop?", E_USER_WARNING);
            }
        }
    }
    
    /**
     * Delegates an action to self.
     *
     * @param $action string The action name.
     */
    function runAction($action, $withCallback=true)
    {
        if (!$action) $action = 'default';
        
        if (preg_match('/[^a-zA-Z0-9]/', $action)) {
            throw new Exception("Malformed action name: Should contain only alphanumeric characters");
        }

        $this->action = $action;
        
        $input = $this->getRequest();
        if (isset($input['transition'])) {
            $this->transition = $input['transition'];
        }

        // Get the action method
        $method_name = "run" . ucwords($this->action) . "Action";
        
        if (!method_exists($this, $method_name)) {
            return $this->runAction('notFound', false);
        }
        else if (!$withCallback || $this->beforeAction($this->action) !== false) {
            // run it
            return $this->$method_name();
        } else {
            return false;
        }
    }
    
    /**
     * Get the request parameters.
     *
     * @return array
     */
    function &getRequest()
    {
        mm_getSession(); // TODO: What is this doing here?
        if (!isset($this->request)) {
            $this->request = getRequest();
        }
        return $this->request;
    }
    
    /**
     * Get the session object
     * @return sess_Session
     */
    function getSession()
    {
        return mm_getSession();
    }

    /**
     * Get a session parameter value.
     *
     * @param $key string
     * @param $default mixed
     * @return mixed
     */
    function getSessionValue($key, $default=null)
    {
        return $this->getSession()->get($key, $default);
    }

    /**
     * Set a session parameter value.
     *
     * @param $key string
     * @param $value mixed
     */
    function setSessionValue($key, $value)
    {
        $this->getSession()->set($key, $value);
    }

    /**
     * Get the value of the given request parameter, throwing an exception if not found.
     *         
     * @param $param_name The parameter name
     * @return mixed  The request parameter value-- either a string or an array
     */
    function getRequiredParam($param_names) {
        if (!is_array($param_names)) $param_names = array($param_names);
        foreach ($param_names as $param_name) {
            if ($value = $this->req($param_name)) {
                return $value;
            }
        }
        throw new mvc_MissingParameterException($param_names);
    }
    
    /**
     * Get values for the given request parameters.
     * 
     * @param $param_names array
     * @return array
     * @throws mvc_MissingParameterException When the parameter does not exist
     */
    function getRequiredParams($param_names) {
        $request = $this->getRequest();
        $values = array();
        foreach ($param_names as $param_name) {
            if (!array_key_exists($param_name, $request)) {
                throw new mvc_MissingParameterException($param_name);
            }
            $values[] = $request[$param_name];
        }
        return $values;
    }
    
    function runSendErrorCommentAction()
    {
        $input = $this->getRequest();
        $email = $input['email'];
        $comment = $input['comment'];
        $subject = "Comment regarding an error the occurred";
        $msg = "Comment:---------------------\n$comment";
        $msg .= "\n-------------------------\n";
        $to = mm_getSetting('webmaster.notification');
        $from = '"' . mm_getSetting('site.name') . '" <' . mm_getSetting('site.noreply') . '>';
        mm_mail($to, $subject, $msg, "From: $from");
        
        $this->addNotice("Thank you. Your comment has been sent to the webmaster.");
        $this->setForward($this->getModuleName());
    }
    
    static function registerController($name, $class)
    {
        self::$controller_registrations[] = (object) array(
                'name'  => $name,
                'class' => $class);
    }
    
    static function findControllerRegistration($module_name)
    {
        foreach (self::$controller_registrations as $reg) {
            if ($reg->name == $module_name) {
                return $reg;
            }
        }
        return null;
    }

    static function getControllerByModuleName($module_name)
    {
        //$controller = mvc_Hooks::lookupController($module_name);
        $reg = self::findControllerRegistration($module_name);
        $controller_class = $reg ? $reg->class : null;
        if (!$controller_class) {
            if (preg_match('/[^a-zA-Z0-9_]/', $module_name)) {
                throw new mvc_ContractException("Illegal characters in module name: $module_name");
            }
            $controller_class = $module_name . '_Controller';
        }
        
        $controller = new $controller_class;

        if (!($controller instanceof mvc_Controller)) {
            throw new mvc_ContractException("Whoa, that ain't no mvc_Controller object: " . get_class($controller));
        }
        
        return $controller;
    }

    /**
     * Set the request parameters.
     *
     * @param $request array
     */
    function setRequest(&$request)
    {
        $this->request =& $request;
    }

    /**
     * Set a request parameter value.
     *
     * @param $key string The parameter name.
     * @param $value string The parameter value.
     */
    function setRequestValue($key, $value)
    {
        $this->request[$key] = $value;
    }

    /**
     * Add a notice to be displayed on the following page.
     *
     * @param $msg string
     */
    function addNotice($msg)
    {
        $this->addMessage($msg, 'notice');
    }
    
    /**
     * Add a warning message to be displayed on the following page.
     *
     * @param $msg string
     */
    function addWarning($msg)
    {
        $this->addMessage($msg, 'warning');
    }
    
    /**
     * Add multiple warning messages to be displayed on the following page.
     *
     * @param $warnings array An array of message strings.
     */
    function addWarnings($warnings) {
        foreach ($warnings as $warning) $this->addWarning($warning);
    }
    
    /**
     * Delete and return all queued warning messages.
     *
     * @param $erase boolean When FALSE, does not delete the queued messages.
     * @return array
     */
    function getWarnings($erase=false)
    {
        return $this->getMessages('warning', $erase);
    }

    /**
     * Delete and return all messages from the 'error' queue.
     *
     * @return array  An array of message strings
     */
    function getErrors($erase=false)
    {
        return $this->getMessages('error', $erase);
    }
    
    /**
     * Delete and return all queued messages of a given type.
     *
     * @param $type string The message type, either 'notice', 'warning', or 'error'.
     * @param $erase boolean If FALSE, does not delete the messages.
     * @return array
     */
    function getMessages($type, $erase=false)
    {
        $sess = mm_getSession();
        $messages = $sess->get(pluralize($type));
        if ($erase) $sess->set(pluralize($type), array());
        if (!$messages) return array();

        return $messages;
    }
    
    /**
     * Add a user message to a queue of a given type.
     *
     * @param $msg string The message
     * @param $type string The message type, either 'notice', 'warning', or 'error'.
     */
    function addMessage($msg, $type)
    {
        $sess = mm_getSession();
        $messages = $sess->get(pluralize($type));
        if (!$messages) {
            $messages = array();
        }
        $messages[] = $msg;
        $sess->set(pluralize($type), $messages);
    }

    /**
     * Add a user message to the 'error' queue.
     *
     * @param $msg string The message
     */
    function addError($msg)
    {
        $this->addMessage($msg, 'error');
    }
    
    /**
     * Get and delete all notice-type messages.
     *
     * @return array An array of message strings.
     */
    function getNotices($erase=false) {
        return $this->getMessages('notice', $erase);
    }

    /**
     * Generic 'error' action.
     */
    function runErrorAction()
    {
        if (!$this->getErrors()) {
            $this->addError("Internal Error");
        }
        $this->setTemplate(false);
    }
    
    /**
     * Generic 404 Not Found action.
     */
    function runNotFoundAction()
    {
        $this->setTemplate(false);
        header("HTTP/1.0 404 Not Found");
        $this->addError("The requested page was not found");
    }
    
    /**
     * Generic 'cancel' action; forwards to the controller's default action.
     */
    function runCancelAction()
    {
        $this->setForward($this->controller_name);
    }
    
    /**
     * Generic 'Not Authorized' action; adds a generic warning message and forwards to the 'error' action.
     */
    function runNotAuthorizedAction()
    {
        $this->addWarning("You are not authorized here.");
        $this->setForward($this->controller_name . '.error');
    }
    
    /**
     * Callback that is called just before the view is rendered.
     */
    function preViewFilter()
    {
        mvc_Hooks::notifyListeners('controller.pre_view_filter', $this);
        return;
    }

    /**
     * Redirect to the given action path.
     *
     * @param $a string  The action path
     * @param $params array  Optional request parameters to be appended to the request.
     */
    function redirectToAction($a, $params=array())
    {
        return $this->redirect(array('a' => $a, 'params' => $params));
    }
    
    /**
     * Perform an HTTP redirect.
     *
     * If $options is a string, it will be treated as a URL (relative or absolute).
     * Otherwise, $options will be considered an array.
     * If $options has an element with the key 'url', the
     * value will be used as the redirect URL. Otherwise, if $options contains
     * the key 'a', the value will be used as an action path to redirect to. If
     * $options contains an element called 'params', the value will be used as an
     * array of request parameters to be added to the request.
     *
     * @param $options string|array
     */
    function redirect($options=array())
    {
        if (is_string($options)) {
            $options = array('url' => $options);
        }
        
        if ($url = array_delete_at($options, 'url')) {
            // Do nothing
        }
        else if (!gv($options, 'a')) {
            //$url = mm_getConfigValue('urls.mm_root');
            $url = '';
            $controller = array_delete_at($options, 'controller');
            if (!$controller) {
                throw new Exception("No controller specified in redirect");
            }
            $action = array_delete_at($options, 'action');
            if (!$action) $action = 'default';
            $options['a'] = $controller . '.' . $action;
        }
    
        $params = array_delete_at($options, 'params');
        if ($params) {
            $options = array_merge($options, $params);
        }
        
        if ($options) {
            $query = makeQuery($options);
            $url = appendQueryToUrl($url, $query);
        }
        
        if ($url) {
            redirect($url);
            return;
        }
    }

    /**
     * Get the template engine that will render the view template.
     *
     * @return object
     */
    public function getTemplateEngine() {
        if (!$this->_template_engine) {
            $this->_template_engine = new tpl_PHPTemplateEngine($this);
        }
        return $this->_template_engine;
    }
    
    /**
     * Render the view for the given action name.
     *
     * @param $action string An action name
     */
    function renderAction($action) {
        //$engine = $this->getTemplateEngine();
        if ($action) {
            $this->main = $this->getEffectiveTemplate();
            ob_start();
            $exceptionInMain = null;
            try {
                $this->render($this->main);
            } catch (Exception $exceptionInMain) { /* empty */ }
            $content = ob_get_clean();
            
            if ($exceptionInMain) {
                trigger_error("There was an exception thrown during the rendering of the main template: "
                    . $exceptionInMain->getMessage() . "\n"
                    . $exceptionInMain->getTraceAsString() . "\n"
                    . (trim($content) ? ("Here is the content up until the error:\n" . $content) : "No content was rendered"));
                
                /* For some reason, this is necessary */
                foreach ($this->getErrors() as $error) {}
                
                if (!$this->getErrors()) {
                    $this->addError("There was an error.");
                }
            } else {
                $this->addContent($content);
            }
            $this->main = null;
        }
        
        return $this->renderLayout();
    }
    
    /**
     * Render the layout template.
     */
    function renderLayout()
    {
        $layout_tpl = $this->getLayoutTemplate();
        return $this->render($layout_tpl, array('mark_template' => false));
    }

    /**
     * Render a page wrapped inside the layout template.
     *
     * @param $tpl string The template path
     */
    function renderPage($tpl) {
        //$engine = $this->getTemplateEngine();

        // Render the action's template, buffering the output.
        $this->main = $tpl;
        ob_start();
        $this->render($this->main);
        $this->addContent(ob_get_clean());
        $this->main = null;
        
        $layout_tpl = $this->getLayoutTemplate();
        return $this->render($layout_tpl, array('mark_template' => false));
    }
    
    /**
     * Get the layout template path.
     *
     * @return string
     */
    function getLayoutTemplate() {
        if ($this->layout) {
            return $this->layout;
        }
        else {
            $this->theme_type = isset($this->theme_type) ? $this->theme_type : 'public';
            $setting = 'theme.' . $this->theme_type;
            $this->layout = mm_getConfigValue('filepaths.themes') . '/' .
                mm_getSetting($setting, mm_getConfigValue($setting)) .
                '/layout';
            return $this->layout;
        }
    }

    /**
     * Render a template
     *
     * @param $tpl string  The template path
     */
    function render($tpl, $options=null) {
        $options = $options ? $options : array();
        $engine = $this->getTemplateEngine();
        $tpl = mvc_Hooks::getTemplateOverride($tpl);
        $path = str_replace('\\', '/', $tpl);

        // Handle templates that have a URL
        if (!preg_match('/^\/|([a-z]:)/i', $path) && strpos($path, '://') === false) {
            $ext = '.tpl';
            if (isset($engine->file_extension)) $ext = $engine->file_extension;
            if (isset($engine->type) && $engine->type=='php') {
                $parts = explode('/', $path);
                $plugin = array_shift($parts);
                array_unshift($parts, 'templates');
                array_unshift($parts, $plugin);
                $path = implode('/', $parts);
            }
            if (endswith($path, $ext)) {
                $path = substr($path, 0, strlen($path)-strlen($ext));
            }
            $path = str_replace('.', '', $path);
            $path .= $ext;
        }

        // Add extension if it doesn't exist
        if (!preg_match('/.*(\.[a-z]+)$/i', $path)) {
            if ($engine->file_extension) {
                $path .= $engine->file_extension;
            }
            else {
                $path .= '.php';
            }
        }

        //$engine->caching = true;
        $engine->cache_lifetime = 3600 * 24; // 1 day
        //$engine->clear_all_cache();
        return $engine->display($path, $options);
    }
    
    /**
     * Render a template, and save it to a named area for insertion into the layout template.
     */
    function renderToArea($areaName, $tpl, $options=array()) {
        ob_start();
        $this->render($tpl, $options);
        $out = ob_get_clean();
        if (!isset($this->contentByName[$areaName])) {
            $this->contentByName[$areaName] = array();
        }
        $this->contentByName[$areaName][] = $out;
    }
    
    /**
     * Get the saved content for the named area.
     */
    function contentForArea($areaName) {
        if (!isset($this->contentByName[$areaName])) {
            return '';
        }
        return implode("\n", $this->contentByName[$areaName]);
    }
    
    /**
     * Get the module name for this controller object.
     *
     * @return string
     */
    function getModuleName() {
        if ($this->controller_name) {
            return $this->controller_name;
        }
        else if (preg_match('/^(.*)_Controller$/', get_class($this), $match)) {
            return $match[1];
        }
        else {
            return mm_getPluginName($this);
        }
    }
    
    /**
     * Add content to an output buffer to be added to the output stream later.
     *
     * @param $content string
     */
    function addContent($content) {
        $this->_content .= $content;
    }
    
    /**
     * Get the contents of the output buffer.
     */
    function getContent() {
        return $this->_content;
    }
    
    /**
     * Get an array of the core view helper objects.
     *
     * @return array An array of mvc_ViewHelper object
     */
    protected function _getCoreViewHelpers() {
        if (!isset($this->_core_view_helpers)) {
            $this->_core_view_helpers = array();
            $this->_core_view_helpers[] = new tpl_WriteUrl($this);
            $this->_core_view_helpers[] = new mvc_HtmlWriter($this);
            $this->_core_view_helpers[] = new mvc_ModelHtmlWriter($this);
        }
        return $this->_core_view_helpers;
    }
    
    /**
     * Get an array of the view helper objects.
     *
     * @return array
     */
    function getViewHelpers() {
        return array_merge($this->_getCoreViewHelpers(), $this->view_helpers);
    }
    
    /**
     * Delegate calls from the templates to the view helpers.
     */
    function __call($func, $args) {
        foreach (array_reverse($this->getViewHelpers()) as $view_helper) {
            if ($view_helper && method_exists($view_helper, $func)) {
                return call_user_func_array(array($view_helper, $func), $args);
            }
        }
        throw new Exception("No method exists '$func()' in controller '" . get_class($this) . "' or the registered view helpers");
    }
    
    /**
     * Get a request parameter value.
     *
     * @param $key string  The parameter name
     * @param $default mixed The return value if the parameter does not exist.
     * @return string The parameter value
     */
    function req($key, $default=null) {
        if (array_key_exists($key, $this->getRequest())) {
            return $this->request[$key];
        }
        else {
            return $default;
        }
    }
    
    /**
     * Returns a description of the "paged results" navigation links of search 
     * results.
     *
     * Describes the page number of the random-access links, offset, 
     * "previous page" offset, and the "next page" offset.
     *
     * @param int $count The number of results 
     * @param int $offset The offset of the current results
     * @param int $max_results The maximum number of results displayed per page
     * @param int $max_links The maximum number of random-access navigation links
     * @param int $extra_params Additional parameters to add to every link
     * @return array Special data structure
     * @todo Clean up this function; should create a mvc_ResultsNav object and return it
     */
    function getResultsNav($count=null, $offset=null, $max_results=null, $max_links=null, $extra_params=null)
    {
        if ($count === null) $count = $this->count;
        if ($offset === null) $offset = $this->offset;
        if ($max_results === null) $max_results = $this->max_per_page;
        if ($max_links === null) $max_links = $this->max_links;
        if ($extra_params === null) $extra_params = $this->extra_params;
        
        if (!$count) return null;
        if (!$max_links) $max_links = 10;
        if (!$max_results) $max_results = 20;
        
        // Calculate $current_page_index
        $current_page_index = intval($offset/$max_results);

        // Calculate $first_page_index
        $first_page_index = intval($current_page_index/$max_links) * $max_links;
            
        // Calculate $num_links
        if( 
           $count - ($first_page_index * $max_results) >=
           ($max_results * $max_links)
           )
        {
            $num_links = $max_links;
        }
        else
        {
            $num_links = ceil(($count - ($first_page_index * $max_results)) / 
                    $max_results);
        }
        if( $num_links == 1 && $count < ($max_results*$max_links) ) {
            return;
        }
            
        $this->numbered = array();
            
        for ( $link_index = 0; $link_index < $num_links; $link_index++ )
        {
            $link_data = array();
            
            // Set the page number
            $link_data["page_number"] = $first_page_index + $link_index + 1;
            
            // Set the link flag
            if ( ($first_page_index + $link_index) == $current_page_index ) {
                $link_data["current_page"] = true;
            }
            else {
                $link_data["current_page"] = false;
            }
            
            // Set the offset
            $t_offset = ($first_page_index + $link_index) * $max_results;
            $params = "offset=" . intval($t_offset);
            $link_data['offset'] = $t_offset;
            
            // Set the extra parameters
            if ($extra_params) {
                $params .= "&" . uriQuery($extra_params);
            }
            $link_data["params"] = $params;
            
            $this->numbered[$link_index] = $link_data;
        }

        // Set "previous page" details
        $this->previous = array();
        if( $current_page_index > 0 )
        {
            $link_data = array();
            
            // Get offset
            $t_offset = ($current_page_index - 1) * $max_results;
            $params = "offset=".intval($t_offset);
            $link_data['offset'] = $t_offset;
            
            // Set the extra parameters
            if ($extra_params) {
                $params .= "&" . uriQuery($extra_params);
            }
            $link_data["params"] = $params;

            $this->previous = $link_data;
        }
            
        // Set "next page" details
        $this->next = array();
        if( ($current_page_index+1)*$max_results < $count )
        {
            $link_data = array();
            
            // Get offset
            $t_offset = ($current_page_index+1) * $max_results;
            $params = "offset=".intval($t_offset);
            $link_data['offset'] = $t_offset;
                
            // Set the extra parameters
            if ($extra_params) {
                $params .= "&" . makeQuery($extra_params);
            }
            $link_data["params"] = $params;

            $this->next = $link_data;
        }

        // Populate the ResultsNav object
        $nav = new mvc_ResultsNav;
        $nav->count = $count;
        $nav->offset = $offset;
        $nav->max_per_page = $max_results;
        $nav->max_links = $max_links;
        $nav->extra_params = $extra_params;
        $nav->numbered = $this->numbered;
        $nav->previous = $this->previous;
        $nav->next = $this->next;
        
        return $nav;
    }
    
    /**
     * Get the text that goes in the title element of the HTML document.
     *
     * @return string
     */
    function getHeadTitle() {
        if (!$this->window_title) {
            if ($this->title) {
                $this->window_title = $this->title . ' - ' . mm_getSetting('site.name');
            }
            else {
                $this->window_title = mm_getSetting('site.name');
            }
        }
        return $this->window_title;
    }
    
    /**
     * Returns TRUE if the current user has administrator privileges.
     *
     * @return boolean
     */
    function isAdmin() {
        $user = mm_getUser();
        if (!$user) return false;
        return $user->isAdmin();
    }
    
    /**
     * Delegates property access to the view helpers.
     */
    function __get($name) {
        $method = 'get' . ucfirst(camelize($name));
        $helpers = $this->getViewHelpers();
        $helpers[] = $this;
        foreach (array_reverse($helpers) as $view_helper) {
            if ($view_helper && method_exists($view_helper, $method)) {
                return call_user_func(array($view_helper, $method));
            }
        }
        
        $vars = get_object_vars($this);
        if (array_key_exists($name, $vars)) {
            return $vars[$name];
        }
        else {
            return null;
        }
    }
    
    /**
     * Returns database-stored content for the given content name.
     *
     * @param $name string
     * @return string
     */
    function dbContent($name) {
        $this->content_name = $name;
        mm_renderContent($name, $this);
    }
    
    /**
     * Render a "edit content" link for the current content_name property.
     */
    function editLink() {
        $user = mm_getUser();
        if ($user && $user->isAdmin()) {
            return $this->linkTag(h("edit '{$this->content_name}'"), mm_actionToUri('content.edit?name=' . urlencode($this->content_name)));
        }
    }
    
    /**
     * Render a template the belongs to the current theme.
     *
     * @param $tpl string  The template path
     */
    function showThemeTemplate($tpl)
    {
        $base = mm_getConfigValue('filepaths.themes') . '/' . $this->getTheme();
        $this->render("$base/$tpl");
    }
    
    /**
     * Get the current theme name.
     *
     * @return string The theme name
     */
    function getTheme()
    {
        $theme_type = $this->theme_type ? $this->theme_type : 'public';
        return mm_getSetting("theme.$theme_type", mm_getConfigValue("theme.$theme_type"));
    }
    
    /**
     * Get the current action path.
     *
     * @return string
     */
    function getActionUri()
    {
        return $this->getModuleName() . '.' . $this->action;
    }
    
    /**
     * Add some HTML to the output document's HEAD tag.
     *
     * @param $content string
     */
    function addHeadContent($content)
    {
        $this->head_content[] = (object) array('type' => 'string', 'content' => $content);
    }
    
    /**
     * Add an external JavaScript include.
     *
     * @param $url string
     */
    function addJavascriptInclude($url)
    {
        $this->head_content[] = (object) array('type' => 'javascript', 'url' => $url);
    }
    
    /**
     * Add some inline JavaScript code to the output document's HEAD tag.
     *
     * @param $code string
     */
    function addJavascriptCode($code)
    {
        $this->head_content[] = (object) array('type' => 'javascript', 'body' => $code);
    }
    
    /**
     * Add an external stylesheet to the output document's HEAD tag.
     *
     * @param $url string
     */
    function addStylesheetInclude($url)
    {
        $this->head_content[] = (object) array('type' => 'stylesheet', 'url' => $url);
    }

    /**
     * Add some inline CSS code to the output document's HEAD tag.
     */
    function addStylesheetStyles($styles)
    {
        $this->head_content[] = (object) array('type' => 'stylesheet', 'body' => $styles);
    }
    
    /**
     * Render a JavaScript tag with an external source.
     *
     * @param $url string
     * @return string  The rendered tag
     */
    function renderJavascriptInclude($url)
    {
      return sprintf('<script type="text/javascript" src="%s"></script>', h(appendQueryToUrl($url, 'v=' . mm_version())));
    }
    
    /**
     * Render a JavaScript script tag with embedded source.
     *
     * @param $code string  The JavaScript source code.
     * @return string  The rendered tag
     */
    function renderJavascriptCode($code) {
      return sprintf('<script type="text/javascript">%s</script>', "\n<!--\n" . h($code) . "\n// -->\n");
    }
    
    /**
     * Render the tag for an external CSS stylesheet.
     *
     * @param $url string
     * @return string  The rendered tag
     */
    function renderStylesheetInclude($url) {
      return sprintf('<link rel="stylesheet" href="%s" type="text/css" />', h(appendQueryToUrl($url, 'v=' . mm_version())));
    }
    
    /**
     * Render a CSS STYLE tag with embedded CSS.
     *
     * @param $styles string  Some CSS code.
     * @return string
     */
    function renderStylesheetStyles($styles) {
      return sprintf('<style type="text/css">%s</style>', "\n" . h($styles) . "\n");
    }
    
    /**
     * Render the HEAD content that was collected with the add*() methods.
     *
     * @return string  The rendered HTML
     */
    function renderHeadContent()
    {
        $head_array = array();
        foreach ($this->head_content as $content) {
            if ($content->type == 'string') {
                $head_array[] = $content->content;
            }
            else if ($content->type == 'javascript') {
                if ($content->url) {
                    $head_array[] = $this->renderJavascriptInclude($content->url);
                }
                else {
                    $head_array[] = $this->renderJavascriptCode($content->body);
                }
            }
            else if ($content->type == 'stylesheet') {
                if ($content->url) {
                    $head_array[] = $this->renderStylesheetInclude($content->url);
                }
                else {
                    $head_array[] = $this->renderStylesheetStyles($content->body);
                }
            }
        }
        return implode("\n", $head_array);
    }
    
    /**
     * Whether the current request is a HTTP POST method.
     *
     * @return boolean
     */
    function getIsPost() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    
    /**
     * A list of CSS classes that can be used to uniquely identify a page by its controller and action.
     *
     * @return string A space-separated list of CSS classes.
     */
    function getPageIdentifiers() {
        return "controller-{$this->controller_name} action-{$this->action} a-{$this->controller_name}-{$this->action}";
    }
    
    /**
     * Callback to allow controller to affect the processing of the action before the action's
     * method is called.
     *
     * @param $actionName string The action name
     */
    function beforeAction($actionName)
    {
        return true;
    }
    
    /**
     * Specify an additional action to be executed.
     *
     * This method may either be called within an action method or within
     * the beginAction() method. When called from beginAction(), the actual
     * action to execute will be replaced by a new one.
     *
     * @param $actionPath string An action, in action path notation
     */
    function setForward($actionPath)
    {
        $this->forward = $actionPath;
    }
    
    /**
     * @deprecated
     */
    function setReturnAction($actionPath)
    {
        $this->returnAction = $actionPath;
    }
    
    /**
     * Set the template that will be rendered automatically after the action handler is finished.
     *
     * The $template should be in ${MODULE}/${NAME} format, where ${MODULE} is the module name,
     * and ${NAME} is the template file without the file extension.
     *
     * @param $templatePath string
     */
    function setTemplate($templatePath)
    {
        $this->template = $templatePath;
    }
    
    function getEffectiveTemplate()
    {
        // Render the action's template, buffering the output.
        $this->action_name = $this->action;
        $this->action_path = $this->controller_name . '.' . $this->action_name;
        $plugin = $this->getModuleName();
        if ($this->template) {
            $template = $this->template;
        } else {
            $actionParts = explode('.', $this->action);
            if (count($actionParts) > 1) {
                $template = implode('/', $actionParts);
            } else {
                $path_1 = str_replace('_', '/', $this->controller_name);
                $template = $path_1 . '/' . $this->action;
            }
        }

        return $template;
    }
    
    /**
     * @deprecated Use setForward() instead
     */
    function goToDefault()
    {
        $this->setForward($this->getModuleName());
    }

    /**
     * @deprecated Use setForward() instead
     */
    function getToError()
    {
        $this->setForward($this->getModuleName() . '.error');
    }
    
    /**
     * @deprected Use redirectToAction() instead.
     */
    function goToRedirect($action)
    {
        $this->redirectToAction($action);
        return false;
    }
    
    /**
     * @deprecated Use setTemplate() instead
     */
    function goToView($template=null)
    {
        if ($template) {
            $this->setTemplate($template);
        }
    }
    
    /**
     * Get a view helper.
     *
     * @param $module string The module of the helper.
     * @return mvc_ViewHelper
     */
    function getHelper($module=null)
    {
        /* Default is this module */
        if (!$module) $module = $this->getModule();
        
        if (!isset($this->_cached_helpers[$module])) {
            $class = $module . '_Helper';
            $this->_cached_helpers[$module] = new $class($this);
        }
        
        return $this->_cached_helpers[$module];
    }
}
