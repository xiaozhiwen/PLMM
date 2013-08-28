<?php

function no_cache_header() {
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . date(DATE_RFC1123));
	// HTTP/1.1
	header('Cache-Control: no-store, no-cache, private, must-revalidate, max-age=10');
	header('Cache-Control: post-check=0, pre-check=0', false);
	// HTTP/1.0
	header('Pragma: no-cache');
}

function OOO_page_cache_header($cache) {
// Set default values:
$last_modified = gmdate('D, d M Y H:i:s', $cache->created) .' GMT';
$etag = '"'.md5($last_modified).'"';
// See if the client has provided the required HTTP headers:
$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) : FALSE;
$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : FALSE;
if ($if_modified_since && $if_none_match
&& $if_none_match == $etag // etag must match
&& $if_modified_since == $last_modified) {  // if-modified-since must match
header('HTTP/1.1 304 Not Modified');
// All 304 responses must send an etag if the 200 response for the same object contained an etag
header("Etag: $etag");
exit();
}
// Send appropriate response:
header("Last-Modified: $last_modified");
header("ETag: $etag");
// The following headers force validation of cache:
header("Expires: Sun, 19 Nov 1978 05:00:00 GMT");
header("Cache-Control: must-revalidate");
// Determine if the browser accepts gzipped data.
if (@strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === FALSE && function_exists('gzencode')) {
// Strip the gzip header and run uncompress.
$cache->data = gzinflate(substr(substr($cache->data, 10), 0, -8));
}
elseif (function_exists('gzencode')) {
header('Content-Encoding: gzip');
}
// Send the original request's headers. We send them one after
// another so PHP's header() function can deal with duplicate
// headers.
$headers = explode("\n", $cache->headers);
foreach ($headers as $header) {
header($header);
}
print $cache->data;
}

//返回微秒时间
function mtime() {
	if ( version_compare(PHP_VERSION, '5.0.0' , '>=') ) {
		return microtime(true);
	}

	list($usec, $sec) = explode(' ', microtime());
	$mtime = (float)$usec + (float)$sec;
	return $mtime;
}

function cutstr($string, $length, $dot = ' ...') {
	global $charset;
	if(strlen($string) <= $length) {
	return $string;
	}
	$strcut = '';
	$charset = strtoupper($charset);
	if( $charset == 'UTF-8' || $charset == 'UTF8') {
	$n = $tn = $noc = 0;
	while ($n < strlen($string)) {
	$t = ord($string[$n]);
	if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
	$tn = 1; $n++; $noc++;
	} elseif(194 <= $t && $t <= 223) {
	$tn = 2; $n += 2; $noc += 2;
	} elseif(224 <= $t && $t < 239) {
	$tn = 3; $n += 3; $noc += 2;
	} elseif(240 <= $t && $t <= 247) {
	$tn = 4; $n += 4; $noc += 2;
	} elseif(248 <= $t && $t <= 251) {
	$tn = 5; $n += 5; $noc += 2;
	} elseif($t == 252 || $t == 253) {
	$tn = 6; $n += 6; $noc += 2;
	} else {
	$n++;
	}
	if ($noc >= $length) {
	break;
	}
	}
	if ($noc > $length) {
	$n -= $tn;
	}
	$strcut = substr($string, 0, $n);
	} else {
	for($i = 0; $i < $length - strlen($dot) - 1; $i++) {
	$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
	}
	}
	return $strcut.$dot;
}

//文件扩展名, 取最后
/*
function fileext($file){
	return strtolower(substr(strrchr(trim($file), '.'), 1));
}*/

function get_file_ext($file) {
	return strtolower(substr(strrchr(trim($file), '.'), 1, 10));
}

//友好显示文件大小, ls -h

function size_format($size, $dec=2) { 
	$tmp = array('B', 'K', 'M', 'G', 'T'); 
	$cnt =0; 
	while ($size>1024) { 
		$cnt++; 
		$size /= 1024; 
	} 
	return round($size, $dec).$tmp[$cnt]; 
}

function format_size($size, $dec=2) {
	return size_format($size, $dec);
}

//sql 过滤特殊字符
function quote_search($str) {
	$str = str_replace(array('%', '_'), array('\%','\_'), $str);
	return $str;
}

function make_dir( $dir, $mode = 0644){	
	if (!file_exists($dir)) {		
		if ( version_compare(PHP_VERSION, '5.0.0' , '>=') ) {
			return mkdir($dir, $mode, true);
		}
		make_dir(dirname($dir), $mode);
		return mkdir($dir, $mode) && chmod($dir, $mode);
	} else if ( is_dir($dir) ) {
		return chmod($dir, $mode);
	} else {
		return false;
	}
}

function strtoplain($str) {
	$str = stripslashes(trim($str));
	return htmlspecialchars($str, ENT_QUOTES);
}
?>
