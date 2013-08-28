<?php

//fix _mkdirs为空的bug,(参数不带目录信息,如setDstImg('demo.png');
//fix _output中 $func_name当不是jpeg时,多出的图片质量参数错误

class Image_Thumb

{

    var $src_img;// 源文件

    var $dst_img;// 目标文件

    var $h_src; // 图片资源句柄

    var $h_dst;// 新图句柄

    var $h_mask;// 水印句柄

    var $img_create_quality = 100;// 图片生成质量

    var $img_display_quality = 75;// 图片显示质量,默认为75

    var $img_scale = 0;// 图片缩放比例

    var $src_w = 0;// 原图宽度

    var $src_h = 0;// 原图高度

    var $dst_w = 0;// 新图总宽度

    var $dst_h = 0;// 新图总高度

    var $fill_w;// 填充图形宽

    var $fill_h;// 填充图形高

    var $start_x;// 新图绘制起始横坐标

    var $start_y;// 新图绘制起始纵坐标

    var $end_x;// 新图绘制结束横坐标

    var $end_y;// 新图绘制结束纵坐标

    

    var $watermarkText;			//水印文字

    var $watermarkImg;			// 水印图片

    var $watermarkPosX = 0;		// 水印横坐标

    var $watermarkPosY = 0;		// 水印纵坐标

    var $watermarkOffsetX = 5;	// 水印横向偏移

    var $watermarkOffsetY = 5;	// 水印纵向偏移

	

    var $font_w;// 水印字体宽

    var $font_h;// 水印字体高

    var $mask_w;// 水印宽

    var $mask_h;// 水印高

    var $watermarkFontColor = "#ffffff";// 水印文字颜色

    var $mask_font = 2;// 水印字体

    var $font_size;// 尺寸

    var $watermarkPos = 0;// 水印位置

    var $mask_img_pct = 50;// 图片合并程度,值越大，合并程序越低

    var $mask_txt_pct = 50;// 文字合并程度,值越小，合并程序越低

    var $img_border_size = 0;// 图片边框尺寸

    var $img_border_color;// 图片边框颜色

    var $imgType;// 文件类型

	

	var $force = false;//是否强制大小,新加

    // 文件类型定义,并指出了用于生成和输出图片的函数

    var $all_type = array(

        "jpg"  => array("create"=>"ImageCreateFromjpeg","output"=>"imagejpeg"),

        "gif"  => array("create"=>"ImageCreateFromGIF" ,"output"=>"imagegif"),

        "png"  => array("create"=>"imagecreatefrompng" ,"output"=>"imagepng"),

        "wbmp" => array("create"=>"imagecreatefromwbmp","output"=>"image2wbmp"),

        "jpeg" => array("create"=>"ImageCreateFromjpeg","output"=>"imagejpeg"));



    /**

     * 构造函数

     */

    function Image_Thumb()
	{

        $this->watermarkFontColor = "#ffffff";
        $this->font = 2;
        $this->watermarkFontSize = 12;
    }

	

	//}}

	//{{ getImgWidth
    /**
     * @param	string	$img	图像地址
	 * @access	private
     */
    function getImgWidth($img)
    {
        return imagesx($img);
    }

	

	//}}

	//{{ getImgHeight

	

    /**

     * @param string $img 图像地址

	 * @access private

     */

    function getImgHeight($img)
    {
        return imagesy($img);
    }



	//}}

	//{{ setSrcImg

	

    /**
     * @设置图像源
     * @
     * @param	string  $img 源图像路径
	 * @ access public 
     */
    function setSrcImg($img)
    {	

        file_exists($img) ? ($this->src_img = realpath($img))
						  : trigger_error("setSrcImg('$img') 文件不存在!", E_USER_ERROR);

    }

	

	//}}

	//{{ setDstImg	



    /**
     * @设置图片生成路径
     *
     * @param    string    $imgpath   目标图片生成路径
     */
    function setDstImg($imgpath)
    {
        $arr  = explode('/',$imgpath);
        $last = array_pop($arr);
		if($arr) {
			$path = implode('/',$arr);
			$this->_mkdirs($path);
		}
      $this->dst_img = $imgpath;
    }



