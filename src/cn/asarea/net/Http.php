<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\net;

/**
 * http类库
 *
 * @author Ather.Shu Nov 9, 2014 7:46:25 PM
 */
class Http {

    /**
     * 404状态码
     */
    public static function header404($page404 = NULL) {
        header( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
        header( "Status: 404 Not Found" );
        $_SERVER ['REDIRECT_STATUS'] = 404;
        if( $page404 ) {
            include $page404;
        }
    }

    /**
     * 301或者302跳转（301是永久跳转）
     *
     * @param $code int 跳转码，默认302临时跳转
     */
    public static function headerRedirect($toUrl, $code = 302) {
        header( "Location: $toUrl", TRUE, $code );
    }

    /**
     * 生成缓存控制头
     *
     * @param $absExpires int 绝对过期时间，如果小于0则代表不缓存，等于0代表永不过期，否则就是某个具体的时刻值(s)
     */
    public static function headerExpires($absExpires = NULL) {
        $ctime = time();
        if( $absExpires < 0 ) {
            // 设为过去时间
            $absExpires = $ctime - 7 * 24 * 3600;
            header( "Pragma: no-cache" );
            header( "Cache-Control: no-store" );
        }
        else {
            // 永不过期则用30天代替
            if( $absExpires == 0 ) {
                $absExpires = $ctime + 30 * 24 * 3600;
            }
            header( "Cache-Control: max-age=" . ($absExpires - $ctime) );
        }
        header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', $absExpires ) . ' GMT' );
    }

    /**
     * 抓取远程内容
     *
     * @param string $url 远程html或者资源地址
     * @param string $refer null代表自动根据url设置，其他则为外部传入特定值
     * @return mixed 抓取成功返回内容string，失败返回false
     */
    public static function request($url, $refer = NULL) {
        $urlInfo = parse_url( $url );
        // url不合格
        if( !isset( $urlInfo ['scheme'] ) || !isset( $urlInfo ['host'] ) ) {
            return false;
        }
        // 抓取
        if( !isset( $refer ) ) {
            $refer = $urlInfo ['scheme'] . '://' . $urlInfo ['host'];
        }
        // 如果有curl用curl，否则用stream
        if( extension_loaded( 'curl' ) ) {
            $ch = curl_init( $url );
            curl_setopt_array( $ch, 
                    array (
                        CURLOPT_HEADER => false,
                        CURLOPT_RETURNTRANSFER => true,
                        // CURLOPT_HTTPHEADER => [
                        // "Accept-Encoding: gzip,deflate",
                        // "Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4",
                        // "Cache-Control: no-cache",
                        // "Pragma: no-cache"
                        // ],
                        // CURLOPT_ENCODING => 'gzip,deflate',
                        CURLOPT_USERAGENT => 'User-Agent: Mozilla/4.0 (compatible; MSIE 4.00; Windows 2000)',
                        CURLOPT_REFERER => $refer 
                    ) );
            $data = curl_exec( $ch );
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close( $ch );
            if($httpCode != 200) {
                return false;
            }
            return $data;
        }
        else {
            $opts = array (
                "http" => array (
                    "method" => "GET",
                    "timeout" => 60,
                    "header" => "User-Agent: Mozilla/4.0 (compatible; MSIE 4.00; Windows 2000)\r\n" . "Accept: */*\r\n" . "Referer: $refer\r\n" .
                             "Accept-Encoding: gzip,deflate,compress\r\n" 
                ) 
            );
            $context = stream_context_create( $opts );
            // try 3 times
            $count = 0;
            while ( $count < 3 && ($content = @file_get_contents( $url, false, $context )) === false ) {
                $count++;
            }
            if( $content === false ) {
                return false;
            }
            else {
                // 解压内容
                return HTTP::decompress( $content );
            }
        }
    }

    /**
     * 尝试采用各种压缩方法解压内容
     */
    private static function decompress($content) {
        $olevel = error_reporting();
        error_reporting( E_ALL & ~E_WARNING );
        // gzip
        if( function_exists( 'gzdecode' ) ) {
            $decompressed = gzdecode( $content );
            if( $decompressed !== false ) {
                return $decompressed;
            }
        }
        // deflate
        $decompressed = gzinflate( $content );
        if( $decompressed !== false ) {
            return $decompressed;
        }
        // compress
        $decompressed = gzuncompress( $content );
        if( $decompressed !== false ) {
            return $decompressed;
        }
        error_reporting( $olevel );
        return $content;
    }
}
?>