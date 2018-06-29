<?php
/**
 * 联动菜单
 * 
 * @author weihan
 * @datetime 2016年12月2日下午5:11:07
 */
namespace app\Models;

use Server\Asyn\Mysql\Miner;
class LinkageModel extends BaseModel
{
    public $tbl_name = 'linkage';
    
    /**
     * 获取子菜单
     * @param int $parent_id
     *
     * @author weihan
     * @datetime 2016年12月2日下午5:14:41
     */
    public function getSubs($parent_id, $is_key_val=false) {
        empty($parent_id) && $parent_id = 1;
        
        //读取当前linkage信息
        $child = $this->getColumn(['linkageid'=>$parent_id], 'child');
        if ($child){
            $contidions_arr = [
                'parentid' => $parent_id,
            ];
        }else {
            $contidions_arr = [
                'parentid' => 0,
                'keyid' => $parent_id,
            ];
        }
        $list = $this->select($contidions_arr, 'name, linkageid as val');
        if ($is_key_val) {
            $_list = [];
            foreach ($list as $v){
                $_list[$v['val']] = $v['name'];
            }
            return $_list;
        }
        return $list;
    }
    
    /**
     * 获取名字
     * @param array $ids_arr
     * @return array $data  
     *
     * @author weihan
     * @datetime 2016年12月23日下午2:00:14
     */
    public function getNames($ids_arr) {
        $contidions_arr = [
            'linkageid' => [Miner::IN, $ids_arr]
        ];
        $data = [];
        $list = $this->select($contidions_arr, 'linkageid, name');
        if ($list){
            foreach ($list as $v){
                $data[$v['linkageid']] = $v['name'];
            }
        }
        return $data;
    }
}