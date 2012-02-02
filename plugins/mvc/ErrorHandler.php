<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_ErrorHandler
{
    protected $mail_subject;
    
    function __construct()
    {
        $this->prefix_msg = "A system error has occurred";
    }
    
    function activate()
    {
        global $old_mm_error_handler_function;
        
        if( !isset($old_mm_error_handler_function) )
        {
            $old_mm_error_handler_function = set_error_handler(array(&$this, "handler"));
        }
    }
    
    function deactivate()
    {
        global $old_mm_error_handler_function;

        if( isset($old_mm_error_handler_function) )
        {
            set_error_handler($old_mm_error_handler_function);
        }
    }
    
    function handler($errno, $errmsg, $filename, $linenum, $vars, $backtrace=null)
    {
        $skip_msgs = array(
            'var: Deprecated',
            'Redefining already defined constructor',
            'Assigning the return value of new by reference',
            'Only variable references should be returned by reference',
            'is_a(): Deprecated'
        );
        foreach ($skip_msgs as $msg)
        {
            if (strpos($errmsg, $msg) !== false) return;
        }
        
        $errortype = array (
            1    =>  "PHP Error",
            2    =>  "PHP Warning",
            4    =>  "Parse Error",
            8    =>  "PHP Notice",
            16   =>  "Core Error",
            32   =>  "Core Warning",
            64   =>  "Compile Error",
            128  =>  "Compile Warning",
            256  =>  "User Error",
            512  =>  "User Warning",
            1024 =>  "User Notice",
            2048 =>  "Strict Compliance",
        );
        
        $dt = date("d-M-Y H:i:s");
        if ($errno > 1024) {
            $type = 'PHP 5';
        }
        else {
            $type = $errortype[$errno];
        }
        $id = uniqid("error_");
        $errmsg = "[$dt] ($type $id)\n$errmsg\n";
        $message = "$errmsg in $filename on line $linenum";
        if ($backtrace) {
            $trace = $backtrace;
        }
        else {
            $trace = debug_backtrace();
        }
        
        $message .= "\n" . mm_backtraceToString($trace);

        //if ( !($errno & E_NOTICE) && !($errno & E_USER_NOTICE) && !($errno & E_STRICT) )
        //{
        //    throw new Exception($message);
        //}
          
        global $MM_CONFIG;
        if (ini_get('log_errors') && ($errno & error_reporting())) {
            error_log($message, 3, $this->getLogFile());
        }
        
        $message = "[$dt]: $message";

        if( ini_get("display_errors") && ($errno & error_reporting()) )
        {
              print "<b>$type:</b> $errmsg in $filename on line $linenum<br />";
              print "<pre>" . $message . "</pre>";
        }

        if( $errno & (E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING) ) {
            if (gv($_SERVER, 'HTTP_HOST')) {
                $message = "{$this->prefix_msg}:\n\n"
                    . "  Error type: $type\n"
                    . "  REMOTE_ADDR:     " . gv($_SERVER, 'REMOTE_ADDR') . "\n"
                    . "  HTTP_USER_AGENT: " . gv($_SERVER, 'HTTP_USER_AGENT') . "\n"
                    . "  HTTP_HOST:       " . gv($_SERVER, 'HTTP_HOST') . "\n"
                    . "  SCRIPT_FILENAME: " . gv($_SERVER, 'SCRIPT_FILENAME') . "\n"
                    . "  SCRIPT_URI:      " . gv($_SERVER, 'REQUEST_URI') . "\n"
                    . "  Error ID:        $id\n"
                    . "  Message: $message\n"
                    . "Requests:------------------------------\n"
                    . implode("\n",
                              array_map(create_function('$e', 'return date("Y-m-d H:i:s", $e->time) . "\t" . $e->uri . "\t" . $e->ip;'),
                            mm_getSession()->get('request_log', array()))) . "\n"
                    . "---------------------------------------\n"
                    . "\n";
                $this->sendErrorNotification($message);
            }

            $input = $output = array();
            $input['errorId'] = $id;
        }
    }
    
    function sendErrorNotification($message)
    {
        $send_error = mm_getConfigValue('email_errors');
        if ($send_error) {
            $sitename = mm_getSetting('site.name');
            $noreply = mm_getSetting('site.noreply');
            $webmaster = mm_getSetting('webmaster.notification');
            mm_mail(
                $webmaster,
                "$sitename: Modern Merchant System Problem",
                $message,
                "From: \"$sitename\" "
                    ."<$noreply>"
            );
        }
    }

    function getLogFile()
    {
        return mm_getConfigValue('filepaths.general_log');
    }
}