    /**

     * 设置图片的显示质量

     *

     * @param    string      $n    质量

     */

    function setImgDisplayQuality($n)

    {

        $this->img_display_quality = (int)$n;

    }



    /**

     * 设置图片的生成质量

     *

     * @param    string      $n    质量

     */

    function setImgCreateQuality($n)

    {

        $this->img_create_quality = (int)$n;

    }



    /**

     * 设置文字水印

     *

     * @param    string     $word    水印文字

     * @param    integer    $font    水印字体

     * @param    string     $color   水印字体颜色

     */

    function setWatermarkText($text)

    {

        $this->watermarkText .= $text;

    }

	//}}

	//{{ setWatermarkFontColor

	

    /**

     * 设置字体颜色

     *

     * @param    string     $color    水印字体颜色

     */

    function setWatermarkFontColor($color="#ffffff")

    {

        $this->watermarkFontColor = $color;

    }



    /**

     * 设置水印字体

     *

     * @param    string|integer    $font    字体

     */

    function setWaterMarkFont($font=2)

    {

        if(!is_numeric($font) && !file_exists($font))

        {

            die("字体文件不存在");

        }

        $this->font = $font;

    }

	//}}

	//{{  setWaterMarkFontSize

    /**

     * 设置文字字体大小,仅对truetype字体有效

     */

    function setWaterMarkFontSize($size = "12")

    {

        $this->watermarkFontSize = $size;

    }



    /**

     * 设置图片水印

     *

     * @param    string    $img     水印图片源

     */

    function setMaskImg($img)

    {

        $this->watermarkImg = $img;

    }



    /**

     * 设置水印横向偏移

     *

     * @param    integer     $x    横向偏移量

     */

    function setMaskOffsetX($x)

    {

        $this->watermarkOffsetX = (int)$x;

    }



    /**

     * 设置水印纵向偏移

     *

     * @param    integer     $y    纵向偏移量

     */

    function setMaskOffsetY($y)

    {

        $this->watermarkOffsetY = (int)$y;

    }



    /**

     * 指定水印位置

     *

     * @param    integer     $position    位置,1:左上,2:左下,3:右上,0/4:右下

     */

    function setMaskPosition($position=0)

    {

        $this->watermarkPos = (int)$position;

    }



    /**

     * 设置图片合并程度

     *

     * @param    integer     $n    合并程度

     */

    function setMaskImgPct($n)

    {

        $this->watermarkImg_pct = (int)$n;

    }



    /**

     * 设置文字合并程度

     *

     * @param    integer     $n    合并程度

     */

    function setMaskTxtPct($n)

    {

        $this->mask_txt_pct = (int)$n;

    }



    /**

     * 设置缩略图边框

     *

     * @param    (类型)     (参数名)    (描述)

     */

    function setDstImgBorder($size=1, $color="#000000")

    {

        $this->img_border_size  = (int)$size;

        $this->img_border_color = $color;

    }



    /**

     * 创建图片,主函数

     * @param    integer    $a     当缺少第二个参数时，此参数将用作百分比，

     *                             否则作为宽度值

     * @param    integer    $b     图片缩放后的高度

     */

    function create($a, $b=null, $force=null)
    {

        $this->_loadImg();

		$this->force = $force;

        $num = func_num_args();

        if(1 == $num)

        {

            $r = (int)$a;

            if($r < 1)

            {

                die("图片缩放比例不得小于1");

            }

            $this->img_scale = $r;

			$this->_setNewImgSize($r);

        }



        if(1 < $num)

        {

            $w = (int)$a;

            $h = (int)$b;

            if(0 == $w)

            {

                die("目标宽度不能为0");

            }

            if(0 == $h)

            {

                die("目标高度不能为0");

            }

            $this->_setNewImgSize($w, $h);

        }

        $this->_createMask();

        $this->_output();



        // 释放

        imagedestroy($this->h_src);

        imagedestroy($this->h_dst);

    }



