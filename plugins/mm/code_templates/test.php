<?php
/**
 * TestCase generation template.
 *
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

echo <<<TEST_EOF
<?php

class $class extends PHPUnit_Framework_TestCase
{
    function testTruth() {
        \$this->assertTrue(true);
    }
}

TEST_EOF;
