<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\db;

use cn\asarea\cache\Cache;
/**
 * 数据库类
 *
 * @author Ather.Shu Nov 9, 2014 7:42:15 PM
 */
class DataBase {

    /**
     * 数据库连接资源 mysqli实例或者resource link
     *
     * @var mysqli
     */
    private $_link;

    /**
     * 构造函数
     *
     * @param mysqli或者resource link $link
     */
    public function __construct($link) {
        $this->_link = $link;
    }

    /**
     * 开始事务
     */
    public function startTransaction() {
        $this->query( "START TRANSACTION" );
    }

    /**
     * 提交事务
     */
    public function commit() {
        $this->query( "COMMIT" );
    }

    /**
     * 回滚事务
     */
    public function rollback() {
        $this->query( "ROLLBACK" );
    }

    /**
     * 执行一条sql语句
     *
     * @param string $sql sql语句
     * @param bool $cache 是否缓存
     * @return ResultSet
     */
    public function query($sql, $cache = false) {
        // 读缓存
        if( $cache ) {
            $cache = new Cache();
            $rtn = $cache->get( $sql );
            if( $rtn ) {
                return unserialize( $rtn );
            }
        }
        // 读数据库
        switch (DBManager::workingMode()) {
            case DBManager::MODE_PDO :
                $rs = $this->_link->query( $sql );
                break;
            case DBManager::MODE_SQLI :
                $rs = $this->_link->query( $sql );
                break;
            case DBManager::MODE_SQL :
                $rs = mysql_query( $sql, $this->_link );
                break;
            default :
                $rs = false;
                break;
        }
        $rtn = new ResultSet( $rs, $sql, $cache );
        return $rtn;
    }

    /**
     * 获取最后一条语句执行的错误
     *
     * @return string 空字符串或者NULL代表没错误
     */
    public function error() {
        switch (DBManager::workingMode()) {
            case DBManager::MODE_PDO :
                $errorInfo = $this->_link->errorInfo();
                return $errorInfo [2];
                break;
            case DBManager::MODE_SQLI :
                return $this->_link->error;
                break;
            case DBManager::MODE_SQL :
                return mysql_error( $this->_link );
            default :
                return '';
        }
    }

    /**
     * 获取insertID
     *
     * @return int
     */
    public function insertID() {
        switch (DBManager::workingMode()) {
            case DBManager::MODE_PDO :
                return $this->_link->lastInsertId();
            case DBManager::MODE_SQLI :
                return $this->_link->insert_id;
            case DBManager::MODE_SQL :
                return mysql_insert_id( $this->_link );
            default :
                return 0;
        }
    }
}