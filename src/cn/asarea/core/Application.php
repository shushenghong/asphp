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
 * 程序，是整体入口
 *
 * @author Ather.Shu Nov 9, 2014 10:13:04 PM
 */
class Application {

    private static $_instance;

    private $_config;

    /**
     * 请求
     *
     * @var Request
     */
    private $_request;

    /**
     * 响应
     *
     * @var Response
     */
    private $_reponse;

    /**
     * 初始化application
     *
     * @param array $config
     * @return \cn\asarea\core\Application
     */
    public static function init($config) {
        if( empty( self::$_instance ) ) {
            self::$_instance = new Application();
            self::$_instance->_config = $config;
            // 设置基本配置
            date_default_timezone_set( $config ['timezone'] );
            ini_set( "session.save_path", $config['session']['save_path'] );
            ini_set( "session.gc_maxlifetime", $config['session']['lifetime'] );
        }
        return self::$_instance;
    }

    /**
     * app单例
     *
     * @return \cn\asarea\core\Application
     */
    public static function getInstance() {
        return self::$_instance;
    }

    /**
     * 程序启动<br>
     * 根据request、route得到对应controller以及action，生成response
     */
    public function startup() {
        $this->_request = new Request();
        $this->_reponse = new Response();
        //TODO set_error_handler 设置全局错误处理
        Route::checkMatchAndRun( $this->_request );
    }

    public function getRequest() {
        return $this->_request;
    }

    public function getResponse() {
        return $this->_reponse;
    }

    /**
     * 获取config
     *
     * @param string $section 如'db' 'xs'，null代表获取整体config，否则代表取某段config
     */
    public function getConfig($section = NULL) {
        return empty( $section ) ? $this->_config : $this->_config [$section];
    }
}