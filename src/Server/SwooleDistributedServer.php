<?php

namespace SwooleDistributedWeb\Server;

use Server\SwooleDistributedServer as _SwooleDistributedServer;
use SwooleDistributedWeb\Server\Cache\CacheFactory;
use Server\Start;
use Server\CoreBase\ControllerFactory;

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
    const version = "2.5.5";
    
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
        parent::onSwooleWorkerStart($serv, $workerId);
        if (!$serv->taskworker) {
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
                            $cache_path = SRC_DIR. $this->config[$_type]['cache_path'];
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
    }
    
    /**---------------SwooleHttpServer.php--------------------->*/
    /**
     * 设置模板引擎
     * {@inheritDoc}
     * @see \Server\SwooleHttpServer::setTemplateEngine()
     *
     * @author weihan
     * @datetime 2017年9月22日下午12:53:30
     */
    public function setTemplateEngine(){
        parent::setTemplateEngine();
        $this->templateEngine->registerFunction('get_www', 'get_www');
        $this->templateEngine->registerFunction('url', 'url');
    }
    
    /**
     * 重写
     * http服务器发来消息
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     *
     * @author weihan
     * @datetime 2017年9月22日下午12:53:54
     */
    public function onSwooleRequest($request, $response){
        //设置响应头中的server
        $response->header('Server', get_instance()->config->get('server.set.server_name'));
        if (Start::$testUnity) {
            $server_port = $request->server_port;
        } else {
            $fdinfo = $this->server->connection_info($request->fd);
            $server_port = $fdinfo['server_port'];
        }
        $route = $this->portManager->getRoute($server_port);
        $error_404 = false;
        $controller_instance = null;
        $route->handleClientRequest($request);
        list($host) = explode(':', $request->header['host']??'');
        $path = $route->getPath();
        if($path=='/404'){
            $response->header('HTTP/1.1', '404 Not Found');
            if (!isset($this->cache404)) {//内存缓存404页面
                $template = $this->loader->view('server::error_404');
                $this->cache404 = $template->render();
            }
            $response->end($this->cache404);
            return;
        }
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        /* 主页如果是静态页面，把注释去掉
         if ($path=="/") {//寻找主页
         $www_path = $this->getHostRoot($host) . $this->getHostIndex($host);
         $result = httpEndFile($www_path, $request, $response);
         if (!$result) {
         $error_404 = true;
         } else {
         return;
         }
         }else */
        if(!empty($extension)){//有后缀
            $www_path = $this->getHostRoot($host) . $path;
            $result = httpEndFile($www_path, $request, $response);
            if (!$result) {
                $error_404 = true;
            }
        }
        else {
            $controller_name = $route->getControllerName();
            $controller_instance = ControllerFactory::getInstance()->getController($controller_name);
            if ($controller_instance != null) {
                if ($route->getMethodName() == '_consul_health') {//健康检查
                    $response->end('ok');
                    $controller_instance->destroy();
                    return;
                }
                $method_name = $this->http_method_prefix . $route->getMethodName();
                //非public方法，不调用
                if (!method_exists($controller_instance, $method_name) || !is_callable([$controller_instance, $method_name])) {
                    $method_name = get_instance()->config->get('http.default_method');
                }
                //debug模式，把信息直接打印到浏览器
                if ($this->config->get('server.debug')){
                    ob_start();
                }
                $controller_instance->setRequestResponse($request, $response, $controller_name, $method_name, $route->getParams());
                return;
            } else {
                $error_404 = true;
            }
        }
        if ($error_404) {
            if ($controller_instance != null) {
                $controller_instance->destroy();
            }
            //重定向到404
            $response->status(302);
            $location = $this->config->get('http.domain')."/".'404';
            $response->header('Location',$location);
            $response->end('');
        }
    }
    /**<-------------------------------------------------------*/
    
    /**---------------SwooleServer.php--------------------->*/
    /**
     * 错误处理函数
     * @param $msg
     * @param $log
     */
    public function onErrorHandel($msg, $log)
    {
        if ($this->config->get('server.debug')){
            print_r($msg . "\n");
            print_r($log . "\n");
        }
    }
    /**<-------------------------------------------------------*/
}