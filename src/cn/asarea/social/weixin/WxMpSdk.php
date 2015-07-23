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
 * 微信公众平台sdk
 *
 * @author Ather.Shu Feb 9, 2015 4:01:58 PM
 */
class WxMpSdk extends BaseWxSdk {

    const MEDIA_TYPE_IMG = "image";

    const MEDIA_TYPE_VOICE = "voice";

    const MEDIA_TYPE_VIDEO = "video";

    const MEDIA_TYPE_THUMB = "thumb";
    
    // 微信后台开发者管理工具中配置的token
    private $token = "weixin";

    /**
     *
     * @var IWxMpResponder
     */
    private $responder;

    public function __construct($responder, $token, $appId, $appSecret, $ticketSavePath) {
        parent::__construct( $appId, $appSecret, $ticketSavePath );
        $this->token = $token;
        $this->responder = $responder;
    }
    
    // 配置相关
    /**
     * 更新菜单配置
     *
     * @param array $menus [
     *        {
     *        "type":"click",
     *        "name":"今日歌曲",
     *        "key":"V1001_TODAY_MUSIC"
     *        },
     *        {
     *        "name":"菜单",
     *        "sub_button":[
     *        {
     *        "type":"view",
     *        "name":"搜索",
     *        "url":"http://www.soso.com/"
     *        },
     *        {
     *        "type":"view",
     *        "name":"视频",
     *        "url":"http://v.qq.com/"
     *        },
     *        {
     *        "type":"click",
     *        "name":"赞一下我们",
     *        "key":"V1001_GOOD"
     *        }]
     *        }]
     */
    public function updateMenus($menus) {
        $response = $this->callAPI( "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->getAccessToken(), 
                json_encode( [ 
                    'button' => $menus 
                ], JSON_UNESCAPED_UNICODE ), 'post' );
    }
    
    //
    // 主动推送相关
    //
    
    /**
     * 上传多媒体文件
     *
     * @param string $filePath
     * @param string $type
     * @return string media id
     */
    public function uploadMedia($filePath, $type) {
        $curlFile = new \CURLFile( $filePath );
        $response = $this->callAPI( "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->getAccessToken(), 
                [ 
                    'type' => $type,
                    'media' => $curlFile 
                ], 'post' );
        if( $response ) {
            return $response->media_id;
        }
    }

    /**
     * 上传图文素材消息
     *
     * @param $articles [] 素材内容数组
     *        {"thumb_media_id":"qI6_Ze_6PtV7svjolgs-rN6stStuHIjs9_DidOHaj0Q-mwvBelOXCFZiq2OsIU-p",
     *        "author":"xxx",
     *        "title":"Happy Day",
     *        "content_source_url":"www.qq.com",
     *        "content":"content",
     *        "digest":"digest",
     *        "show_cover_pic":"1"}
     * @return string media id
     */
    public function uploadNews($articles) {
        $response = $this->callAPI( "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=" . $this->getAccessToken(), 
                json_encode( [ 
                    'articles' => $articles 
                ], JSON_UNESCAPED_UNICODE ), 'post' );
        if( $response ) {
            return $response->media_id;
        }
    }

    /**
     * 推送图文消息到所有成员
     *
     * @param string $mediaId
     */
    public function pushNewsToAll($mediaId) {
        $response = $this->callAPI( "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . $this->getAccessToken(), 
                json_encode( 
                        [ 
                            'filter' => [ 
                                'is_to_all' => true 
                            ],
                            'mpnews' => [ 
                                'media_id' => $mediaId 
                            ],
                            'msgtype' => 'mpnews' 
                        ], JSON_UNESCAPED_UNICODE ), 'post' );
        if( $response ) {
            return $response->msg_id;
        }
    }
    
