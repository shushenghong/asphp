<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\image;

use cn\asarea\net\Http;

/**
 * 图像处理类
 *
 * @author Ather.Shu Apr 21, 2013 4:38:53 PM
 */
class GD {

    const POS_TOP_LEFT = 0;

    const POS_TOP_CENTER = 1;

    const POS_TOP_RIGHT = 2;

    const POS_BOTTOM_LEFT = 3;

    const POS_BOTTOM_CENER = 4;

    const POS_BOTTOM_RIGHT = 5;

    /**
     * 内部存储的image resource
     *
     * @var resource
     */
    private $_img;

    private static $_emptyGD;

    /**
     * 加载图片到resource
     *
     * @param string $filename 本地图片地址
     * @return GD
     */
    public static function load($filename) {
        $type = self::imageType( $filename );
        switch ($type) {
            case IMAGETYPE_GIF :
                $img = imagecreatefromgif( $filename );
                break;
            case IMAGETYPE_JPEG :
                $img = imagecreatefromjpeg( $filename );
                break;
            case IMAGETYPE_PNG :
                $img = imagecreatefrompng( $filename );
                break;
            case IMAGETYPE_BMP :
                $img = self::imagecreatefrombmp( $filename );
                break;
            case IMAGETYPE_WBMP :
                $img = imagecreatefromwbmp( $filename );
                break;
        }
        return isset( $img ) ? new GD( $img ) : self::emptyGD();
    }

    /**
     * bmp图片文件load到resource，来自php帮助文档
     * @param string $filename
     * @return resource
     */
    private static function imagecreatefrombmp($filename) {
        // Load the image into a string
        $file = fopen( $filename, "rb" );
        $read = fread( $file, 10 );
        while ( !feof( $file ) && ($read != "") ) {
            $read .= fread( $file, 1024 );
        }
        
        $temp = unpack( "H*", $read );
        $hex = $temp [1];
        $header = substr( $hex, 0, 108 );
        // Process the header
        // Structure: http://www.fastgraph.com/help/bmp_header_format.html
        if( substr( $header, 0, 4 ) == "424d" ) {
            // Cut it in parts of 2 bytes
            $header_parts = str_split( $header, 2 );
            // Get the width 4 bytes
            $width = hexdec( $header_parts [19] . $header_parts [18] );
            // Get the height 4 bytes
            $height = hexdec( $header_parts [23] . $header_parts [22] );
            // Unset the header params
            unset( $header_parts );
        }
        
        // Define starting X and Y
        $x = 0;
        $y = 1;
        // Create newimage
        $image = imagecreatetruecolor( $width, $height );
        // Grab the body from the image
        $body = substr( $hex, 108 );
        // Calculate if padding at the end-line is needed
        // Divided by two to keep overview.
        // 1 byte = 2 HEX-chars
        $body_size = (strlen( $body ) / 2);
        $header_size = ($width * $height);
        // Use end-line padding? Only when needed
        $usePadding = ($body_size > ($header_size * 3) + 4);
        // Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption
        // Calculate the next DWORD-position in the body
        for($i = 0; $i < $body_size; $i += 3) {
            // Calculate line-ending and padding
            if( $x >= $width ) {
                // If padding needed, ignore image-padding
                // Shift i to the ending of the current 32-bit-block
                if( $usePadding ) {
                    $i += $width % 4;
                }
                // Reset horizontal position
                $x = 0;
                // Raise the height-position (bottom-up)
                $y++;
                // Reached the image-height? Break the for-loop
                if( $y > $height ) {
                    break;
                }
            }
            // Calculation of the RGB-pixel (defined as BGR in image-data)
            // Define $i_pos as absolute position in the body
            $i_pos = $i * 2;
            $r = hexdec( $body [$i_pos + 4] . $body [$i_pos + 5] );
            $g = hexdec( $body [$i_pos + 2] . $body [$i_pos + 3] );
            $b = hexdec( $body [$i_pos] . $body [$i_pos + 1] );
            // Calculate and draw the pixel
            $color = imagecolorallocate( $image, $r, $g, $b );
            imagesetpixel( $image, $x, $height - $y, $color );
            // Raise the horizontal position
            $x++;
        }
        // Unset the body / free the memory
        unset( $body );
        // Return image-object
        return $image;
    }

