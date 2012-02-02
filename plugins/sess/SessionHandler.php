<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class sess_SessionHandler
{
    public $sessname = null;
    private $data;
    
    function __construct() {
        $this->sessname = "ModernMerchant" . str_replace('.', '', mm_version());
    }
    
    /**
     * Starts the session and returns a session object.
     *
     * @return sess_Session The session
     */
    function start()
    {
        global $MM_SESSION;
        
        if (isset($this->data)) {
            $sess = new sess_Session;
            $sess->data =& $this->data;
            return $sess;
        }
        else {
            if (!definedAndTrue('MM_SESSION_STARTED')) {
                session_name($this->sessname);
                $r = session_set_save_handler(
                    "sess_SessionHandler_open",
                    "sess_SessionHandler_close",
                    "sess_SessionHandler_read",
                    "sess_SessionHandler_write",
                    "sess_SessionHandler_destroy",
                    "sess_SessionHandler_gc");
                if (!$r) {
                    throw new Exception("Session failed to start");
                }
            }
            else {
                $this->sessname = session_name();
            }
            
            if (!definedAndTrue('MM_SESSION_STARTED') && mm_getConfigValue('environment') != 'test' && isset($_SERVER['REQUEST_URI'])) {
                session_start();
                define('MM_SESSION_STARTED', true);
            }
            
            if (mm_getConfigValue('environment') == 'test') {
                $_SESSION = array();
            }
            $sess = new sess_Session;
            $MM_SESSION = $sess;
            $sess->data =& $_SESSION;
            $this->data =& $_SESSION;
            if (mm_getConfigValue('environment') != 'test') {
                $sess->sid = session_id();
            }
            else {
                $sess->sid = uniqid();
            }
            //$reqlog = $sess->get('request_log', array());
            //$request_record = (object) array('uri'=>$_SERVER['REQUEST_URI'], 'time' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
            //$reqlog[] = $request_record;
            //$sess->set('request_log', $reqlog);
            $sess->set('REMOTE_ADDR',  @$_SERVER['REMOTE_ADDR']);
            $sess->set('HTTP_REFERER', @$_SERVER['HTTP_REFERER']);
            $sess->set('HTTP_USER_AGENT', @$_SERVER['HTTP_USER_AGENT']);
            
            if (isset($_SESSION['sess_SessionHandler.creation_date'])) {
                $sess->creation_date = (double) $_SESSION['sess_SessionHandler.creation_date'];
            }
            else {
                $sess->set('sess_SessionHandler.creation_date', (double) $sess->creation_date);
            }
            return $sess;
        }
    }
    
    function setData(&$data)
    {
        $this->data =& $data;
    }
    
    function &getData()
    {
        return $this->data;
    }
}

function sess_SessionHandler_open($save_path, $session_name)
{
    return true;
}
    
function sess_SessionHandler_close()
{
    return true;
}

function sess_SessionHandler_read($sid)
{
    global $MM_FOUND_SID;
    
    try {
        $db = mm_getDatabase();
        $row = $db->getOneAssoc("SELECT * FROM mm_session WHERE sid=?", array($sid));
        if ($row) {
            $MM_FOUND_SID = $sid;
        }
        $data = $row['data'];
        //mm_log("Fetched session data (id=$row[id], mdate=$row[modify_date], cdate=$row[creation_date]): $data");
        return $data;
    }
    catch (Exception $e) {
        echo $e->getMessage() . "<br />\n";
        mm_log($e->getMessage());
        echo $e->getTraceAsString();
        mm_log($e->getTraceAsString());
    }
}

function sess_SessionHandler_write($sid, $sess_data)
{
    global $MM_FOUND_SID;
    try {
        $db = mm_getDatabase();
        if ($sid == $MM_FOUND_SID) {
            $db->execute("UPDATE mm_session SET data=?, modify_date=NOW() WHERE sid=?", array($sess_data, $sid));
        }
        else {
            $db->execute("INSERT INTO mm_session (sid,data,creation_date,modify_date) VALUES (?,?,NOW(),NOW())", array($sid, $sess_data));
        }
        return TRUE;
    }
    catch (Exception $e) {
        mm_log($e->getMessage());
        mm_log($e->getTraceAsString());
        return false;
    }
}

function sess_SessionHandler_destroy($sid)
{
    try {
        $dao = new sess_SessionDAO;
        $dao->deleteBySid($sid);
    }
    catch (Exception $e) {
        return false;
    }
    return true;
}

function sess_SessionHandler_gc($maxlifetime)
{
    try {
        $dao = new sess_SessionDAO;
        $dao->deleteExpired($maxlifetime);
    }
    catch (Exception $e) {
        return false;
    }
    return true;
}
