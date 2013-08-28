<?php
class CLog
{
    const LOG_LEVEL_NONE    = 0;
    const LOG_LEVEL_FATAL   = 1;
    const LOG_LEVEL_WARNING = 2;
    const LOG_LEVEL_NOTICE  = 4;
    const LOG_LEVEL_TRACE   = 8;
    const LOG_LEVEL_DEBUG   = 16;
    const LOG_LEVEL_ALL     = 31; 
    const LOG_MAX_FILE_SIZE = 1024000000;

    private $_arrLogLevels = array(
        CLog::LOG_LEVEL_NONE      => 'NONE',
        CLog::LOG_LEVEL_FATAL     => 'FATAL',
        CLog::LOG_LEVEL_WARNING   => 'WARNING',
        CLog::LOG_LEVEL_NOTICE    => 'NOTICE',
        CLog::LOG_LEVEL_TRACE     => 'TRACE',
        CLog::LOG_LEVEL_DEBUG     => 'DEBUG',
        CLog::LOG_LEVEL_ALL       => 'ALL',
    );

    protected $_intLevel;
 
    private function __construct($intLevel = E_ALL)
    {
        $this->_intLevel = 31;//$intLevel;
        //defined('APP_CLIENT_IP') || define('APP_CLIENT_IP', '-');
    }

    public static function getInstance()
    {
        $log = isset($GLOBALS['_instance']['log'])
             ? $GLOBALS['_instance']['log']
             : 0 ;
        if (!is_object($log)) {
            defined('APP_LOG_LEVEL') or define('APP_LOG_LEVEL', 31);
            $GLOBALS['_instance']['log'] = new CLog(APP_LOG_LEVEL);
            $log = $GLOBALS['_instance']['log'];
        }
        return $log;
    }

    public function writeLog($type, $intLevel, $str, $bolEcho = false)
    {
        if ($intLevel > $this->_intLevel) {
            return;
        }

        $strLevel   = $this->_arrLogLevels[$intLevel];
        if (strlen($strLevel) == 0) {
            $strLevel = $intLevel;
        }

        $strLogFile = ($intLevel > CLog::LOG_LEVEL_WARNING)
            ? $type.'.log'
            : $type.'.log.wf';
             
        if (strlen($strLogFile) == 0) {
            $strLogFile = 'undefined.log';
        }
        
        if (defined('APP_AUTH_USER')) {
            $str = sprintf("[%s] [%s] [%s] [%s] %s\n", $strLevel, date('m-d H:i:s'),
                APP_AUTH_USER, APP_CLIENT_IP, $str);
        } else {
            $str = sprintf("[%s] [%s] [-] [%s] %s\n", $strLevel, date('m-d H:i:s'),
                APP_CLIENT_IP, $str);
        }
        
        if ($bolEcho === true) {
            echo "$str <hr>\n";
        }
        /***rotate log
        @clearstatcache();
        $arrFileStats = @stat($strLogFile);
        if (is_array($arrFileStats) && floatval($arrFileStats['size']) > CLog::LOG_MAX_FILE_SIZE) {
            unlink($strLogFile);
        }*/
        return file_put_contents($strLogFile, $str, FILE_APPEND);
    }

    public static function debug($type, $str, $bolEcho = false)
    {
        $log = CLog::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog($type, CLog::LOG_LEVEL_DEBUG, $str, $bolEcho);
    }

    public static function trace($type, $str, $bolEcho = false)
    {
        $log = CLog::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog($type, Clog::LOG_LEVEL_TRACE, $str, $bolEcho);
    }

    public static function notice($type, $str, $bolEcho = false)
    {
        $log = CLog::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog($type, CLog::LOG_LEVEL_NOTICE, $str, $bolEcho);
    }

    public static function warning($type, $str, $bolEcho = false)
    {
        $log = CLog::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog($type, CLog::LOG_LEVEL_WARNING, $str, $bolEcho);
    }

    public static function fatal($type, $str, $bolEcho = false)
    {
        $log = CLog::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog($type, CLog::LOG_LEVEL_FATAL, $str, $bolEcho);
    }
}

/* vim: set et ts=4 et: */
?>
