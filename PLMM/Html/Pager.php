<?php
/**
 *
 $p = new TWT_Html_Pager(array('ttl'=>100, 'ppg'=>5,'var'=>'p'));
$p->gethtml
<style>
ul{margin:0px;padding:0px;float:left;list-style:none;font:12px/20px Tahoma,Georgia,serif;border:#ccc 1px solid;border-right:none;background:#fff} 
li{margin:0px;float:left;border-right:#ccc 1px solid;width:30px;text-align:center;}
li a{text-decoration:underline;color:#666}
li a:hover{background:blue;color:#fff}
.curpage,.prevpage,.nextpage{font-weight:bold;}
</style>
*/
class TWT_Html_Pager
{
    //总记录数
	var $ttl = 0;
	//每页数
    var $ppg     = 10;
	//每次显示的数据量
    var $delta   = 2;
	//当前页
    var $curPage = 1;
	//总页数
    var $ttlpage = 1;
	//访问路径
    var $path ;
	//被访问的文件,静态的时候要指定
    var $file;
	//是否是静态分页
    var $isStatic = false;
	//页标志
    var $urlVar      = 'p';
    var $extraVars   = array();
    var $excludeVars = array();

    var $attributes  = '';
    var $prev    = '&laquo;';
    var $next    = '&raquo;';
    var $separator   = '';//分隔符
	var $prevCurPage = '';
	var $postCurPage = '';	
	//url参数
	var $qs = array();

    var $_options = array(
        'ttl',
        'ppg',
        'delta',
        'isStatic',
        'urlVar',
        'extraVars',
        'curPage',
		'prev','next'
    );

    function TWT_Html_Pager($options = array())
    {
		//set options
        foreach ($options as $key => $value) {
            if (in_array($key, $this->_options) && (!is_null($value))) {
                $this->{$key} = $value;
            }
        }

		if (substr($_SERVER['PHP_SELF'], -1) == '/') {
			$this->file = '';
			$this->path = 'http://'.$_SERVER['HTTP_HOST'].str_replace('\\', '/', $_SERVER['PHP_SELF']);
		} else {
			$this->file = preg_replace('/(.*)\?.*/', '\\1', basename($_SERVER['PHP_SELF']));
			$this->path = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
		}
		
		//静态分页
        if ($this->isStatic) {			
			$this->url = $this->path;
            if (strncasecmp($this->file, 'javascript', 10) != 0) {
                $this->url .= '/';
            }
            if (!strstr($this->file, '%d')) {
                trigger_error('静态分页文件名要有%d替换单元', E_USER_WARNING);
            }
        } 
		//正常分页
		else {
            $this->url = $this->path.'/'.$this->file;
        }

        $this->file = ltrim($this->file, '/');  //strip leading slash
        $this->path = rtrim($this->path, '/');  //strip trailing slash

		$this->ppg = max($this->ppg, 1); 
		//总页数
		$this->ttlpage = ceil($this->ttl / $this->ppg);
        
		//当前页
        if (isset($_GET[$this->urlVar]) && empty($options['curpage'])) {
            $this->curPage = (int)$_REQUEST[$this->urlVar];
        }
		$this->curPage = min($this->curPage, $this->ttlpage);
        $this->curPage = max($this->curPage, 1);

		//处理url数据
        $tmp = $_GET;
        if (count($this->extraVars)){
            $this->_recursive_urldecode($this->extraVars);
        }

        $tmp = array_merge($tmp, $this->extraVars);
        foreach ($this->excludeVars as $exclude) {
            if (array_key_exists($exclude, $tmp)) {
                unset($tmp[$exclude]);
            }
        }
        if (count($tmp) && get_magic_quotes_gpc()){
            $this->_recursive_stripslashes($tmp);
        }
        $this->qs = $tmp;
		//----url end
    } 
	   
    // }}}
    // {{{ getSlideLink()
    
    /**
     * Helper	method
     * @param	empty
     * @access	public
     */	
    function getSlideLink()
    {	
		if ( $this->ttl < 1 )
			return '';

		$html = '';
		$html.= '<li class="desc">';
		$html.= '共[<b>'.$this->ttl.'</b>]条/分[<b>'.$this->ttlpage.'</b>]页</li>';
		
		if ($this->curPage>1) {
			//打印上一页
			$href = $this->renderUrl($this->curPage - 1);
			$html .= sprintf( '%s<li class="prevpage"><a href="%s" title="上一页">%s</a></li>',
							  $this->separator, $href, $this->prev
					);
			//打印首页		
			$href = $this->renderUrl(1);
			$html .= sprintf('<li><a href="%s" title="首页">1</a></li>', $href);
		} else {
			//不可点击状态
			$html .= '<li class="prevpage">'.$this->prev.'</li>';			
			$html .= '<li class="curpage">'.$this->prevCurPage.'1'.$this->postCurPage.'</li>';
		}

		//页码在位置
		$c = ceil($this->curPage/$this->delta);
		
		$s = $this->curPage - $this->delta;
		$e = $this->curPage + $this->delta;
		
		$s < 2 && $s = 2;
		($e >= $this->ttlpage) && ($e = $this->ttlpage -1);
			
		if ($this->curPage>$this->delta+1) {
			$html .= $this->separator.'...';
		}
		//echo $s,'---',$e;exit;
		for ($i=$s; $i<=$e; $i++) {
			if ($this->curPage == $i) {
				$html .= $this->separator.'<li class="curpage">'.$this->prevCurPage.$i.$this->postCurPage.'</li>';			
			} else {				
				$href = $this->renderUrl($i);
				$html.= sprintf('%s<li><a href="%s" title="第%d页">%d</a></a>', $this->separator, $href, $i, $i);
			}
		}
			
		//打印后省略符
		if ( $this->curPage + $this->delta < $this->ttlpage ) {
			$html .= $this->separator.'...';
		}

		if (($this->curPage < $this->ttlpage) && ($this->ttlpage > 1)) {
			$href = $this->renderUrl($this->ttlpage);
			$html .= sprintf(	'%s<li><a href="%s" title="尾页">%s</a></li>',
								$this->separator, $href, $this->ttlpage
					);		
			$href = $this->renderUrl($this->curPage + 1);
			$html .= sprintf(	'%s<li><a href="%s" title="下一页" class="nextpage">%s</a></li>',
								$this->separator, $href, $this->next
					);						
		} else {
			if ($this->ttlpage > 1) {
				$html .= '<li class="curpage">'.$this->prevCurPage.$this->ttlpage.$this->postCurPage.'</li>';		
			}
			$html .= "<li class=\"nextpage\">$this->next</li>";		
		}

		//$html .= '</div>';
		return $html;
    }

