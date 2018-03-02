<?php

namespace SwooleDistributedWeb\Server\Cache;

use SwooleDistributedWeb\Server\Cache\ICache;
use Server\Memory\Cache;

/**
 * 跨进程的高速内存缓存
 * 
 * @author weihan
 * @datetime 2017年11月22日下午4:00:46
 *
 */
class TaskCache implements ICache
{
    /*缓存默认配置*/
    protected $setting = array(
        'default_ttl' => 3600,  //默认有效期，单位秒
    );
    /**
     * @var Cache
     */
    private $cache;
    
    public function __construct($setting) {
        $this->setting = array_merge($this->setting, $setting);
        $this->cache = Cache::getCache('CacheTask');;
    }

    /**
     * 设置key、value
     * 
     * @author weihan
     * @datetime 2017年11月22日下午4:20:39
     */
    public function set($key, $value, $ttl=0){
        if ($ttl == 0) {
            $ttl = $this->setting['default_ttl'];
        }
        return $this->cache->set($key, $value, $ttl);
    }

    /**
     * 获取key值
     * 
     * @author weihan
     * @datetime 2017年11月22日下午4:08:48
     */
    public function get($key){
        return $this->cache->get($key);
    }
    
    /**
     * 删除key
     * 
     * @author weihan
     * @datetime 2017年11月22日下午4:08:36
     */
    public function delete($key){
        return $this->cache->del($key);
    }
}