    /**
     * 抓取远程图片
     *
     * @param string $url 远程图片地址
     * @param string $filename 保存的文件地址
     * @return GD
     */
    public static function urlCatch($url, $filename, $refer = NULL) {
        $content = Http::request( $url, $refer );
        // if catch fail, try use refer '' to catch again
        if( $content === false && $refer !== '' ) {
            $content = Http::request( $url, '' );
        }
        if( $content === false ) {
            return self::emptyGD();
        }
        else {
            file_put_contents( $filename, $content );
            return self::load( $filename );
        }
    }

    /**
     * 获取图片文件类型
     *
     * @param string $filename
     * @return int IMAGETYPE_*
     */
    public static function imageType($filename) {
        if( file_exists( $filename ) ) {
            if( function_exists( 'exif_imagetype' ) ) {
                return exif_imagetype( $filename );
            }
            
            list ( $w, $h, $type ) = @getimagesize( $filename );
            return $type;
        }
        return 0;
    }

    private static function emptyGD() {
        if( !self::$_emptyGD ) {
            self::$_emptyGD = new GD( NULL );
        }
        return self::$_emptyGD;
    }

    public function __construct($img) {
        if( is_resource( $img ) ) {
            $this->_img = $img;
        }
    }

    /**
     * 内部图片资源
     *
     * @return resource
     */
    public function img() {
        return $this->_img;
    }

    /**
     * 是否空，没有image resource
     *
     * @return boolean
     */
    public function isEmpty() {
        return empty( $this->_img );
    }

    /**
     * 图片宽度
     *
     * @return number
     */
    public function width() {
        if( $this->isEmpty() ) {
            return 0;
        }
        return imagesx( $this->img() );
    }

    /**
     * 图片高度
     *
     * @return number
     */
    public function height() {
        if( $this->isEmpty() ) {
            return 0;
        }
        return imagesy( $this->img() );
    }

    /**
     * 缩放图片，图片会自动按原始比例缩放到需要的宽高（不会导致图片比例失真）
     *
     * @param int $w 目标宽度
     * @param int $h 目标高度
     * @param bool $keepRatio 是否保持原始图片高宽比，false会强制缩放到新的尺寸
     * @return GD
     */
    public function resize($w, $h, $keepRatio = true) {
        if( $this->isEmpty() ) {
            return $this;
        }
        $img = $this->_img;
        $ow = imagesx( $img );
        $oh = imagesy( $img );
        if( $ow == $w && $oh == $h ) {
            return $this;
        }
        else {
            $nw = $w;
            $nh = $h;
            if( $keepRatio ) {
                $oscale = $ow / $oh;
                $scale = $w / $h;
                if( $oscale > $scale ) {
                    $nw = $w;
                    $nh = $w / $oscale;
                }
                else {
                    $nw = $h * $oscale;
                    $nh = $h;
                }
            }
            $nimg = imagecreatetruecolor( $nw, $nh );
            imagecopyresampled( $nimg, $img, 0, 0, 0, 0, $nw, $nh, $ow, $oh );
            $this->_img = $nimg;
            return $this;
        }
    }

