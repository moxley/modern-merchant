/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

currentClicked = null;
clickedColor = "#FFAAAA";
overColor = "#0000FF";

var ProductEdit = {
  popupImage: null,
  onProductImageMouseOver: function(event, img) {
    if (ProductEdit.popupImage) return;
    var mouseX = (typeof(event) != "undefined" && event.clientX) || window.event.x;
    var mouseY = (typeof(event) != "undefined" && event.clientY) || window.event.y;
    //ProductEdit.popupImage = img.cloneNode(true);
    ProductEdit.popupImage = new Image();
    ProductEdit.popupImage.src = img.src;
    ProductEdit.popupImage.style.position = "absolute";
    var scrollX = (typeof(window.scrollX) != "undefined" && window.scrollX) || document.documentElement.scrollLeft;
    var scrollY = (typeof(window.scrollY) != "undefined" && window.scrollY) || document.documentElement.scrollTop;
    ProductEdit.popupImage.style.left = mouseX + scrollX + 1 + "px";
    ProductEdit.popupImage.style.top = mouseY + scrollY + 1 + "px";
    document.body.appendChild(ProductEdit.popupImage);
    ProductEdit.productImageMouseOverState = true;
  },
  onProductImageMouseOut: function(event) {
    if (!ProductEdit.popupImage) return;
    ProductEdit.popupImage.parentNode.removeChild(ProductEdit.popupImage);
    ProductEdit.popupImage = null;
  },
  onSkuClick: function() {
    document.getElementById('product_sku').value = '';
    if (document.getElementById('product_sku_same_as_id_1').checked) {
      document.getElementById('product_sku').disabled = true;
      document.getElementById('product_sku').className = "disabled";
    }
    else {
      document.getElementById('product_sku').className = "";
      document.getElementById('product_sku').disabled = false;
    }
  }
}

var ImageManager = {
  addImageField: function() {
    //var rowsContainer = document.getElementById('new_image_rows');
    var row = document.getElementById('new_image_template').cloneNode(true);
    var newRows = document.getElementById('new_image_rows');
    ImageManager.configureNewRow(row);
    newRows.appendChild(row);
  },
  configureNewRow: function(row) {
    row.id = null;
    row.style.cssText = "";
    var sortOrder = ImageManager.getNextSortOrder();
    //console.log("Next sortOrder: " + sortOrder);
    var inputs = row.getElementsByTagName('input');
    var fileInput = null;
    for (var i=0; i < inputs.length; i++) {
      if (inputs[i].type == "file") {
        fileInput = inputs[i];
        break;
      }
    }
    fileInput.name = "product[image_uploads][" + sortOrder + "]";
  },
  getNextSortOrder: function() {
    var rows = ImageManager.getNewImageRows();
    return -rows.length - 1;
  },
  getNewImageRows: function() {
    var parent = document.getElementById('new_image_rows');
    var rows = [];
    var rowIndex = 0;
    for (var i=0; i < parent.childNodes.length; i++) {
      if (parent.childNodes[i].tagName == 'DIV') {
        rows[rowIndex++] = parent.childNodes[i];
      }
    }
    return rows;
  }
}

function itemOver(obj)
{
	if( !window.opener ) return;
	
	if (!obj.defaultClassName)
	{
		obj.defaultClassName = obj.className;
	}
	obj.className = 'selectItemOver';
}

function itemOut(obj)
{
	if( !window.opener ) return;
	if (obj.isSelected)
	{
		obj.className = 'selectItemSelected';
	}
	else if(obj.defaultClassName)
	{
		obj.className = obj.defaultClassName;
	}
}

function itemClick(obj, id, name)
{
	if( !window.opener ) return;

	if (obj.isSelected)
	{
		obj.isSelected = false;
	}
	else
	{
		obj.isSelected = true;
		obj.className = 'selectItemSelected';
	}		

	if( window.opener && window.opener.formSelected ) {
		window.opener.formSelected(id, name);
	}
}

function deleteProduct(id)
{
	result = confirm("Are you sure you want to delete this product and all its dependencies?");
	if( result ) {
		window.location = "?action=product.delete&id="+escape(id);
		return;
	}
	return;
}

function deleteProducts()
{
	result = confirm("Are you sure you want to delete these products and all their dependencies?");
	if( result ) {
		return true;
	}
	return false;
}

function categorySelectedForProduct(obj, id, name)
{
	document.form1.category_id.value = id;
	document.form1.submit();
}

function showImages(product_id)
{
	alert('showImages(' + product_id + ')');
}

function displayFullSizeImage(image)
{
  
}

// Set up on-hover product images
registerOnBodyLoad(function(event) {
  var hoverImages = getElementsByClassName("image-hover");
  event = typeof(event) == "undefined" ? {} : event;
  for (var i=0; i < hoverImages.length; i++) {
    var f = function() {
      var img = hoverImages[i];
      img.onmouseover = function(event) {
        ProductEdit.onProductImageMouseOver(event, img);
      }
      img.onmouseout = function(event) {
        ProductEdit.onProductImageMouseOut(event, img);
      }
    }
    f();
  }
});

addCatSelectedListener(categorySelectedForProduct);
