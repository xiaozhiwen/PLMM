<?php

class Db_Pdo {

	var $qryNum = 0;

	var $qryInfo = '';

	var $qryTime = 0.0;

	var $debug = true;

	var $dbh;

	var $tblPrefix = '';

	var $tblFields = array();

	var $lastQuery ;

	var $openedQueries = array();//没有释放的查询

	var $transaction;

	var $type = 'mysql';//final const

	var $dsn;

	var $user;

	var $pass;


	var $prefix;

	var $db_version;

	var $cache_path;

	var $sql;
	
	public $affected_rows = 0;
	
	constant STATE_OK = '00000';
	
	function __construct($cfg = array())
	{
		(APP_ERROR_REPORTING & ERROR_DB) && ($this->debug = true);
		$this->cache_path = APP_SQL_CACHE_PATH;
		
		if ( $cfg ) {
			$this->connect($cfg);
		}
	}

	function connect($config) 
	{	
		$dsn = $config['dsn'];
		$driver = $config['driver'];
		$port = $config['port'];
		
		$unix_socket = $config['unix_socket'];
		
		$host = $config['host'];
		$user= $config['user'];
		$pass = $config['pass'];
		$name = $config['name'];
		$charset = $config['charset'];
		
		if ( $unix_socket ) {
			$dsn = sprintf("%s:unix_socket=%s;dbname=%s", $driver, $name);
		} else {
			$port || ($port = 3306);
			$dsn = sprintf("%s:host=%s;port=%s;dbname=%s", $driver, $host, $port);
		}
		
		$driver_options= isset($config['driver_options']) ? $config['driver_options'] : array();
		
		try 
		{
			$this->dbh = new PDO($dsn, $user, $pass, $driver_options);
		} catch (PDOException $e) {
			exit("数据库连接错误:".$e->getMessage());//.'<br>文件:'.$e.getFile().'<br>行:'.$e.getLine());
		}
		$this->dsn = $dsn;
		
		$this->server_version =  $this->dbh->getAttribute(PDO::ATTR_SERVER_INFO);
		$this->client_version = $this->dbh->getAttribute(PDO::ATTR_CLIENT_VERSION);
		
		if ( $charset ) 
		{
			$this->dbh->exec("SET NAMES $charset");
		}
	}
	
	function selectDb($name) {

		if(!mysql_query("use `$name`", $this->dbh)) {
			$this->halt();
		}
	}
	
	//delete, update, insert等,返回影响行数
	public function exec($statement) {
		$this->affected_rows = $this->dbh->exec($statement);
		if ( $this->dbh->errorCode() == self::STATE_OK ) {
			return $this->affected_rows;
		} else {
			//error 
		}
	}

	public function fetchAll()
	{
	
	}

	function registerTableName($prefix) {

		$this->tblPrefix = $prefix;

		$qry = $this->query('SHOW TABLES');

		while ($row = $this->fetchRow($qry)) {

			if(substr($row[0],0,4) == 'cdb_') continue;

			$key = $prefix ? str_replace($prefix, 'tbl_', $row[0]) : 'tbl_'.$row[0];			

			$GLOBALS[$key] = $row[0];

		}

		return 1;

	}

	

	function insert($table, $data) {		

		$fields = $this->getFields($table);

		//print_r($fields);exit;

		$values = $columns = array();



		foreach ($fields as $field) {

			$name = &$field['name'];

			$type = &$field['type'];

			$default = &$field['default'];

		

			if ( array_key_exists($name, $data) ) {		

				$value = $data[$name];

				if($type == 'int') {

					$values[] = intval($value);

				} else if($type == 'time') {

					$values[] = $value;

				} else {

					$values[] = "'" . $value . "'";

				}

			} else {

				if($default) {

					if($type == 'auto_increment') {

						$values[] = 'NULL';

					}else if($type == 'int') {

						$values[] = intval($default);

					} else {

						$values[] = mysql_escape_string(strval($default));

					}

				} else {

					if($type == 'auto_increment') {

						$values[] = 'NULL';

					} else if($type == 'time') {

						$values[] = 'current_timestamp()';

					} else if($type == 'int') {

						$values[] = 0;

					} else {

						$values[] = "''";

					}

				}

			}



			$columns[] = $name;

		}

		

		$sql = "REPLACE INTO `$table`(`" . implode('`,`', $columns) .'`) VALUES('. implode(',', $values) .')';		

		$this->sql = $sql;

		return $this->query($sql);

	}

	

	function update($table, $data, $conds) {

		$updates = array();

		$fields  = $this->getFields($table);

		foreach ($fields as $field) {

			$column = $field['Field'];

			if (isset($data[$column])) {

				$updates[] = "$column='" . $data[$column] . "'";

			}

		}

		

		if (is_array($conds)) {

			$tmp = array();

			foreach ($conds as $column=>$value) {

				$tmp[] = $column."='".$value."'";

			}

			$cond = implode(' AND ', $tmp);

		} else {

			$cond = $conds;

		}

		

		//不更新

		$cond || $cond = 0;

		

		$sql = "UPDATE $table SET ". implode(',', $updates) ." WHERE $cond";

		$qry = $this->query($sql);

		return $qry ? $this->affectedRows() : -1;

	}



	function delete($table, $conds) {

		$qry = $this->query("DELETE FROM $table WHERE $conds");

		return $qry ? $this->affectedRows() : -1;

	}

	

