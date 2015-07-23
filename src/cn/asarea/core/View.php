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
 * 视图
 *
 * @author Ather.Shu Nov 9, 2014 10:20:03 PM
 */
class View {

    /**
     * 渲染某个view file
     *
     * @param string $viewFile view文件的绝对地址
     * @param array $variables
     * @return string
     */
    public static function renderFile($viewFile, $variables = array()) {
        ob_start();
        ob_implicit_flush( false );
        
        extract( $variables, EXTR_OVERWRITE );
        require_once $viewFile;
        $rtn = ob_get_clean();
        
        return $rtn;
    }
}