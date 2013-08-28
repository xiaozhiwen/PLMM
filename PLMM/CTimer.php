<?php

/**
 * Concrete class for handling view scripts.
 *
 * @category   PLMM
 * @author     xiaozhiwen@baidu.com
 * @date       2008-06-05 19:42
 * @copyright  Copyright (c) 2007 Baidu Inc. (http://www.baidu.com)
 */

class CTimer
{
	static $TIMER = array();
	static function start($key) {
		self::$TIMER[$key][0] = self::mtime();
		self::$TIMER[$key][1] = 0.0;
	}
	
	static function stop($key) {
		self::$TIMER[$key][1] = self::mtime();
	}

	static function rewind($key) {
		self::start($key);
	}

	static function getElapse($key, $r = 6) {
		return round(self::$TIMER[$key][1] - self::$TIMER[$key][0], 6);
	}

	/**
	 * @desc
     * @output timer keys
     */
	static function getTimers() {
		return array_keys(self::$TIMER);
	}

	/**
	 * @desc   主要用于CLog中方便的输出各模块时间, like iknow
     * @return ...
     */
	static function getElapses() {
		$output = array();
		foreach (self::$TIMER as $key => $val) {
			$output[$key] = self::getElapse($key);	
		}
		return $output;
	}
		
	static function mtime() {
    	if ( version_compare(PHP_VERSION, '5.0.0' , '>=') ) {
        	return microtime(true);
	    }

    	list($usec, $sec) = explode(' ', microtime());
	    $mtime = (float)$usec + (float)$sec;
    	return $mtime;
	}
}

//CTimer::start('a');
//CTimer::start('b');
//sleep(1);
//CTimer::start('c');
//usleep(100000);
//echo CTimer::getElapse('a');echo "\n";
//echo CTimer::getElapse('b');echo "\n";
//echo CTimer::getElapse('c');

