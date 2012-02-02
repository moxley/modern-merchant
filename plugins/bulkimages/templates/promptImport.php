<?php
/**
 * @package bulkimages
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<p>The bulk image importer imports any number of product images that you upload. The image files should be
    uploaded to <code><?php ph($this->source_path) ?></code>. <code>${MM_LIB}</code> is the location
    of the <code>mm</code> folder on your web site.</p>

<p>The files should be named a special way. Here are some examples:</p>
<pre>  UU0123.2.jpg
  a001.1.jpg
  ywtq67.2.jpeg
</pre>

<p>Each file name has three pieces of information, each piece is separated with a period (.).
    Let's look at the first file in the example. The <code>UU0123</code> is the product's SKU.
    The number <code>2</code> enclosed in periods means that, of all the product's images,
    this image is the #2 image. The <code>.jpg</code> is the image file type -- a JPEG.</p>

<div style="border-bottom: 1px solid black"></div>

<p>
    Number of files    to import in folder &quot;<?php ph($this->source_path) ?>&quot;: <?php ph($this->source_count) ?>
</p>

<p>
    <a href="?a=bulkimages.promptImport">Click</a> to refresh.
</p>

<?php if ($this->source_count) : ?>
<form method="POST" action="?action=bulkimages.import">
    <input type="hidden" name="action" value="bulkimages.import" />
    <input type="submit" value="Import" />
</form>
<p />
<?php endif; ?>
