<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-6-17
 * Time: 下午1:56
 */

require_once 'define.php';
$worker = new \SwooleDistributedWeb\app\AppServer();
Server\Start::run();