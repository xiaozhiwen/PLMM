<?php

class Db_Mysql {



	var $qryNum = 0;

	var $qryInfo = '';

	var $qryTime = 0.0;

	var $debug = true;

	var $connId;

	var $tblPrefix = '';

	var $tblFields = array();

	var $lastQuery ;

	var $openedQueries = array();//没有释放的查询

	var $transaction;

	var $host;

	var $user;

	var $pass;

	var $name;

	var $prefix;

	var $version;

	var $cache_path;

	var $sql;
	
	var $num_rows = 0;

	function Db_Mysql() {

		(APP_ERROR_REPORTING & ERROR_DB) && ($this->debug = true);

		$this->cache_path = APP_SQL_CACHE_PATH;

	}	



	function connect($host, $user, $pass, $name='', $charset = '', $pconnect=false ) {
		if($pconnect) {
			$this->connId = mysql_pconnect($host, $user, $pass);
		} else {
			$this->connId = mysql_connect($host, $user, $pass);
		}

		is_resource($this->connId) || $this->halt("Cann't connect to server");

		$this->server_info = mysql_get_server_info($this->connId);
		$this->client_info = mysql_get_client_info();
		
		if($name) 
		{
			$this->selectDb($name);
		}
		
		$this->charset = $charset;

		if ($this->charset) 
		{
			$this->query("SET NAMES {$this->charset}");
		}
	
		//mysql_query("SET SESSION sql_mode=''");

		$this->name = $name;
	}

	function selectDb($name) {
		if(!mysql_query("use `$name`", $this->connId)) {
			$this->halt();
		}
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

		

		$sql = "INSERT INTO `$table`(`" . implode('`,`', $columns) .'`) VALUES('. implode(',', $values) .')';		

		return $this->query($sql);

	}

	

	function update($table, $data, $conds) {

		$updates = array();

		$fields  = $this->getFields($table);

		foreach ($fields as $field) {

			$column = $field['name'];

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

		return $qry; 

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
		$this->sql = $sql;
		//这里可以做sql缓存
		$rtn = array();
		$qry = $this->query($sql);

		while ($row = mysql_fetch_assoc($qry)) {
			$rtn[] = $row; 
		}
		
		$this->num_rows = mysql_num_rows($qry);
		$this->freeResult($qry);
		
		return $rtn;
	}



	function &fetchOne($sql, $type = MYSQL_ASSOC) {
		if (strpos(substr($sql, -10), 'LIMIT') === false) 
		{
			$sql .= ' LIMIT 1'; 
		}
		
		$this->sql = $sql;
		
		$rtn = array();
		$qry = $this->query($sql);
		
		$this->num_rows = mysql_num_rows($qry);
		
		$rtn = mysql_fetch_array($qry, $type);
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

		$this->lastQuery = @mysql_query($sql, $this->connId);
		$this->lastQuery || $this->halt($sql);		

		if ($this->debug){
			$mtime = explode(' ', microtime());
			$etime = $mtime[1] + $mtime[0] - $stime;
			$this->qryInfo .= sprintf("<li><b>%1.5f</b> %s<hr size=1 noshadow>\r\n", $etime, $sql);
			$this->qryTime += $etime;
		}
		$this->qryNum++;

		if (preg_match('/^\s*select\s+/i', $sql) && $this->lastQuery) {
			$this->openedQueries[(int) $this->lastQuery] = $this->lastQuery;
			$this->num_rows = mysql_num_rows($this->lastQuery);
		} else {
			$sql_action = strtolower(substr($sql, 0, 10));
			if (preg_match('/insert|update|delete|replace|drop/', $sql_action)) {
				$str = "#Time ". date('Y-m-d H:i:s');
				//$str .= " user ".APP_AUTH_USER.'@'.APP_CLIENT_IP.date(' U')."\n";
				$str .= $sql."\n";
				$log = APP_LOG_PATH .'/sql_'.date('Y-m').'.log';
				
				$fp = fopen($log, 'a');
				flock($fp, 	LOCK_EX);
				fwrite($fp, $str);
				fclose($fp);
			}
		}
		return $this->lastQuery;
	}	

	

	function debugOn() {$this->debug = true;}

	function debugOff() {$this->debug = false;}

	function getQueryNum() {return $this->qryNum;}

	function affectedRows() {return @mysql_affected_rows($this->connId); }



	function numRows($qryId = null) {
		$qryId || ($qryId = $this->lastQuery);
		
		if (is_resource($qryId)) {
			return mysql_num_rows($qryId);
		} else {
			$this->halt('illegal query result');
		}
	}

	

	function lastInsertId() {

		return @mysql_insert_id($this->connId);		

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

		if (!is_resource($this->connId)) {

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

		

		@mysql_close($this->connId);

		$this->connId = null;	

		return true;	

	}

	function halt()
	{	
		$err = sprintf("<li>MYSQL错误代码:%s</li>\r\n", @mysql_errno($this->connId));
		$err.= sprintf("<li>MYSQL错误原因:%s</li>\r\n", @mysql_error($this->connId));

		if (func_num_args()) {
			$arg = func_get_args();
			$sql = array_pop($arg);
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
