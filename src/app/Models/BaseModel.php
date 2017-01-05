<?php
/**
 * model基类
 * @author weihan
 * @datetime 2016年11月15日上午11:32:06
 */
namespace app\Models;

use Server\CoreBase\Model;
use Server\DataBase\Miner;

class BaseModel extends Model
{
    /**
     * 表前缀
     */
    public $tbl_prefix = 'sdw_';
    /**
     * 表名，不需要表前缀
     */
    public $tbl_name = '';
    
    /**
     * 设置Form Secret防止，非当前页面提交数据
     */
    public $_form_secret = true;
    
    /**
     * 表单
     * 
     * key=>[ //key一般是表中的字段，cascade_select除外
            'label' => '', //
            'tips' => '',
            'type' => 'input', //表单类型，详见form types
            'value' => '', //默认值
            'placeholder' => '',
            'class' => '', //表单样式
            'validates' =>[], //验证规则，详见validates
            .... //也可以定义其他，会自动加到表单中
        ]
     * 
     */
    public $_form = [];
    
    /**
     * 获取表单需要展示的输入项
     * @param string $form_name
     *
     * @author weihan
     * @datetime 2016年11月18日下午3:19:23
     */
    public function form($form_name){
        $form = array();
        if (method_exists($this, $form_name)){
            $fields = $this->$form_name();
            foreach ($fields as $k=>$v){
                $field = $v;
                $_label = '';
                if (is_string($k)){
                    $field = $k;
                    $_label = $v;
                }
                if (isset($this->_form[$field])){
                    $form[$field] = $this->_form[$field];
                    if ($_label){
                        if (is_array($_label)){
                            $form[$field] = array_merge($form[$field],$_label);
                        }else
                            $form[$field]['label'] = $_label;
                    }
                }
            }
        }
        return $form;
    }
    
    /**
     * 返回完整表名
     * @return string
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:54:21
     */
    public function table(){
        return $this->tbl_prefix. $this->tbl_name;
    }
    
    /**
     * 获取某一列的值，
     * 需要加yield
     * @param array $contidions_arr 查询条件
     * @param string $fields    sql列名
     * @param string $order_column
     * @param string $order_by
     * @param string $group
     * @return string|null
     *
     * @author weihan
     * @datetime 2016年11月15日下午3:48:33
     */
    public function getColumn($contidions_arr, $field, $order_column = '', $order_by='DESC', $group = '') {
        $return_result = true;
        $result = yield $this->getOne($contidions_arr, $field, $return_result, $order_column, $order_by, $group);
        return array_pop($result) ?? null;
    }
    
    /**
     * 查询一条结果
     * 需要加yield
     * @param array $contidions_arr 查询条件
     * @param string $fields    sql列名
     * @param string $return_result true:返回查询结果；false:返回coroutine，需要额外获取结果
     * @param string $order_column
     * @param string $order_by
     * @param string $group
     * @return array|\Generator
     *
     * @author weihan
     * @datetime 2016年11月15日下午2:35:56
     */
    public function getOne($contidions_arr, $fields='*', $return_result=true, $order_column = '', $order_by='DESC', $group = '') {
        $this->mysql_pool->dbQueryBuilder->select($fields)->from($this->table());
        $this->_setConditions($contidions_arr);
        $this->mysql_pool->dbQueryBuilder->limit(1);
        if ($order_column) {
            $this->mysql_pool->dbQueryBuilder->orderBy($order_column, $order_by);
        }
        if ($group) {
            $this->mysql_pool->dbQueryBuilder->groupBy($group, null);
        }
        
        //使用协程，发送sql
        $mySqlCoroutine = $this->mysql_pool->dbQueryBuilder->coroutineSend();
        if ($return_result){
            //等待查询结果，返回结果
            $result = yield $mySqlCoroutine;
            return isset($result['result'][0]) ? $result['result'][0] : [];
        }
        //不等待查询结果，直接返回，通过yield获取结果
        return $mySqlCoroutine;
    }
    
    /**
     * 查询
     * 返回多条结果
     * 需要加yield
     * @param array $contidions_arr     查询条件
     * @param string $fields   sql列名
     * @param bool $return_result   true:返回查询结果；false:返回coroutine，需要额外获取结果
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $group
     * @param int $bind_id
     * @return array|\Generator
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:52:39
     */
    public function select($contidions_arr, $fields='*', $return_result=true, $page=1, $pagesize=0, $order = '', $group = '', $bind_id=null) {
        $this->mysql_pool->dbQueryBuilder->select($fields)->from($this->table());
        $this->_setConditions($contidions_arr);
        
        if ($order) {
            $_arr = array_filter(explode(',', $order));
            foreach ($_arr as $v){
                $_arr2 = array_filter(explode(' ', $v, 2));
                $_column = trim($_arr2[0]);
                $_order_by = isset($_arr2[1]) ? trim($_arr2[1]) : Miner::ORDER_BY_ASC;
                $this->mysql_pool->dbQueryBuilder->orderBy($_column, $_order_by);
            }
        }
        if ($group) {
            $this->mysql_pool->dbQueryBuilder->groupBy($group, null);
        }
        
        $sql = $this->mysql_pool->dbQueryBuilder->getStatement(false);
        //必须有clear，否则sql会紊乱
        $this->mysql_pool->dbQueryBuilder->clear();
        return yield $this->query($sql, $return_result, $page, $pagesize, $bind_id);
    }
    
