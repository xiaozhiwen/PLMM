<?php
/**
 *  基本图片处理，用于完成图片缩入，水印添加
 *  当水印图超过目标图片尺寸时，水印图能自动适应目标图片而缩小
 *  水印图可以设置跟背景的合并度
 *
 *  Copyright(c) 2005 by ustb99. All rights reserved
 *
 *  To contact the author write to {@link mailto:ustb99@hotmail.com}
 *
 * @author 偶然
 * @version $Id: thumb.class.php,v 1.0 2005/6/21 19:00:04 $
 * @package system
 */

/**
 * ThumbHandler
 * @access public
 */
class ThumbHandler
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
    var $mask_word;// 水印文字
    var $mask_img;// 水印图片
    var $mask_pos_x = 0;// 水印横坐标
    var $mask_pos_y = 0;// 水印纵坐标
    var $mask_offset_x = 5;// 水印横向偏移
    var $mask_offset_y = 5;// 水印纵向偏移
    var $font_w;// 水印字体宽
    var $font_h;// 水印字体高
    var $mask_w;// 水印宽
    var $mask_h;// 水印高
    var $mask_font_color = "#ffffff";// 水印文字颜色
    var $mask_font = 2;// 水印字体
    var $font_size;// 尺寸
    var $mask_position = 0;// 水印位置
    var $mask_img_pct = 50;// 图片合并程度,值越大，合并程序越低
    var $mask_txt_pct = 50;// 文字合并程度,值越小，合并程序越低
    var $img_border_size = 0;// 图片边框尺寸
    var $img_border_color;// 图片边框颜色
    var $img_type;// 文件类型

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
    function ThumbHandler()
    {
        $this->mask_font_color = "#ffffff";
        $this->font = 2;
        $this->font_size = 12;
    }

    /**
     * 取得图片的宽
     */
    function getImgWidth($src)
    {
        return imagesx($src);
    }

    /**
     * 取得图片的高
     */
    function getImgHeight($src)
    {
        return imagesy($src);
    }

    /**
     * 设置图片生成路径
     *
     * @param    string    $src_img   图片生成路径
     */
    function setSrcImg($src_img)
    {
        file_exists($src_img)?($this->src_img = $src_img):die("图片不存在");
    }

    /**
     * 设置图片生成路径
     *
     * @param    string    $dst_img   图片生成路径
     */
    function setDstImg($dst_img)
    {
        $arr  = explode('/',$dst_img);
        $last = array_pop($arr);
        $path = implode('/',$arr);
        $this->_mkdirs($path);
        $this->dst_img = $dst_img;
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
    function setMaskWord($word)
    {
        $this->mask_word .= $word;
    }

    /**
     * 设置字体颜色
     *
     * @param    string     $color    字体颜色
     */
    function setMaskFontColor($color="#ffffff")
    {
        $this->mask_font_color = $color;
    }

    /**
     * 设置水印字体
     *
     * @param    string|integer    $font    字体
     */
    function setMaskFont($font=2)
    {
        if(!is_numeric($font) && !file_exists($font))
        {
            die("字体文件不存在");
        }
        $this->font = $font;
    }

    /**
     * 设置文字字体大小,仅对truetype字体有效
     */
    function setMaskFontSize($size = "12")
    {
        $this->font_size = $size;
    }

    /**
     * 设置图片水印
     *
     * @param    string    $img     水印图片源
     */
    function setMaskImg($img)
    {
        $this->mask_img = $img;
    }

    /**
     * 设置水印横向偏移
     *
     * @param    integer     $x    横向偏移量
     */
    function setMaskOffsetX($x)
    {
        $this->mask_offset_x = (int)$x;
    }

    /**
     * 设置水印纵向偏移
     *
     * @param    integer     $y    纵向偏移量
     */
    function setMaskOffsetY($y)
    {
        $this->mask_offset_y = (int)$y;
    }

    /**
     * 指定水印位置
     *
     * @param    integer     $position    位置,1:左上,2:左下,3:右上,0/4:右下
     */
    function setMaskPosition($position=0)
    {
        $this->mask_position = (int)$position;
    }

    /**
     * 设置图片合并程度
     *
     * @param    integer     $n    合并程度
     */
    function setMaskImgPct($n)
    {
        $this->mask_img_pct = (int)$n;
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
    function createImg($a, $b=null)
    {
        $this->_loadImg();
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

        if(2 == $num)
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
        if($this->mask_word)
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

        if($this->mask_img)
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

        if(empty($this->mask_word) && empty($this->mask_img))
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
        $this->_countMaskPos();
        $this->_checkMaskValid();

        $c = $this->_parseColor($this->mask_font_color);
        $color = imagecolorallocatealpha($src, $c[0], $c[1], $c[2], $this->mask_txt_pct);

        if(is_numeric($this->font))
        {
            imagestring($src,
                        $this->font,
                        $this->mask_pos_x, $this->mask_pos_y,
                        $this->mask_word,
                        $color);
        }
        else
        {
            imagettftext($src,
                        $this->font_size, 0,
                        $this->mask_pos_x, $this->mask_pos_y,
                        $color,
                        $this->font,
                        $this->mask_word);
        }
    }

    /**
     * 生成水印图
     */
    function _createMaskImg($src)
    {
        $this->_countMaskPos();
        $this->_checkMaskValid();

        imagecopymerge($src,
                        $this->h_mask,
                        $this->mask_pos_x ,$this->mask_pos_y,
                        0, 0,
                        $this->mask_w, $this->mask_h,
                        $this->mask_img_pct);

        imagedestroy($this->h_mask);
    }

    /**
     * 加载水印图
     */
    function _loadMaskImg()
    {
        $this->img_type = $this->_getPostfix($this->mask_img);
        $this->_checkValid($this->img_type);

        $img_type  = $this->img_type;
        $func_name = $this->all_type[$img_type]['create'];
        if(function_exists($func_name))
        {
            $this->h_mask = $func_name($this->mask_img);
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
        $this->img_type = $this->_getPostfix($this->src_img);
        $this->_checkValid($this->img_type);

        $img_type  = $this->img_type;
        $func_name = $this->all_type[$img_type]['create'];
        if(function_exists($func_name))
        {
            $this->h_src = $func_name($this->src_img);
            $this->src_w = $this->getImgWidth($this->h_src);
            $this->src_h = $this->getImgHeight($this->h_src);
        }
        else
        {
            die($func_name."函数不被支持");
        }
    }

    /**
     * 图片输出
     */
    function _output()
    {
        $img_type  = $this->img_type;
        $func_name = $this->all_type[$img_type]['output'];
        if(function_exists($func_name))
        {
			// 判断浏览器,若是IE就不发送头
			if(isset($_SERVER['HTTP_USER_AGENT']))
			{
				$ua = strtoupper($_SERVER['HTTP_USER_AGENT']);
				if(!preg_match('/^.*MSIE.*\)$/i',$ua))
				{
					header("Content-type:$img_type");
				}
			}
            $func_name($this->h_dst, $this->dst_img, $this->img_display_quality);
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
    function _countMaskPos()
    {
        switch($this->mask_position)
        {
            case 1:
                // 左上
                $this->mask_pos_x = $this->mask_offset_x + $this->img_border_size;
                $this->mask_pos_y = $this->mask_offset_y + $this->img_border_size;
                break;

            case 2:
                // 左下
                $this->mask_pos_x = $this->mask_offset_x + $this->img_border_size;
                $this->mask_pos_y = $this->src_h - $this->mask_h - $this->mask_offset_y;
                break;

            case 3:
                // 右上
                $this->mask_pos_x = $this->src_w - $this->mask_w - $this->mask_offset_x;
                $this->mask_pos_y = $this->mask_offset_y + $this->img_border_size;
                break;

            case 4:
                // 右下
                $this->mask_pos_x = $this->src_w - $this->mask_w - $this->mask_offset_x;
                $this->mask_pos_y = $this->src_h - $this->mask_h - $this->mask_offset_y;
                break;

            default:
                // 默认将水印放到右下,偏移指定像素
                $this->mask_pos_x = $this->src_w - $this->mask_w - $this->mask_offset_x;
                $this->mask_pos_y = $this->src_h - $this->mask_h - $this->mask_offset_y;
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
            $word_length   = strlen($this->mask_word);
            $this->mask_w  = $this->font_w*$word_length;
            $this->mask_h  = $this->font_h;
        }
        else
        {
            $arr = imagettfbbox ($this->font_size,0, $this->font,$this->mask_word);
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
        Return (   $this->mask_w + $this->mask_offset_x > $this->fill_w
                || $this->mask_h + $this->mask_offset_y > $this->fill_h)
                   ?true:false;
    }

    /**
     * 检查水印图是否超过原图
     */
    function _checkMaskValid()
    {
        if(    $this->mask_w + $this->mask_offset_x > $this->src_w
            || $this->mask_h + $this->mask_offset_y > $this->src_h)
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
     * @param    string     $img_type    文件类型
     */
    function _checkValid($img_type)
    {
        if(!array_key_exists($img_type, $this->all_type))
        {
            Return false;
        }
    }

    /**
     * 按指定路径生成目录
     *
     * @param    string     $path    路径
     */
    function _mkdirs($path)
    {
        $adir = explode('/',$path);
        $dirlist = '';
        $rootdir = array_shift($adir);
        if(($rootdir!='.'||$rootdir!='..')&&!file_exists($rootdir))
        {
            mkdir($rootdir);
        }
        foreach($adir as $key=>$val)
        {
            if($val!='.'&&$val!='..')
            {
                $dirlist .= "/".$val;
                $dirpath = $rootdir.$dirlist;
                if(!file_exists($dirpath))
                {
                    mkdir($dirpath);
                    chmod($dirpath,0777);
                }
            }
        }
    }
}
?>