    /**

     * 生成水印,调用了生成水印文字和水印图片两个方法

     */

    function _createMask()

    {

        if($this->watermarkText)

        {

            // 获取字体信息

            $this->_setFontInfo();



            if($this->_isFull())

            {

                die("水印文字过大");

            }

            else

            {

                $this->h_dst = imagecreatetruecolor($this->dst_w, $this->dst_h);

                $this->_drawBorder();

                imagecopyresampled( $this->h_dst, $this->h_src,

                                    $this->start_x, $this->start_y,

                                    0, 0,

                                    $this->end_x, $this->end_y,

                                    $this->src_w, $this->src_h);

                $this->_createMaskWord($this->h_dst);

            }

        }



        if($this->watermarkImg)

        {

            $this->_loadMaskImg();//加载时，取得宽高



            if($this->_isFull())

            {

                // 将水印生成在原图上再拷

                $this->_createMaskImg($this->h_src);

                $this->h_dst = imagecreatetruecolor($this->dst_w, $this->dst_h);

                $this->_drawBorder();

                imagecopyresampled( $this->h_dst, $this->h_src,

                                    $this->start_x, $this->start_y,

                                    0, 0,

                                    $this->end_x, $this->end_y,

                                    $this->src_w, $this->src_h);

            }

            else

            {

                // 创建新图并拷贝

                $this->h_dst = imagecreatetruecolor($this->dst_w, $this->dst_h);

                $this->_drawBorder();

                imagecopyresampled( $this->h_dst, $this->h_src,

                                    $this->start_x, $this->start_y,

                                    0, 0,

                                    $this->end_x, $this->end_y,

                                    $this->src_w, $this->src_h);

                $this->_createMaskImg($this->h_dst);

            }

        }



        if(empty($this->watermarkText) && empty($this->watermarkImg))

        {

            $this->h_dst = imagecreatetruecolor($this->dst_w, $this->dst_h);

            $this->_drawBorder();

            imagecopyresampled( $this->h_dst, $this->h_src,

                                $this->start_x, $this->start_y,

                                0, 0,

                                $this->end_x, $this->end_y,

                                $this->src_w, $this->src_h);

        }

    }



    /**

     * 画边框

     */

    function _drawBorder()

    {

        if(!empty($this->img_border_size))

        {

            $c = $this->_parseColor($this->img_border_color);

            $color = ImageColorAllocate($this->h_src,$c[0], $c[1], $c[2]);

            imagefilledrectangle($this->h_dst,0,0,$this->dst_w,$this->dst_h,$color);// 填充背景色

        }

    }



    /**

     * 生成水印文字

     */

    function _createMaskWord($src)

    {

        $this->_countWatermarkPos();

        $this->_checkMaskValid();



        $c = $this->_parseColor($this->watermarkFontColor);

        $color = imagecolorallocatealpha($src, $c[0], $c[1], $c[2], $this->mask_txt_pct);



        if(is_numeric($this->font))

        {

            imagestring($src,

                        $this->font,

                        $this->watermarkPosX, $this->watermarkPosY,

                        $this->watermarkText,

                        $color);

        }

        else

        {

            imagettftext($src,

                        $this->watermarkFontSize, 0,

                        $this->watermarkPosX, $this->watermarkPosY,

                        $color,

                        $this->font,

                        $this->watermarkText);

        }

    }



    /**

     * 生成水印图

     */

    function _createMaskImg($src)

    {

        $this->_countWatermarkPos();

        $this->_checkMaskValid();



        imagecopymerge($src,

                        $this->h_mask,

                        $this->watermarkPosX ,$this->watermarkPosY,

                        0, 0,

                        $this->mask_w, $this->mask_h,

                        $this->watermarkImg_pct);



        imagedestroy($this->h_mask);

    }



    /**

     * 加载水印图

     */

    function _loadMaskImg()

