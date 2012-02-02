/**
 * @package admin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

var Admin = {
	textAreaTaller: function(id) {
		var textArea = document.getElementById(id);
		textArea.rows = (textArea.rows || 3) + 5;
	},
	textAreaShorter: function(id) {
		var textArea = document.getElementById(id);
		var rows = parseInt(textArea.rows || 3);
		if (rows >= 8) {
			textArea.rows = textArea.rows - 5;
		}
	}
}

onBodyLoadListeners = [];
function registerOnBodyLoad(func)
{
	onBodyLoadListeners.push(func);
}

function notifyOnBodyLoad()
{
	for (i=0; i < onBodyLoadListeners.length; i++)
	{
		onBodyLoadListeners[i]();
	}
}

window.onload = notifyOnBodyLoad;

onCatSelectListeners = [];
function addCatSelectedListener(listener) {
	onCatSelectListeners.push(listener);
}

function catOver(obj)
{
	setCatOver(obj);
}

function setCatOver(obj)
{
	obj.defaultClassName = obj.className;
	obj.className = 'selectItemOver';
}

function catOut(obj)
{
	if( obj == currentClicked ) {
		setClicked(currentClicked);
		return;
	}
	setDefault(obj);
}

function catClick(obj, id, name)
{

	for(i=0; i<onCatSelectListeners.length; i++) {
		onCatSelectListeners[i](obj, id, name);
	}

	setClicked(obj);
	if( currentClicked != null ) {
		setDefault(currentClicked);
	}
	currentClicked = obj;
	
	if( window.opener && window.opener.categorySelected ) {
		window.opener.categorySelected(id,name);
	}
	
}

function Content() {

	this.onDelete = function (id, name)
	{
		var doit = confirm("Are you sure you want to delete content " + name + "'?");
		if (doit) {
			window.location = '?action=Content.delete&id=' + id;
		}
	}
}

var content = new Content();

function hideElement(id) {
	var element = document.getElementById(id);
	element.style.visibility = 'hidden';
}

function showElement(id) {
	var element = document.getElementById(id);
	element.style.visibility = 'visible';
}

function visibilityOffOn(id1, id2) {
	hideElement(id1);
	showElement(id2);
}

function getElementsByClassName(className, root) {
  if (!root) root = document.body;
  var matches = [];
  for (var i=0; i < root.childNodes.length; i++) {
    var child = root.childNodes[i];
    if (child.nodeType == 1) { // '1' is element
      if (elementHasClass(child, className)) {
        matches.push(child);
      }
      appendArray(matches, getElementsByClassName(className, child));
    }
  }
  return matches;
}

function elementHasClass(element, className) {
  var classes = element.className.split(/ +/);
  for (var c=0; c < classes.length; c++) {
    if (classes[c] == className) {
      return true;
    }
  }
  return false;
}

function appendArray(a1, a2) {
  for (var i=0; i < a2.length; i++) {
    a1.push(a2[i]);
  }
}
