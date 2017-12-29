<?php
/**
 * 个人用户表
 * @author weihan
 * @datetime 2016年11月15日上午11:32:06
 */
namespace app\Models;

use Server\Components\CatCache\TimerCallBack;
use Server\Components\CatCache\CatCacheRpcProxy;
class CatCacheModel extends BaseModel
{
    public function timerCall($value, $token)
    {
        unset(CatCacheRpcProxy::getRpc()[$value]);
        TimerCallBack::ack($token);
    }
}