	function &getFields($table) {

		if (isset($this->tblFields[$table])) {

			return $this->tblFields[$table];

		} /*else {

			$cacheFile = $this->cache_path . "structure/{$this->name}.{$table}.php";

			if( file_exists($cacheFile)) {

				include $cacheFile;

				$this->tblFields[$table] = $structure;

				return $structure;

			}

		}*/



		$columns = array();

		$qry = $this->query("DESC $table");		

		while($row = mysql_fetch_assoc($qry)) {

			$type = strtolower($row['Type']);

			if(strpos($row['Extra'], 'auto_increment') !== false) {

				$type = 'auto_increment';

			} else if(strpos($type, 'int')!==false) {

				$type = 'int';

			} else {

				$type = 'str';

			}			

			$columns[] = array('name'=>$row['Field'], 'type'=>$type, 'default'=>$row['Default']);

		}

		

		mysql_free_result($qry);



		$this->tblFields[$table] = $columns;

		return $columns;

	}

	

	function &fetchAll($sql) {

		$rtn = array();

		$qry = $this->query($sql);

		while ($row = mysql_fetch_assoc($qry)) {

			$rtn[] = $row; 

		}

		$this->freeResult($qry);

		return $rtn;

	}



	function &fetchOne($sql, $type = MYSQL_ASSOC) {

		$rtn = array();

		$qry = $this->query($sql);

		if (is_resource($qry) && $this->numRows($qry)) {

			$rtn = mysql_fetch_array($qry, $type);

		}

		$this->freeResult($qry); 

		return $rtn;

	}

	

	//只取一行一个字段

	function fetchVar($sql) {

		$qry = $this->query($sql);

		if (is_resource($qry) && $this->numRows($qry)) {

			$rtn = mysql_fetch_row($qry);

			$this->freeResult($qry); 

			return $rtn[0];

		}

		return false;

	}



	function &fetchRow($qryId = null) {

		is_resource($qryId) || $qryId = $this->lastQuery;

		$rtn =  mysql_fetch_row($qryId);

		return $rtn;

	}

	

	function &fetchArray($qryId = null, $mode = MYSQL_ASSOC) {

		is_resource($qryId) || $qryId = $this->lastQuery;

		$rtn = mysql_fetch_array($qryId, $mode);

		return $rtn;

	}

	

	function &fetchAssoc($qryId = null) {

		is_resource($qryId) || $qryId = $this->lastQuery;

		$rtn = mysql_fetch_array($qryId, MYSQL_ASSOC);

		return $rtn;

	}



	function query($sql) {

		

		if ( $this->debug ) {

			$mtime = explode(' ', microtime());

			$stime = $mtime[1] + $mtime[0];

		}

	

		$this->lastQuery = @mysql_query($sql, $this->dbh);

		$this->lastQuery || $this->halt($sql);

		

		if ($this->debug){

			$mtime = explode(' ', microtime());

			$etime = $mtime[1] + $mtime[0] - $stime;

			$this->qryInfo .= sprintf("<li><b>%1.5f</b> %s<hr size=1 noshadow>\r\n", $etime, $sql);

			$this->qryTime += $etime;

		}

		

		$this->qryNum++;

		if (preg_match('/select\s+/i', $sql) && $this->lastQuery) {

			$this->openedQueries[(int) $this->lastQuery] = $this->lastQuery;

		}

		

		return $this->lastQuery;

	}	

	

	function debugOn() {$this->debug = true;}

	function debugOff() {$this->debug = false;}

	function getQueryNum() {return $this->qryNum;}

	function affectedRows() {return @mysql_affected_rows($this->dbh); }



	function numRows($qryId = null) {

		is_resource($qryId) || $qryId = $this->lastQuery;

		return @mysql_num_rows($qryId);

	}

	

	function lastInsertId() {

		return @mysql_insert_id($this->dbh);		

	}

	

	function freeResult($qryId) {	



		if (!is_resource($qryId)) return false;



		if (isset($this->openedQueries[(int) $qryId])) {

			unset($this->openedQueries[(int) $qryId]);

			return @mysql_free_result($qryId);

		}

		return false;

	}

	

	function transaction() {

		//服务器不支持，略

	}



	function close() 

	{		

		if (!is_resource($this->dbh)) {

			return false;

		}

		

		if ($this->transaction)	{

			$this->transaction('commit');

		}



		if ($this->openedQueries){

			foreach ($this->openedQueries as $qryId){

				$this->freeresult($qryId);

			}

		}

		

		@mysql_close($this->dbh);

		$this->dbh = null;	

		return true;	

	}



	function halt() 

	{	

		$err = sprintf("<li>错误代码:%s</li>\r\n", @mysql_errno($this->dbh));

		$err.= sprintf("<li>错误原因:%s</li>\r\n", @mysql_error($this->dbh));

		

		if (func_num_args()) {

			$sql = array_pop(func_get_args());

			$err .= sprintf("<li>SQL &nbsp; &nbsp;:%s</li>\r\n", $sql);

		}

		

		$log_file = APP_LOG_PATH . 'error_'.date('Y-m').'.log'; //按月分

		error_log($err, 3, $log_file);

		trigger_error($err, E_USER_ERROR);	 

	}



	function debug() 

	{

		$str = sprintf("<li>共执行(%s)次查询</li>\r\n", $this->qryNum);

		$str.= $this->qryInfo;

		$str.= sprintf("<li>总用时:<b>[%1.5f]</b></li>\r\n", $this->qryTime);

		return $str;

	}

}



?>