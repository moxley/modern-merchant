/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
var colDragActivated = false;
var mouseX = -1;
var mouseY = -1;
var startX = -1;
var startY = -1;
var leftCellWidth = 200;
var leftCell = null;
var bar = null;

function initializeDragSettings(leftCellId, barId)
{
  leftCell = document.getElementById(leftCellId);
  bar = document.getElementById(barId);
  bar.onmousedown = startDrag;
  document.onmouseup = stopDrag;
  document.onmousemove = dragIt;
}

function moveByOffset(x, y)
{
  leftCell.width = leftCellWidth + x;
}

function dragIt(evt)
{
  if (!colDragActivated) return;
  if (evt==null)
  {
    x = window.event.clientX;
    y = window.event.clientY;
  }
  else
  {
    x = evt.pageX;
    y = evt.pageY;
  }
  if (startX != -1)
  {
    moveByOffset(x-startX, y-startY);
  }
  mouseX = x;
  mouseY = y;
}

function startDrag(evt)
{
  colDragActivated = true;
  if (evt==null)
  {
    startX = window.event.clientX;
    startY = window.event.clientY;
  }
  else
  {
    startX = evt.pageX;
    startY = evt.pageY;
  }
  leftCellWidth = leftCell.offsetWidth;
}

function stopDrag(evt)
{
  if (colDragActivated==false) return;
  colDragActivated = false;
  startX = -1;
  startY = -1;
  leftCellWidth = parseInt(leftCell.width);
}

