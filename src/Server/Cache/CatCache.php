<?php
/**
 * redis缓存，支持分布式
 * @author weihan
 * @datetime 2016年11月23日上午10:32:16
 */
namespace SwooleDistributedWeb\Server\Cache;

use Server\Components\CatCache\CatCacheRpcProxy;
use Server\Components\CatCache\TimerCallBack;
use app\Models\CatCacheModel;
class CatCache implements ICache{
    
    /*缓存默认配置*/
    protected $setting = array(
        'default_ttl' => 3600,  //默认有效期，单位秒
    );
    
    public function __construct($setting) {
        $this->setting = array_merge($this->setting, $setting);
    }
    
    /**
     * 设置key、value
     * @param string $key
     * @param mixed $value
     * @param number $ttl   过期时间，单位秒
     *
     * @author weihan
     * @datetime 2017年12月29日下午4:34:18
     */
    function set($key, $value, $ttl=0){
        CatCacheRpcProxy::getRpc()[$key] = $value;
        if ($ttl == 0) {
            $ttl = $this->setting['default_ttl'];
        }
        //定时清除
        if ($ttl) {
            TimerCallBack::addTimer($ttl, CatCacheModel::class,'timerCall',[$key]);
        }
    }

    /**
     * redis 获取key值
     * @param string $key
     * @return mixed
     *
     * @author weihan
     * @datetime 2017年12月29日下午4:34:18
     */
    function get($key){
        $result = CatCacheRpcProxy::getRpc()->offsetGet($key);
        return $result;
    }
    
    /**
     * redis  删除key
     * @param string $key
     *
     * @author weihan
     * @datetime 2017年12月29日下午4:34:18
     */
    function delete($key){
        unset(CatCacheRpcProxy::getRpc()[$key]);
    }
}