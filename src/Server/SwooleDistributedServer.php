<?php

namespace SwooleDistributedWeb\Server;

use Server\SwooleDistributedServer as _SwooleDistributedServer;
use SwooleDistributedWeb\Server\Cache\CacheFactory;

/**
 * 
 * @author weihan
 * @datetime 2017年9月22日下午12:36:03
 *
 */
abstract class SwooleDistributedServer extends _SwooleDistributedServer
{
    /**
     * 版本
     */
    const version = "3.6.2";
    
    /**
     * 缓存
     * @var ICache
     */
    public $cache;
    /**
     * session handler
     * @var ICache
     */
    public $session_handler;
    
    /**
     * 重写onSwooleWorkerStart方法，
     * {@inheritDoc}
     * @see \Server\SwooleDistributedServer::onSwooleWorkerStart()
     * 
     * @author weihan
     * @datetime 2017年9月22日下午12:43:21
     */
    public function onSwooleWorkerStart($serv, $workerId){
        if (!$this->isTaskWorker()) {
            //初始化缓存
            $this->cache = CacheFactory::getCache();
            //初始化缓存
            $this->session_handler = CacheFactory::getCache($this->config['session']);
        
            //第一个worker中启动单一定时清理器
            if ($workerId == 0){
                //缓存，启动定时清理器
                foreach (['cache', 'session'] as $_type){
                    if (isset($this->config[$_type]['cleaner_tick']) && $this->config[$_type]['cleaner_tick']){
                        //文件缓存，启动定时清理器
                        if ($this->config[$_type]['handler'] == 'file' && isset($this->config[$_type]['cache_path'])){
                            $cache_path = APP_DIR. DIRECTORY_SEPARATOR. $this->config[$_type]['cache_path'];
                            swoole_timer_tick($this->config[$_type]['cleaner_tick'], function($timmerID) use ($cache_path){
                                fileUnlink($cache_path);
                            });
                                //mysql数据库缓存，启动定时清理器
                        }elseif ($this->config[$_type]['handler'] == 'mysql' && isset($this->config[$_type]['tbl_name'])){
                            $tbl_name = $this->config[$_type]['tbl_name'];
                            swoole_timer_tick($this->config[$_type]['cleaner_tick'], function($timmerID) use ($tbl_name){
                                tableCleaner($tbl_name);
                            });
                        }
                    }
                }
            }
        }
        parent::onSwooleWorkerStart($serv, $workerId);
    }
}