<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

if (!function_exists('image_type_to_extension')) {

    function image_type_to_extension($imagetype, $include_dot = true) {
        if (empty($imagetype))
            return false;
        $dot = $include_dot ? '.' : '';
        switch ($imagetype) {
            case IMAGETYPE_GIF : return $dot . 'gif';
            case IMAGETYPE_JPEG : return $dot . 'jpg';
            case IMAGETYPE_PNG : return $dot . 'png';
            case IMAGETYPE_SWF : return $dot . 'swf';
            case IMAGETYPE_PSD : return $dot . 'psd';
            case IMAGETYPE_WBMP : return $dot . 'wbmp';
            case IMAGETYPE_XBM : return $dot . 'xbm';
            case IMAGETYPE_TIFF_II : return $dot . 'tiff';
            case IMAGETYPE_TIFF_MM : return $dot . 'tiff';
            case IMAGETYPE_IFF : return $dot . 'aiff';
            case IMAGETYPE_JB2 : return $dot . 'jb2';
            case IMAGETYPE_JPC : return $dot . 'jpc';
            case IMAGETYPE_JP2 : return $dot . 'jp2';
            case IMAGETYPE_JPX : return $dot . 'jpf';
            case IMAGETYPE_SWC : return $dot . 'swc';
            default : return false;
        }
    }

}

/**
  +------------------------------------------------------------------------------
 * 图像操作类库
  +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
  +------------------------------------------------------------------------------
 */
class Upload_Image
{

    /**
      +----------------------------------------------------------
     * 取得图像信息
     *
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $image 图像文件名
      +----------------------------------------------------------
     * @return mixed
      +----------------------------------------------------------
     */

