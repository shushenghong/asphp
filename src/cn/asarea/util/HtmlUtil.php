<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\util;
/**
 * html辅助
 * 
 * @author Ather.Shu Nov 9, 2014 7:48:01 PM
 */
class HtmlUtil {

    /**
     * 重定向到url
     * 
     * @param string $url 新url
     */
    public static function redirect($url) {
        echo "<script type='text/javascript'>location.href='$url';</script>";
    }

    /**
     * 输出js
     * 
     * @param string $js 代码
     */
    public static function writeJS($js) {
        echo "<script type='text/javascript'>$js</script>";
    }

    /**
     * 输出<a href='url' title='title'>txt</a>链接
     * 
     * @param $txt string 链接文字
     * @param $url string 链接url
     * @param $addHttp bool 是否自动添加http://在url前面
     * @param $newWin bool 是否在新窗口打开url
     * @param $title string 链接tip
     */
    public static function href($txt, $url, $addHttp = FALSE, $newWin = TRUE, $title = '') {
        // 将没有http的url前面加上http，将空设置为不链接
        // <a href="javascript:void(0)" onclick="" > 或者 <a href='' onclick='return false'>
        if( empty( $url ) ) {
            $url = "javascript:void(0)";
        }
        else if( $addHttp && stripos( $url, "http://" ) === FALSE ) {
            $url = "http://$url";
        }
        //
        return "<a href='$url'" . ($newWin ? " target='_blank'" : '') . (empty( $title ) ? '' : " title='$title'") . ">$txt</a>";
    }
}