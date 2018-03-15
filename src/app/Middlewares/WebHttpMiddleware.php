<?php
/**
 * 
 * @author weihan
 * @datetime 2017年11月2日下午3:16:28
 */
namespace app\Middlewares;

use Server\Components\Middleware\HttpMiddleware;

class WebHttpMiddleware extends HttpMiddleware
{
    protected static $cache404;
    //首页是否使用静态页面
    protected $index_isfile;

    public function __construct()
    {
        parent::__construct();
        if (WebHttpMiddleware::$cache404 == null) {
            $template = get_instance()->loader->view('app::error_404');
            WebHttpMiddleware::$cache404 = $template;
        }
        $this->index_isfile = boolval(get_instance()->config->get('http.index_isfile'));
    }

    public function before_handle()
    {
        //设置响应头中的server
        $this->response->header('Server', get_instance()->config->get('server.set.server_name'));
        list($host) = explode(':', $this->request->header['host'] ?? '');
        $path = $this->request->server['path_info'];
        if ($path == '/404') {
            $this->response->status(400);
            $this->response->header('HTTP/1.1', '404 Not Found');
            $this->response->end(WebHttpMiddleware::$cache404);
            $this->interrupt();
        }
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($path == "/" && $this->index_isfile) {//寻找主页
            $www_path = $this->getHostRoot($host) . $this->getHostIndex($host);
            $result = httpEndFile($www_path, $this->request, $this->response);
            if (!$result) {
                $this->redirect404();
            } else {
                $this->interrupt();
            }
        } else if (!empty($extension)) {//有后缀
            $www_path = $this->getHostRoot($host) . $path;
            $result = httpEndFile($www_path, $this->request, $this->response);
            if (!$result) {
                $this->redirect404();
            } else {
                $this->interrupt();
            }
        }
    }

    public function after_handle($path)
    {
        // TODO: Implement after_handle() method.
    }
}