    static function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }

    /**
      +----------------------------------------------------------
     * 显示服务器图像文件
     * 支持URL方式
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $imgFile 图像文件名
     * @param string $text 文字字符串
     * @param string $width 图像宽度
     * @param string $height 图像高度
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     */
    static function showImg($imgFile, $text = '', $width = 80, $height = 30) {
        //获取图像文件信息
        $info = Image::getImageInfo($imgFile);
        if ($info !== false) {
            $createFun = str_replace('/', 'createfrom', $info['mime']);
            $im = $createFun($imgFile);
            if ($im) {
                $ImageFun = str_replace('/', '', $info['mime']);
                if (!empty($text)) {
                    $tc = imagecolorallocate($im, 0, 0, 0);
                    imagestring($im, 3, 5, 5, $text, $tc);
                }
                if ($info['type'] == 'png' || $info['type'] == 'gif') {
                    imagealphablending($im, false); //取消默认的混色模式
                    imagesavealpha($im, true); //设定保存完整的 alpha 通道信息
                }
                header("Content-type: " . $info['mime']);
                $ImageFun($im);
                imagedestroy($im);
                return;
            }
        }
        //获取或者创建图像文件失败则生成空白PNG图片
        $im = imagecreatetruecolor($width, $height);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
        imagestring($im, 4, 5, 5, "NO PIC", $tc);
        Image::output($im);
        return;
    }

    /**
      +----------------------------------------------------------
     * 生成缩略图
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $image  原图
     * @param string $type 图像格式
     * @param string $thumbname 缩略图文件名
     * @param string $maxWidth  宽度
     * @param string $maxHeight  高度
     * @param string $position 缩略图保存目录
     * @param boolean $interlace 启用隔行扫描
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     */
    static function thumb($image, $thumbname, $type = '', $maxWidth = 200, $maxHeight = 50, $interlace = true) {
        // 获取原图信息
        $info = Image::getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            if ($scale >= 1) {
                // 超过原图大小不再缩略
                $width = $srcWidth;
                $height = $srcHeight;
            } else {
                // 缩略图尺寸
                $width = (int) ($srcWidth * $scale);
                $height = (int) ($srcHeight * $scale);
            }
            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $srcImg = $createFun($image);
            ini_set("memory_limit", "50M");
            //创建缩略图
            #if($type!='gif' && function_exists('imagecreatetruecolor'))
            #    $thumbImg = imagecreatetruecolor($width, $height);
            #else
            $thumbImg = imagecreate($width, $height);

            // 复制图片
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);

            // 正方形填充
            /* $length = abs($width - $height) / 2;
              if ($width > $height) {
              $size = $width;
              $size_x = 0;
              $size_y = $length;
              }else {
              $size = $height;
              $size_x = $length;
              $size_y = 0;
              } */

            if ($width > $height) {
                $size_x = 0;
                $size_y = abs($maxHeight - $height) / 2;
            } else {
                $size_x = abs($maxWidth - $width) / 2;
                $size_y = 0;
            }
            // 创建新的画布
            //$im = imagecreatetruecolor($size, $size);
            #$im = imagecreatetruecolor($maxWidth, $maxHeight);
            $im = imagecreate($maxWidth, $maxHeight);
            // 矩形涂色
            $white = imagecolorallocate($im, 255, 255, 255);

            // imagefilledrectangle($im, 0, 0, $size, $size, $white);
            imagefilledrectangle($im, 0, 0, $maxWidth, $maxHeight, $white);


            // 拷贝图像到画布
            imagecopy($im, $thumbImg, $size_x, $size_y, 0, 0, $width, $height);
            if ('gif' == $type || 'png' == $type) {
                $background_color = imagecolorallocate($im, 0, 255, 0);  //  指派一个绿色
                imagecolortransparent($im, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }

            $thumbImg = $im;
            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($thumbImg, $interlace);

            //$gray=ImageColorAllocate($thumbImg,255,0,0);
            //ImageString($thumbImg,2,5,5,"ThinkPHP",$gray);
            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($thumbImg, $thumbname);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbname;
        }
        return false;
    }

    /**
      +----------------------------------------------------------
     * 根据给定的字符串生成图像
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $string  字符串
     * @param string $size  图像大小 width,height 或者 array(width,height)
     * @param string $font  字体信息 fontface,fontsize 或者 array(fontface,fontsize)
     * @param string $type 图像格式 默认PNG
     * @param integer $disturb 是否干扰 1 点干扰 2 线干扰 3 复合干扰 0 无干扰
     * @param bool $border  是否加边框 array(color)
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     */
    static function buildString($string, $rgb = array(), $filename = '', $type = 'png', $disturb = 1, $border = true) {
        if (is_string($size))
            $size = explode(',', $size);
        $width = $size[0];
        $height = $size[1];
        if (is_string($font))
            $font = explode(',', $font);
        $fontface = $font[0];
        $fontsize = $font[1];
        $length = strlen($string);
        $width = ($length * 9 + 10) > $width ? $length * 9 + 10 : $width;
        $height = 22;
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = @imagecreatetruecolor($width, $height);
        } else {
            $im = @imagecreate($width, $height);
        }
        if (empty($rgb)) {
            $color = imagecolorallocate($im, 102, 104, 104);
        } else {
            $color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        }
        $backColor = imagecolorallocate($im, 255, 255, 255);    //背景色（随机）
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        $pointColor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));                 //点颜色

        @imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        @imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        @imagestring($im, 5, 5, 3, $string, $color);
        if (!empty($disturb)) {
            // 添加干扰
            if ($disturb = 1 || $disturb = 3) {
                for ($i = 0; $i < 25; $i++) {
                    imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $pointColor);
                }
            } elseif ($disturb = 2 || $disturb = 3) {
                for ($i = 0; $i < 10; $i++) {
                    imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $pointColor);
                }
            }
        }
        Image::output($im, $type, $filename);
    }

    /**
      +----------------------------------------------------------
     * 生成图像验证码
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $length  位数
     * @param string $mode  类型
     * @param string $type 图像格式
     * @param string $width  宽度
     * @param string $height  高度
     * @param string $fontface  字体
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     */
    static function buildImageVerify($length = 4, $mode = 5, $type = 'png', $width = 100, $height = 40, $fontface = 'crystal.ttf', $verifyName = 'verify') {
        import('@.ORG.Util.String');
        $randval = String::rand_string($length, $mode);
        $_SESSION[$verifyName] = md5($randval);
        $width = ($length * 10 + 10) > $width ? $length * 10 + 10 : $width;
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = @imagecreatetruecolor($width, $height);
        } else {
            $im = @imagecreate($width, $height);
        }

        $backColor = imagecolorallocate($im, 255, 255, 255);    //背景色（白色）
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        $pointColor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)); //生成波浪线的颜色（随机）

        @imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        @imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        $stringColor = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        //干扰线
        $w = 100;
        $h = 40;
        $h1 = rand(-5, 5);
        $h2 = rand(-1, 1);
        $w2 = rand(10, 15);
        $h3 = rand(4, 6);

        for ($i = -$w / 2; $i < $w / 2; $i = $i + 0.1) {
            $y = $h / $h3 * sin($i / $w2) + $h / 2 + $h1;
            imagesetpixel($im, $i + $w / 2, $y, $pointColor);
            $h2 != 0 ? imagesetpixel($im, $i + $w / 2, $y + $h2, $pointColor) : null;
        }

        if (!is_file($fontface)) {
            $fontface = dirname(__FILE__) . "/" . $fontface;
        }

        for ($i = 0; $i < $length; $i++) {
            $fontcolor = imagecolorallocate($im, 33, 165, 87); //这样保证随机出来的颜色较深。
            $code = msubstr($randval, $i, 1, 'utf-8', '');
            imagettftext($im, 25, 0, 20 * $i + 10, mt_rand(25, 35), $fontcolor, $fontface, $code);
        }
        Image::output($im, $type);
    }

    /**
      +----------------------------------------------------------
     * 生成图像验证码
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $length  位数
     * @param string $mode  类型
     * @param string $type 图像格式
     * @param string $width  宽度
     * @param string $height  高度
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     */
    static function buildImageVerifyBak($length = 4, $mode = 1, $type = 'png', $width = 48, $height = 22, $verifyName = 'verify') {
        import('@.ORG.Util.String');
        $randval = String::rand_string($length, $mode);
        $_SESSION[$verifyName] = md5($randval);
        $width = ($length * 10 + 10) > $width ? $length * 10 + 10 : $width;
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = @imagecreatetruecolor($width, $height);
        } else {
            $im = @imagecreate($width, $height);
        }
        $r = Array(225, 255, 255, 223);
        $g = Array(225, 236, 237, 255);
        $b = Array(225, 236, 166, 125);
        $key = mt_rand(0, 3);

        $backColor = imagecolorallocate($im, $r[$key], $g[$key], $b[$key]);    //背景色（随机）
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        $pointColor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));                 //点颜色

        @imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        @imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        $stringColor = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        // 干扰
        for ($i = 0; $i < 10; $i++) {
            $fontcolor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $fontcolor);
        }
        for ($i = 0; $i < 25; $i++) {
            $fontcolor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $pointColor);
        }
        for ($i = 0; $i < $length; $i++) {
            imagestring($im, 5, $i * 10 + 5, mt_rand(1, 8), $randval{$i}, $stringColor);
        }
