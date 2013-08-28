<?php

$PLMM_error_code_map = array(
	1    => 'E_ERROR', 
	2    => 'E_WARNING',
	4    => 'E_PARSE',
	8    => 'E_NOTICE',
	16   => 'E_CORE_ERROR',
	32   => 'E_CORE_WARNING',
	64   => 'E_COMPILE_ERROR',
	128  => 'E_COMPILE_WARNING',
	256  => 'E_USER_ERROR',
	512  => 'E_USER_WARNING',
	1024 => 'E_USER_NOTICE',
	2048 => 'E_STRICT',
	4096 => 'E_RECOVERABLE_ERROR',
	6143 => 'E_ALL'
);

$PLMM_error_str = '';

function PLMM_error_handler($errno, $errstr, $errfile, $errline, $errContent) {
	//echo $GLOBALS['Debug'];
	switch ($errno) {
		case E_CORE_ERROR:
		case E_CORE_WARNING:
			$desc = '错误';
			$log = 'fatal';
			break;
		case E_WARNING:
			$desc = '警告';
			$log = 'error';

			break;
		case E_NOTICE:
			$desc = '注意';
			$log = 'error';			
			break;		
		case E_STRICT:
			$desc = '兼容';
			$log = 'error';
			
			break;
		case E_PARSE:
		case E_COMPILE_ERROR:
		case E_COMPILE_WARNING:
			$desc = '编译';
			$log = 'error';
			break;
		case E_USER_ERROR:
		case E_USER_NOTICE:
		case E_USER_WARNING:
			$desc = '运行';
			$log = 'user';
			break;
		case E_RECOVERABLE_ERROR:
		default:
			$desc = '其它';
			$log = 'error';
			break;
	}
	$err = array('desc'=>$desc, 'file'=>$errfile, 'line'=>$errline, 'no'=>$errno, 'str'=>$errstr);
	$log_file = APP_LOG_PATH . $log .'_'.date('Y-m').'.log'; //按月分
	$str = $GLOBALS['glb_client_ip']."\t[".date('Y-m-d H:i:s')."]\t"."File {$errfile} Line {$errline} No {$errno} Desc {$errstr}\n";
	
	error_log($str, 3, $log_file);
	
	if($errno & APP_ERROR_REPORTING) {//错误模式
		$GLOBALS['PLMM_error_str'][] = $err;
	}
}


function PLMM_error_print() {
	$tmp = $GLOBALS['PLMM_error_str'];
	if(!$tmp) {
		return ;
	}
	$APP_CHARSET = APP_CHARSET;
	if (!headers_sent()) {
	   header("Content-type: text/html; charset={$APP_CHARSET}");
	}
	//<meta http-equiv="content-type" content="text/html; charset={$APP_CHARSET}">
	$str =<<<HTML
<style>
	body{ height:100%; margin:0; width:100%}
	#PLMM_debug *{margin:0;font-size:14px;}
	#PLMM_debug_info{
		width:100%; height:100%; 
		background: #fefefe;
		position:absolute; left:0;right:0; top:0; bottom:0; 
		-moz-opacity:1 filter:alpha(opacity=50%); z-index:99;
		overflow-y:auto;
		display:none;
		padding:auto;
	}
	#PLMM_debug_tag {
		width:120px;height:30px;
		position:absolute;top:8px;right:18px; z-index:100;
		border:1px solid red; 
		background-color:red;
		word-wrap: nowrap;
	}
	#PLMM_debug_tag a{ 
		position:fixed;padding:5px;
		font-size:14px; font-weight:bold; text-align:center;text-decoration:none;
		color:white;
		filter:alpha(opacity=100%);-moz-opacity:1;
		display:block;
	}
	/*#PLMM_debug_tag a:hover{background-color:white; color:red}*/
	.tr{background:white;}
</style>
<script>
	var bolShow=0;
	function PLMM_show() {
		if(bolShow) {
			bolShow = 0;
			document.getElementById('PLMM_debug_info').style.display = 'none';
			document.getElementById('PLMM_debug_status').innerHTML = '显示调试信息?';
		} else {
			bolShow = 1;
			document.getElementById('PLMM_debug_info').style.display = 'block';
			document.getElementById('PLMM_debug_status').innerHTML = '隐藏调试信息?';
		}
		return false;
	}
</script><div id="PLMM_debug">
<div  id="PLMM_debug_info">
	<table width="760px" align=center border=0 style="background:#ccc; table-layout:fixed" cellspacing="1" cellpadding="5">
	<caption>
	NSOP Debug 平台
	</caption>
	<tbody><tr class=tr><td width=50>错误</td><td>描述</td></tr>
HTML;

	foreach($tmp as $err) {
		$str .= "<tr class=tr>\n\t<td rowspan=2><b>{$err['desc']}:</b></td>\n\t";
		$str .= "<td>File &lt;<b>{$err['file']}</b>&gt;, Line [<b>{$err['line']}</b>], Errno [<b>{$err['no']}</b>]</td>\n</tr>\n";
		$str .= "<tr class=tr>\n\t<td>{$err['str']}&nbsp;</td>\n</tr>\n";
		$str .= "<tr><td colspan=2 bgcolor=#eeeeee>&nbsp;</td></tr>\n\n";
	}
	$str .= '</tbody></table></div>';
	$str .= '<div id="PLMM_debug_tag"><a href="#" onclick="return PLMM_show();" id="PLMM_debug_status" hidefocus="hidefocus">有错误发生了!</a></div></div>';

	echo $str;	
}
set_error_handler('PLMM_error_handler');
register_shutdown_function('PLMM_error_print');

function fatal_error($errmsg) {
	trigger_error($errmsg, E_USER_ERROR);
}

function log_access() {
}

function log_error() {
}
