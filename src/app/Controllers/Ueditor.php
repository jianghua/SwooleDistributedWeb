<?php
namespace app\Controllers;

/**
 * ueditor编辑器专用控制器
 * @author weihan
 * @datetime 2017年6月20日上午10:37:03
 *
 */
class Ueditor extends Ajax
{
    
    public function initialization($controller_name, $method_name){
        parent::initialization($controller_name, $method_name);
    }
    
    /**
     * 入口
     *
     * @author weihan
     * @datetime 2017年6月20日上午10:37:41
     */
    public function index(){
        $action = $this->get('action');
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($this->getHostRoot(). "/js/ueditor/config.json")), true);
        switch ($action){
            case 'config':
                $result =  $CONFIG;
                break;
                
            case 'uploadimage': //上传图片
                $result = $this->_image();
                break;
                
            default:
                $result = array(
                'state'=> '请求地址出错'
                    );
                break;
        }
        
        /* 输出结果 */
        $callback = $this->get('callback');
        $str = ' ';
        if ($callback) {
            if (preg_match("/^[\w_]+$/", $callback)) {
                $str = htmlspecialchars($callback) . '(' . $result . ')';
            } else {
                $str = json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            $str = $result;
        }
        $this->ajaxOutput($str);
    }
    
    /**
     * 上传图片
     *
     * @author weihan
     * @datetime 2017年6月20日下午2:16:34
     */
    private function _image() {
        $max_size = get_instance()->config->get('upload_maxsize');
        $allow = array('jpg', 'gif', 'png', 'jpeg');
        $up_arr = $this->upload($max_size, $allow, 'upfile', true);
        $ret = [
            "state" => $up_arr['status'] ? 'SUCCESS' : 'error',          //上传状态，上传成功时必须返回"SUCCESS"
            "url" => $up_arr['url'],            //返回的地址
            "title" => "",          //新文件名
            "original" => "",       //原始文件名
            "type" => "",            //文件类型
            "size" => "",           //文件大小
        ];
        return $ret;
    }
    
    
}