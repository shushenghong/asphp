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

    public $title;

    public $layoutFile;

    private $metas = [ ];

    private $cssFiles = [ ];

    private $jsFiles = [ ];

    public function registerMeta($name, $content) {
        $this->metas [$name] = $content;
    }

    public function registerJSFile($jsFile, $name = '') {
        if( empty( $name ) ) {
            $name = md5( $jsFile );
        }
        $this->jsFiles [$name] = $jsFile;
    }

    public function registerCSSFile($cssFile, $name = '') {
        if( empty( $name ) ) {
            $name = md5( $cssFile );
        }
        $this->cssFiles [$name] = $cssFile;
    }

    private function getHeaderTags() {
        $rtn = '';
        foreach ( $this->metas as $name => $content ) {
            $rtn .= "<meta name='{$name}' content='{$content}'>\n";
        }
        foreach ( $this->cssFiles as $cssFile ) {
            $rtn .= "<link rel='stylesheet' type='text/css' href='{$cssFile}'>\n";
        }
        return $rtn;
    }

    private function getBodyEndHtml() {
        $rtn = '';
        foreach ( $this->jsFiles as $jsFile ) {
            $rtn .= "<script type='text/javascript' src='{$jsFile}'></script>\n";
        }
        return $rtn;
    }

    /**
     * 渲染某个view，并自动套用layout
     *
     * @param string $viewFile view文件的绝对地址
     * @param array $variables
     * @return string
     */
    public function render($viewFile, $variables = []) {
        $content = $this->renderFile( $viewFile, $variables );
        if( !empty( $this->layoutFile ) ) {
            $content = $this->renderFile( $this->layoutFile, [ 
                'content' => $content 
            ] );
        }
        return $content;
    }

    /**
     * 渲染某个 file
     *
     * @param string $viewFile view文件的绝对地址
     * @param array $variables
     * @return string
     */
    private function renderFile($viewFile, $variables = []) {
        ob_start();
        ob_implicit_flush( false );
        
        extract( $variables, EXTR_OVERWRITE );
        require_once $viewFile;
        $rtn = ob_get_clean();
        
        return $rtn;
    }
}