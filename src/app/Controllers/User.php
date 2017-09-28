<?php
namespace app\Controllers;

use app\Helpers\Libs\RandomKey;
use app\Helpers\Libs\Filter;
/**
 * 用户控制器
 * @author weihan
 * @datetime 2016年11月15日上午11:22:00
 *
 */
class User extends BaseController
{
    public $userid = 0;
    public $model;
    
    /**
     * 初始化
     * {@inheritDoc}
     * @see \app\Controllers\BaseController::initialization()
     * 
     * @author weihan
     * @datetime 2016年11月21日下午3:29:34
     */
    protected function initialization($controller_name, $method_name){
        parent::initialization($controller_name, $method_name);
        
        $this->model = $this->model('UserModel');
        $this->userid = $this->getUserid();
        if ($this->userid == 0 
            && !in_array($this->method_name, ['index', 'ajaxLogin', 'reg'])){
            
            $this->redirect('/');
            
            return false;
        }
        //引入uc
        $this->uc();
        return true;
    }
    
    /**
     * 首页 
     *
     * @author weihan
     * @datetime 2016年11月16日下午3:44:35
     */
    public function index() {
        
        $this->view('User/index');
    }
    
    /**
     * 生成密码
     * @param string $password
     * @param string $salt
     *
     * @author weihan
     * @datetime 2017年1月3日下午4:28:08
     */
    private function _create_pass($password, $salt) {
        return md5($password. md5($salt));
    }
    
    /**
     * 用户登录
     *
     * @author weihan
     * @datetime 2016年11月15日下午2:54:45
     */
    public function ajaxLogin() {
        $form_name = 'loginForm';
        
        $data = ['msg'=>'', 'url'=>''];
        if ($this->isAjax()) {
            $error = '';
            $this->model->_form_secret = false;
            $data_arr = [];
            $is_pass = yield $this->checkForm($this->model, $form_name, $this->get(), $error, $data_arr);
            if ($is_pass !== true){
                $data['msg'] = $error;
                $this->ajaxOutput($data);
                return ;
            }
            
            $username = $data_arr['username'];
            $password = $data_arr['password'];
            
            //读取密码
            $info = yield $this->model->getOne(['username'=>$username], 'id,password,salt');
            if (empty($info)){
                $data['msg'] = '用户不存在';
                $this->ajaxOutput($data);
                return ;
            }
            if ($info['password'] != $this->_create_pass($password, $info['salt'])){
                $data['msg'] = '密码错误';
                $this->ajaxOutput($data);
                return ;
            }
            
            //设置cookie
            $this->_setUserCookies($username, $info['id']);
            $data['url'] = url('user/profile');
            $this->ajaxOutput($data);
            return;
        }
        $this->ajaxOutput($data);
    }
    
    /**
     * 用户注册
     * 
     * @author weihan
     * @datetime 2016年11月21日下午2:54:07
     */
    public function reg(){
        if ($this->userid){
            $this->redirect('profile');
        }
        
        $form_name = 'regForm';
        
        //表单提交
        if ($this->isPost()){
            //表单验证
            $error = '';
            $data_arr = [];
            $is_pass = yield $this->checkForm($this->model, $form_name, $this->post(), $error, $data_arr);
            if ($is_pass !== true){
                $this->showmessage($error);
                return ;
            }
            
            $insert_data = [];
            $insert_data['username'] = $data_arr['username_reg'];
            $insert_data['salt'] = $salt = RandomKey::string(4);
            $insert_data['password'] = $this->_create_pass($data_arr['password'], $salt);
            $insert_data['ip'] = $this->ip();
            $userid = yield $this->model->insert($insert_data);
            if ($userid <= 0){
                $this->showmessage('注册失败，请稍后重试。');
                return;
            }
            
            //设置cookie
            $this->_setUserCookies($data_arr['username'], $userid);
            
            $this->showmessage('注册成功', 'user/profile');
            return;
        }
        
        //
        $form = $this->getForm($this->model, $form_name);
        $this->view('User/reg', ['form'=>$form]);
    }
    
    /**
     * 用户注册
     *
     * @author weihan
     * @datetime 2016年11月21日下午2:54:07
     */
    public function profile(){
        $form_name = 'profileForm';
    
        //表单提交
        if ($this->isPost()){
            //表单验证
            $error = '';
            $data_arr = [];
            $is_pass = yield $this->checkForm($this->model, $form_name, $this->post(), $error, $data_arr);
            if ($is_pass !== true){
                $this->showmessage($error);
                return ;
            }
    
            $is_updated = yield $this->model->update($data_arr, ['id'=>$this->userid]);
            if ($is_updated <= 0){
                $this->showmessage('更新失败，请稍后重试。');
                return;
            }
    
            $this->showmessage('更新成功');
            return;
        }
    
        //获取用户信息
        $userinfo = yield $this->model->getOne(['id'=>$this->userid]);
        //把用户信息传入form，可回显
        $form = $this->getForm($this->model, $form_name, $userinfo);
        $this->view('User/profile', ['form'=>$form, 'userid'=>$this->userid, 'userinfo'=>$userinfo]);
    }
    
    /**
     * 退出
     * 
     * @author weihan
     * @datetime 2016年12月7日上午11:21:37
     */
    public function logout(){
        $this->_clearUserCookies();
        $this->redirect('/');
    }
    
    /**
     * 过滤器
     * ?id=1&arr[]=a&arr[]=b&email=test@test.com&wrong_email=test@test
     *
     * @author weihan
     * @datetime 2017年1月6日下午12:31:52
     */
    public function filter() {
        $id = Filter::filter($this->get('id'), Filter::INT);
        var_dump($id);
        $arr = Filter::filter($this->get('arr'), Filter::STRING);
        var_dump($arr);
        $email = Filter::filter($this->get('email'), [Filter::CTYPE, 'email']);
        var_dump($email);
        $wrong_email = Filter::filter($this->get('wrong_email'), [Filter::CTYPE, 'email']);
        var_dump($wrong_email);
        $regx = Filter::filter($this->get('regx'), [Filter::REGX, '/\d+/']);
        var_dump($regx);
        $this->output();
    }
    
    public function sessionSet() {
        $this->setSession('sess_test', '1');
        $this->output();
    }
    public function sessionGet() {
        echo yield $this->getSession('sess_test');
        $this->output();
    }
    
    /**
     * 设置用户cookie
     * @param string $username
     * @param string $randpass
     * @param int $userid
     *
     * @author weihan
     * @datetime 2016年11月24日下午5:22:37
     */
    private function _setUserCookies($username, $userid) {
        //设置cookie
        $auth = authcode($username."\t".$userid, 'ENCODE');
        $this->setCookie($this->cookie_userinfo, $auth);
    }
    
    /**
     * 清除cookie
     *
     * @author weihan
     * @datetime 2016年11月25日上午11:26:47
     */
    private function _clearUserCookies() {
        $this->setCookie($this->cookie_userinfo, '');
    }
    
    /**
     * 提示信息
     * {@inheritDoc}
     * @see \app\Controllers\BaseController::showmessage($msg)
     * 
     * @author weihan
     * @datetime 2016年12月28日下午5:01:08
     */
    protected function showmessage($msg, $url='back') {
        $data['dialog_message'] = $msg;
        switch ($url) {
            case 'back':
                $url = $this->referer() ? : url('/');
                break;
    
            default:
                $url = url($url);
                break;
        }
        $data['location'] = $url;
        $this->view('User/showmessage', $data);
    }
}