<?php
class PLMM_Graphics_AntiSpamImage
{
	var $width = 60;
	var $height = 20;
	var $font = 5;
	var $textLen = 4;
	var $text = '';
	var $textColor = array();
	var $bgColor = '#FFFFFF';
	var $sessionVar = 'twt_asi_code';
	var $im;
	var $density = 0.02;//不要设太高
	var $validOptions = array('width', 'height','textLen','canvasColor','sessionVar');
	
    function PLMM_Graphics_AntiSpamImage($options = array())
    {
		foreach($options as $var => $val ) {
			if (array_key_exists($var, $this->validOptions)) 
				$this->$var = $val;
		}
 	}
	
    function build()
    {
		$this->setText();
		$this->setSession();
		$this->setImg();
		$this->setTextColor();
		
		$fw = imagefontwidth($this->font);
		$fh = imagefontheight($this->font);		
		
		//水平剩余宽度
		$sh = $this->width - $fw*$this->textLen;
		$sv = $this->height - $fh;
		
		//$y = (int) $sv / 2;
		//字符间距
		$c = (int) ($sh - 10)/($this->textLen - 1);
		//初始x
		$x = 5 - $fw - $c;
		$density = (int)($fw+2*$c)*($this->width-2)*$this->density;
		for($i=0; $i<$this->textLen; $i++) {
			$y = rand(1, $sv-1);
			$x += $fw + $c;
			imagestring($this->im, $this->font, $x, $y, $this->text[$i], $this->textColor[$i]);
			for ( $j=0; $j<$density; $j++ ) {
				imagesetpixel($this->im, rand($x-$c, $x+$fw+$c), rand(1,$this->height-1),$this->textColor[$i]);
			}
			
		}
	}

	function set($option, $value)
	{
		array_key_exists($option, $this->validOptions) && $this->$option = $value;
	}
		
	function setImg()
	{
		$b = hexdec(substr($this->bgColor, -2));
		$g = hexdec(substr($this->bgColor, -4, 2));
		$r = hexdec(substr($this->bgColor, -6, 2));		
		$im = imagecreatetruecolor($this->width, $this->height);
		$bg =  imagecolorallocate($im, $r, $g, $b);
		imagefill($im, 0, 0, $bg);
		$this->im = $im;
	}
	
	/**
	 * 
	 * @ access private
	 */
	function setSession() {
		isset($_SESSION) || session_start();
		$_SESSION[$this->sessionVar] = $this->text;
		return 1;
	}
	
	function setText() {	
		$s = '';
		$c = 'ABCDEFGHJKLMNPQRTUVWXY346789';
		for ($i = 0, $ml = strlen($c)-1; $i < $this->textLen; $i++) {
			$s .= $c[rand(0,$ml)];
		}
		$this->text = $s;
	}

	function setTextColor() {	
		for ($i = 0; $i < $this->textLen; $i++) {
			$this->textColor[] = imagecolorallocate ($this->im, rand(0,0xCC), rand(0x00,0x55), rand(0,0xCC));
		}
	}
		
	function output() 
	{
		$this->build();
		//clear 
		while( ob_get_level() ) {
			@ob_end_clean();
		}
		
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		//  HTTP/1.1
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		//  HTTP/1.0
		header('Pragma: no-cache');
		//  Let it more flexible!
		if (0&&function_exists('imagepng')) {
		   header('Content-type: image/png');
		   imagepng($this->im);
		} elseif (0 && function_exists('imagegif')) {
		   header('Content-type: image/gif');
		   imagegif($this->im);
		} elseif (function_exists('imagejpeg')) {
		   header('Content-type: image/jpeg');
		   imagejpeg($this->im);
		} else {
		   die('No image support in this PHP server!');
		}
	
		imagedestroy ($this->im);    
		exit;
	}
}
?>
