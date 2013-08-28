<?php
defined('PLMM_PATH') || exit();

define('PHP4', version_compare(PHP_VERSION, '5.0.0', '<'));
define('PHP5', !PHP4);

//define('ERR_MODE', E_ALL);

//数据库引擎 mysql, mysqli, access, sqlite
//类型检查放在相应类里
/*
if( defined('APP_DB_ENGINE') ) {
	define('PLMM_DB_ENGINE', APP_DB_ENGINE);
} else {
	//use default settings
	define('APP_DB_ENGINE', 'mysql');
	define('PLMM_DB_ENGINE', 'mysql');
}
*/
//错误类型定义， 数据库
define('ERROR_DB',		0x0001);
define('ERROR_PHP',		0x0002);

//模版引擎
//define('PLMM_TEMPLATE_ENGINE', (defined('APP_TEMPLATE_ENGINE') ? APP_TEMPLATE_ENGINE : 'Smarty'));
//session 存储引擎
//define('PLMM_SESSION_ENGINE', (defined('APP_SESSION_ENGINE') ? APP_SESSION_ENGINE : 'mysql'));
//系统语言包定义APP_LANG
//define('PLMM_LANG', (defined('APP_LANG') ? APP_LANG : 'zh-cn'));

define('PLMM_SESSION_NAME', 'PMSID');
