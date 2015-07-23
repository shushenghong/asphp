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
 * 数据库结果集类
 *
 * @author Ather.Shu Nov 9, 2014 7:42:58 PM
 */
class ResultSet {

    const RS_ASSOC = 1;

    const RS_NUM = 2;

    const RS_BOTH = 3;

    const RS_OBJECT = 4;

    /**
     * 原始rs，可以是mysqli_result或者mysql_query的resource或者从缓存反序列化过来的数据array
     *
     * @var mysqli_result | result set | array | bool
     */
    private $_rs;

    /**
     * 结果集column的keynames
     *
     * @var array
     */
    private $_keys;

    /**
     * 当前数据指针行index
     *
     * @var int
     */
    private $_seekIndex;

    /**
     * 数据行数
     *
     * @var int
     */
    private $_num;

    /**
     * 构造函数
     *
     * @param mysqli_result | result set | bool $rs
     * @param string $sql 对应的sql语句
     * @param bool $cache 是否缓存
     */
    public function __construct($rs, $sql, $cache) {
        $this->_rs = $rs;
        $this->_seekIndex = 0;
        // 缓存
        if( $cache && !is_bool( $rs ) ) {
            $cache = new Cache();
            $cache->set( $sql, serialize( $this ) );
        }
    }

    /**
     * 序列化（主要用于存储到cache）
     */
    public function __sleep() {
        // 转化为array
        $this->fetchAll( ResultSet::RS_NUM );
        return array (
                '_rs',
                '_keys' 
        );
    }

    /**
     * 反序列化（用于当从cache中取出来）
     */
    public function __wakeup() {
        $this->seek( 0 );
    }

    /**
     * 数据集行数
     *
     * @return number
     */
    public function num_rows() {
        if( !isset( $this->_num ) ) {
            // 数据数量
            if( is_bool( $this->_rs ) ) {
                $this->_num = 0;
            }
            else if( is_array( $this->_rs ) ) {
                $this->_num = count( $this->_rs );
            }
            else {
                switch (DBManager::workingMode()) {
                    case DBManager::MODE_PDO :
                        $this->_num = $this->_rs->rowCount();
                        break;
                    case DBManager::MODE_SQLI :
                        $this->_num = $this->_rs->num_rows;
                        break;
                    case DBManager::MODE_SQL :
                        $this->_num = mysql_num_rows( $this->_rs );
                        break;
                    default :
                        $this->_num = 0;
                        break;
                }
            }
        }
        
        return $this->_num;
    }

    /**
     * 设置当前数据指针行index
     *
     * @param int $index
     * @return boolean 是否设置成功
     */
    public function seek($index) {
        if( $index < 0 || $index >= $this->num_rows() ) {
            $rtn = false;
        }
        else if( is_bool( $this->_rs ) ) {
            $rtn = false;
        }
        // 缓存
        else if( is_array( $this->_rs ) ) {
            $rtn = true;
        }
        else {
            switch (DBManager::workingMode()) {
                case DBManager::MODE_PDO :
                    // mysql don't support abs cursor, and seek
                    trigger_error( "Sorry, PDO doesn't support mysql seek." );
                    $rtn = false;
                    break;
                case DBManager::MODE_SQLI :
                    $rtn = $this->_rs->data_seek( $index );
                    break;
                case DBManager::MODE_SQL :
                    $rtn = mysql_data_seek( $this->_rs, $index );
                    break;
                default :
                    $rtn = false;
                    break;
            }
        }
        
        if( $rtn ) {
            $this->_seekIndex = $index;
        }
        return $rtn;
    }

    /**
     * 获取field names
     *
     * @return array string组成的数组
     */
    private function getKeys() {
        if( isset( $this->_keys ) ) {
            return $this->_keys;
        }
        else {
            $_keys = array ();
            switch (DBManager::workingMode()) {
                case DBManager::MODE_PDO :
                    $columnCount = $this->_rs->columnCount();
                    for($i = 0; $i < $columnCount; $i++) {
                        $columnMeta = $this->_rs->getColumnMeta( $i );
                        array_push( $_keys, $columnMeta ['name'] );
                    }
                    break;
                case DBManager::MODE_SQLI :
                    $finfos = $this->_rs->fetch_fields();
                    foreach ( $finfos as $finfo ) {
                        array_push( $_keys, $finfo->name );
                    }
                    break;
                case DBManager::MODE_SQL :
                    $columnCount = mysql_num_fields( $this->_rs );
                    for($i = 0; $i < $columnCount; $i++) {
                        array_push( $_keys, mysql_field_name( $this->_rs, $i ) );
                    }
                    break;
                default :
                    break;
            }
            $this->_keys = $_keys;
            return $_keys;
        }
    }

