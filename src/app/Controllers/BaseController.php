<?php
namespace app\Controllers;

use Server\CoreBase\Controller as SController;
use app\Models\BaseModel;
use app\Helpers\Libs\RandomKey;
use app\Helpers\Libs\Form;
use app\Helpers\Libs\Validate;
use Server\CoreBase\XssClean;

/**
 * app中控制器基类
 * 此类中的方法，必须设为protected，防止url直接访问
 * @author weihan
 * @datetime 2016年11月15日上午11:23:06
 *
 */
class BaseController extends SController
{
    public $controller_name;
    public $method_name;
    
    protected $gzip = true;
    public $cookie_userinfo = 'userinfo';
    protected $auth_str;
    /**
     * 不能有yield
     * 初始化每次执行方法之前都会执行initialization
     */
    protected function initialization($controller_name, $method_name)
    {
        $this->controller_name = $controller_name;
        $this->method_name = $method_name;
        parent::initialization($controller_name, $method_name);
    }
    
    /**
     * 获取参数值，先get后post
     * @param string $index 变量名
     * @param mixed $default 默认值
     * @param bool $xss_clean
     * @return string/array
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:37:52
     */
    protected function request($index='', $default='', $xss_clean = true) {
        if ($index == ''){
            if ($xss_clean){
                return XssClean::getXssClean()->xss_clean($this->http_input->getAllPostGet()??'');
            }
            return $this->http_input->getAllPostGet();
        }
        $val = $this->http_input->getPost($index, $xss_clean);
        if ($val === '' && $default){
            $val = $default;
        }
        return $val;
    }
    
    /**
     * 获取post参数值
     * @param string $index 变量名
     * @param mixed $default 默认值
     * @param bool $xss_clean
     * @return string/array
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:40:31
     */
    protected function post($index='', $default='', $xss_clean = true) {
        if ($index == ''){
            if ($xss_clean){
                return XssClean::getXssClean()->xss_clean($this->http_input->request->post??'');
            }
            return $this->http_input->request->post;
        }
        $val = $this->http_input->post($index, $xss_clean);
        if ($val === '' && $default){
            $val = $default;
        }
        return $val;
    }
    
    /**
     * 获取get参数值
     * @param string $index 变量名
     * @param mixed $default 默认值
     * @param bool $xss_clean
     * @return string/array
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:40:31
     */
    protected function get($index='', $default='',$xss_clean = true) {
        if ($index == ''){
            if ($xss_clean){
                return XssClean::getXssClean()->xss_clean($this->http_input->request->get??'');
            }
            return $this->http_input->request->get;
        }
        $val = $this->http_input->get($index, $xss_clean);
        if ($val === '' && $default){
            $val = $default;
        }
        return $val;
    }
    
    /**
     * 获取一个model
     * @param string $model_name
     * @return BaseModel
     *
     * @author weihan
     * @datetime 2016年11月15日下午1:43:23
     */
    protected function model($model_name) {
        return $this->loader->model($model_name, $this);
    }
    
    /**
     * 视图
     * @param string $template
     * @param array $data
     *
     * @author weihan
     * @datetime 2016年11月15日下午4:49:51
     */
    protected function view($template, $data=[]) {
        $template = $this->loader->view('app::'. $template);
        $this->http_output->end($template->render($data));
    }
    
    /**
     * 获取cookie
     * @param string $key
     * @return string
     *
     * @author weihan
     * @datetime 2016年11月16日下午3:39:53
     */
    protected function getCookie($key) {
        return $this->http_input->cookie($key, true);
    }
    