    /**
     * 执行sql
     * 需要yield
     * @param string $sql
     * @param string $return_result
     * @param number $page
     * @param number $pagesize
     * @param int $bind_id
     *
     * @author weihan
     * @datetime 2016年12月13日下午2:13:20
     */
    public function query($sql, $return_result=true, $page=1, $pagesize=0, $bind_id=null) {
        if ($pagesize){
            $page = max(1, $page);
            $offset = ($page-1)* $pagesize;
            $sql .= " limit {$offset},{$pagesize}";
        }
        //使用协程，发送sql
        $mySqlCoroutine = $this->mysql_pool->dbQueryBuilder->coroutineSend($bind_id, $sql);
        if ($return_result){
            //等待查询结果，返回结果
            $result = yield $mySqlCoroutine;
            return $result['result'];
        }
        //不等待查询结果，直接返回，通过yield获取结果
        return $mySqlCoroutine;
    }
    
    /**
     * 设置查询条件
     * @param array $contidions_arr
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:52:39
     */
    private function _setConditions($contidions_arr){
        if ($contidions_arr){
            foreach ($contidions_arr as $_column=>$_value){
                if (is_array($_value)) {
                    $this->mysql_pool->dbQueryBuilder->where($_column, $_value[1], $_value[0], $_value[2]??Miner::LOGICAL_AND);
                }else {
                    $this->mysql_pool->dbQueryBuilder->where($_column, $_value);
                }
                
            }
        }
    }
    
    /**
     * 插入
     * 需要yield
     * @param array $data_arr   要插入的数据
     * @return insert_id
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:52:39
     */
    public function insert($data_arr) {
        //构造sql
        $this->mysql_pool->dbQueryBuilder
            ->insertInto($this->table())
            ->intoColumns(array_keys($data_arr))
            ->intoValues(array_values($data_arr));
        
        $result = yield $this->mysql_pool->dbQueryBuilder->coroutineSend();
        return $result['insert_id'];
    }
    
    /**
     * 更新
     * 需要yield
     * @param array $data_arr
     * @param array $contidions_arr
     * @return bool 
     *
     * @author weihan
     * @datetime 2016年11月15日下午2:27:26
     */
    public function update($data_arr, $contidions_arr) {
        $this->mysql_pool->dbQueryBuilder->update($this->table());
        foreach ($data_arr as $_column=>$_value){
            $this->mysql_pool->dbQueryBuilder->set($_column, $_value);
        }
        $this->_setConditions($contidions_arr);
        $result = yield $this->mysql_pool->dbQueryBuilder->coroutineSend();
        return $result['affected_rows'] ? true : false;
    }
    
    /**
     * 删除
     * 需要yield
     * @param array $contidions_arr
     * @return bool 
     *
     * @author weihan
     * @datetime 2016年11月15日下午2:28:49
     */
    public function delete($contidions_arr) {
        $this->mysql_pool->dbQueryBuilder->delete()->from($this->table());
        $this->_setConditions($contidions_arr);
        $result = yield $this->mysql_pool->dbQueryBuilder->coroutineSend();
        return $result['affected_rows'] ? true : false;
    }
    
    /**
     * 统计数量
     * 需要加yield
     * @param array $contidions_arr 查询条件
     * @param string $return_result
     * @return int|Generator
     *
     * @author weihan
     * @datetime 2016年11月21日下午5:27:13
     */
    public function count($contidions_arr, $return_result=true) {
        $this->mysql_pool->dbQueryBuilder->select('count(*) as nums')->from($this->table());
        $this->_setConditions($contidions_arr);
        $this->mysql_pool->dbQueryBuilder->limit(1);
    
        //使用协程，发送sql
        $mySqlCoroutine = $this->mysql_pool->dbQueryBuilder->coroutineSend();
        if ($return_result){
            //等待查询结果，返回结果
            $result = yield $mySqlCoroutine;
            return $result['result'][0]['nums'];
        }
        //不等待查询结果，直接返回，通过yield获取结果
        return $mySqlCoroutine;
    }
}