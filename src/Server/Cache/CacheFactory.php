<?php
/**
 * 缓存工厂
 * @author weihan
 * @datetime 2016年11月23日上午10:39:16
 */
namespace SwooleDistributedWeb\Server\Cache;

use Server\Memory\Cache;
class CacheFactory{
    
    /**
     * 获取缓存类
     * @param string $type
     *
     * @author weihan
     * @datetime 2016年11月23日下午3:20:12
     */
    public static function getCache($setting=null): ICache{
        
        $setting = $setting ?? get_instance()->config['cache'];
        $handler = $setting['handler'];
        switch($handler){
            case 'catCache': return new CatCache($setting);
            case 'task': return new TaskCache($setting);
            case 'redis': return new RedisCache($setting, get_instance()->redis_pool);
            case 'file': return new FileCache($setting);
            case 'mysql': return new MysqlCache($setting, get_instance()->mysql_pool);
        }
    }
}