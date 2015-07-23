<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\db;

use cn\asarea\core\Application;

/**
 * 数据库管理类
 *
 * @author Ather.Shu Nov 9, 2014 7:35:59 PM
 */
class DBManager {

    /**
     * 在一个函数中可能嵌套调用其他函数，可能会导致调用好几次openlink<br>
     * 如果同时new多个link，直接返回老的link即可知道最外层函数返回关闭时，再统一关闭
     *
     * @var DataBase （app配置）数据库当前链接实例
     */
    private static $_db;

    /**
     * mysqli扩展模式
     *
     * @var int
     */
    const MODE_SQLI = 0;

    /**
     * mysql扩展模式
     *
     * @var int
     */
    const MODE_SQL = 1;

    /**
     * pdo扩张模式
     *
     * @var int
     */
    const MODE_PDO = 2;

    /**
     * 当前工作模式：sqli或者sql
     *
     * @var int
     */
    private static $_mode;

    /**
     * 获取当前工作模式：sqli或者sql
     *
     * @return int
     */
    public static function workingMode() {
        return DBManager::$_mode;
    }

    /**
     * 建立数据库链接
     *
     * @param $config array null代表用app config<br>
     *        array('host' => '127.0.0.1',
     *        'port' => '3306',
     *        'db' => 'test',
     *        'user' => 'root',
     *        'password' => '')
     * @return DataBase 数据库实例
     */
    public static function open($config = NULL) {
        $appDbConfig = Application::getInstance()->getConfig( 'db' );
        if( empty( $config ) ) {
            $config = $appDbConfig;
        }
        // 只缓存全体app公用的config
        if( $config == $appDbConfig && isset( DBManager::$_db ) ) {
            $db = DBManager::$_db;
        }
        else {
            if( class_exists( 'PDO' ) ) {
                $link = new \PDO( 'mysql:host=' . $config ['host'] . ';port=' . $config ['port'] . ';dbname=' . $config ['db'], $config ['user'], 
                        $config ['password'] );
                if( $link->errorCode() ) {
                    $errorInfo = $link->errorInfo();
                    exit( "无法连接数据库：" . $errorInfo [2] );
                }
                
                DBManager::$_mode = DBManager::MODE_PDO;
                $db = new DataBase( $link );
            }
            else if( class_exists( 'mysqli' ) ) {
                $link = new \mysqli( $config ['host'], $config ['user'], $config ['password'], $config ['db'], $config ['port'] );
                if( $link->connect_error ) {
                    exit( "无法连接数据库：" . $link->connect_error );
                }
                
                DBManager::$_mode = DBManager::MODE_SQLI;
                $db = new DataBase( $link );
            }
            else {
                $link = mysql_connect( $config ['host'] . ':' . $config ['port'], $config ['user'], $config ['password'] ) or
                         exit( "无法连接数据库：" . mysql_error() );
                mysql_select_db( DB_NAME, $link ) or exit( "不能使用" . DB_NAME . "数据库：" . mysql_error() );
                
                DBManager::$_mode = DBManager::MODE_SQL;
                $db = new DataBase( $link );
            }
            
            // utf8改为utf8mb4，以支持一些表情符
            $db->query( "set names 'utf8mb4'" );
            if( $config == $appDbConfig ) {
                DBManager::$_db = $db;
            }
        }
        return $db;
    }

    /**
     * 自动建立连接，并且执行某条sql语句，返回resultset资源
     *
     * @param string $sql sql语句
     * @param bool $cache 是否缓存
     * @return ResultSet result set资源集
     */
    public static function autoExecute($sql, $cache = false) {
        $db = DBManager::open();
        $rs = $db->query( $sql, $cache );
        return $rs;
    }

    /**
     * 对sql语句添加分页参数
     *
     * @return string 需要拼接到sql语句后的字串
     * @param $page int 从第几页开始，默认从0页开始，用于分页，负数代表拿倒数第几页
     * @param $num int 一页要显示多少行，默认显示30行，用于分页
     * @param $desc bool 是否降序显示
     */
    public static function genPageParams($page = 0, $num = 30, $desc = false) {
        $page = $page;
        $num = $num;
        
        if( $page >= 0 && $num > 0 ) {
            return ($desc ? " desc" : " ") . " limit " . ($page * $num) . ", " . $num;
        }
        else if( $page < 0 && $num > 0 ) {
            return ($desc ? " " : " desc") . " limit " . -($page + 1) * $num . ", " . $num;
        }
        else {
            return ($desc ? " desc" : " ") . " limit 0, 30";
        }
    }
}

?>