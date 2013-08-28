<?php

class CSession {
	
	private $dbh;
	private $tbl;
	private $uid;
	private $username;
	private $cookieLifetime;
	
	private function __construct() {
		$this->dbh = &$GLOBALS['_instance']['dbh'];
		$this->tbl = APP_SESSION_TABLE;
		$this->uid = APP_AUTH_ID;
		$this->username = APP_AUTH_USER;
		
		$this->cookieLifetime = APP_SESSION_COOKIE_LIFETIME;
	}

	public static function getInstance()
    {
        $session = isset($GLOBALS['_instance']['session'])
        		 ? $GLOBALS['_instance']['session']
                 : 0 ;
        if (!is_object($session)) {
            $GLOBALS['_instance']['session'] = new CSession(APP_SESSION_TABLE);
            $session = &$GLOBALS['_instance']['session'];
        }
        return $session;
    }
	
	public static function start() {
		if(defined('APP_SESSION_NAME') && preg_match('/^[0-9a-zA-Z_]+$/', APP_SESSION_NAME)) {
			session_name(APP_SESSION_NAME);		
		}
			
		if(defined('APP_SESSION_COOKIE_PATH') && APP_SESSION_COOKIE_PATH) {		
			ini_set('session.cookie_path', APP_SESSION_COOKIE_PATH);		
		} 
		
		if(defined('APP_SESSION_COOKIE_DOMAIN') && APP_SESSION_COOKIE_DOMAIN) {
			ini_set('session.cookie_domain', APP_SESSION_COOKIE_DOMAIN);		
		}
		
		if(defined(APP_SESSION_COOKIE_LIFETIME) && APP_SESSION_COOKIE_LIFETIME) {		
			$tmp = time() +  intval(APP_SESSION_COOKIE_LIFETIME);		
			ini_set('session.cookie_lifetime', $tmp);		
		}
		session_set_cookie_params(APP_SESSION_COOKIE_LIFETIME, APP_SESSION_COOKIE_PATH, APP_SESSION_COOKIE_DOMAIN );
		session_set_save_handler('CSession::open', 'CSession::close', 'CSession::read', 'CSession::write', 'CSession::destroy', 'CSession::gc');
		
		session_start();	
		//$sid = session_id();
		//if(!preg_match('/^[0-9a-zA-Z]+$/', $sid)) {
		//	session_regenerate_id();	
		//}
	}

	public static function open($sesspath, $sessname) {	
		return true;	
	}
	
	public static function &read($sid) {
		$sess = self::getInstance();
		$data = '';
		$sql = sprintf("SELECT data FROM `%s` WHERE `sid`='%s'", $sess->tbl, $sid);	
		if($qry = $sess->dbh->query($sql)) {
			if($sess->dbh->numRows($qry)) {
				$rst = $sess->dbh->fetchRow($qry);
				$data = $rst[0];
			} else {	
				$tim = time();
				$sql = sprintf("REPLACE INTO `%s` SET sid='%s', uid='%d',username='%s',ctime='%d', atime='%d', data=''", 
						$sess->tbl, $sid, $sess->uid, $sess->username, $tim, $tim);
				$sess->dbh->query($sql);
			}
			$sess->dbh->freeResult($qry);
		}
		return $data;	
	}
	
	public static function write($sid, $data) {
		$sess = self::getInstance();
		$sql = sprintf("UPDATE `%s` SET atime='%d', data='%s' WHERE sid='%s'", 
			$sess->tbl, time(), $data, $sid);
		$sess->dbh->query($sql);
	}
	
	public static function close() {
		return true;
	}
	
	public static function destroy($sid) {
		$sess = self::getInstance();	
		$sql = sprintf("DELETE FROM `%s` WHERE sid='%s'", $sess->tbl, $sid);
		$sess->dbh->query($sql);
	}
	
	public static function gc() {
		$sess = self::getInstance();
		$expire = time() - $sess->cookie_lifetime; 
		$sql = sprintf("DELETE FROM `%s` WHERE atime<'%d'", $sess->tbl, $expire);	
		$sess->dbh->query($sql);
	}
}	

