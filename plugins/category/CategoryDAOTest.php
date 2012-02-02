<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class category_CategoryDAOTest extends PHPUnit_Framework_TestCase
{
    private $dao;
    
    function setUp()
    {
        $this->dao = new category_CategoryDAO;
    }
    
    function testGetPrimaryKey()
    {
        $this->assertTrue(isset($this->dao->primary), 'primary key should be defined');
    }
    
    function setUpCategories()
    {
        $db = mm_getDatabase();
        $dao = new category_CategoryDAO;
        $dao->createCategoryTable();
        $sqlt = "INSERT INTO mm_category (id, parent_id, lft, rgt, name) VALUES (?, ?, ?, ?, ?)";
        $category_data = array(
            array(1, 0, 0,  7, 'Product Attributes'),
              array(2, 1, 1, 2, 'Category 1'),
              array(3, 1, 3, 6, 'Category 2'),
                array(4, 3, 4, 5, 'Category 2.1'),
            array(5, 0, 8, 23, 'Product Categories'),
              array(6, 5, 9, 10, "Category 4"),
              array(7, 5, 11, 18, 'Category 5'),
                array(8,  7, 12,  13, 'Category 5.6'),
                array(9,  7, 14,  15, 'Category 5.7'),
                array(10, 7, 16, 17, 'Category 5.8'),
              array(11, 5, 19, 20, 'Category 9'),
              array(12, 5, 21, 22, 'Category 10'));
        // Produces this graph:
        $original = 'id=01 p=00   [00-07] "Product Attributes"
  id=02 p=01   [01-02] "Category 1"
  id=03 p=01   [03-06] "Category 2"
    id=04 p=03   [04-05] "Category 2.1"
id=05 p=00   [08-23] "Product Categories"
  id=06 p=05   [09-10] "Category 4"
  id=07 p=05   [11-18] "Category 5"
    id=08 p=07   [12-13] "Category 5.6"
    id=09 p=07   [14-15] "Category 5.7"
    id=10 p=07   [16-17] "Category 5.8"
  id=11 p=05   [19-20] "Category 9"
  id=12 p=05   [21-22] "Category 10"
';

        foreach ($category_data as $c) {
            $db->execute($sqlt, $c);
        }
    }
    
    /**
     * Move a category up.
     */
    function testSetOrder1()
    {
        $this->setUpCategories();
        $dao = new category_CategoryDAO;
        $all = $dao->allToString();
        
        $subject = $dao->fetch(9);
        $subject->place_before = 8;
        $subject->save();
        
        $actual = $dao->allToString();
        $expected = 'id=01 p=00   [00-07] "Product Attributes"
  id=02 p=01   [01-02] "Category 1"
  id=03 p=01   [03-06] "Category 2"
    id=04 p=03   [04-05] "Category 2.1"
id=05 p=00   [08-23] "Product Categories"
  id=06 p=05   [09-10] "Category 4"
  id=07 p=05   [11-18] "Category 5"
    id=09 p=07   [12-13] "Category 5.7"
    id=08 p=07   [14-15] "Category 5.6"
    id=10 p=07   [16-17] "Category 5.8"
  id=11 p=05   [19-20] "Category 9"
  id=12 p=05   [21-22] "Category 10"
';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Move a category down.
     */
    function testSetOrder2()
    {
        $this->setUpCategories();
        $dao = new category_CategoryDAO;
        $all = $dao->allToString();

        $subject = $dao->fetch(8);
        $subject->place_before = 10;
        $subject->save();

        $actual = $dao->allToString();
        $expected = 'id=01 p=00   [00-07] "Product Attributes"
  id=02 p=01   [01-02] "Category 1"
  id=03 p=01   [03-06] "Category 2"
    id=04 p=03   [04-05] "Category 2.1"
id=05 p=00   [08-23] "Product Categories"
  id=06 p=05   [09-10] "Category 4"
  id=07 p=05   [11-18] "Category 5"
    id=09 p=07   [12-13] "Category 5.7"
    id=08 p=07   [14-15] "Category 5.6"
    id=10 p=07   [16-17] "Category 5.8"
  id=11 p=05   [19-20] "Category 9"
  id=12 p=05   [21-22] "Category 10"
';

        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Move a compound category down.
     */
    function testSetOrder3()
    {
        $this->setUpCategories();
        $dao = new category_CategoryDAO;
        $all = $dao->allToString();

        $subject = $dao->fetch(7);
        $subject->place_before = 12;
        $subject->save();

        $actual = $dao->allToString();
        $expected = 'id=01 p=00   [00-07] "Product Attributes"
  id=02 p=01   [01-02] "Category 1"
  id=03 p=01   [03-06] "Category 2"
    id=04 p=03   [04-05] "Category 2.1"
id=05 p=00   [08-23] "Product Categories"
  id=06 p=05   [09-10] "Category 4"
  id=11 p=05   [11-12] "Category 9"
  id=07 p=05   [13-20] "Category 5"
    id=08 p=07   [14-15] "Category 5.6"
    id=09 p=07   [16-17] "Category 5.7"
    id=10 p=07   [18-19] "Category 5.8"
  id=12 p=05   [21-22] "Category 10"
';
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Move a compound category up.
     */
    function testSetOrder4()
    {
        $this->setUpCategories();
        $dao = new category_CategoryDAO;
        $all = $dao->allToString();

        $subject = $dao->fetch(7);
        $subject->place_before = 6;
        $subject->save();

        $actual = $dao->allToString();
        $expected = 'id=01 p=00   [00-07] "Product Attributes"
  id=02 p=01   [01-02] "Category 1"
  id=03 p=01   [03-06] "Category 2"
    id=04 p=03   [04-05] "Category 2.1"
id=05 p=00   [08-23] "Product Categories"
  id=07 p=05   [09-16] "Category 5"
    id=08 p=07   [10-11] "Category 5.6"
    id=09 p=07   [12-13] "Category 5.7"
    id=10 p=07   [14-15] "Category 5.8"
  id=06 p=05   [17-18] "Category 4"
  id=11 p=05   [19-20] "Category 9"
  id=12 p=05   [21-22] "Category 10"
';
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Set $place_before to a category that has a different parent than $subject.
     */
    function testSetOrder5()
    {
        $this->setUpCategories();
        $dao = new category_CategoryDAO;

        $subject = $dao->fetch(7);
        $subject->place_before = 3;
        $subject->save();

        $actual = $dao->allToString();
        $expected = 'id=01 p=00   [00-15] "Product Attributes"
  id=02 p=01   [01-02] "Category 1"
  id=07 p=05   [03-10] "Category 5"
    id=08 p=07   [04-05] "Category 5.6"
    id=09 p=07   [06-07] "Category 5.7"
    id=10 p=07   [08-09] "Category 5.8"
  id=03 p=01   [11-14] "Category 2"
    id=04 p=03   [12-13] "Category 2.1"
id=05 p=00   [16-23] "Product Categories"
  id=06 p=05   [17-18] "Category 4"
  id=11 p=05   [19-20] "Category 9"
  id=12 p=05   [21-22] "Category 10"
';
        $this->assertEquals($expected, $actual);
    }
    
    function testSetOrder6()
    {
        $this->setUpCategories();
        $dao = new category_CategoryDAO;

        $subject = $dao->fetch(3);
        $subject->place_before = 7;
        $subject->save();

        $actual = $dao->allToString();
        $expected = 'id=01 p=00   [00-03] "Product Attributes"
  id=02 p=01   [01-02] "Category 1"
id=05 p=00   [04-23] "Product Categories"
  id=06 p=05   [05-06] "Category 4"
  id=03 p=01   [07-10] "Category 2"
    id=04 p=03   [08-09] "Category 2.1"
  id=07 p=05   [11-18] "Category 5"
    id=08 p=07   [12-13] "Category 5.6"
    id=09 p=07   [14-15] "Category 5.7"
    id=10 p=07   [16-17] "Category 5.8"
  id=11 p=05   [19-20] "Category 9"
  id=12 p=05   [21-22] "Category 10"
';
        $this->assertEquals($expected, $actual);
    }
}
