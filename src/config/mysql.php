<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午4:49
 */
$config['mysql']['enable'] = true;
$config['mysql']['active'] = 'test';
$config['mysql']['test']['host'] = '10.19.1.131';
$config['mysql']['test']['port'] = '3306';
$config['mysql']['test']['user'] = 'weihan';
$config['mysql']['test']['password'] = 'jiang';
$config['mysql']['test']['database'] = 'swooledistributedweb';
$config['mysql']['test']['charset'] = 'utf8';
$config['mysql']['asyn_max_count'] = 10;
return $config;