    // }}}
    // {{{ renderUrl()
    
    /**
     * Helper method
     * @param int $pageId
     * @access private
     */
    function renderUrl($pageId)
    {
		$this->qs[$this->urlVar] = $pageId;

		$qs = $this->isStatic ? str_replace('%d', $this->qs[$this->urlVar], $this->file)
							: '?' . $this->_http_build_query_wrapper($this->qs);

		return $this->url.$qs;
    }

    // }}}
    // {{{ _recursive_stripslashes()
    
    /**
     * Helper method
     * @param mixed $var
     * @access private
     */
    function _recursive_stripslashes(&$var)
    {
        if (is_array($var)) {
            foreach (array_keys($var) as $k) {
                $this->_recursive_stripslashes($var[$k]);
            }
        } else {
            $var = stripslashes($var);
        }
    }

    // }}}
    // {{{ _recursive_urldecode()

    /**
     * Helper method
     * @param mixed $var
     * @access private
     */
    function _recursive_urldecode(&$var)
    {
        if (is_array($var)) {
            foreach (array_keys($var) as $k) {
                $this->_recursive_urldecode($var[$k]);
            }
        } else {
            $trans_tbl = array_flip(get_html_translation_table(HTML_ENTITIES));
            $var = strtr($var, $trans_tbl);
        }
    }

    // }}}
    // {{{ getPageSelectBox()

    /**
     * Returns a string with a XHTML SELECT menu with the page numbers,
     * useful as an alternative to the links
     *
     * @param array   - 'optionText': text to show in each option.
     *                  Use '%d' where you want to see the number of pages selected.
     *                - 'autoSubmit': if TRUE, add some js code to submit the
     *                  form on the onChange event
     * @param string   $extraAttributes (html attributes) Tag attributes or
     *                  HTML attributes (id="foo" pairs), will be inserted in the
     *                  <select> tag
     * @return string xhtml select Box
     * @access public
     */
    function getPageSelectBox($params = array(), $extraAttributes = '')
    {
        require_once 'Pager/HtmlWidgets.php';
        $widget =& new Pager_HtmlWidgets($this);
        return $widget->getPageSelectBox($params, $extraAttributes);
    }

    // }}}
    // {{{ _http_build_query_wrapper()
    
    /**
     * This is a slightly modified version of the http_build_query() function;
     * it heavily borrows code from PHP_Compat's http_build_query().
     * The main change is the usage of htmlentities instead of urlencode,
     * since it's too aggressive
     *
     * @author Stephan Schmidt <schst@php.net>
     * @author Aidan Lister <aidan@php.net>
     * @author Lorenzo Alberton <l dot alberton at quipo dot it>
     * @param array $data
     * @return string
     * @access private
     */
    function _http_build_query_wrapper($data)
    {
        $data = (array)$data;
        if (empty($data)) {
            return '';
        }
        $separator = ini_get('arg_separator.output');
        if ($separator == '&amp;') {
            $separator = '&'; //the string is escaped by htmlentities anyway...
        }
        $tmp = array ();
        foreach ($data as $key => $val) {
            if (is_scalar($val)) {
                //array_push($tmp, $key.'='.$val);
                $val = urlencode($val);
                $key = urlencode($key);
                array_push($tmp, $key .'='. str_replace('%2F', '/', $val));
                continue;
            }
            // If the value is an array, recursively parse it
            if (is_array($val)) {
                array_push($tmp, $this->__http_build_query($val, htmlentities($key)));
                continue;
            }
        }
        return implode($separator, $tmp);
    }

    // }}}
    // {{{ __http_build_query()

    /**
     * Helper function
     * @author Stephan Schmidt <schst@php.net>
     * @author Aidan Lister <aidan@php.net>
     * @access private
     */
    function __http_build_query($array, $name)
    {
        $tmp = array ();
        $separator = ini_get('arg_separator.output');
        if ($separator == '&amp;') {
            $separator = '&'; //the string is escaped by htmlentities anyway...
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                //array_push($tmp, $this->__http_build_query($value, sprintf('%s[%s]', $name, $key)));
                array_push($tmp, $this->__http_build_query($value, $name.'%5B'.$key.'%5D'));
            } elseif (is_scalar($value)) {
                //array_push($tmp, sprintf('%s[%s]=%s', $name, htmlentities($key), htmlentities($value)));
                array_push($tmp, $name.'%5B'.htmlentities($key).'%5D='.htmlentities($value));
            } elseif (is_object($value)) {
                //array_push($tmp, $this->__http_build_query(get_object_vars($value), sprintf('%s[%s]', $name, $key)));
                array_push($tmp, $this->__http_build_query(get_object_vars($value), $name.'%5B'.$key.'%5D'));
            }
        }
        return implode($separator, $tmp);
    }
}
?>