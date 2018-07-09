<?php
/**
 * swooleDistributedWeb 专用配置
 */

$config['server']['debug'] = 1;
/**
 * 服务器设置
 */
$config['server']['set'] = [
    'server_name' => "sdw",
];

//http服务器绑定的真实的域名或者ip:port，一定要填对,否则获取不到文件的绝对路径
$config['http']['domain'] = 'http://10.19.1.131/sdw';
$config['http']['default_controller'] = 'User';  //默认控制器
$config['http']['default_method'] = 'index';          //默认方法
$config['http']['index_isfile'] = '0';         //首页是否使用静态页面，1：静态页面，0：非静态页面

//编码
$config['charset'] = 'utf-8';
//上传目录，位于www目录下
$config['upload_dir'] = 'upload';
//上传文件大小
$config['upload_maxsize'] = 5 * 1024 * 1024;
//auth加解密的key
$config['auth_key'] = '1234567890';

return $config;