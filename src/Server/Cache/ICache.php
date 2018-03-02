<?php
/**
 * 缓存接口
 * 
 * get是协程，需要加yield，其他都不是协程，没有返回值
 * 
 * @author weihan
 * @datetime 2016年11月23日上午10:32:16
 */
namespace SwooleDistributedWeb\Server\Cache;


interface ICache{
    function set($key, $value, $ttl=0);
    
    function get($key);
    
    function delete($key);
}