<?php
/**
 * redis缓存，支持分布式
 * @author weihan
 * @datetime 2016年11月23日上午10:32:16
 */
namespace SwooleDistributedWeb\Server\Cache;

use Server\Asyn\Redis\RedisAsynPool;
class RedisCache implements ICache{
    /*缓存默认配置*/
    protected $setting = array(
        'default_ttl' => 3600,  //默认有效期，单位秒
    );
    /**
     * @var RedisAsynPool
     */
    private $redis_pool;
    
    public function __construct($setting, $redis_pool) {
        $this->setting = array_merge($this->setting, $setting);
        $this->redis_pool = $redis_pool;
    }
    
    /**
     * redis 设置key、value
     * @param string $key
     * @param mixed $value
     * @param number $ttl   过期时间，单位秒
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:34:18
     */
    function set($key, $value, $ttl=0){
        $value = serialize($value);
        
        if ($ttl == 0) {
            $ttl = $this->setting['default_ttl'];
        }
        $this->redis_pool->setex($key, $ttl, $value, function ($result){});
    }

    /**
     * redis 获取key值
     * @param string $key
     * @return mixed
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:25:10
     */
    function get($key){
        $result = $this->redis_pool->coroutineSend('get', $key);
        if ($result){
            return unserialize($result);
        }
        return false;
    }
    
    /**
     * redis  删除key
     * @param string $key
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:39:14
     */
    function delete($key){
        $this->redis_pool->delete($key, function ($result){});
    }
}