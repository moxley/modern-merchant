<?php
/**
 * @package catalog
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package catalog
 */
class catalog_Plugin  extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => 'Product Catalog',
            'version' => '0.2',
            'author'  => 'Moxley Stratton',
            'url'     => 'http://www.modernmerchant.org/',
            'depends' => array('category', 'product'));
    }

    function install() {
        $db = mm_getDatabase();
        
        // This should be done using the data model
        $queries = array();
        $columns = "(`id`, `parent_id`, `lft`, `rgt`, `name`, `url_name`, `image_id`, `description`, `comment`, `sortorder`)";
        $queries[] = "INSERT INTO `mm_category` $columns VALUES (1,0,0,3,  'Product Attributes','Product_Attributes',NULL,'','', 0)";
        $queries[] = "INSERT INTO `mm_category` $columns VALUES (2,1,1,2,  'New','New',NULL,'','', 0)";
        $queries[] = "INSERT INTO `mm_category` $columns VALUES (3,0,4,15, 'Product Categories','Product_Categories',NULL,'','', 0)";
        $queries[] = "INSERT INTO `mm_category` $columns VALUES (4,3,5,10, 'Lampwork Beads','Lampwork Beads',NULL,'','', 0)";
        $queries[] = "INSERT INTO `mm_category` $columns VALUES (5,4,6,7,  'Sets','Sets',NULL,'Beads available here or on eBay.','', 0)";
        $queries[] = "INSERT INTO `mm_category` $columns VALUES (6,3,11,12,'Featured Item','Featured_Item',NULL,'Appears as a full-size image on my homepage','', 0)";
        $queries[] = "INSERT INTO `mm_category` $columns VALUES (7,4,8,9,  'Jewelry','Jewelry',NULL,'Bracelets, earrings, necklaces, pendants, key fobs, lamp pulls, and the occasional bead pen or wine stopper.','', 0)";
        $queries[] = "INSERT INTO `mm_category` $columns VALUES (8,3,13,14,'Fused Glass','Fused_Glass',NULL,'Fused cabochons, tiles, pendants, etc.','', 0)";

        $columns = "(`id`, `created_on`, `modify_date`, `available_on`, `modify_user`, `sku`, `sortorder`, `name`, `active`, `description`, `comment`, `price`, `count`, `weight`)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (1,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'20072',0,'Labyrinth Pendant',1,'Organic barrel focal with mesmerizing labyrinthine webbing! Wired on sterling silver with Bali and Swarovski accents. Bead measures 27x10mm. Pendant measures 45mm overall, comes strung on adjustable black cord and ready to wear.','',18.00,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (2,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'20073',0,'Cosmic Trails Pendant',1,'Dark trails with silver borders and a hint of deep mahogany; mystery encased in transparent. Wired on sterling silver. with Bali and Swarovski accents. Bead measures 12x8mm. Pendant measures 23mm overall, comes strung on adjustable black cord and ready to wear.','',10.00,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (3,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'20133',0,'Subtlety pendant',1,'It\'s not quite as subtle as the picture makes it seem... a light blue base with russet and cobalt, very pretty! Bead measures 12x7mm, pendant measures 25mm long overall. Wired on sterling silver with Bali silver and sparkling Swarovski crystal accents, comes with a black satin cord, ready to wear.','',10.00,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (4,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'20080',0,'Patina Blue Pendant',1,'Copper leaf interacts with white enamel on this little organic bead for a dramatic flash of turquoise amid black and brown webbing. Wired on sterling silver with Bali and Swarovski accents. Bead measures 12x8mm. Pendant measures 15mm overall, comes strung on adjustable black cord and ready to wear.','',10.00,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (5,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'20081',0,'Patina Blue II Pendant',1,'More copper leaf and enamel, for this gorgeous organic patina effect. Wired on sterling silver with Bali and Swarovski accents. Bead measures 10x11mm. Pendant measures 14mm overall, comes strung on adjustable black cord and ready to wear.','',10.00,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (6,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'20086',0,'Mystery Pendant',1,'Silver leaf, cobalt blue, and rubino together for a dark, mysterious bead with hints of burgundy and flashes of mettalic silver. The cobalt becomes apparent only when you hold it up to the light. Wired on sterling silver with Bali and Swarovski accents. Bead measures 11x7mm. Pendant measures 22mm overall, comes strung on adjustable black cord and ready to wear.','',10.00,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (7,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'20089',0,'Wine and Roses Pendant',1,'Deep burgundy plunged florals amid twisting green vines, all on a base of black. Wired on sterling silver with Bali and Swarovski accents. Bead measures 12x7mm. Pendant measures 32mm overall, comes strung on adjustable black cord and ready to wear.','',10.00,1,NULL)";
        $desc = 'Autumn is still a long way off, but thoughts of cooler days are starting to creep in at the corners of my mind; the expressiveness of boro brings forth the colors of fall in these yummy striped beads. Seven beads, measuring about 12x6mm to 14x7mm each, made on 3/32" mandrels. Czech glass spacer beads included.';
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (8,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'0587',0,'Boro beads - Autumn Stripes',1,'$desc','',38.00,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (9,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'1042',0,'Cabochon 27',1,'This bright and sparkling dichroic cabochon is lovingly made and carefully annealed. It measures 26x23mm.','',9.95,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (10,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'1064',0,'Cabochon 44',1,'Like all my work, this bright and sparkling dichroic cab is lovingly made and carefully annealed. It measures 21x19mm. Please note that unlike beads, cabochons do not have holes in them for stringing, but are incorporated into finished work with a metal or beaded bezel, wire-wrapping, or glue.','',9.95,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (11,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'1065',0,'Cabochon 45',1,'Returning to fusing has been like a spa for my muse; I\'d forgotten how fun and inspiring it is! Like all my work, this bright and sparkling dichroic cabochon is lovingly made and carefully annealed. It measures 22x20mm. Please note that unlike beads, cabochons do not have holes in them for stringing, but are incorporated into finished work with a metal or beaded bezel, wire-wrapping, or glue.','',9.95,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (12,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'1066',0,'Cabochon 46',1,'Returning to fusing has been like a spa for my muse; I\'d forgotten how fun and inspiring it is! Like all my work, this bright and sparkling dichroic cabochon is lovingly made and carefully annealed. It measures 27x22mm. Please note that unlike beads, cabochons do not have holes in them for stringing, but are incorporated into finished work with a metal or beaded bezel, wire-wrapping, or glue.','',9.95,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (13,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'1067',0,'Cabochon 47',1,'Returning to fusing has been like a spa for my muse; I\'d forgotten how fun and inspiring it is! Like all my work, this bright and sparkling dichroic cabochon is lovingly made and carefully annealed. It measures 23x22mm. Please note that unlike beads, cabochons do not have holes in them for stringing, but are incorporated into finished work with a metal or beaded bezel, wire-wrapping, or glue.','',9.95,1,NULL)";
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (14,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'1068',0,'Cabochon 48',1,'21x23x6mm','',9.95,1,NULL)";
        $desc = 'Fine silver leaf hot-burnished onto lapis blue. Seven beads, measuring about 11x8mm to 12x9mm each, made on 1/16" mandrels. Czech glass spacer beads included.';
        $queries[] = "INSERT INTO `mm_product` $columns VALUES (15,'2007-05-26 11:06:00','2007-05-26 11:06:00','2007-05-26 11:06:00',NULL,'0606',0,'Elegance',1,'$desc','',30.00,1,NULL)";

        $queries[] = "INSERT INTO `mm_product_category` VALUES (1,  5,8,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (2,  5,15,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (3,  7,1,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (4,  7,2,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (5,  7,3,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (6,  7,4,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (7,  7,5,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (8,  7,6,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (9,  7,7,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (10, 8,9,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (11, 8,10,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (12, 8,11,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (13, 8,12,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (14, 8,13,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (15, 8,14,0)";
        $queries[] = "INSERT INTO `mm_product_category` VALUES (16, 6,8,0)";

        foreach ($queries as $query) {
            $db->execute($query);
        }
        
        mm_setSetting('actions.catalog.default', 'catalog.products');
        mm_setSetting('catalog.default_category', 6);
        mm_setSetting('catalog.root_category', 3);
        mm_setSetting('templates.catalog.category.list', 'catalog/categorygrid');
        mm_setSetting('templates.catalog.product.list', 'catalog/products');
        mm_setSetting('templates.catalog.product.detail', 'catalog/productDetail');
        mm_setSetting('plugins.catalog.products_per_page', 20);

        $this->upgrade_to_0_2();

        return $this->saveSampleImages();
    }

    function upgrade_to_0_1()
    {
        $acd = mm_getSetting('actions.catalog.default');
        if ($acd == "Catalog.products2Tier") {
            mm_setSetting('actions.catalog.default', 'catalog.products');
        }
        mm_setSetting('templates.catalog.category.list', 'catalog/categorygrid');
        mm_removeSetting('templates.catalog.layout');
        mm_setSetting('templates.catalog.product.detail', 'catalog/productDetail');
        mm_setSetting('templates.catalog.product.list', 'catalog/products');
        mm_setSetting('plugins.catalog.products_per_page', 20);

        return true;
    }
    
    function upgrade_to_0_2()
    {
        mm_setSetting('plugins.catalog.sort_order', '{product}.sortorder, {product}.available_on DESC');
    }
    
    function saveSampleImages() {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sample';
        $samples = $this->getSampleData();
        
        foreach ($samples as $sample) {
            $path = $dir . '/' . $sample['filename'];
            $upload = new mvc_FileUpload;
            $upload->file = $path;
            $upload->mime_type = gv($sample, 'mime', 'image/jpeg');
            $upload->size = filesize($path);
            $upload->original = $sample['filename'];
            $upload->keep_source = true;
            
            $media = new media_Media;
            $media->file_upload = $upload;
            $mdao = new media_MediaDAO;
            $product = new product_Product;
            $fetched = $mdao->fetch($sample['id']);
            if (!$fetched) {
                foreach ($sample as $k=>$v) {
                    if ($k == 'id' || $k == 'name') continue;
                    $media->$k = $v;
                }
                if (!$media->save()) {
                    $this->addErrors($media->errors);
                    return false;
                }
            }
        }
        return true;
    }
    
    function getSampleData() {
        $lines = file(dirname(__FILE__) . '/sample/table.txt');
        $rows = array();
        foreach ($lines as $line) {
            $row = explode(',', trim($line));
            if (!$row) continue;
            foreach ($row as $k=>$v) {
                if ($v == "NULL") $row[$k] = null;
                if ($v[0] == "'") {
                    $row[$k] = substr($v, 1, -1);
                }
            }
            $row['id'] = $row[0];
            $row['_owner_type'] = 'product_Product';
            $row['_owner_id'] = $row[1];
            $row['sortorder'] = $row[2];
            $row['name'] = $row[3];
            $row['description'] = $row[4];
            $row['width'] = $row[5];
            $row['height'] = $row[6];
            $row['filename'] = $row[7];
            $row['mime_type'] = $row[8];
            $rows[] = $row;
        }
        return $rows;
    }
    
    function __toString() {
        return '<' . __CLASS__ . '>';
    }
}
