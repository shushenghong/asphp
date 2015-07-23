<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\cache;

use cn\asarea\cache\driver\FileCache;
use cn\asarea\core\Application;

/**
 * 缓存类
 *
 * @author Ather.Shu Nov 9, 2014 7:15:21 PM
 */
class Cache {

    /**
     * 用memcached扩展
     *
     * @var int
     */
    const MODE_MEMCACHED = 0;

    /**
     * 用memcache扩展
     *
     * @var int
     */
    const MODE_MEMCACHE = 1;

    /**
     * 用文件缓存
     *
     * @var int
     */
    const MODE_FILE = 2;

    /**
     * APC缓存
     *
     * @var int
     */
    const MODE_APC = 3;

    /**
     * YAC缓存
     *
     * @var int
     */
    const MODE_YAC = 4;

    /**
     * 当前模式
     *
     * @var int
     */
    private $_mode;

    /**
     * 内部缓存器实例
     *
     * @var Memcached FileCache
     */
    private $_cache;

    /**
     * 构造函数
     */
    public function __construct($mode = NULL) {
        // 获取使用模式
        if( is_null( $mode ) ) {
            if( extension_loaded( 'yac' ) ) {
                $mode = Cache::MODE_YAC;
            }
            else if( extension_loaded( 'apc' ) ) {
                $mode = Cache::MODE_APC;
            }
            else if( extension_loaded( 'memcached' ) ) {
                $mode = Cache::MODE_MEMCACHED;
            }
            else if( extension_loaded( 'memcache' ) ) {
                $mode = Cache::MODE_MEMCACHE;
            }
            else {
                $mode = Cache::MODE_FILE;
            }
        }
        $this->_mode = $mode;
        
        $appConfig = Application::getInstance()->getConfig();
        switch ($mode) {
            case Cache::MODE_YAC :
                $this->_cache = new \Yac( $appConfig ['yac'] ['prefix'] );
                break;
            case Cache::MODE_APC :
                break;
            case Cache::MODE_MEMCACHED :
                $this->_cache = new \Memcached();
                $this->_cache->addServer( $appConfig ['memcache'] ['host'], $appConfig ['memcache'] ['port'] );
                break;
            case Cache::MODE_MEMCACHE :
                $this->_cache = new \Memcache();
                $this->_cache->addServer( $appConfig ['memcache'] ['host'], $appConfig ['memcache'] ['port'] );
                break;
            case Cache::MODE_FILE :
                $this->_cache = new FileCache( $appConfig ['file_cache'] ['dir'] );
                break;
        }
    }

    /**
     * 获取当前缓存工作模式
     *
     * @return number
     */
    public function workingMode() {
        return $this->_mode;
    }

    /**
     * 获取缓存值
     *
     * @param string $key
     * @return boolean string
     */
    public function get($key) {
        switch ($this->workingMode()) {
            case Cache::MODE_APC :
                $value = apc_fetch( $key );
                break;
            default :
                $value = $this->_cache->get( $key );
                break;
        }
        return $value;
    }

    /**
     * 存储
     *
     * @param string $key 键
     * @param string $value 值（必须是字符串，在外部序列化或者encode）
     * @param int $expires 多少s之内过期，0代表永不过期
     */
    public function set($key, $value, $expires = 0) {
        if( !is_string( $value ) ) {
            trigger_error( 'Please serialize(like json_encode, serialize, strval) the value before add to cache' );
            return;
        }
        switch ($this->workingMode()) {
            case Cache::MODE_APC :
                apc_store( $key, $value, $expires );
                break;
            case Cache::MODE_MEMCACHE :
                // false改为 MEMCACHE_COMPRESSED可以压缩存储数据
                $this->_cache->set( $key, $value, false, $expires );
                break;
            default :
                $this->_cache->set( $key, $value, $expires );
                break;
        }
    }

    /**
     * 删除某缓存
     *
     * @param string $key
     */
    public function delete($key) {
        switch ($this->workingMode()) {
            case Cache::MODE_APC :
                apc_delete( $key );
                break;
            default :
                $this->_cache->delete( $key );
                break;
        }
    }

    /**
     * 删除所有缓存
     */
    public function clear() {
        switch ($this->workingMode()) {
            case Cache::MODE_APC :
                apc_clear_cache( 'user' );
                break;
            default :
                $this->_cache->flush();
                break;
        }
    }
}