    /**
     * 设置cookie
     * @param string $key
     * @param string $value
     * @param number $expire    多长时间过期，单位秒
     * @param string $path
     * @param string $domain
     * @param string $secure
     * @param string $httponly
     *
     * @author weihan
     * @datetime 2016年11月16日下午3:41:33
     */
    protected function setCookie($key, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
        if ($expire){
            $expire += time();
        }
        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    /**
     * 跳转
     * @param string $uri 
     * @param $params 参数
     *
     * @author weihan
     * @datetime 2016年11月16日下午3:57:35
     */
    protected function redirect($uri, $params=[]) {
        //是否当前控制器的方法
        if (strpos($uri, '/') === false){
            $c_name = strtolower(get_class_name(get_class($this)));
            $uri = "{$c_name}/{$uri}";
        }
        
        $url = url($uri, $params);
        
        $this->http_output->setHeader('location', $url);
        $this->http_output->setStatusHeader(302);
        $this->http_output->end();
    }
    
    /**
     * 输出到浏览器
     * @param mixed $data
     *
     * @author weihan
     * @datetime 2016年11月21日上午10:56:28
     */
    protected function output($data='', $is_ajax=false) {
        
        if ($is_ajax){
            $this->http_output->setHeader('Content-Type', 'application/json;charset=utf-8');
            $data = json_encode($data);
        }else{
            if (is_array($data)){
                $data = var_export($data, true);
            }
        }
        $this->http_output->end($data, $this->gzip);
    }
    
    protected function ajaxOutput($data) {
        $this->output($data, true, $this->gzip);
    }
    
    /**
     * 是否post提交
     *
     * @author weihan
     * @datetime 2016年11月16日下午4:13:53
     */
    protected function isPost() {
        return $this->request->server['request_method'] == 'POST' ? true : false;
    }
    
    /**
     * 是否get提交
     *
     * @author weihan
     * @datetime 2016年11月16日下午4:13:53
     */
    protected function isGet() {
        return $this->request->server['request_method'] == 'GET' ? true : false;
    }
    
    /**
     * 是否ajax提交
     *
     * @author weihan
     * @datetime 2016年11月16日下午4:13:53
     */
    protected function isAjax() {
        return isset($this->request->header['x-requested-with']) && $this->request->header['x-requested-with'] == 'XMLHttpRequest' ? true : false;
    }
    
    /**
     * 获取php session_id
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:07:29
     */
    protected function getPhpsessid() {
        $sessid = $this->getCookie('PHPSESSID');
        if ($sessid && preg_match("/[a-zA-z0-9]+/", $sessid)){
            return $sessid;
        }
        return '';
    }
    
    
    /**
     * 保存session
     * @param string $key
     * @param string $value
     * @param int $ttl  session过期时间
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:12:46
     */
    protected function setSession($key, $value, $ttl=0) {
        //先看看是否已经设置过其他session
        $sessid = $this->getPhpsessid();
        if (empty($sessid)) {
            $sessid = RandomKey::randmd5(40);
            $this->setCookie('PHPSESSID', $sessid, 3600);
        }
        $key = 'sess'. $sessid. $key;
        $this->session_handler->set($key, $value, $ttl);
    }
    
    /**
     * 获取session
     * 需要加yield
     * @param string $key
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:42:43
     */
    protected function getSession($key) {
        $sessid = $this->getPhpsessid();
        if (!empty($sessid)) {
            $key = 'sess'. $sessid. $key;
            return yield $this->session_handler->get($key);
        }
        return '';
    }
    
    /**
     * 删除session
     * @param string $key
     *
     * @author weihan
     * @datetime 2016年11月17日下午3:43:43
     */
    protected function delSession($key) {
        $sessid = $this->getPhpsessid();
        if (!empty($sessid)) {
            $key = 'sess'. $sessid. $key;
            $this->session_handler->delete($key);
        }
    }
    
    /**
     * 自动生成表单
     * @param BaseModel $modelObj
     * @param string $form_name
     * @param array $data   默认值
     *
     * @author weihan
     * @datetime 2016年12月9日上午10:51:52
     */
    protected function getForm(BaseModel $modelObj, $form_name, $data=[])
    {
        $form = $modelObj->form($form_name);
        if ($modelObj->_form_secret)
        {
            $this->setFormsecret(get_class($modelObj), $form_name);
        }
        return Form::autoform($form, $data);
    }
    
    /**
     * 表单验证
     * 开启form_secret（默认开启）时，需要使用yield
     * 
     * @param BaseModel $modelObj   模型
     * @param string $form_name 模型中的表单名字
     * @param array $input 表单输入项
     * @param string $error
     * @param array $data_arr
     * @return boolean|Generator
     *
     * @author weihan
     * @datetime 2016年11月22日下午2:29:51
     */
    protected function checkForm(BaseModel $modelObj, $form_name, $input, &$error, &$data_arr=[])
    {
        if($modelObj->_form_secret)
        {
            $is_pass = yield $this->checkFormsecret(get_class($modelObj), $form_name);
            if (! $is_pass){
                $error = '错误的请求';
                return false;
            }
            
        }
        $form = $modelObj->form($form_name);
        return yield Form::checkInput($this->request, $input, $form, $error, $data_arr);
    }
    
    private function _getFormSecretKey($model_name, $form_name) {
        $model_name = get_class_name($model_name);
        return "form_{$model_name}_{$form_name}";
    }
    
    /**
     * 设置form secret
     * @param string $form_name
     *
     * @author weihan
     * @datetime 2016年11月18日下午5:38:03
     */
    protected function setFormsecret($model_name, $form_name) {
        $secret = Form::secret();
        $key = $this->_getFormSecretKey($model_name, $form_name);
        $this->setCookie($key, $secret);
        $this->setSession($key, $secret);
    }
    
    /**
     * 验证form secret
     * 需要加yield
     * @param string $form_name
     * @return Generator
     *
     * @author weihan
     * @datetime 2016年11月18日下午5:38:56
     */
    protected function checkFormsecret($model_name, $form_name) {
        $key = $this->_getFormSecretKey($model_name, $form_name);
        $cookie_val = $this->getCookie($key);
        $sess_val = yield $this->getSession($key);
        if (!empty($cookie_val) && $cookie_val == $sess_val) {
            //删掉已用的session
            $this->delSession($key);
            return true;
        }
        return false;
    }
    
    /**
     * 获取表单中字段的值
     * @param BaseModel $modelObj   模型
     * @param string $form_name 模型中的表单名字
     * @param array $input 表单输入项
     *
     * @author weihan
     * @datetime 2016年12月6日上午9:09:58
     */
    protected function getFormValue(BaseModel $modelObj, $form_name, $input){
        $form = $modelObj->form($form_name);
        $arr = [];
        foreach ($form as $key){
            if (isset($input[$key])){
                $arr[$key] = $input[$key]; 
            }
        }
        return $arr;
    }
    
    /**
     * 获取ip
     *
     * @author weihan
     * @datetime 2016年11月22日下午4:55:53
     */
    protected function ip(){
        $ip = '';
        if (isset($this->request->header['x-forwarded-for'])){
            $ip = $this->request->header['x-forwarded-for'];
        }else {
            $ip = $this->request->server['remote_addr'];
        }
        if (! Validate::ip($ip)){
            $ip = 'unvalid';
        }
        return $ip;
    }
    
    /**
     * 从cookie中得到userid
     *
     * @author weihan
     * @datetime 2016年11月25日上午11:31:16
     */
    protected function getUserid() {
        $userid = 0;
        //读取cookie
        $cookie_userinfo = $this->getCookie($this->cookie_userinfo);
        $this->auth_str = $cookie_userinfo = str_replace(' ', '+', $cookie_userinfo);
        if ($cookie_userinfo && $cookie_userinfo=authcode($cookie_userinfo)){
            $arr = explode("\t", $cookie_userinfo);
            if (count($arr) == 2){
                $userid = intval($arr[1]);
            }
        }
        return $userid;
    }
    
    /**
     * 分页函数
     *
     * @param $num 信息总数
     * @param $curr_page 当前分页
     * @param $perpage 每页显示数
     * @param $urlrule URL规则
     * @param $array 需要传递的数组，用于增加额外的方法
     * @return 分页
     */
    protected function pages($num, $curr_page, $perpage = 20, $urlrule = '', $array = array(),$setpages = 5) {
        $urlrule = $this->url_par('page={$page}');
        $multipage = '';
        $curr_page = max($curr_page, 1);
        if($num > $perpage) {
            $page = $setpages+1;
            $offset = ceil($setpages/2-1);
            $pages = ceil($num / $perpage);
            $from = $curr_page - $offset;
            $to = $curr_page + $offset;
            $more = 0;
            if($page >= $pages) {
                $from = 1;
                $to = $pages;
            } else {
                if($from <= 1) {
                    $to = $page-1;
                    $from = 1;
                }  elseif($to >= $pages) {
                    $from = $pages-($page-1);
                    $to = $pages-1;
                }
                $more = 1;
            }
            if($curr_page > 1) {
                $multipage .= ' <a href="'.$this->pageurl($urlrule, $curr_page-1, $array).'">上一页</a>';
            }
            for($i = $from; $i <= $to; $i++) {
                if($i != $curr_page) {
                    $multipage .= ' <a href="'.$this->pageurl($urlrule, $i, $array).'">'.$i.'</a>';
                } else {
                    $multipage .= ' <a href="'.$this->pageurl($urlrule, $i, $array).'" class="current">'.$i.'</a>';
                }
            }
            if($curr_page<$pages) {
                $multipage .= ' <a href="'.$this->pageurl($urlrule, $curr_page+1, $array).'">下一页</a>';
            } 
        }
        return $multipage;
    }
    
    /**
     * URL路径解析，pages 函数的辅助函数
     *
     * @param $par 传入需要解析的变量 默认为，page={$page}
     * @param $url URL地址
     * @return URL
     */
    protected function url_par($par, $url = '') {
        if($url == '') $url = $this->get_url();
        $pos = strpos($url, '?');
        if($pos === false) {
            $url .= '?'.$par;
        } else {
            $querystring = substr(strstr($url, '?'), 1);
            parse_str($querystring, $pars);
            $query_array = array();
            foreach($pars as $k=>$v) {
                if($k != 'page') $query_array[$k] = $v;
            }
            $querystring = http_build_query($query_array).'&'.$par;
            $url = substr($url, 0, $pos).'?'.$querystring;
        }
        return $url;
    }
    
    /**
     * 返回分页路径
     *
     * @param $urlrule 分页规则
     * @param $page 当前页
     * @param $array 需要传递的数组，用于增加额外的方法
     * @return 完整的URL路径
     */
    protected function pageurl($urlrule, $page, $array = array()) {
        if(strpos($urlrule, '~')) {
            $urlrules = explode('~', $urlrule);
            $urlrule = $page < 2 ? $urlrules[0] : $urlrules[1];
        }
        $findme = array('{$page}');
        $replaceme = array($page);
        if (is_array($array)) foreach ($array as $k=>$v) {
            $findme[] = '{$'.$k.'}';
            $replaceme[] = $v;
        }
        $url = str_replace($findme, $replaceme, $urlrule);
        $url = str_replace(array('http://','//','~'), array('~','/','http://'), $url);
        return $url;
    }
    
    /**
     * 获取当前页面的url
     *
     * @author weihan
     * @datetime 2016年12月8日下午4:32:15
     */
    protected function get_url() {
        $query_str = '';
        if (isset($this->request->server['query_string'])){
            $query_str = '?'. $this->request->server['query_string'];
        }
        return get_www($this->request->server['path_info']. $query_str);
    }
    
    /**
     * 输出提示信息
     * @param string $msg
     *
     * @author weihan
     * @datetime 2016年12月9日上午11:02:36
     */
    protected function showmessage($msg) {
        $this->output($msg);
    }
    
    /**
     * 输出错误信息
     * @param string $msg
     *
     * @author weihan
     * @datetime 2016年12月9日上午11:02:47
     */
    protected function error($msg) {
        $this->showmessage($msg);
    }
    
    /**
     * 模拟http post
     * 协程，不支持传图片，
     * @param string $api_domain  域名，必须带端口
     * @param string $api_uri   
     * @param array $params 参数
     * @return Generator
     *
     * @author weihan
     * @datetime 2016年12月21日上午11:01:51
     */
    protected function httpPost($api_domain, $api_uri, $params=[]) {
        $cookie_userinfo = $this->getCookie($this->cookie_userinfo);
        $cookie_userinfo = str_replace(' ', '+', $cookie_userinfo);
        $params['auth'] = $cookie_userinfo;
        $params['userid'] = $this->getUserid();
        
        $httpClient = yield $this->client->coroutineGetHttpClient($api_domain);
        return yield $httpClient->coroutinePost($api_uri, $params);
    }
    
    /**
     * 引入uc
     *
     * @author weihan
     * @datetime 2016年12月26日上午9:26:50
     */
    protected function uc() {
        //uc里面用到
        !defined('HTTP_USER_AGENT') && define('HTTP_USER_AGENT', $this->request->header['user-agent']);
        //引入uc
        require_once APP_DIR. '/Helpers/Libs/uc_client/client.php';
    }
    
    /**
     * 获取referer
     *
     * @author weihan
     * @datetime 2016年12月29日上午11:38:08
     */
    protected function referer() {
        return $this->request->header['referer'] ?? null;
    }
}