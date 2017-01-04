<?php

/**
 * 缓存
 * 
 * 配置格式与session一样
 */

//redis
$config['cache']['handler'] = 'redis';

/* 
//文件缓存
$config['cache']['handler'] = 'file';
$config['cache']['cache_path'] = 'caches/files';  //src目录里面
$config['cache']['suf'] = '.cache';     //文件后缀，不要以php结尾，否则inotify会执行
$config['cache']['default_ttl'] = 86400;    //默认过期时间，单位秒
$config['cache']['cleaner_tick'] = 500*1000;    //清理器定时器，单位毫秒
 */
/* 
//mysql
$config['cache']['handler'] = 'mysql';
$config['cache']['tbl_name'] = 'caches';  //表名字
$config['cache']['default_ttl'] = 86400;    //默认过期时间，单位秒
$config['cache']['cleaner_tick'] = 500*1000;    //清理器定时器，单位毫秒
 */

return $config;