//        @imagestring($im, 5, 5, 3, $randval, $stringColor);
        Image::output($im, $type);
    }

    // 中文验证码
    static function GBVerify($length = 4, $type = 'png', $width = 180, $height = 50, $fontface = 'simhei.ttf', $verifyName = 'verify') {
        import('ORG.Util.String');
        $code = String::rand_string($length, 4);
        $width = ($length * 45) > $width ? $length * 45 : $width;
        $_SESSION[$verifyName] = md5($code);
        $im = imagecreatetruecolor($width, $height);
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        $bkcolor = imagecolorallocate($im, 250, 250, 250);
        imagefill($im, 0, 0, $bkcolor);
        @imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        // 干扰
        for ($i = 0; $i < 15; $i++) {
            $fontcolor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $fontcolor);
        }
        for ($i = 0; $i < 255; $i++) {
            $fontcolor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $fontcolor);
        }
        if (!is_file($fontface)) {
            $fontface = dirname(__FILE__) . "/" . $fontface;
        }
        for ($i = 0; $i < $length; $i++) {
            $fontcolor = imagecolorallocate($im, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120)); //这样保证随机出来的颜色较深。
            $codex = msubstr($code, $i, 1);
            imagettftext($im, mt_rand(16, 20), mt_rand(-60, 60), 40 * $i + 20, mt_rand(30, 35), $fontcolor, $fontface, $codex);
        }
        Image::output($im, $type);
    }

    /**
      +----------------------------------------------------------
     * 把图像转换成字符显示
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $image  要显示的图像
     * @param string $type  图像类型，默认自动获取
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     */
    static function showASCIIImg($image, $string = '', $type = '') {
        $info = Image::getImageInfo($image);
        if ($info !== false) {
            $type = empty($type) ? $info['type'] : $type;
            unset($info);
            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $im = $createFun($image);
            $dx = imagesx($im);
            $dy = imagesy($im);
            $i = 0;
            $out = '<span style="padding:0px;margin:0;line-height:100%;font-size:1px;">';
            set_time_limit(0);
            for ($y = 0; $y < $dy; $y++) {
                for ($x = 0; $x < $dx; $x++) {
                    $col = imagecolorat($im, $x, $y);
                    $rgb = imagecolorsforindex($im, $col);
                    $str = empty($string) ? '*' : $string[$i++];
                    $out .= sprintf('<span style="margin:0px;color:#%02x%02x%02x">' . $str . '</span>', $rgb['red'], $rgb['green'], $rgb['blue']);
                }
                $out .= "<br>\n";
            }
            $out .= '</span>';
            imagedestroy($im);
            return $out;
        }
        return false;
    }

    /**
      +----------------------------------------------------------
     * 生成高级图像验证码
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $type 图像格式
     * @param string $width  宽度
     * @param string $height  高度
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     */
    static function showAdvVerify($type = 'png', $width = 180, $height = 40, $verifyName = 'verifyCode') {
        $rand = range('a', 'z');
        shuffle($rand);
        $verifyCode = array_slice($rand, 0, 10);
        $letter = implode(" ", $verifyCode);
        $_SESSION[$verifyName] = $verifyCode;
        $im = imagecreate($width, $height);
        $r = array(225, 255, 255, 223);
        $g = array(225, 236, 237, 255);
        $b = array(225, 236, 166, 125);
        $key = mt_rand(0, 3);
        $backColor = imagecolorallocate($im, $r[$key], $g[$key], $b[$key]);
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        $numberColor = imagecolorallocate($im, 255, rand(0, 100), rand(0, 100));
        $stringColor = imagecolorallocate($im, rand(0, 100), rand(0, 100), 255);
        // 添加干扰
        /*
          for($i=0;$i<10;$i++){
          $fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
          imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
          }
          for($i=0;$i<255;$i++){
          $fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
          imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$fontcolor);
          } */
        imagestring($im, 5, 5, 1, "0 1 2 3 4 5 6 7 8 9", $numberColor);
        imagestring($im, 5, 5, 20, $letter, $stringColor);
        Image::output($im, $type);
    }

    /**
      +----------------------------------------------------------
     * 生成UPC-A条形码
      +----------------------------------------------------------
     * @static
      +----------------------------------------------------------
     * @param string $type 图像格式
     * @param string $type 图像格式
     * @param string $lw  单元宽度
     * @param string $hi   条码高度
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     */
    static function UPCA($code, $type = 'png', $lw = 2, $hi = 100) {
        static $Lencode = array('0001101', '0011001', '0010011', '0111101', '0100011',
            '0110001', '0101111', '0111011', '0110111', '0001011');
        static $Rencode = array('1110010', '1100110', '1101100', '1000010', '1011100',
            '1001110', '1010000', '1000100', '1001000', '1110100');
        $ends = '101';
        $center = '01010';
        /* UPC-A Must be 11 digits, we compute the checksum. */
        if (strlen($code) != 11) {
            die("UPC-A Must be 11 digits.");
        }
        /* Compute the EAN-13 Checksum digit */
        $ncode = '0' . $code;
        $even = 0;
        $odd = 0;
        for ($x = 0; $x < 12; $x++) {
            if ($x % 2) {
                $odd += $ncode[$x];
            } else {
                $even += $ncode[$x];
            }
        }
        $code.=(10 - (($odd * 3 + $even) % 10)) % 10;
        /* Create the bar encoding using a binary string */
        $bars = $ends;
        $bars.=$Lencode[$code[0]];
        for ($x = 1; $x < 6; $x++) {
            $bars.=$Lencode[$code[$x]];
        }
        $bars.=$center;
        for ($x = 6; $x < 12; $x++) {
            $bars.=$Rencode[$code[$x]];
        }
        $bars.=$ends;
        /* Generate the Barcode Image */
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($lw * 95 + 30, $hi + 30);
        } else {
            $im = imagecreate($lw * 95 + 30, $hi + 30);
        }
        $fg = ImageColorAllocate($im, 0, 0, 0);
        $bg = ImageColorAllocate($im, 255, 255, 255);
        ImageFilledRectangle($im, 0, 0, $lw * 95 + 30, $hi + 30, $bg);
        $shift = 10;
        for ($x = 0; $x < strlen($bars); $x++) {
            if (($x < 10) || ($x >= 45 && $x < 50) || ($x >= 85)) {
                $sh = 10;
            } else {
                $sh = 0;
            }
            if ($bars[$x] == '1') {
                $color = $fg;
            } else {
                $color = $bg;
            }
            ImageFilledRectangle($im, ($x * $lw) + 15, 5, ($x + 1) * $lw + 14, $hi + 5 + $sh, $color);
        }
        /* Add the Human Readable Label */
        ImageString($im, 4, 5, $hi - 5, $code[0], $fg);
        for ($x = 0; $x < 5; $x++) {
            ImageString($im, 5, $lw * (13 + $x * 6) + 15, $hi + 5, $code[$x + 1], $fg);
            ImageString($im, 5, $lw * (53 + $x * 6) + 15, $hi + 5, $code[$x + 6], $fg);
        }
        ImageString($im, 4, $lw * 95 + 17, $hi - 5, $code[11], $fg);
        /* Output the Header and Content. */
        Image::output($im, $type);
    }

    static function output($im, $type = 'png', $filename = '') {
        header("Content-type: image/" . $type);
        $ImageFun = 'image' . $type;
        if (empty($filename)) {
            $ImageFun($im);
        } else {
            $ImageFun($im, $filename);
        }
        imagedestroy($im);
    }

    /**
      +----------------------------------------------------------
     * 裁剪图片并缩略
      +----------------------------------------------------------
     * @static
     * @access public
      +----------------------------------------------------------
     * @param string $image  原图
     * @param string $thumbname 裁剪图文件名
     * @param string $widthStart 宽度开始点
     * @param string $getWidth  宽度
     * @param string $heightStart  高度开始点
     * @param string $getHeight  高度
     * @param string $position 图片保存目录
     * @param string $thumbWidth 缩略图宽度
     * @param string $thumbHeight  缩略图宽度
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     */
    static function clippingThumb($image, $clipname, $widthStart = 0, $getWidth = 0, $heightStart = 0, $getHeight = 0, $thumbWidth = 0, $thumbHeight = 0) {
        // 获取原图信息
        $info = Image::getImageInfo($image);
        if ($info !== false) {

            $width = $getWidth ? ($getWidth - $widthStart) : ($info['width'] - $widthStart);
            $height = $getHeight ? ($getHeight - $heightStart) : ($info['height'] - $heightStart);

            $srcWidth = $widthStart ? $width : ($getWidth ? $getWidth : $info['width']);
            $srcHeight = $heightStart ? $height : ($getHeight ? $getHeight : $info['height']);

            $type = strtolower($info['type']);
            $interlace = 1;
            unset($info);

            if ($thumbWidth) {
                $height = $height * ($thumbWidth / $width);
                $width = $thumbWidth;
            }

            if ($thumbHeight) {
                $width = $width * ($thumbHeight / $height);
                $height = $thumbHeight;
            }

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $srcImg = $createFun($image);

            @ini_set("memory_limit", '50M');

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);

            // 复制图片
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, $widthStart, $heightStart, $width, $height, $srcWidth, $srcHeight);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            if ('gif' == $type || 'png' == $type) {
                $background_color = imagecolorallocate($thumbImg, 0, 255, 0); // 指派一个绿色
                imagecolortransparent($thumbImg, $background_color); // 设置为透明色，若注释掉该行则输出绿色的图
            }

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($thumbImg, $interlace);

            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($thumbImg, $clipname);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $clipname;
        }
        return false;
    }

}

//类定义结束
?>