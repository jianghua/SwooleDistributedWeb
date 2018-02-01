# CHANGELOG
## 2.7.5.2
控制器$this->getForm前需加yield
## 2.7.6
BaseModel封装的增删改查优化，$return_result=false时，
只需要再加个yield就能获取结果
## 2.7.6.1
BaseModel支持事务，写法和SD一样，只是把增删改查换了下，也可以用SD原生的。
$transaction_id = yield $this->mysql_pool->coroutineBegin($this);
$user = yield $this->model->getOne(['username'=>'weihan'], $fields='*', $return_result=true, $order_column = '', $order_by='DESC', $group = '', $transaction_id, true);
$is_succ = false;
if ($user) {
    $is_succ = yield $this->model->update(['realname'=>'new name'], ['username'=>'weihan'], true, $transaction_id);
}
if ($is_succ) {
    yield $this->mysql_pool->coroutineCommit($transaction_id);
}else{
    yield $this->mysql_pool->coroutineRollback($transaction_id);
}