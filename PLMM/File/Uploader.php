<?php

class PLMM_File_Uploader
{
	var $allowedExt = array('jpg','gif','png');
	var $bannedExt = array();
	var $maxFileSize = 512000;
	var $basePath = 'upload/';
	var $archiveType = 'month';
	var $allowedOptions = array('basePath','allowedExt','maxFileSize');

	function PLMM_File_Uploader($options = array())
	{
		foreach($options as $key=>$opt) {
			if(in_array($key, $this->allowedOptions)) {
				$this->$key = $opt;
			}
		}
	}

	function setSrcFile($key) {
		if(func_num_args() == 2) {
			$tmp = array();
			$idx = func_get_arg(1);
			$tmp['name'] =  $_FILES[$key]['name'][$idx];
			$tmp['size'] =  $_FILES[$key]['size'][$idx];
			$tmp['type'] =  $_FILES[$key]['type'][$idx];
			$tmp['tmp_name'] =  $_FILES[$key]['tmp_name'][$idx];
		} else {
			$tmp = &$_FILES[$key];
		}

		$this->file = $tmp;
		return ;
	}

	function getName($name = '') {
		return $this->geneName($name);
	}

	function save($dst) {
		$info = array();
		$info['ext'] = $this->getExt();
		$info['size'] = $this->getSize();
		
		$err = 1;
		$err && $err = $this->checkSize($info['size']);
		$err && $err = $this->checkExt($info['ext']);
		if(!$err) return false;

		$dirname = dirname($dst);
		$this->mkdir($this->basePath .'/'.$dirname);
		$err = @copy($this->file['tmp_name'], $this->basePath.'/'.$dst);
		$err && @chmod($err, 0777);		
		return true;
	}
	
	function mkdir( $dir, $mode = 0777){	
		if ( ! file_exists($dir) ) {
			$this->mkdir(dirname($dir), $mode);
			@mkdir($dir, $mode);
			@chmod($dir, $mode);
		}
	}
	
	function geneName($str = '') 
	{
		$rand = uniqid(microtime(), true);
		$name = md5($str.$rand);
		$name = substr($name, 8, 16);
		return $name;
	}

	function check() 
	{	
		$err = 1;
		$err && $err = $this->checkSize();
		$err && $err = $this->checkExt();
		//$err || $err = $this->checkType();
		return $err;
	}

	function getExt()
	{
		return array_pop(explode('.', $this->file['name']));
	}
	
	function getSize() {
		return $this->file['size'];
	}
	
	function checkExt($ext) 
	{		
		return $this->allowedExt ? true : in_array($ext, $this->allowedExt);
	}
	
	function checkSize($size) 
	{
		return $size <= $this->maxFileSize;
	}		
}
?>