    //
    // 被动推送相关
    //
    /**
     * 检查消息是否来自微信
     */
    public function checkSignature() {
        if( !isset( $_GET ["signature"] ) || !isset( $_GET ["timestamp"] ) || !isset( $_GET ["nonce"] ) ) {
            return false;
        }
        $signature = $_GET ["signature"];
        $timestamp = $_GET ["timestamp"];
        $nonce = $_GET ["nonce"];
        
        $tmpArr = array (
            $this->token,
            $timestamp,
            $nonce 
        );
        sort( $tmpArr, SORT_STRING );
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 根据微信用户输入消息得到响应字串
     *
     * @return string 响应字串
     */
    public function response() {
        if( !$this->checkSignature() ) {
            return;
        }
        // get post data, May be due to the different environments
        $postStr = file_get_contents( "php://input" );
        // log
        $this->log( "收到用户消息：{$postStr}" );
        
        // extract post data
        if( !empty( $postStr ) ) {
            $postObj = simplexml_load_string( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
            if( $postObj === false ) {
                return;
            }
            // 解析消息
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $time = $postObj->CreateTime;
            $msgType = $postObj->MsgType;
            switch ($msgType) {
                // 普通文本消息
                case 'text' :
                    $keyword = trim( $postObj->Content );
                    $response = $this->responder->onText( $keyword );
                    break;
                // 图片消息
                case 'image' :
                    $response = $this->responseText( "哟，给我发了张图啊" );
                    break;
                // 语音消息
                case 'voice' :
                    $response = $this->responder->onVoice( $postObj->Recognition );
                    break;
                // 视频消息
                case 'video' :
                    $response = $this->responseText( "哟，这视频挺带感" );
                    break;
                // 位置消息
                case 'location' :
                    $response = $this->responseText( "哟，离我挺近啊" );
                    break;
                // 链接消息
                case 'link' :
                    $response = $this->responseText( "哟，这链接啥玩意啊" );
                    break;
                // 其他事件
                case 'event' :
                    $response = $this->onEvent( $postObj );
                    break;
                default :
                    return;
            }
            
            if( !empty( $response ) ) {
                $textTpl = "<xml>" . "<ToUserName><![CDATA[%s]]></ToUserName>" . "<FromUserName><![CDATA[%s]]></FromUserName>" .
                         "<CreateTime>%s</CreateTime>" . "%s" . "</xml>";
                $response = sprintf( $textTpl, $fromUsername, $toUsername, time(), $response );
                // log
                $this->log( "发送被动响应：{$response}" );
                
                return $response;
            }
        }
    }

    private function onEvent($postObj) {
        $event = $postObj->Event;
        $response = '';
        switch ($event) {
            // 订阅
            case 'subscribe' :
                $response = $this->responder->onEventSubscribe();
                break;
            // 取消订阅
            case 'unsubscribe' :
                $response = $this->responseText( "很遗憾没能达到亲的要求，期待亲的归来。" );
                break;
            // 自动上传位置
            case 'LOCATION' :
                break;
            // 点击某自定义响应菜单
            case 'CLICK' :
                // EventKey
                $response = $this->responder->onEventMenuClick( $postObj->EventKey );
                break;
            // 点击某链接菜单
            case 'VIEW' :
                break;
        }
        return $response;
    }

    /**
     * 拼接简单文本响应
     *
     * @return string
     */
    public function responseText($content) {
        $tmpl = "<MsgType><![CDATA[text]]></MsgType>" . "<Content><![CDATA[%s]]></Content>";
        return sprintf( $tmpl, $content );
    }

    /**
     * 发送media响应，如图片、语音、视频
     *
     * @param unknown $mediaType
     */
    public function responseMedia($mediaType) {
    }

    /**
     * 发送音乐消息
     */
    public function responseMusic() {
    }

    /**
     * 拼接发送图文响应
     *
     * @param $news [] [{'title', 'info', 'pic', 'url'}]
     * @return string
     */
    public function responseNews($news) {
        $num = count( $news );
        $tmpl = "<MsgType><![CDATA[news]]></MsgType>" . "<ArticleCount>%d</ArticleCount>" . "<Articles>" . "%s" . "</Articles>";
        
        $itemTmpl = "<item>" . "<Title><![CDATA[%s]]></Title>" . "<Description><![CDATA[%s]]></Description>" . "<PicUrl><![CDATA[%s]]></PicUrl>" .
                 "<Url><![CDATA[%s]]></Url>" . "</item>";
        
        $itemStr = "";
        foreach ( $news as $item ) {
            // 只有单图文的描述才会显示，多图文的描述不会被显示
            $itemStr .= sprintf( $itemTmpl, $item ['title'], ($num > 1 ? "" : $item ['info']), $item ['pic'], $item ['url'] );
        }
        
        return sprintf( $tmpl, count( $news ), $itemStr );
    }
}