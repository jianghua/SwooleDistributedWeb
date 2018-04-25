<?php
/**
 * 
 * @author weihan
 * @datetime 2016年11月16日上午10:32:41
 */
namespace app\Route;


use Server\CoreBase\SwooleException;
use Server\Route\IRoute;
class NormalRoute implements IRoute
{
    private $client_data;

    public function __construct()
    {
        $this->client_data = new \stdClass();
    }

    /**
     * 设置反序列化后的数据 Object
     * @param $data
     * @return \stdClass
     * @throws SwooleException
     */
    public function handleClientData($data)
    {
        $this->client_data = $data;
        if (isset($this->client_data->controller_name) && isset($this->client_data->method_name)) {
            return $this->client_data;
        } else {
            throw new SwooleException('route 数据缺少必要字段');
        }

    }

    /**
     * 处理http request
     * @param $request
     */
    public function handleClientRequest($request)
    {
        $this->client_data->controller_name = null;
        $this->client_data->method_name = null;
        
        $this->client_data->path = $request->server['path_info'];
        //去掉多余的/
        $this->client_data->path = trim($this->client_data->path, '/');
        $this->client_data->path = '/'. $this->client_data->path;
        
        //www目录下的首页入口
        if ($this->client_data->path == '/index.html') {
            $this->client_data->path = '/';
        }
        
        $route = explode('/', $this->client_data->path, 3);
        if (count($route) == 2) {
            //根目录
            if (empty($route[0]) && empty($route[1])){
                $this->client_data->controller_name = get_instance()->config->get('http.default_controller');
                $this->client_data->method_name = get_instance()->config->get('http.default_method');
             //php的url不允许出现点.
            }else if(strpos($route[1], '.') === false){
                $this->client_data->controller_name = ucfirst($route[1]);
                $this->client_data->method_name = get_instance()->config->get('http.default_method');
            }
        } elseif (count($route) == 3) {
            //php的url不允许出现点.
            if (strpos($route[2], '.') === false){
                $this->client_data->controller_name = ucfirst($route[1]);
                $this->client_data->method_name = $route[2];
            }
        }
    }

    /**
     * 获取控制器名称
     * @return string
     */
    public function getControllerName()
    {
        return $this->client_data->controller_name;
    }

    /**
     * 获取方法名称
     * @return string
     */
    public function getMethodName()
    {
        return $this->client_data->method_name;
    }

    public function getPath()
    {
        return $this->client_data->path ?? "";
    }

    public function getParams()
    {
        return $this->client_data->params??null;
    }

    public function errorHandle(\Throwable $e, $fd)
    {
        get_instance()->send($fd, "Error:" . $e->getMessage(), true);
        get_instance()->close($fd);
    }

    public function errorHttpHandle(\Throwable $e, $request, $response)
    {
        //重定向到404
        $response->status(302);
        $location = 'http://' . $request->header['host'] . "/" . '404';
        $response->header('Location', $location);
        $response->end('');
    }

}