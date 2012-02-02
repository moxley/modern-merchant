<?php
/**
 * @package test
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class test_DataGenerator
{
    private $lower = "abcdefghijklmnopqrstuvwxyz";
    private $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private $digits = "0123456789";
    private $chars = "-_!@#\$%^&*()_+<>[]{}\|~`,./'\":; ";
    
    function makeString($length)
    {
        $pool =& $this->getStringPool();
        $pool_len = strlen($pool);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, $pool_len-1);
            $string .= $pool{$index};
        }
        return $string;
    }
    
    function makePosInt($max)
    {
        return rand(1, $max);
    }
    
    function makeIntId()
    {
        return $this->makePosInt(3000);
    }
    
    function makePrice($max)
    {
        $max = (int) $max;
        $whole = rand(1, $max);
        $cents = rand(0, 99);
        return number_format($whole . '.' . $cents, 2);
    }
    
    function &getStringPool()
    {
        static $pool;
        if (!isset($pool)) {
            $lower = "abcdefghijklmnopqrstuvwxyz";
            $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $digits = "0123456789";
            $other = "-_!@#$%^&*()_+<>[]{}\|~`,./'\":;";
            $pool = str_repeat($lower, 2) 
                . $upper 
                . str_repeat($digits, 4)
                . str_repeat(' ', 30)
                . $other;
        }
        return $pool;
    }
    
    function makeLowerLetters($length)
    {
        $letters = '';
        for ($i=0; $i < $length; $i++) {
            $index = rand(0, strlen($this->lower)-1);
            $letters .= $this->lower{$index};
        }
        return $letters;
    }
    
    function makeUpperLetters($length)
    {
        $letters = '';
        for ($i=0; $i < $length; $i++) {
            $index = rand(0, strlen($this->upper)-1);
            $letters .= $this->upper{$index};
        }
        return $letters;
    }
    
    function makeCapWord($length)
    {
        $word = $this->makeUpperLetters(1);
        $word .= $this->makeLowerLetters($length-1);
        return $word;
    }
    
    function makeWords($count)
    {
        $words = '';
        for ($i=0; $i < $count; $i++) {
            if ($i > 0) $words .= ' ';
            $words .= $this->makeWord();
        }
        return $words;
    }
    
    function makeWord($length=0)
    {
        if (!$length) $length = rand(1, 10);
        return $this->makeLowerLetters($length);
    }
    
    function makeName($length=0)
    {
        if (!$length) $length = rand(5, 14);
        $name = $this->makeUpperLetters(1);
        $name .= $this->makeLowerLetters($length - 1);
        return $name . $this->makeChars(2);
    }
    
    function makeNumber($length)
    {
        $digits = rand(1, 9);
        for ($i=0; $i < $length-1; $i++) {
            $index = rand(0, strlen($this->digits)-1);
            $digits .= $this->digits{$index};
        }
        return $digits;
    }
    
    function makeEmail()
    {
        $email = $this->makeLowerLetters(8);
        $email .= '@' . $this->makeLowerLetters(12);
        $email .= '.' . $this->makeLowerLetters(3);
        return $email . $this->makeChars(5);
    }
    
    function makeAddressLine()
    {
        $addr = $this->makeNumber(4) . ' ';
        $addr .= $this->makeName() . ' ';
        $addr .= 'St.';
        return $addr . $this->makeChars(5);
    }
    
    function makeStateCode()
    {
        return $this->makeUpperLetters(2) . $this->makeChars(5);
    }
    
    function makeCountryCode()
    {
        return $this->makeUpperLetters(2) . $this->makeChars(5);
    }
    
    function makeZip()
    {
        return $this->makeNumber(5) . '-' . $this->makeNumber(4) . $this->makeChars(5);
    }
    
    function makePhone()
    {
        return $this->makeNumber(3) . '-' . $this->makeNumber(3) . '-' 
            . $this->makeNumber(4) . $this->makeChars(5);
    }
    
    function makeCompany()
    {
        return $this->makeName() . ' ' . $this->makeName() . $this->makeChars(5);
    }
    
    function makeChars($length)
    {
        $chars = '';
        for ($i=0; $i < $length; $i++) {
            $index = rand(0, strlen($this->chars)-1);
            $chars .= $this->chars{$index};
        }
        return $chars;
    }
    
    function makeDate()
    {
        $years_back = 3;
        $periods = 50;
        $now = time();
        $year = 60 * 60 * 24 * 365.25;
        $period = ($years_back * $year) / $periods;
        $period_number = rand(0, $periods-1);
        return (float) $now - $period * $period_number;
    }
    
    function makeUnique($length)
    {
        $unique = '';
        $pool = $this->lower . $this->digits . $this->digits;
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($pool)-1);
            $unique .= $pool{$index};
        }
        return $unique;
    }
    
    function makeMoney($limit)
    {
        return $this->makePrice($limit);
    }
}
