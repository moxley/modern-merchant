<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div id="admin_list_php">
<style type="text/css">

    A:link.control { color: #AAAAAA; }
    A:visited.control { color: #AAAAAA; }
    A:active.control { color: #FF0000; }
    
    A:hover.control { cursor: hand; cursor: pointer; color: #FFFFFF; }
</style>

<h1>Categories</h1>                   
<script type="text/javascript" language="Javascript">
<!--
    currentClicked = null;
    clickedColor = "#FFAAAA";
    overColor = "#0000FF";
    
    function setInitialCat(id)
    {
        var obj = document.getElementById(id);
        if (!obj) return;

        setClicked(obj);
        if( currentClicked != null ) {
            setDefault(currentClicked);
        }
        currentClicked = obj;
    }
    
    function setDefault(obj)
    {
        if( obj.defaultClassName )
        {
            obj.className = obj.defaultClassName;
            return;
        }
        obj.className = 'selectItemCell';
    }
    
    function setClicked(obj)
    {
        obj.className = 'selectItemSelected';
    }
    
    function popUp(url)
    {
        day =  Date();
        id = day.getTime();
        
        popup = window.open(url,id,'toolbar=0,scrollbars=1,location=0,status=0,menubar=0,resizable=1,width=600,height=500,left = 276,top = 269.5');
        popup = window.open(url,id,'toolbar=0,scrollbars=1,location=0,status=0,menubar=0,resizable=1,width=600,height=500,left = 276,top = 269.5'); // for bugs on Macs and X11
        if (!popup.opener)
        popup.opener = self;
    }

<?php if( $this->edit ) { ?>
    function deleteCategory(id)
    {
        result = confirm("Are you sure you want to delete this category and all its dependencies?");
        if( result ) {
            window.location = "?a=category.delete&id="+escape(id);
            return;
        }
        return;
    }
<?php } ?>

-->
</script>

<style type="text/css">
#catList li {
margin: 0px;
padding: 0px;
}
#catList ul {
padding-left: 15px;
}
#catList ul.list0 {
padding-left: 0px;
}
#catList li {
list-style: none;
}
</style>

<?php
if (!function_exists('printCategories')) {
    function printCategories($categories, $level = 0) {
        print "<ul class=\"list$level\">\n";
        foreach ($categories as $cat) {
            $id = (int) $cat->id;
            $products_link = "?a=product.list&amp;category_id=$id";
            $add_product_link = "?a=product.new&amp;category_id=$id";
            $add_child_link = "?a=category.new&amp;parent_id=$id";
            print "<li id=\"cat$id\">";
            print "<a href=\"$products_link\">" . h($cat->name) . "</a> ";
            print "[ <a href=\"?action=category.edit&id=" . h($cat->id) . "\"";
            print " title=\"Edit\">E</a> ";
            print "<a href=\"$add_child_link\" title=\"Add New Sub-Category\">C</a> ";
            print "<a href=\"$add_product_link\" title=\"Add New Product\">P</a> ";
            print "<a href=\"javascript:void(deleteCategory($id))\" title=\"Delete\">D</a> ";
            print " ]";
            print "</li>\n";
            if ($cat->children) {
                printCategories($cat->children, $level+1);
            }
        }
        print "</ul>\n";
    }
}

$this->root_category = $this->getRootCategory();
if (!$this->root_category) {
    print "<div>No Records Found</div>\n";
}
else {
    print "<div id=\"catList\">\n";
    printCategories($this->root_category);
    print "</div>\n";
}

?>
<a href="?a=product.list&amp;category_id=none">Un-categorized products</a>

<script type="text/javascript">
<?php
    if( isset($this->category_id) )
        print "setInitialCat('cat" . i($this->category_id) . "')";
?>
</script>
<br />
</div>
