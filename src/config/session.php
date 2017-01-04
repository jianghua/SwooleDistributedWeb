<?php
/**
 * session保存
 * 
 * 配置格式与cache一样
 */

//redis
$config['session']['handler'] = 'redis';

/* 
//文件缓存
$config['session']['handler'] = 'file';
$config['session']['cache_path'] = 'caches/session';  //src目录里面
$config['session']['suf'] = '.sess';     //文件后缀，不要以php结尾，否则inotify会执行
$config['session']['default_ttl'] = 3600;    //默认过期时间，单位秒
$config['session']['cleaner_tick'] = 300*1000;    //清理器定时器，单位毫秒 
*/
/* 
//mysql
$config['session']['handler'] = 'mysql';
$config['session']['tbl_name'] = 'caches_sess';  //表名字
$config['session']['default_ttl'] = 3600;    //默认过期时间，单位秒
$config['session']['cleaner_tick'] = 300*1000;    //清理器定时器，单位毫秒
 */

return $config;