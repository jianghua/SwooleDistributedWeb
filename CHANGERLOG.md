# CHANGELOG
## 2.7.5.2
控制器$this->getForm前需加yield
## 2.7.6
BaseModel封装的增删改查优化，$return_result=false时，
只需要再加个yield就能获取结果
## 2.7.6.1
BaseModel支持事务，写法和SD一样，只是把增删改查换了下，也可以用SD原生的。
$transaction_id = $this->mysql_pool->coroutineBegin($this);
$user = $this->model->getOne(['username'=>'weihan'], $fields='*', $return_result=true, $order_column = '', $order_by='DESC', $group = '', $transaction_id, true);
$is_succ = false;
if ($user) {
    $is_succ = $this->model->update(['realname'=>'new name'], ['username'=>'weihan'], true, $transaction_id);
}
if ($is_succ) {
    $this->mysql_pool->coroutineCommit($transaction_id);
}else{
    $this->mysql_pool->coroutineRollback($transaction_id);
}

websocket例子，浏览器地址http://yourhost/im
# 3.0-beta

SD3.0版本beta版本，支持swoole2.0协程，需要安装swoole2.0扩展，编译命令如下：
```
./configure --enable-async-redis  --enable-openssl --enable-coroutine
```
2.0版本迁移3.0需要做的修改，非常简单

1.去除业务代码中所有的yield字样

2.如果使用了协程超时，需要修改为这样，通过set回调函数设置协程的参数
```php
$data = EventDispatcher::getInstance()->addOnceCoroutine('unlock', function (EventCoroutine $e) {
            $e->setTimeout(10000);
        });
```
请注意这是一个测试版本，并没达到线上运行水平，已知框架问题和swoole2.0扩展问题还在积极修复。
# 3.0.2
model中如果$return_result=false，需要通过调用recv()方法获取结果
# 3.1.2
##模板引擎
默认模板依旧是plates引擎，如果使用blade注释AppServer.php中的setTemplateEngine方法
##mysql
coroutineSend已经被弃用，更换为query。query的第一个参数为sql，第二个参数为超时时间（单位ms）
事务已改版
# 3.1.12
SD中TimerCallBack::addTimer过期时间有bug，需手动调整CatCacheRpcProxy.php 38行$time = time() * 1000;改为$time = time();
model中目前不支持recv()