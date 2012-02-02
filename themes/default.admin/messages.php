<?php

/**
 * @package themes-default-admin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

// Display errors, warnings and notices

//
// Errors
//
$errors = $this->getErrors(true);
// Multiple errors
if ($errors && count($errors)>1)
{
    print "<div class=\"error\">\n";
    print "  <span class=\"messageIntro\">The following problems occurred:</span>\n<ol>\n";
    foreach ($errors as $msg)
    {
        print "  <li>" . h($msg) . "</li>\n";
    }
    print "</ol>\n</div>\n";
}
// Single error
else if ($errors)
{
    print "<div class=\"error\">\n";
    print "  <span class=\"messageIntro\">An error occurred:</span>\n";
    print h("$errors[0]\n");
    print "</div>\n";
}

//
// Warnings
//
$warnings = $this->getWarnings(true);
// Multiple warnings
if ($warnings && count($warnings)>1)
{
    print "<div class=\"warning\">\n";
    print "  <span class=\"messageIntro\">The following problems were found:</span>\n<ol>\n";
    foreach ($warnings as $msg)
    {
        print "  <li>" . h($msg) . "</li>\n";
    }
    print "</ol>\n</div>\n";
}
// Single warning
else if ($warnings)
{
    print "<div class=\"warning\">\n";
    print "  <span class=\"messageIntro\">Warning:</span>\n";
    print h("$warnings[0]\n");
    print "</div>\n";
}

//
// Notices
//
$notices = $this->getNotices(true);
// Multiple notices
if ($notices && count($notices)>1)
{
    print "<div class=\"notice\">\n";
    print "  <span class=\"messageIntro\">Notice:</span>\n<ol>\n";
    foreach ($notices as $msg)
    {
        print "  <li>" . h($msg) . "</li>\n";
    }
    print "</ol>\n</div>\n";
}
// Single notice
else if ($notices)
{
    print "<div class=\"notice\">\n";
    print "  <span class=\"messageIntro\">Notice:</span>\n";
    print h("$notices[0]\n");
    print "</div>\n";
}
