<?php
/**
 * Redis缓存类
 */
class Cache_Driver_Redis extends Cache_Cache
{
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            throwException('不支持'.':redis');
        }
        $options = array_merge(
            array (
                'host'          => getConfig('redis', 'host') ? getConfig('redis', 'host') : '127.0.0.1',
                'port'          => getConfig('redis', 'port') ? getConfig('redis', 'port') : 6379,
                'timeout'       => getConfig('redis', 'timeout') ? getConfig('redis','timeout') : false,
                'persistent'    => false,
            ),
            $options
        );
        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   getConfig('cache', 'expire');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   getConfig('cache', 'prefix');
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;        
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new Redis;
        $options['timeout'] === false ?
        $this->handler->$func($options['host'], $options['port']) :
        $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * 读取缓存 string类型
     * @access public
     * @param string $name 缓存变量名
     * @param int $flag 是否返回object值，1是0否
     * @return mixed
     */
    public function get($name,$flag=1) {
        $value = $this->handler->get($this->options['prefix'] . $name);
        $jsonData  = ($flag==1)?json_decode( $value):$value;
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value, $expire = null) {
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        $result = $this->handler->set($name, $value);
        if ($expire) {
            $this->expire($name, $expire);
        }
        return $result;
    }


    /**
     * 读取缓存 hash类型
     * @access public
     * @param string $tablename 缓存变量名
     * @param string $field 存储数据
     * @return mixed
     */
    public function hget($tablename, $field) {
        $value = $this->handler->hGet($this->options['prefix'] . $tablename, $field);
        $jsonData  = json_decode( $value);
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 读取缓存 hash类型
     * @access public
     * @param string $tablename 缓存变量名
     * @return mixed
     */
    public function hgetall($tablename) {
        $value = $this->handler->hGetAll($this->options['prefix'] . $tablename);
        return $value;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $tablename 缓存变量名
     * @param array $field  存储数据 key value
     * @param mixed $expire  过期时间
     * @return bool
     */
    public function hmset($tablename, $field, $expire = null) {
        foreach ($field as $k => &$v) {
            $v = (is_object($v) || is_array($v)) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
        }
        $result = $this->handler->hMset($tablename, $field);
        if ($expire != null)
            $this->expire($tablename, $expire);
        return $result;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $tablename 缓存变量名
     * @param mixed $field  存储数据
     * @param mixed $value  存储数据值
     * @param mixed $expire  过期时间
     * @return bool
     */
    public function hset($tablename, $field, $value, $expire = null) {
        $name   = $this->options['prefix'] . $tablename;
        $value  = (is_object($value) || is_array($value)) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        $result = $this->handler->hSet($name, $field, $value);
        if ($expire != null)
            $this->expire($tablename, $expire);
        return $result;
    }

    /**
     * 设置过期时间
     * @access public
     * @param  string $key
     * @param  int $expire
     * @return bool
     */
    public function expire($key, $expire) {
        return $this->handler->expire($key, $expire);
    }

    /**
     * 查看过期时间
     * @access public
     * @param  string $key
     * @return bool
     */
    public function ttl($key) {
        return $this->handler->ttl($key);
    }

    /**
     * 查看键是否存在
     * @access public
     * @param  int $key
     * @return bool
     */
    public function exists($key) {
        return $this->handler->exists($key);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function delete($name) {
        return $this->handler->delete($this->options['prefix'] . $name);
    }

    /**
     * 获取redis_sentinel信息
     * @access public
     * @return mixed
     */
    public function info() {
        return $this->handler->info();
    }

    /**
     * 插入队列
     * @access public
     * @param string $queue 队列名
     * @param mixed $field  存储数据
     * @return mixed
     */
    public function lpush($queue, $field) {
        return $this->handler->lpush($queue, $field);
    }

    /**
     * 移除并返回列表 key 的头元素
     * @access public
     * @param string $queue 队列名
     * @return mixed
     */
    public function lpop($queue) {
        return $this->handler->lpop($queue);
    }

    /**
     * 移除并返回列表 key 的尾元素
     * @access public
     * @param string $queue 队列名
     * @return mixed
     */
    public function rpop($queue) {
        return $this->handler->rpop($queue);
    }

    /**
     * 返回列表 key 中指定区间内的元素，区间以偏移量 start 和 stop 指定
     * @access public
     * @param string $queue 队列名
     * @param string $start 开始
     * @param string $stop 结束
     * @return mixed
     */
    public function lrange($queue, $start, $stop) {
        return $this->handler->lrange($queue, $start, $stop);
    }

    /**
     * 关闭redis连接
     * @access public
     * @return boolen
     */
    public function close() {
        return $this->handler->close();
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->flushDB();
    }

}
