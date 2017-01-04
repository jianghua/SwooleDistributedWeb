<?php
/**
 * Created by PhpStorm.
 * User: tmtbe
 * Date: 16-7-14
 * Time: 下午1:58
 */
/**
 * tcp访问时方法的前缀
 */
$config['tcp']['method_prefix'] = 'tcp_';
/**
 * http访问时方法的前缀
 */
$config['http']['method_prefix'] = '';
/**
 * websocket访问时方法的前缀
 */
$config['websocket']['method_prefix'] = 'websocket_';

//http服务器绑定的真实的域名或者ip:port，一定要填对,否则获取不到文件的绝对路径
$config['http']['domain'] = 'http://10.16.198.131/sdw';
$config['http']['default_controller'] = 'User';  //默认控制器
$config['http']['default_method'] = 'index';          //默认方法

//是否服务器启动时自动清除群组信息
$config['autoClearGroup'] = true;
//编码
$config['charset'] = 'utf-8';
//上传目录，位于www目录下
$config['upload_dir'] = 'upload';
//上传文件大小
$config['upload_maxsize'] = 5 * 1024 * 1024;
//auth加解密的key
$config['auth_key'] = '1234567890';

return $config;