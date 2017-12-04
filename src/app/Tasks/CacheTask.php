<?php

namespace app\Tasks;

use Server\CoreBase\Task;

/**
 * 跨进程的高速内存缓存
 * 
 * @author weihan
 * @datetime 2017年11月22日下午4:00:46
 *
 */
class CacheTask extends Task
{
    private $map = [];
    private $expires = [];
    private $pieces = 3600;
    private $piece_idx = 0;
    /**
     * 设置
     * @param string $key
     * @param mixed $value
     * @param number $ttl
     * @return boolean
     *
     * @author weihan
     * @datetime 2017年11月22日下午4:43:38
     */
    function set($key, $value, $ttl=0){
        $expire_time = time() + $ttl;
        $this->map[$key] = [$value, $expire_time];
        
        $idx = $expire_time % $this->pieces;
        $this->expires[$idx][$expire_time] = $key;
        
        $this->delete_expires();
        return true;
    }

    /**
     * 获取key值
     * 
     * @author weihan
     * @datetime 2017年11月22日下午4:08:48
     */
    function get($key){
        $result = $this->map[$key] ?? false;
        if ($result){
            //未过期
            if ($result[1] > time()){
                $result = $result[0];
            //已过期
            }else{
                unset($this->map[$key]);
                $result = false;
            }
        }
        
        $this->delete_expires();
        return $result;
    }
    
    /**
     * 删除key
     * 
     * @author weihan
     * @datetime 2017年11月22日下午4:08:36
     */
    function delete($key){
        unset($this->map[$key]);
        
        $this->delete_expires();
        return true;
    }
    
    /**
     * 删除过期的
     *
     * @author weihan
     * @datetime 2017年11月22日下午5:05:55
     */
    function delete_expires() {
        if (isset($this->expires[$this->piece_idx]) && $arr = $this->expires[$this->piece_idx]){
            $time = time();
            foreach($arr as $expire_time=>$key){
                if ($time > $expire_time) {
                    unset($this->expires[$this->piece_idx][$expire_time]);
                    unset($this->map[$key]);
                }
            }
        }
        $this->piece_idx ++;
        if ($this->piece_idx >= $this->pieces-1){
            $this->piece_idx = 0;
        }
    }
}