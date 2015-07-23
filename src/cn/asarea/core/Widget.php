<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\core;

/**
 * 组件
 *
 * @author Ather.Shu Jan 3, 2015 10:25:51 AM
 */
class Widget {

    /**
     * 运行某widget
     *
     * @param array $config
     * @return string
     */
    public static function widget($config = []) {
        ob_start();
        ob_implicit_flush( false );
        
        $cls = get_called_class();
        /* @var $widget Widget */
        $widget = new $cls();
        foreach ( $config as $key => $value ) {
            $widget->$key = $value;
        }
        $out = $widget->run();
        
        $rtn = ob_get_clean() . $out;
        return $rtn;
    }

    /**
     * 运行widget，可以直接输出字符串，也可以return字符串
     *
     * @return string
     */
    public function run() {
    }
}