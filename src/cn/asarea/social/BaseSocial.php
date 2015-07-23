<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\social;

/**
 * 基础social类
 *
 * @author Ather.Shu Feb 9, 2015 3:41:57 PM
 */
class BaseSocial {

    protected $debug = false;

    protected $logFilePath;

    public function setDebug($debug, $logFilePath = null) {
        $this->debug = $debug;
        if( $logFilePath ) {
            $this->logFilePath = $logFilePath;
        }
    }

    /**
     * 调用某API接口
     *
     * @param string $url 接口url
     * @param array $queryParams 传递参数
     * @param string $method http方法POST、GET，默认GET
     * @return string http响应
     */
    protected function callAPI($url, $queryParams = NULL, $method = "GET") {
        $method = strtoupper( $method );
        $ch = curl_init();
        // 设置URL和相应的选项
        if( $method != "POST" && (is_array( $queryParams ) || is_object( $queryParams )) ) {
            $queryParams = http_build_query( $queryParams );
        }
        // http方法
        switch ($method) {
            case "POST" :
                curl_setopt_array( $ch, 
                        array (
                            CURLOPT_SAFE_UPLOAD => true,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => $queryParams 
                        ) );
                break;
            default :
                $url = empty( $queryParams ) ? $url : "{$url}?{$queryParams}";
                break;
        }
        // 其他http选项
        curl_setopt_array( $ch, 
                array (
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_CONNECTTIMEOUT => 30,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_ENCODING => '' 
                ) );
        // 抓取URL并获取内容
        $response = curl_exec( $ch );
        // 关闭cURL资源，并且释放系统资源
        curl_close( $ch );
        // log
        $this->log( "接口：{$url} 返回：\n{$response}" );
        
        return $response;
    }

    /**
     * 生成一串随机长度额字母串
     *
     * @param number $length
     * @return string
     */
    protected function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i++) {
            $str .= substr( $chars, mt_rand( 0, strlen( $chars ) - 1 ), 1 );
        }
        return $str;
    }

    /**
     * 写log
     *
     * @param string $msg
     */
    protected function log($msg) {
        if( !$this->debug ) {
            return;
        }
        $log = date( "Y-m-d H:i:s" ) . "\n" . $msg . "\n";
        file_put_contents( $this->logFilePath, $log, FILE_APPEND );
    }
}