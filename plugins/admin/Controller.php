<?php
/**
 * @package admin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Base class for administrator controllers. 
 * @package admin
 */
class admin_Controller extends mvc_Controller
{
    protected $theme_type = 'admin';
    
    function getMaxResults($input=null)
    {
        return mm_getConfigValue('ui.admin_max_list_results');
    }
    
    function getMaxPageLinks()
    {
        return mm_getConfigValue('ui.admin_max_page_links');
    }
    
    function getOffset($input=null, $default=0)
    {
        if ($input == null) $input =& $this->getRequest();
        return intval(gv($input, 'offset', $default));
    }
    
    function getRequestParser()
    {
        return new admin_AdminRequestParser($_SERVER['REQUEST_URI']);
    }
    
    function runDefaultAction()
    {
        $user = mm_getUser();
        if (!$user || !$user->isAdmin()) {
            $this->redirectToAction(mm_getConfigValue('actions.admin_login'));
            return false;
        }
        $this->redirectToAction(mm_getConfigValue('actions.admin_default'));
        return false;
    }

    function beforeAction($action)
    {
        return $this->isAuthorized($action);
    }
    
    function isAuthorized($action)
    {
        $user = mm_getUser();
        if (!$user) {
            $this->addWarning("Please log in");
            $this->redirectToAction('user.login', array('transition' => $this->getModuleName() . '.' . $action));
            return false;
        }
        if (!$user->isAdmin()) {
            // Not logged in
            $this->addWarning("You are not authorized");
            $this->redirectToAction('user.login', array('transition' => $this->getModuleName() . '.' . $action));
            return false;
        }
        return true;
    }
    
    function runNotAuthorized()
    {
        $this->redirectToAction(mm_getConfigValue('actions.admin_login'));
        return false;
    }
    
    function preViewFilter()
    {
        parent::preViewFilter();
        
        $user = mm_getUser();
        if ($user && $user->isAdmin()) {
            $this->edit = true;
        } else {
            $this->edit = false;
        }

        if( $user && $user->username ) {
            $this->SESS_Username = $user->username;
        }
        else {
            $this->SESS_Username = null;
        }
        $this->DBMS = "MySQL";
        
        $this->addJavascriptInclude(mm_getConfigValue('urls.plugins') . '/admin/admin.js');
        $this->addJavascriptInclude(mm_getConfigValue('urls.plugins') . '/admin/JSCookMenu.js');
    }

    function adminBaseUrl() {
        return mm_getConfigValue('urls.mm_root');
    }
    
    function mm_printCategoryOptions($categories, $selected_id) {
        print "<option value=\"\"> - NOT SELECTED - </option>\n";
        foreach ($categories as $c) {
            $selected = '';
            if ($c->id == $selected_id) $selected = ' selected';
            print '<option value="' . h($c->id) . "\"$selected>" .h($this->mm_formatCatNameRecursive($c))
                . "</option>\n";
        }
    }

    function mm_printCategorySelect()
    {
        $dao = new category_CategoryDAO;
        $categories = $dao->getFlattenedHierarchy();

        print "<select id=\"saved_options\" style=\"visibility: hidden; position: absolute\">";
        $this->mm_printCategoryOptions($categories, null);
        print "</select>\n";
    }

    function mm_printParentSelect()
    {
        print "<div id=\"parentSelects\">\n";
        $this->mm_printCategorySelect();
        print "</div>\n";
    }

    function mm_printCategorySelectionsCall($name, $categories)
    {
        print "<script type=\"text/javascript\">\n";
        print "<!--\n";
        foreach ($categories as $cat) {
            $this->mm_printCategorySelectCall($name, $cat->id);
        }
        print "//-->\n</script>\n";
    }

    function mm_printCategorySelectCall($name, $category_id)
    {
        print "CategorySelections.add('$name', " . intval($category_id) . ");\n";
    }

    function mm_printCategorySelectionsJavascript()
    {
    ?>
    <script type="text/javascript">
    <!--
        var selects = null;

        function CategorySelections() {

            this.id = 1;
            this.initialized = false;

            this.init = function() {
                if (this.initialized) return;
                this.container = document.getElementById('parentSelects');
                this.lookup = {};
                this.initialized = true;
            }

            this.add = function(name, categoryId) {
                this.init();

                var div = document.createElement('div');

                var select = document.createElement('select');
                select.name = name || 'parent_ids[]';
                var selectSavedOptions = document.getElementById('saved_options');
                if (selectSavedOptions == null) throw 'Cannot find saved_options';
                var nodes = selectSavedOptions.childNodes;
                var node = null;
                var option = null;
                for (i=0; i < nodes.length; i++) {
                    node = nodes[i];
                    if (node.nodeName == 'OPTION') {
                        option = node.cloneNode(true);
                        if (option.value == categoryId) {
                            option.selected = true;
                        }
                        select.appendChild(option);                    
                    }
                    else {
                        select.appendChild(node.cloneNode(true));
                    }
                }
                div.appendChild(select);

                var deleteLink = document.createElement('a');
                var id = this.id++;
                deleteLink.setAttribute('href', 'javascript:CategorySelections.remove(' + id + ')');
                deleteLink.appendChild(document.createTextNode('Remove from this category'));
                div.appendChild(deleteLink);

                this.container.appendChild(div);

                this.lookup['div' + id] = div;
            }

            this.remove = function(id) {
                var div = this.lookup['div' + id];
                if (div == null) throw 'Cannot find div for given id';
                this.container.removeChild(div);
            }
        }

        var CategorySelections = new CategorySelections();

    // -->
    </script>
    <?php
    }
    
    function mm_formatCatNameRecursive($category) {
        if ($category->parent_id) {
            return $this->mm_formatCatNameRecursive($category->parent) . " > " . $category->name;
        }
        return $category->name;
    }

    //function showResultsNav()
    //{
    //    $base = mm_getConfigValue('templates.shared');
    //    $tpl = "$base/resultsNav.php";
    //    $this->showTemplate($tpl);
    //}

    function showTemplate($template)
    {
        $path = $template;
    
        if (strpos($path, '://') !== false) {
            $file = $path;
        }
        else if (preg_match('/^\/|([a-z]:)/i', $path)) {
            $file = $path;
        }
        else {
            $parts = explode('/', $template);
            $plugins = mm_getConfigValue('plugin_names');
            if (count($parts) > 1 && in_array($parts[0], $plugins)) {
                $plugin = $parts[0];
                $base = mm_getConfigValue('filepaths.plugins') . '/' . $plugin . '/templates';
                $path = substr($path, strlen($plugin) + 1);
            }
            else {
                $base = mm_getConfigValue('filepaths.admin_tpl');
            }
            $file = $base . '/' . $path;
        }
    
        $this->render_start = true;
        include $file;
        return;
    }
}
