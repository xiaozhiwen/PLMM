<?php

//define('SMARTY_DIR', dirname(__FILE__).'/Smarty/');
require_once dirname(__FILE__) .'/Smarty/Smarty.class.php';
class Template_Smarty extends Smarty
{
	/**
	 * Template Engine of Smarty
	 *
	 * @return Template
	 */
	function Template_Smarty () 
	{
		$this->Smarty();
	
		//init to config params
		defined('APP_TEMPLATE_PATH') && $this->template_dir = APP_TEMPLATE_PATH;
    	defined('APP_TEMPLATE_CACHE_PATH') && $this->cache_dir	= APP_TEMPLATE_CACHE_PATH;
    	defined('APP_TEMPLATE_COMPILE_PATH') && $this->compile_dir  = APP_TEMPLATE_COMPILE_PATH;

    	$this->assign('APP_NAME', APP_NAME);    	
    	$this->assign('APP_VERSION', APP_VERSION);    	
    	$this->assign('APP_CHARSET', APP_CHARSET);
		$this->assign('APP_WEB_PATH', APP_WEB_PATH);
		$this->assign('APP_JS_PATH', APP_JS_PATH);
		$this->assign('APP_IMAGE_PATH', APP_IMAGE_PATH);
	}
}
