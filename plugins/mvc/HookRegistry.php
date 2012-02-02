<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_HookRegistry
{
    public $id;
    private $controllers;
    private $view;
    private $before_action_callbacks;
    private $after_action_callbacks;
    private $actions;
    private $_menus = array();
    private static $instance;
    
    /**
     * Holds a lookup table of instances, each referenced by a unique ID
     * @var array
     */
    public static $instances;
    
    public $model_extensions;
    public $template_overrides;

    private function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->id = uniqid('');
        $this->controllers = array();
        $this->before_action_callbacks = array();
        $this->after_action_callbacks = array();
        $this->actions = array();
    }

    public static function instance($id=null)
    {
        if ($id) {
            if (!isset(self::$instances)) {
                return null;
            }
            else {
                return self::$instances[$id];
            }
        }
        elseif (!isset(self::$instance)) {
            self::$instance = self::newInstance();
        }
        return self::$instance;
    }

    public static function newInstance()
    {
        $reg = new mvc_HookRegistry;
        if (!isset(mvc_HookRegistry::$instances)) {
            mvc_HookRegistry::$instances = array();
        }
        mvc_HookRegistry::$instances[$reg->id] = $reg;
        return $reg;
    }

    /**
     * Register controller to handle requests.
     *
     * Controller may override existing controller where $module_name matches module name of existing
     * controller, and $priority is greater than priority of existing controller.
     *
     * @param int $priority Lower value is higher priority.
     */
    public function registerController($module_name, $object_or_classname, $priority=0)
    {
        $reg = (object) array(
            'module'     => $module_name,
            'controller' => $object_or_classname,
            'priority'   => $priority);
        $this->controllers[] = $reg;
        return $reg;
    }

    public function lookupController($module_name, $default=null)
    {
        foreach ($this->controllers as $reg) {
            if (ucfirst($reg->module) == ucfirst($module_name)) {
                return $reg;
            }
        }
        return $default;
    }

    /**
     * Register view to handle requests.
     *
     * View may override existing view where $module_name matches module name of existing
     * view, and $priority is greater than priority of existing view.
     *
     * @param int $priority Lower value is higher priority.
     */
    public function registerView($module_name, $object_or_classname, $priority=0)
    {
        $reg = (object) array(
            'module'     => $module_name,
            'view'       => $object_or_classname,
            'priority'   => $priority);
        $this->views[] = $reg;
        return $reg;
    }

    public function lookupView($module_name, $default=null)
    {
        foreach ($this->views as $reg) {
            if (ucfirst($reg->module) == ucfirst($module_name)) {
                return $reg;
            }
        }
        return $default;
    }

    /**
     * Register an action callback that can override another action with same URI.
     */
    public function registerAction(mvc_ActionCallback $reg)
    {
        $this->actions[] = $reg;
        return count($this->actions);
    }

    public function lookupAction($action_uri)
    {
        $priority_to_callback = array();

        foreach ($this->actions as $reg) {
            if (ucfirst($reg->action_uri) == ucfirst($action_uri)) {
                $priority_to_callback[$reg->priority] = $reg;
            }
        }
        if (!$priority_to_callback) return null;
        ksort($priority_to_callback);
        return array_shift($priority_to_callback);
    }

    public function registerMenu($menu)
    {
        $this->registerMenuItem($menu);
    }

    public function setMenus($menus)
    {
        $this->_menus = $menus;
    }

    public function registerMenuItem($item)
    {
        $item = admin_Menu::normalizeItem($item);
        $item->registry_id = $this->id;
        if (!$item->path) {
            throw new Exception("Menu item cannot be added without path");
        }
        $this->_menus[] = $item;
    }
    
    public function matchMenuPath($menu_list, $path)
    {
        foreach ($menu_list as $index=>$menu) {
            if ($path == $menu->path) return $index;
        }
        return false;
    }

    public function getMenus()
    {
        return $this->_menus;
    }

    public function getMenu($path)
    {
        foreach ($this->_menus as $menu) {
            if ($menu->path == $path) return $menu;
        }
        return null;
    }
    
    public function getSubMenus($path='')
    {
        if (!$path) return $this->getRootMenus();
        
        $path_parts = explode('/', $path);
        $path_size = count($path_parts);
        $matches = array();
        foreach ($this->_menus as $menu) {
            $parts = explode('/', $menu->path);
            $size = count($parts);
            if ($size == $path_size + 1 && array_slice($parts, 0, $path_size) == $path_parts) {
                $matches[] = $menu;
            }
        }
        
        return $this->prioritizeMatches($matches);
    }
    
    public function getRootMenus()
    {
        foreach ($this->_menus as $menu) {
            $parts = explode('/', $menu->path);
            if (count($parts) == 1) {
                $matches[] = $menu;
            }
        }

        return $this->prioritizeMatches($matches);
    }
    
    protected function prioritizeMatches($matches) {
        if ($matches) {
            //$this->displayPriorities($matches, "before");
            //$matches = array_reverse($matches);
            usort($matches, array($this, 'comparePriorities'));
            //$this->displayPriorities($matches, "after");
        }
        return array_reverse($matches);
    }
    
    public function comparePriorities($a, $b) {
        if ($a->priority == $b->priority) return 0;
        return -$a->priority > -$b->priority ? 1 : -1;
    }
    
    function displayPriorities($matches, $msg) {
        echo "Priorities $msg:\n";
        foreach ($matches as $menu) {
            echo "  $menu->path: $menu->priority\n";
        }
    }
    
    function extendModel($base_class, $extension_class) {
        if (!isset($this->model_extensions)) {
            $this->model_extensions = array();
        }
        $this->model_extensions[] = array($base_class, $extension_class);
    }
    
    function getExtensionsForModelClass($base_class) {
        if (!isset($this->model_extensions)) return array();
        $extension_classes = array();
        foreach ($this->model_extensions as $ext) {
            if ($ext[0] == $base_class) {
                $extension_classes[] = $ext[1];
            }
        }
        return $extension_classes;
    }
    
    function overrideTemplate($original_template, $new_template) {
        if (!isset($this->template_overrides)) $this->template_overrides = array();
        $this->template_overrides[] = array($original_template, $new_template);
    }
    
    function getTemplateOverride($original_template) {
        $new_template = $original_template;
        // Later overrides take precedence of earlier ones
        if (isset($this->template_overrides)) {
            foreach (array_reverse($this->template_overrides) as $o) {
                if ($o[0] == $original_template) {
                    $new_template = $o[1];
                    break;
                }
            }
        }
        return $new_template;
    }
}
