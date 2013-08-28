<?php

class PLMM_Db {
	
	function &factory($adapter = null)
    {
        if (!is_string($adapter) || empty($adapter)) {
			$empty = 1;
			$adapter = 'System';
        } else {
        	$adapter = ucfirst(strtolower($adapter));
        }
		
        $class = __CLASS__ . '_' . $adapter;
        
        $class_file = PLMM_PATH . str_replace('_', DIRECTORY_SEPARATOR, $class). '.php';	
        
        if(!is_file($class_file)) {
        	trigger_error("PLMM_Db_ENGINE 参数设置错误, 请检查是否存在数据库引擎[{$adapter}]!", E_USER_ERROR);
        }         
		  
        include_once $class_file;
        
        if(!class_exists($class)) {
        	trigger_error("数据库引擎[{$class}]载入失败, 请检查{$adapter}.php 文件设置", E_USER_ERROR);
        }
               
        $tpl =  new $class();
                		
		return $tpl;
    }
}