    /**
     * 根据当前驱动转换到具体数据获取类型
     *
     * @param [string] $type
     * @return [string]
     */
    private function getFetchType($type) {
        // object 类型单独处理
        if( $type == ResultSet::RS_OBJECT ) {
            return $type;
        }
        switch (DBManager::workingMode()) {
            case DBManager::MODE_PDO :
                $type = $type == ResultSet::RS_BOTH ? \PDO::FETCH_BOTH : ($type == ResultSet::RS_ASSOC ? \PDO::FETCH_ASSOC : \PDO::FETCH_NUM);
                break;
            default :
                break;
        }
        return $type;
    }

    /**
     * 从key既含column index又含column name的row array转化为需要的数据，主要用于缓存后的获取
     *
     * @param array $row 原始的仅含index为key的行数据
     * @param string $type 需要获取的数据类型
     * @param array $ctorArgs 如果是获取RS_OBJECT，传递给构造函数的参数数组
     */
    private function boxRow2Type($row, $type, $className = null, $ctorArgs = null) {
        if( !is_array( $row ) ) {
            return $row;
        }
        
        switch ($type) {
            case ResultSet::RS_ASSOC :
                $assoc = array_combine( $this->getKeys(), $row );
                $rtn = $assoc;
                break;
            case ResultSet::RS_BOTH :
                $assoc = array_combine( $this->getKeys(), $row );
                $rtn = array_merge( $assoc, $row );
                break;
            case ResultSet::RS_OBJECT :
                $assoc = array_combine( $this->getKeys(), $row );
                // 不提供className，则转为标准内置类
                if( empty( $className ) ) {
                    $className = 'stdClass';
                }
                if( $className === 'stdClass' ) {
                    // 自动强制类型转换
                    $rtn = ( object ) $assoc;
                }
                else {
                    $ref = new \ReflectionClass( $className );
                    $rtn = $ref->newInstanceArgs( $ctorArgs ? $ctorArgs : array () );
                    foreach ( $assoc as $key => $value ) {
                        $rtn->$key = $value;
                    }
                }
                break;
            default :
                $rtn = $row;
                break;
        }
        return $rtn;
    }

    /**
     * 获取一行数据并转化为某class实例
     *
     * @return bool object
     */
    public function fetchObject($className = null, $ctorArgs = null) {
        return $this->fetchArray( ResultSet::RS_OBJECT, $className, $ctorArgs );
    }

    /**
     * 获取一行数据assoc，仅有字段名做key
     *
     * @return bool array
     */
    public function fetchAssoc() {
        return $this->fetchArray( ResultSet::RS_ASSOC );
    }

    /**
     * 获取一行数据array
     *
     * @param int $type 需要的结果类型（index还是字段名做key RS_BOTH | RS_ASSOC(默认) | RS_NUM | RS_OBJECT）
     * @return boolean array
     */
    public function fetchArray($type = ResultSet::RS_ASSOC, $className = null, $ctorArgs = null) {
        if( is_bool( $this->_rs ) ) {
            $rtn = false;
        }
        else if( is_array( $this->_rs ) ) {
            if( $this->_seekIndex < $this->num_rows() ) {
                $rtn = $this->boxRow2Type( $this->_rs [$this->_seekIndex], $type, $className, $ctorArgs );
            }
            else {
                $rtn = false;
            }
        }
        else {
            $newType = $this->getFetchType( $type );
            switch (DBManager::workingMode()) {
                case DBManager::MODE_PDO :
                    $rtn = ($type == ResultSet::RS_OBJECT) ? $this->_rs->fetchObject( $className ? $className : 'stdClass', 
                            $ctorArgs ? $ctorArgs : array () ) : $this->_rs->fetch( $newType );
                    break;
                case DBManager::MODE_SQLI :
                    $rtn = ($type == ResultSet::RS_OBJECT) ? $this->_rs->fetch_object( $className ? $className : 'stdClass', 
                            $ctorArgs ? $ctorArgs : array () ) : $this->_rs->fetch_array( $newType );
                    break;
                case DBManager::MODE_SQL :
                    $rtn = ($type == ResultSet::RS_OBJECT) ? $this->boxRow2Type( mysql_fetch_array( $this->_rs, MYSQL_NUM ), ResultSet::RS_OBJECT, 
                            $className, $ctorArgs ) : mysql_fetch_array( $this->_rs, $newType );
                    break;
                default :
                    $rtn = false;
                    break;
            }
        }
        
        if( $rtn ) {
            $this->_seekIndex++;
        }
        
        return $rtn;
    }
    
