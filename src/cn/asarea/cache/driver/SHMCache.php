<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\cache\driver;

/**
 * 共享内存缓存类
 *
 * @author Ather.Shu Nov 9, 2014 7:16:39 PM
 */
class SHMCache {

    private static $SHM_META_KEY = 1;

    /**
     * key到内存块id以及过期时间映射表 key=>[shmKey=>, expires=>]
     *
     * @var array
     */
    private $_keyDict;

    /**
     * 当前最后使用的一个shm key，用于递增key
     *
     * @var int
     */
    private $_lastSHMKey;

    public function __construct() {
        if( !function_exists( 'shmop_open' ) ) {
            trigger_error( 'The extension shmop is not active.' );
            return;
        }
        $this->_keyDict = array ();
        $this->_lastSHMKey = SHMCache::$SHM_META_KEY;
        // meta信息内存块
        $metaSHMId = $this->checkSHMExist( SHMCache::$SHM_META_KEY );
        if( $metaSHMId ) {
            $metaSize = shmop_size( $metaSHMId );
            $meta = unserialize( shmop_read( $metaSHMId, 0, $metaSize ) );
            if( isset( $meta ['lastSHMKey'] ) ) {
                $this->_lastSHMKey = $meta ['lastSHMKey'];
            }
            if( isset( $meta ['keyDict'] ) ) {
                $this->_keyDict = $meta ['keyDict'];
            }
            shmop_close( $metaSHMId );
        }
    }

    /**
     * 检查某个key对应的sharememory块是否已经创建
     *
     * @param int $key 内存块标示key
     * @return boolean | int 如果存在，返回对应共享内存块id，否则返回false
     */
    private function checkSHMExist($shmKey) {
        $shmId = @shmop_open( $shmKey, 'a', 0, 0 );
        return $shmId;
    }

    /**
     * 写入一个值到某shmkey对应的内存块
     *
     * @param int $shmKey 内存块标示key
     * @param string $value 值（必须是字符串，在外部序列化或者encode）
     */
    private function writeValueToSHM($shmKey, $value) {
        $data = $value;
        $size = mb_strlen( $data, 'UTF-8' );
        $shmId = shmop_open( $shmKey, 'c', 0644, $size );
        shmop_write( $shmId, $data, 0 );
        shmop_close( $shmId );
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
        
        if( array_key_exists( $key, $this->_keyDict ) ) {
            $shmKey = $this->_keyDict [$key] ['shmKey'];
            $shmId = $this->checkSHMExist( $shmKey );
            if( $shmId ) {
                // If you have created the block and need to delete it
                // you must call shmop_delete **BEFORE** calling shmop_close (for reasons outlined in shmop_delete help page notes).
                // 删除并关闭老的
                shmop_delete( $shmId );
                shmop_close( $shmId );
            }
        }
        
        $this->_lastSHMKey++;
        $this->writeValueToSHM( $this->_lastSHMKey, $value );
        // 设置过期以及shmid等meta信息
        $ctime = time();
        if( $expires > 0 ) {
            $expires = $ctime + $expires;
        }
        $this->_keyDict [$key] = array (
                'shmKey' => $this->_lastSHMKey,
                'expires' => $expires 
        );
        $this->resetMetaSHM();
    }

    /**
     * 获取缓存值
     *
     * @param string $key
     * @return boolean string
     */
    public function get($key) {
        if( array_key_exists( $key, $this->_keyDict ) ) {
            // 检查是否过期
            $ctime = time();
            $expires = $this->_keyDict [$key] ['expires'];
            // 缓存已经过期
            if( $expires != 0 && $ctime > $expires ) {
                $this->delete( $key );
                return false;
            }
            // 获取内存块key和具体id
            $shmKey = $this->_keyDict [$key] ['shmKey'];
            $shmId = $this->checkSHMExist( $shmKey );
            if( $shmId ) {
                $size = shmop_size( $shmId );
                $data = shmop_read( $shmId, 0, $size );
                shmop_close( $shmId );
                return $data;
            }
            // 内存块id已经不存在的话也要删除key
            else {
                $this->delete( $key );
                return false;
            }
        }
        return false;
    }

    /**
     * 删除某缓存
     *
     * @param string $key
     */
    public function delete($key) {
        if( array_key_exists( $key, $this->_keyDict ) ) {
            // 获取内存块key和具体id
            $shmKey = $this->_keyDict [$key] ['shmKey'];
            $shmId = $this->checkSHMExist( $shmKey );
            if( $shmId ) {
                // 删除缓存
                shmop_delete( $shmId );
                shmop_close( $shmId );
            }
            unset( $this->_keyDict [$key] );
            $this->resetMetaSHM();
        }
    }

    /**
     * 作废所有缓存
     */
    public function flush() {
        foreach ( $this->_keyDict as $key => $value ) {
            $this->delete( $key );
        }
    }

    /**
     * 重置刷新meta内存块内容
     */
    private function resetMetaSHM() {
        // meta信息内存块
        $metaSHMId = $this->checkSHMExist( SHMCache::$SHM_META_KEY );
        if( $metaSHMId ) {
            shmop_delete( $metaSHMId );
            shmop_close( $metaSHMId );
        }
        $meta = array (
                'lastSHMKey' => $this->_lastSHMKey,
                'keyDict' => $this->_keyDict 
        );
        $this->writeValueToSHM( SHMCache::$SHM_META_KEY, serialize( $meta ) );
    }
}