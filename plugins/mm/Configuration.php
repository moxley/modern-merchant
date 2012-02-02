<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_Configuration
{
    private $values;
    private $db_loaded = false;

    function __construct(&$config_hash)
    {
        $this->values =& $config_hash;
    }
    
    function get($key, $default=null)
    {
        return $this->getRef($key, $default);
    }
    
    function &getRef($key, $default=null)
    {
        if (!$this->db_loaded) $this->loadDBSettings();
        if (!array_key_exists($key, $this->values)) {
            return $default;
        }
        return $this->values[$key];
    }

    function set($key, $value)
    {
        $this->setRef($key, $value);
    }
    
    function setRef($key, &$value)
    {
        $this->values[$key] =& $value;
    }

    /**
     * Alias for mergeAssoc()
     */        
    function mergeHash($config_hash)
    {
        return $this->mergeAssoc($config_hash);
    }
    
    /**
    * Merge a associative array into the configuration.
    *
    * $config_hash may have one and two dimensional elements
    */
    function mergeAssoc($config_assoc)
    {
        if( ! $this->values )
        {
            $this->values =& $config_assoc;
            return;
        }
        hash_merge($this->values, $config_assoc);
    }
    
    /**
     * Get a group of configuration key-value pairs
     *
     * Use this to limit the configuration values to a local portion of the configuration
     *
     * @param string Configuration sub-key, for instance, 'payment_method.paypal' retreives all the values who's keys start with 'payment_method.paypal'
     * @return object Configuration
     */
    function getGroup($base)
    {
        $keys = array_keys($this->values);
        $group = array();
        for( $i=0; $i < count($keys); $i++ )
        {
            if( strpos($keys[$i], $base) === 0 )
            {
                if( strlen($keys[$i])+1 > $base )
                {
                    $new_key = substr($keys[$i], strlen($base)+1);
                    $group[$new_key] =& $this->values[$keys[$i]];
                }
                else
                {
                    $group[] =& $this->values[$keys[$i]];
                }
            }
        }
        
        $c = new mm_Configuration($group);
        return $c;
    }
    
    function toAssoc()
    {
        return $this->values;
    }

    /**
     * Convert 1-dimensional associative array to multi-dimensional
     *
     * Convert 1-dimensional associative array to multi-dimensional
     * associative array such that:<blockquote>
     * $assoc['parent.child'] = $value;<br>
     * - becomes -<br>
     * $newassoc['parent']['child'] = $value;<br>
     * </blockquote>
     */
    function &toAssocMulti()
    {
        $assoc = array();
        $config =& $this->values;
        foreach( $config as $key=>$value )
        {
            $this->assocAppendValue($assoc, $key, $value);
        }
        
        return $assoc;
    }
    
    function assocAppendValue(&$assoc, $keystr, $value)
    {
        $keyparts = explode('.', $keystr);
        if (count($keyparts) == 1)
        {
            $assoc[$keystr] = $value;
            return;
        }
        else
        {
            if (!array_key_exists($keyparts[0], $assoc))
            {
                $assoc[$keyparts[0]] = array();
            }
            $this->assocAppendValue(
                $assoc[$keyparts[0]],
                implode('.', array_slice($keyparts, 1)),
                $value);
        }
    }
    
    protected function loadDBSettings()
    {
        return;
        
        $dbh = $this->getDatabase();
        $sql = "SELECT name,value FROM mm_setting";
        $res = $dbh->query($sql);
        while ($record = $res->fetchAssoc())
        {
            $this->set($record['name'], $record['value']);
        }
        $res->free();
    }
}