    /**
     * 获取某列所有值
     * @param string $column
     * @param bool $unique 是否去重
     * @return [] 
     */
    public function fetchSingleColumnAll($column, $unique=false) {
        if($unique) {
            return array_keys( $this->fetchAll(ResultSet::RS_ASSOC, $column) );
        }
        $all = $this->fetchAll();
        $rtn = [];
        foreach ($all as $tmp) {
            $rtn[] = $tmp[$column];
        }
        return $rtn;
    }

    /**
     * 获取所有数据
     *
     * @param int $type 需要的结果类型（index还是字段名做key RS_BOTH | RS_ASSOC(默认) | RS_NUM | RS_OBJECT）
     * @param string $keyColumn 以某个column的值如ID（该列值必须唯一）作为返回数组的key name（不返回默认的rowindex作为key的数组）
     * @return array 没有数据返回空数组
     */
    public function fetchAll($type = ResultSet::RS_ASSOC, $keyColumn = null, $className = null, $ctorArgs = null) {
        if( is_bool( $this->_rs ) ) {
            $datas = array ();
        }
        // 已经生成缓存数据
        else if( is_array( $this->_rs ) ) {
            $datas = $this->_rs;
        }
        else {
            switch (DBManager::workingMode()) {
                // PDO mysql不支持seek
                case DBManager::MODE_PDO :
                    if( $this->_seekIndex > 0 ) {
                        trigger_error( "PDO doesn't support mysql seek, the already fetched datas will not contain in the fetchAll command." );
                    }
                    $datas = $this->_rs->fetchAll( \PDO::FETCH_NUM );
                    break;
                case DBManager::MODE_SQLI :
                    $this->seek( 0 );
                    $datas = $this->_rs->fetch_all( MYSQLI_NUM );
                    break;
                case DBManager::MODE_SQL :
                    $this->seek( 0 );
                    $datas = array ();
                    while ( $row = mysql_fetch_array( $this->_rs, MYSQL_NUM ) ) {
                        array_push( $datas, $row );
                    }
                    break;
                default :
                    $datas = array ();
                    break;
            }
            // 获取完成，清空原始结果集，将rs用数组方式存储
            $this->getKeys();
            $this->free();
            $this->_rs = $datas;
            $this->seek( 0 );
        }
        // 封装成需要的数据格式
        $rtn = array ();
        $customKey = !empty( $keyColumn );
        foreach ( $datas as $row ) {
            $data = $this->boxRow2Type( $row, $type, $className, $ctorArgs );
            if( !$customKey ) {
                array_push( $rtn, $data );
            }
            else {
                $rtn [($type == ResultSet::RS_OBJECT ? $data->$keyColumn : $data [$keyColumn])] = $data;
            }
        }
        return $rtn;
    }

    /**
     * 清空结果集内存
     *
     * @return boolean 是否清空成功
     */
    public function free() {
        if( is_bool( $this->_rs ) ) {
            $rtn = true;
        }
        else if( is_array( $this->_rs ) ) {
            $rtn = true;
        }
        else {
            switch (DBManager::workingMode()) {
                case DBManager::MODE_PDO :
                    $rtn = $this->_rs->closeCursor();
                    break;
                case DBManager::MODE_SQLI :
                    $rtn = $this->_rs->free();
                    break;
                case DBManager::MODE_SQL :
                    $rtn = mysql_free_result( $this->_rs );
                    break;
                default :
                    $rtn = true;
                    break;
            }
        }
        unset( $this->_rs );
        return $rtn;
    }
}