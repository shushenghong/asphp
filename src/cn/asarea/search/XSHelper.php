<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\search;

use cn\asarea\core\Application;

/**
 * XunSearch Helper
 *
 * @link http://www.xunsearch.com/
 * @author Ather.Shu Nov 9, 2014 8:10:12 PM
 */
class XSHelper {

    private static $_inited = false;

    private static function init() {
        if( !self::$_inited ) {
            $xsConfig = Application::getInstance()->getConfig( 'xs' );
            require_once $xsConfig ['api'];
            self::$_inited = true;
        }
    }

    /**
     * 获取某库索引对象
     *
     * @param string $dbName 库名称
     * @return \XSIndex
     */
    private static function _getIndex($dbName) {
        // 建立 XS 对象，项目名称为：demo
        $xs = new \XS( $dbName );
        // 获取 索引对象
        return $xs->index;
    }

    /**
     * 重建某索引库
     *
     * @param string $dbName 库名称
     * @param array $datas n行数据集
     */
    public static function rebuild($dbName, $datas) {
        self::init();
        // 获取 索引对象
        $index = self::_getIndex( $dbName );
        // 宣布开始重建索引
        $index->beginRebuild();
        
        // 添加数据
        foreach ( $datas as $data ) {
            // 创建文档对象
            $doc = new \XSDocument();
            $doc->setFields( $data );
            // 添加到索引数据库中
            $index->add( $doc );
        }
        
        // 告诉服务器重建完比
        $index->endRebuild();
    }

    /**
     * 添加或更新items
     *
     * @param string $dbName 库名称
     * @param array $datas 单行数据（一维数组） | 多行数据（多维数组）
     * @param bool $add 是否是添加，为提高效率，如果确定是新增，设置为true
     */
    public static function addOrUpdateItems($dbName, $datas, $add = FALSE) {
        self::init();
        if( !is_array( $datas ) || empty( $datas ) ) {
            return;
        }
        $datas = is_array( $datas [0] ) ? $datas : array (
            $datas 
        );
        
        $index = self::_getIndex( $dbName );
        $index->openBuffer();
        
        foreach ( $datas as $data ) {
            $doc = new \XSDocument();
            $doc->setFields( $data );
            // 更新
            $index->update( $doc, $add );
        }
        
        $index->closeBuffer();
    }

    /**
     * 删除items
     *
     * @param string $dbName 库名称
     * @param int|array $ids id或者id数组
     */
    public static function removeItem($dbName, $ids) {
        self::init();
        $ids = is_array( $ids ) ? $ids : array (
            $ids 
        );
        
        $index = self::_getIndex( $dbName );
        $index->openBuffer();
        $index->del( $ids );
        $index->closeBuffer();
    }

    /**
     * 获取原生xs search
     *
     * @param string $dbName 库名称
     * @return \XSSearch
     */
    public static function getSearcher($dbName) {
        self::init();
        // 建立 XS 对象，项目名称为：demo
        $xs = new \XS( $dbName );
        return $xs->search;
    }
}