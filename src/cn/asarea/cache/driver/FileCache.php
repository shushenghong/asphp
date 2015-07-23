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
 * 文件缓存类
 * 
 * @author Ather.Shu Nov 9, 2014 7:17:36 PM
 */
class FileCache {

    private $_cacheDir;

    /**
     * 过期时间映射表
     * 
     * @var array
     */
    private $_expiresDict;

    public function __construct($cacheDir) {
        $this->_cacheDir = $cacheDir;
        $this->_expiresDict = array ();
        // check meta file
        $metaFilename = $this->metaFilename();
        if( file_exists( $metaFilename ) ) {
            $this->_expiresDict = unserialize( file_get_contents( $metaFilename ) );
        }
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
        file_put_contents( $this->filename( $key ), $value );
        $this->_expiresDict [$key] = $expires;
        $this->resetMetaFile();
    }

    /**
     * 获取缓存值
     * 
     * @param string $key
     * @return boolean string
     */
    public function get($key) {
        if( array_key_exists( $key, $this->_expiresDict ) ) {
            // 检查是否过期
            $ctime = time();
            $expires = $this->_expiresDict [$key];
            $filename = $this->filename( $key );
            // 文件不存在，或者已经过期
            if( !file_exists( $filename ) || ($expires != 0 && $ctime > $expires + filemtime( $filename )) ) {
                $this->delete( $key );
                return false;
            }
            return file_get_contents( $filename );
        }
        return false;
    }

    /**
     * 删除某缓存
     * 
     * @param string $key
     */
    public function delete($key) {
        $filename = $this->filename( $key );
        // 删除缓存
        unset( $this->_expiresDict [$key] );
        if( file_exists( $filename ) ) {
            unlink( $filename );
        }
        $this->resetMetaFile();
    }

    /**
     * 作废所有缓存
     */
    public function flush() {
        foreach ( $this->_expiresDict as $key => $value ) {
            $this->delete( $key );
        }
    }

    /**
     * 根据key获取缓存文件名
     * 
     * @param string $key
     * @return string
     */
    private function filename($key) {
        $filename = md5( $key );
        return $this->_cacheDir . '/' . $filename;
    }

    /**
     * 过期时间存储文件名
     * 
     * @return string
     */
    private function metaFilename() {
        return $this->_cacheDir . '/___internal___cache___meta';
    }

    /**
     * 重置刷新metafile内容
     */
    private function resetMetaFile() {
        $metaFilename = $this->metaFilename();
        if( count( $this->_expiresDict ) == 0 ) {
            if( file_exists( $metaFilename ) ) {
                unlink( $metaFilename );
            }
        }
        else {
            file_put_contents( $metaFilename, serialize( $this->_expiresDict ) );
        }
    }
}