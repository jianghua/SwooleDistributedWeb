<?php
/**
 * mysql缓存，支持分布式
 * @author weihan
 * @datetime 2016年11月24日上午11:12:07
 */
namespace SwooleDistributedWeb\Server\Cache;

use Server\Asyn\Mysql\MysqlAsynPool;
use Server\Asyn\Mysql\Miner;
class MysqlCache implements ICache{
    /*缓存默认配置*/
    protected $setting = array(
        'tbl_name' => 'caches',	//表
        'default_ttl' => 3600,  //默认有效期，单位秒
    );
    /**
     * @var MysqlAsynPool
     */
    private $mysql_pool;
    
    public function __construct($setting, $mysql_pool) {
        $this->setting = array_merge($this->setting, $setting);
        $this->mysql_pool = $mysql_pool;
        
        //创建表
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->setting['tbl_name']}` (
                  `name` VARCHAR(100) NOT NULL,
                  `val` TEXT,
                  `expire` INT(11) DEFAULT NULL,
                  PRIMARY KEY (`name`),
                  KEY `expire` (`expire`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;";
        $this->mysql_pool->query(function (){}, null, $sql);
    }
    
    /**
     * mysql 设置key、value
     * @param string $key
     * @param mixed $value
     * @param number $ttl   过期时间，单位秒
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:34:18
     */
    function set($key, $value, $ttl=0){
        $value = json_encode($value);
        //设置过期时间
        $ttl == 0 && $ttl = $this->setting['default_ttl'];
        $expire = time()+ $ttl;
        $this->mysql_pool->dbQueryBuilder
            ->replace($this->setting['tbl_name'])
            ->set('name', $key)
            ->set('val', $value)
            ->set('expire', $expire);
        $this->mysql_pool->query(function (){});
    }

    /**
     * mysql 获取key值
     * @param string $key
     * @return mixed
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:25:10
     */
    function get($key){
        $result = $this->mysql_pool->dbQueryBuilder
            ->select('val')->from($this->setting['tbl_name'])
            ->where('name', $key)->where('expire', time(), Miner::GREATER_THAN_OR_EQUAL)
            ->coroutineSend();
        if ($result && isset($result['result'][0])){
            return json_decode($result['result'][0]['val']);
        }
        return false;
    }
    
    /**
     * mysql  删除key
     * @param string $key
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:39:14
     */
    function delete($key){
        $this->mysql_pool->dbQueryBuilder->delete()->from($this->setting['tbl_name'])->where('name', $key);
        $this->mysql_pool->query(function (){});
    }
}