    {

        $this->imgType = $this->_getPostfix($this->watermarkImg);

        $this->_checkValid($this->imgType);

        $imgType  = $this->imgType;

        $func_name = $this->all_type[$imgType]['create'];

        if(function_exists($func_name))

        {

            $this->h_mask = $func_name($this->watermarkImg);

            $this->mask_w = $this->getImgWidth($this->h_mask);

            $this->mask_h = $this->getImgHeight($this->h_mask);

        }

        else

        {

            die($func_name."函数不被支持");

        }

    }



    /**

     * 取得图片资源

     */

    function _loadImg()

    {
	
        $this->imgType = $this->_getPostfix($this->src_img);

        $this->_checkValid($this->imgType);

        $imgType  = $this->imgType;

		$func_name = $this->all_type[$imgType]['create'];

        if(function_exists($func_name))

        {

           $this->h_src = $func_name($this->src_img);

            $this->src_w = $this->getImgWidth($this->h_src);

            $this->src_h = $this->getImgHeight($this->h_src);

        }

        else

        {

            die("error:".$func_name."函数不被支持");

        }

    }



    /**

     * 图片输出

     */

    function _output()

    {

        $imgType  = $this->imgType;

        $func_name = $this->all_type[$imgType]['output'];

        if(function_exists($func_name))

        {

			// 判断浏览器,若是IE就不发送头

			if(isset($_SERVER['HTTP_USER_AGENT']))

			{

				$ua = strtoupper($_SERVER['HTTP_USER_AGENT']);

				if(!preg_match('/^.*MSIE.*\)$/i',$ua))

				{

					header("Content-type:$imgType");

				}

			}

			if(strpos($func_name, 'jpeg') !== false) {
					$func_name($this->h_dst, $this->dst_img, $this->img_display_quality);
			} else {
				$func_name($this->h_dst, $this->dst_img);
			}

        }

        else

        {

            Return false;

        }

    }



    /**

     * 分析颜色

     *

     * @param    string     $color    十六进制颜色

     */

    function _parseColor($color)

    {

        $arr = array();

        for($ii=1; $ii<strlen($color); $ii++)

        {

            $arr[] = hexdec(substr($color,$ii,2));

            $ii++;

        }



        Return $arr;

    }



    /**

     * 计算出位置坐标

     */

    function _countWatermarkPos()

    {

        switch($this->watermarkPos)

        {

            case 1:// 左上

                $this->watermarkPosX = $this->watermarkOffsetX + $this->img_border_size;

                $this->watermarkPosY = $this->watermarkOffsetY + $this->img_border_size;

                break;

            case 2: // 左下

                $this->watermarkPosX = $this->watermarkOffsetX + $this->img_border_size;

                $this->watermarkPosY = $this->src_h - $this->mask_h - $this->watermarkOffsetY;

                break;

            case 3:// 右上                

                $this->watermarkPosX = $this->src_w - $this->mask_w - $this->watermarkOffsetX;

                $this->watermarkPosY = $this->watermarkOffsetY + $this->img_border_size;

                break;

            case 4:// 右下                

                $this->watermarkPosX = $this->src_w - $this->mask_w - $this->watermarkOffsetX;

                $this->watermarkPosY = $this->src_h - $this->mask_h - $this->watermarkOffsetY;

                break;

            default:// 默认将水印放到右下,偏移指定像素                

                $this->watermarkPosX = $this->src_w - $this->mask_w - $this->watermarkOffsetX;

                $this->watermarkPosY = $this->src_h - $this->mask_h - $this->watermarkOffsetY;

                break;

        }

    }



    /**

     * 设置字体信息

     */

    function _setFontInfo()

    {

        if(is_numeric($this->font))

        {

            $this->font_w  = imagefontwidth($this->font);

            $this->font_h  = imagefontheight($this->font);



            // 计算水印字体所占宽高

            $word_length   = strlen($this->watermarkText);

            $this->mask_w  = $this->font_w*$word_length;

            $this->mask_h  = $this->font_h;

        }

        else

        {

            $arr = imagettfbbox ($this->watermarkFontSize,0, $this->font,$this->watermarkText);

            $this->mask_w  = abs($arr[0] - $arr[2]);

            $this->mask_h  = abs($arr[7] - $arr[1]);

        }

    }



