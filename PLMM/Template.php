<?php

class PLMM_Template {
	
	function &factory($adapter = null)
    {
        if (!is_string($adapter) || empty($adapter)) {
			$empty = 1;
			$adapter = 'System';
        } else {
        	$adapter = ucfirst(strtolower($adapter));
        }
		
        $class = __CLASS__ . '_' . $adapter;
        
        $tpl_class = APP_LIB_PATH . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';	
        
        if(!is_file($tpl_class)) {
        	trigger_error("APP_TEMPLATE_ENGINE �������ô���, �����Ƿ����ģ������[{$adapter}]!", E_USER_ERROR);
        }
              		
		@require_once $tpl_class;
        
        if(!class_exists($class)) {
        	trigger_error("ģ������[$class]����ʧ��, ����{$adapter}.php �ļ�����", E_USER_ERROR);
        }
               
        $tpl =  new $class();
                		
		return $tpl;
    }
}
