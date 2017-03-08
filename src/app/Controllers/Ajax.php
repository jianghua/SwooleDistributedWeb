<?php
namespace app\Controllers;

use app\Helpers\Libs\Upload;
use app\Helpers\Libs\RandomKey;
/**
 * ajax
 * 所有的验证方法，1：表示通过，0：表示验证不通过
 * @author weihan
 * @datetime 2016年11月21日下午5:17:45
 *
 */
class Ajax extends BaseController
{
    protected function initialization($controller_name, $method_name){
        parent::initialization($controller_name, $method_name);
    }
    
    /**
     * 判断用户名是否存在
     *
     * @author weihan
     * @datetime 2016年11月21日下午5:17:09
     */
    public function checkUsername($is_return=false){
        $resonse = 0;
        $username = $this->request('username_reg');
        if ($username){
            $userModel = $this->model('UserModel');
            $resonse = yield $userModel->isUsernameExist($username);
            $resonse = $resonse ? 0 : 1;
        }
        if ($is_return){
            return $resonse;
        }
        $this->output($resonse);
    }
    
    /**
     * 验证  验证码
     *
     * @author weihan
     * @datetime 2016年11月22日上午11:31:37
     */
    public function checkCode($is_return=false) {
        $resonse = 0;
        $checkcode = $this->request('checkcode');
        if ($checkcode){
            $checkcode = strtolower($checkcode);
            $result = yield $this->getSession('checkcode');
            $checkcode == $result && $resonse = 1;
        }
        if ($is_return){
            return $resonse;
        }
        $this->output($resonse);
    }
    
    /**
     * ajax上传，不对外服务
     * @author weihan
     * @datetime 2016年11月21日上午10:50:00
     */
    protected function upload($max_size, $allow, $name='', $is_return=false) {
        $data = ['status'=>'0', 'url'=>'', 'msg'=>''];
        if (($this->isPost() || $this->isAjax()) && property_exists($this->request, 'files')) {
            $name = $name ? $name : $this->post('name');
            $width = $this->post('width');
            $width = intval($width);
            $height = $this->post('height');
            $height = intval($height);
            
            $upload_cls = new Upload($this->request->files);
            $upload_cls->max_size = $max_size;
            $upload_cls->allow = $allow;
            $upload_cls->max_width = $width;
            $upload_cls->max_height = $height;
            
            $result = $upload_cls->save($name);
            //上传失败
            if ($result === false){
                $data['msg'] = $upload_cls->error_msg;
            }else{
                $data['url'] = $result['url'];
                $data['status'] = 1;
            }
        }
        if ($is_return) {
            return $data;
        }
        //不能返回ajax头部，因为ie不支持
        $this->output(json_encode($data));
        return;
    }
    
    /**
     * 图片上传
     *
     * @author weihan
     * @datetime 2016年12月1日上午10:01:51
     */
    public function image() {
        $max_size = get_instance()->config->get('upload_maxsize');
        $allow = array('jpg', 'gif', 'png', 'jpeg');
        $this->upload($max_size, $allow);
        return ;
    }
    
    /**
     * 获取下级
     *
     * @author weihan
     * @datetime 2016年12月2日下午5:09:08
     */
    public function linkage() {
        $linkage_id = $this->request('linkage_id');
        $linkage_id = intval($linkage_id);
        
        $model = $this->model('LinkageModel');
        $data = yield $model->getSubs($linkage_id);
        
        $this->ajaxOutput($data);
        return;
    }
    
    /**
     * ckeditor
     * 图片上传
     *
     * @author weihan
     * @datetime 2016年12月8日上午10:50:42
     */
    public function ckeditorImage() {
        $max_size = get_instance()->config->get('upload_maxsize');
        $allow = array('jpg', 'gif', 'png', 'jpeg');
        $data = $this->upload($max_size, $allow, 'upload', true);
        
        $CKEditorFuncNum = $this->request('CKEditorFuncNum');
        $url = '';
        $msg = $data['msg'];
        if ($data['status'] == 1){
            $url = $data['url'];
        }
        $msg = addslashes($msg);
        $str = "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction({$CKEditorFuncNum}, '{$url}', '{$msg}');</script>";
        $this->output($str);
    }
    
    
}