    /**
     * 设置新图尺寸
     *
     * @param    integer     $img_w   目标宽度
     * @param    integer     $img_h   目标高度
     */

    function _setNewImgSize($img_w, $img_h=null)
    {

		$num = func_num_args();
		if(1 == $num)
		{
			$this->img_scale = $img_w;// 宽度作为比例
			$this->fill_w = round($this->src_w * $this->img_scale / 100) - $this->img_border_size*2;
		    $this->fill_h = round($this->src_h * $this->img_scale / 100) - $this->img_border_size*2;
			$this->dst_w   = $this->fill_w + $this->img_border_size*2;
			$this->dst_h   = $this->fill_h + $this->img_border_size*2;
		}



		if(2 == $num)
		{

			$fill_w   = (int)$img_w - $this->img_border_size*2;
			$fill_h   = (int)$img_h - $this->img_border_size*2;
			if($fill_w < 0 || $fill_h < 0)
			{
				die("图片边框过大，已超过了图片的宽度");
			}

			if($this->force) {
				$this->fill_w = (int)$fill_w;
				$this->fill_h = (int)$fill_h;
			} else {
				$rate_w = $this->src_w/$fill_w;
				$rate_h = $this->src_h/$fill_h;

				if($rate_w > $rate_h)
				{
					$this->fill_w = (int)$fill_w;
					$this->fill_h = round($this->src_h/$rate_w);
				}
				else
				{
					$this->fill_w = round($this->src_w/$rate_h);
					$this->fill_h = (int)$fill_h;
				}
			} 
			
			if ( $fill_w >= $this->src_w && $fill_h >= $this->src_h) {
				$this->fill_w = $this->src_w;
				$this->fill_h = $this->src_h;				
			} else if ( $fill_w >= $this->src_w && $fill_h <= $this->src_h) {
				$this->fill_h = $fill_h;
				$this->fill_w = $fill_h/$this->src_h*$this->src_w;				
			} else if ( $fill_w <= $this->src_w && $fill_h >= $this->src_h) {
				$this->fill_w = $fill_w;
				$this->fill_h = $fill_w/$this->src_w*$this->src_h;				
			}
			
			$this->dst_w   = $this->fill_w + $this->img_border_size*2;
			$this->dst_h   = $this->fill_h + $this->img_border_size*2;
		}

		

		$this->start_x = $this->img_border_size;

		$this->start_y = $this->img_border_size;

		$this->end_x   = $this->fill_w;

		$this->end_y   = $this->fill_h;
    }



    /**

     * 检查水印图是否大于生成后的图片宽高

     */

    function _isFull()

    {

        Return (   $this->mask_w + $this->watermarkOffsetX > $this->fill_w

                || $this->mask_h + $this->watermarkOffsetY > $this->fill_h)

                   ?true:false;

    }



    /**

     * 检查水印图是否超过原图

     */

    function _checkMaskValid()

    {

        if(    $this->mask_w + $this->watermarkOffsetX > $this->src_w

            || $this->mask_h + $this->watermarkOffsetY > $this->src_h)

        {

            die("水印图片尺寸大于原图，请缩小水印图");

        }

    }



    /**

     * 取得文件后缀，作为类成员

     */

    function _getPostfix($filename)

    {

        return substr(strrchr(trim(strtolower($filename)),"."),1);

    }



    /**

     * 检查图片类型是否合法,调用了array_key_exists函数，此函数要求

     * php版本大于4.1.0

     *

     * @param    string     $imgType    文件类型

     */

    function _checkValid($imgType)

    {

        if(!array_key_exists($imgType, $this->all_type))

        {

            Return false;

        }

    }



    /**

     * 按指定路径生成目录

     *

     * @param    string     $path    路径

     */

    function _mkdirs($path)    {

		if ( ! file_exists($path) ) {

			$this->_mkdirs(dirname($path));

			@mkdir($path, 0777);

			@chmod($path, 0777);

		}
		return;
	}

}

?>
