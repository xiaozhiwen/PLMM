<?php

/**
 * PLMM Web Development Stardard PHP Library
 *
 * @category   PLMM
 * @package    PLMM
 * @copyright  Copyright (c) 2007 PLMM Studio. (http://www.baidu.com)
 * @version    $Id: PLMM.php 3900 2007-03-13 18:51:49 phphp $
 */

//版本设置
define('PLMM_VERSION', '1.1.0dev');

//类库根
defined('LIB_PATH')  || define('LIB_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR) ; 
defined('PLMM_PATH') || define('PLMM_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PLMM' . DIRECTORY_SEPARATOR);

//常量定义
//require_once PLMM_PATH . 'Define.php';

define('PHP4', version_compare(PHP_VERSION, '5.0.0', '<'));
define('PHP5', !PHP4);

//define('ERR_MODE', E_ALL);

//错误类型定义， 数据库
define('ERROR_DB',              0x0001);
define('ERROR_PHP',             0x0002);

//模版引擎
//define('PLMM_TEMPLATE_ENGINE', (defined('APP_TEMPLATE_ENGINE') ? APP_TEMPLATE_ENGINE : 'Smarty'));
//session 存储引擎
//define('PLMM_SESSION_ENGINE', (defined('APP_SESSION_ENGINE') ? APP_SESSION_ENGINE : 'mysql'));
//系统语言包定义APP_LANG
//define('PLMM_LANG', (defined('APP_LANG') ? APP_LANG : 'zh-cn'));

//系统默认session cookie var
define('PLMM_SESSION_NAME', 'PMSID');

//加载版本兼容文件, 改自pear, 用utils下的transFromPearToPLMMCompat.php生成
//当前为5,不需要
//require_once PLMM_PATH . 'Compat.php';

//错误控制文件, 错误全部转发到此, 若要由程序接管, 可 restore_error_handler后再重设
require_once PLMM_PATH . 'Error.php';

//加载系统函数库
require_once PLMM_PATH . 'Function.php';
require_once PLMM_PATH . 'CLog.php';
require_once PLMM_PATH . 'Db/Mysql.php';
//require_once PLMM_PATH . 'CSession.php';
require_once PLMM_PATH . 'Template/Smarty.php';
?>
