<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */
/**
 * 是否启用redis，如果redis宕机，可临时关闭
 * 简单的key=>value缓存，建议使用cache，扩展性更好些
 */
$config['redis']['enable'] = true;
/**
 * 选择数据库环境
 */
$config['redis']['active'] = 'local';

/**
 * 本地环境
 */
$config['redis']['local']['ip'] = '127.0.0.1';
$config['redis']['local']['port'] = 6379;
$config['redis']['local']['select'] = 1;
$config['redis']['local']['password'] = 'weihan';

/**
 * 这个不要删除，dispatch使用的redis环境
 * dispatch使用的环境
 */
$config['redis']['dispatch']['ip'] = 'unix:/var/run/redis/redis.sock';
$config['redis']['dispatch']['port'] = 0;
$config['redis']['dispatch']['select'] = 1;
$config['redis']['dispatch']['password'] = '123456';

$config['redis']['asyn_max_count'] = 10;

/**
 * 最终的返回，固定写这里
 */
return $config;
