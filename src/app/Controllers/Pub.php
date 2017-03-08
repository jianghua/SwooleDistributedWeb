<?php
namespace app\Controllers;

use app\Helpers\Libs\Checkcode;
use app\Helpers\Libs\Upload;
/**
 * 公共控制器
 * @author weihan
 * @datetime 2016年11月15日上午11:22:00
 *
 */
class Pub extends BaseController
{
    protected function initialization($controller_name, $method_name){
        parent::initialization($controller_name, $method_name);
    }
    
    /**
     * 生成验证码
     *
     * @author weihan
     * @datetime 2016年11月17日上午10:56:38
     */
    public function checkcode() {
        $checkcode = new Checkcode();
        if (!empty($this->get('code_len')) && intval($this->get('code_len'))) 
            $checkcode->code_len = intval($this->get('code_len'));
        if ($checkcode->code_len > 8 || $checkcode->code_len < 2) {
            $checkcode->code_len = 4;
        }
        if (!empty($this->get('font_size')) && intval($this->get('font_size'))) 
            $checkcode->font_size = intval($this->get('font_size'));
        if (!empty($this->get('width')) && intval($this->get('width'))) 
            $checkcode->width = intval($this->get('width'));
        if ($checkcode->width <= 0) {
            $checkcode->width = 130;
        }
        if (!empty($this->get('height')) && intval($this->get('height'))) 
            $checkcode->height = intval($this->get('height'));
        if ($checkcode->height <= 0) {
            $checkcode->height = 50;
        }
        if (!empty($this->get('font_color')) && trim(urldecode($this->get('font_color'))) && preg_match('/(^#[a-z0-9){6}$)/im', trim(urldecode($this->get('font_color'))))) 
            $checkcode->font_color = trim(urldecode($this->get('font_color')));
        if (!empty($this->get('background')) && trim(urldecode($this->get('background'))) && preg_match('/(^#[a-z0-9){6}$)/im', trim(urldecode($this->get('background'))))) 
            $checkcode->background = trim(urldecode($this->get('background')));
        
        $this->http_output->setHeader("content-type", "image/png");        
        $checkcode->doimage();
        //debug模式，把信息直接打印到浏览器
        $data = '';
        if (! get_instance()->config->get('server.debug')){
            $data = ob_get_clean();
        }
        $checkcode = $checkcode->get_code();
        $this->setSession('checkcode', $checkcode, 120);
        $this->output($data);
    }
    
}