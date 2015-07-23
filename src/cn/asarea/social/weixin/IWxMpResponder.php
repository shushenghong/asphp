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
 * 微信公众号被动响应接口
 * 
 * @author Ather.Shu Feb 10, 2015 1:03:52 PM
 */
interface IWxMpResponder {

    public function onText($text);

//     public function onImage();

    public function onVoice($recognition);

//     public function onVideo();

//     public function onLocation($location);

//     public function onLink($link);

    public function onEventSubscribe();
//     public function onEventLocation();
    public function onEventMenuClick($key);
//     public function onEventMenuLinkClick();
}