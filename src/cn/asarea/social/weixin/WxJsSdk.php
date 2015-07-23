<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\social\weixin;

/**
 * weixin jssdk
 *
 * @author Ather.Shu Feb 5, 2015 9:40:20 PM
 */
class WxJsSdk extends BaseWxSdk {

    /**
     * 获取js sdk签名参数数组
     *
     * @return array
     */
    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] !== 'off' || $_SERVER ['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        
        $signature = sha1( $string );
        
        $signPackage = array (
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string 
        );
        return $signPackage;
    }

    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $ticketFile = "{$this->ticketSavePath}/wx_jsapi_ticket.json";
        $data = file_exists( $ticketFile ) ? json_decode( file_get_contents( $ticketFile ) ) : null;
        if( empty( $data ) || $data->expire_time < time() ) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = $this->callAPI( $url );
            if( $res ) {
                $data = new \stdClass();
                $data->expire_time = time() + $res->expires_in;
                $data->jsapi_ticket = $res->ticket;
                file_put_contents( $ticketFile, json_encode( $data ) );
            }
        }
        $ticket = $data->jsapi_ticket;
        
        return $ticket;
    }
}

