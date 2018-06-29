<?php
/**
 * model基类
 * @author weihan
 * @datetime 2016年11月15日上午11:32:06
 */
namespace Web;

use Server\CoreBase\Model;
use Server\Asyn\Mysql\Miner;
use Server\Asyn\Mysql\MySqlCoroutine;
use Web\Helpers\Libs\Arr;

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
     * 此表含有的字段
     * 如果非空，则只使用配置的字段
     * @var []
     *
     * @author weihan
     * @datetime 2017年7月18日上午10:17:22
     */
    public $fields = [];
    
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
                            $form[$field] = Arr::arrayMergeDimensional($form[$field],$_label);
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
     * @param array $contidions_arr 查询条件
     * @param string $fields    sql列名
     * @param string $order_column
     * @param string $order_by
     * @param string $group
     * @return []
     *
     * @author weihan
     * @datetime 2016年11月15日下午3:48:33
     */
    public function getColumn($contidions_arr, $field, $order_column = '', $order_by='DESC', $group = '') {
        $return_result = true;
        $result = $this->getOne($contidions_arr, $field, $return_result, $order_column, $order_by, $group);
        return array_pop($result) ?? null;
    }
    
    /**
     * 查询一条结果
     * @param array $contidions_arr 查询条件
     * @param string $fields    sql列名
     * @param string $return_result 此参数已无效
     * @param string $order_column
     * @param string $order_by
     * @param string $group
     * @param bool $add_for_udpate  是否在sql末尾加上FOR UPDATE
     * @return [] | MySqlCoroutine
     *
     * @author weihan
     * @datetime 2016年11月15日下午2:35:56
     */
    public function getOne($contidions_arr, $fields='*', $return_result=true, $order_column = '', $order_by='DESC', $group = '', $add_for_udpate=false) {
        $this->db->select($fields)->from($this->table());
        $this->_setConditions($contidions_arr);
        $this->db->limit(1);
        if ($order_column) {
            $this->db->orderBy($order_column, $order_by);
        }
        if ($group) {
            $this->db->groupBy($group, null);
        }
        $sql = null;
        if ($add_for_udpate) {
            $sql = $this->db->getStatement(false);
            $sql .= ' FOR UPDATE';
        }
        return $this->db->query($sql)->row();
    }
    
    /**
     * 查询
     * 返回多条结果
     * @param array $contidions_arr     查询条件
     * @param string $fields   sql列名
     * @param bool $return_result 此参数已无效
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $group
     * @return [] | MySqlCoroutine
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:52:39
     */
    public function select($contidions_arr, $fields='*', $return_result=true, $page=1, $pagesize=0, $order = '', $group = '') {
        $this->db->select($fields)->from($this->table());
        $this->_setConditions($contidions_arr);
        
        if ($order) {
            $_arr = array_filter(explode(',', $order));
            foreach ($_arr as $v){
                $_arr2 = array_filter(explode(' ', trim($v), 2));
                $_column = trim($_arr2[0]);
                $_order_by = isset($_arr2[1]) ? trim($_arr2[1]) : Miner::ORDER_BY_ASC;
                $this->db->orderBy($_column, $_order_by);
            }
        }
        if ($group) {
            $this->db->groupBy($group, null);
        }
        
        $sql = null;
        return $this->query($sql, $return_result, $page, $pagesize);
    }
    
    /**
     * 执行sql
     * @param string $sql
     * @param string $return_result 此参数已无效
     * @param number $page
     * @param number $pagesize
     * @return [] | MySqlCoroutine
     *
     * @author weihan
     * @datetime 2016年12月13日下午2:13:20
     */
    public function query($sql, $return_result=true, $page=1, $pagesize=0) {
        if ($pagesize){
            $page = max(1, $page);
            $offset = ($page-1)* $pagesize;
            $this->db->limit($pagesize, $offset);
        }
        
        return $this->db->query($sql)->result_array();
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
                    $this->db->where($_column, $_value[1], $_value[0], $_value[2]??Miner::LOGICAL_AND);
                }else {
                    $this->db->where($_column, $_value);
                }
                
            }
        }
    }
    
    /**
     * 插入
     * @param array $data_arr   要插入的数据
     * @param bool $return_result 此参数已无效
     * @return insert_id
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:52:39
     */
    public function insert($data_arr, $return_result=true) {
        $data_arr = $this->filterFields($data_arr);
        //构造sql
        $this->db
            ->insertInto($this->table())
            ->intoColumns(array_keys($data_arr))
            ->intoValues(array_values($data_arr));
        
        return $this->db->query()->insert_id();
    }
    
    /**
     * 更新
     * @param array $data_arr
     * @param array $contidions_arr
     * @return bool 
     *
     * @author weihan
     * @datetime 2016年11月15日下午2:27:26
     */
    public function update($data_arr, $contidions_arr) {
        $data_arr = $this->filterFields($data_arr);
        if (empty($data_arr)){
            return true;
        }
        $this->db->update($this->table());
        foreach ($data_arr as $_column=>$_value){
            $this->db->set($_column, $_value);
        }
        $this->_setConditions($contidions_arr);
        
        return $this->db->query()->affected_rows() ? true : false;
    }
    
    /**
     * 删除
     * @param array $contidions_arr
     * @return bool 
     *
     * @author weihan
     * @datetime 2016年11月15日下午2:28:49
     */
    public function delete($contidions_arr) {
        $this->db->delete()->from($this->table());
        $this->_setConditions($contidions_arr);
        
        return $this->db->query()->affected_rows() ? true : false;
    }
    
    /**
     * 统计数量
     * @param array $contidions_arr 查询条件
     * @return int
     *
     * @author weihan
     * @datetime 2016年11月21日下午5:27:13
     */
    public function count($contidions_arr) {
        $this->db->select('count(*) as nums')->from($this->table());
        $this->_setConditions($contidions_arr);
        $this->db->limit(1);
    
        return $this->db->query()->row()['nums'] ?? 0;
    }
    
    /**
     * 过滤无用的字段
     * @param [] $data_arr
     *
     * @author weihan
     * @datetime 2017年7月18日上午10:25:38
     */
    public function filterFields($data_arr) {
        if ($this->fields){
            foreach ($data_arr as $k=>$v){
                if (!in_array($k, $this->fields)){
                    unset($data_arr[$k]);
                }
            }
        }
        return $data_arr;
    }
    
    /**
     * 获取一个model
     * @param string $model_name
     * @return BaseModel
     *
     * @author weihan
     * @datetime 2018年3月8日下午2:32:21
     */
    public function model($model_name) {
        if (strpos($model_name, '/') === false &&
            strpos($model_name, '\\') === false) {
                $model_name = ucfirst($model_name);
            }
            return $this->loader->model($model_name, $this);
    }
}