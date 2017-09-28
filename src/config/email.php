<?php
/**
 * 邮箱服务器设置
 * 
 * qq企业邮箱  
 * http://service.exmail.qq.com/cgi-bin/help?subtype=1&&id=28&&no=1000585
 * 发送邮件服务器：smtp.exmail.qq.com ，使用SSL，端口号465
 * STMP服务器  ssl://smtp.exmail.qq.com STMP端口 465
 */
$config['email'] = [
    'admin_email' => 'q@qq.cn',
    'maxloginfailedtimes' => '10',
    'minrefreshtime' => '5',
    'mail_type' => '1', //邮件发送模式    0：表示使用mail发送，1：表示使用邮箱服务
    'mail_server' => 'ssl://smtp.exmail.qq.com',
    'mail_port' => '465',
    'category_ajax' => '0',
    'mail_user' => '',
    'mail_auth' => '1',
    'mail_from' => '',
    'mail_password' => '',
    'errorlog_size' => '20',
];
return $config;