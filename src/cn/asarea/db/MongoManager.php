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
use MongoDB\Client;
use MongoDB\Database;

/**
 * mongo 数据库
 *
 * @author Ather.Shu Nov 25, 2014 3:05:49 PM
 */
class MongoManager {

    /**
     * 公用的采用app系统配置的mongo数据库
     *
     * @var MongoDB\Database
     */
    private static $_db;

    /**
     * 建立mongo数据库连接
     *
     * @param array $config array (
     *        'host' => '127.0.0.1',
     *        'port' => '27017',
     *        'db' => 'laoxinwen'
     *        )
     * @return MongoDB\Database
     */
    public static function open($config = NULL) {
        $appMongoConfig = Application::getInstance()->getConfig( 'mongo' );
        if( empty( $config ) ) {
            $config = $appMongoConfig;
        }
        // 只缓存全体app公用的config
        if( $config == $appMongoConfig && isset( MongoManager::$_db ) ) {
            $db = MongoManager::$_db;
        }
        else {
            $mongo = new Client( "mongodb://{$config['host']}:{$config['port']}" );
//             $mongo = new \Mongo( "mongodb://{$config['host']}:{$config['port']}" );
            $db = $config['db'];
            $db = $mongo->$db;
            if( $config == $appMongoConfig ) {
                MongoManager::$_db = $db;
            }
        }
        return $db;
    }
}