    /**
     * 添加水印
     *
     * @param string $waterFile 水印文件地址
     * @param int $pos 水印到的位置POS_*
     * @param int $opacity 透明值 0 - 100
     * @return GD
     */
    public function watermark($waterFile, $pos, $opacity = 50) {
        if( $this->isEmpty() ) {
            return $this;
        }
        $waterImg = GD::load( $waterFile );
        if( $waterImg->isEmpty() ) {
            return $this;
        }
        
        $ww = $waterImg->width();
        $wh = $waterImg->height();
        $w = $this->width();
        $h = $this->height();
        if( $ww > $w || $wh > $h ) {
            return $this;
        }
        switch ($pos) {
            case self::POS_TOP_LEFT :
                $px = 0;
                $py = 0;
                break;
            case self::POS_TOP_CENTER :
                $px = ($w - $ww) / 2;
                $py = 0;
                break;
            case self::POS_TOP_RIGHT :
                $px = $w - $ww;
                $py = 0;
                break;
            case self::POS_BOTTOM_LEFT :
                $px = 0;
                $py = $h - $wh;
                break;
            case self::POS_BOTTOM_CENER :
                $px = ($w - $ww) / 2;
                $py = $h - $wh;
                break;
            case self::POS_BOTTOM_RIGHT :
                $px = $w - $ww;
                $py = $h - $wh;
                break;
        }
        // imagecopymerge( $this->img(), $waterImg->img(), $px, $py, 0, 0, $ww, $wh, $opacity );
        // 如果水印图片本身带透明色，则使用imagecopy方法，imagecopymerge不支持alpha水印图！
        // imagecopy( $this->img(), $waterImg->img(), $px, $py, 0, 0, $ww, $wh );
        // 解决方案：http://sina.salek.ws/content/alpha-support-phps-imagecopymerge-function
        // creating a cut resource
        $cut = imagecreatetruecolor( $ww, $wh );
        // copying that section of the background to the cut
        imagecopy( $cut, $this->img(), 0, 0, $px, $py, $ww, $wh );
        // placing the watermark now
        imagecopy( $cut, $waterImg->img(), 0, 0, 0, 0, $ww, $wh );
        imagecopymerge( $this->img(), $cut, $px, $py, 0, 0, $ww, $wh, $opacity );
        
        return $this;
    }

    /**
     * 自动裁剪图片（裁剪透明背景或者四周相同颜色）
     *
     * @return \cn\asarea\image\GD
     */
    public function cropAuto() {
        if( $this->isEmpty() ) {
            return $this;
        }
        $this->_img = imagecropauto( $this->img(), IMG_CROP_DEFAULT );
        return $this;
    }

    /**
     * 裁剪图片到某个区域
     *
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     */
    public function crop($x, $y, $w, $h) {
        if( $this->isEmpty() ) {
            return $this;
        }
        $this->_img = imagecrop( $this->img(), 
                [ 
                    'x' => $x,
                    'y' => $y,
                    'width' => $w,
                    'height' => $h 
                ] );
        return $this;
    }

    /**
     * 转换图像到jpeg格式
     *
     * @param string $desfilename 目标图片地址
     * @param int $quality 0-100 质量
     * @return bool 是否转换成功
     */
    public function jpeg($desfilename, $quality = 100) {
        if( !$this->isEmpty() ) {
            return imagejpeg( $this->img(), $desfilename, $quality );
        }
        else {
            return false;
        }
    }

    /**
     * 转换图像到png格式
     *
     * @param string $desfilename 目标图片地址
     * @return bool 是否转换成功
     */
    public function png($desfilename) {
        if( !$this->isEmpty() ) {
            return imagepng( $this->img(), $desfilename );
        }
        else {
            return false;
        }
    }

    /**
     * 转换图像到gif格式
     *
     * @param string $desfilename 目标图片地址
     * @return bool 是否转换成功
     */
    public function gif($desfilename) {
        if( !$this->isEmpty() ) {
            return imagegif( $this->img(), $desfilename );
        }
        else {
            return false;
        }
    }

    /**
     * 覆盖某文件，自动根据该图片类型决定存储类型
     *
     * @param string $filename 要覆盖的文件
     * @return bool 是否覆盖成功
     */
    public function overwrite($filename) {
        $type = self::imageType( $filename );
        switch ($type) {
            case IMAGETYPE_GIF :
                return $this->gif( $filename );
            case IMAGETYPE_JPEG :
                return $this->jpeg( $filename );
            case IMAGETYPE_PNG :
                return $this->png( $filename );
        }
        return false;
    }
}
?>