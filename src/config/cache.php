<?php

/**
 * 缓存
 * 
 * 配置格式与session一样
 */
//catCache仿Redis可落地高速缓存，配置文件是catCache.php
$config['cache']['handler'] = 'catCache';

//task，SD服务reload或重启失效
// $config['cache']['handler'] = 'task';

//redis
// $config['cache']['handler'] = 'redis';

/* 
//文件缓存
$config['cache']['handler'] = 'file';
$config['cache']['cache_path'] = 'caches/files';  //src目录里面
$config['cache']['suf'] = '.cache';     //文件后缀，不要以php结尾，否则inotify会执行
$config['cache']['cleaner_tick'] = 500*1000;    //清理器定时器，单位毫秒
 */
/* 
//mysql
$config['cache']['handler'] = 'mysql';
$config['cache']['tbl_name'] = 'caches';  //表名字
$config['cache']['cleaner_tick'] = 500*1000;    //清理器定时器，单位毫秒
 */
$config['cache']['default_ttl'] = 3600;    //默认过期时间，单位秒
return $config;