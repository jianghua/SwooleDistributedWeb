<?php

namespace app\Controllers;

use Server\CoreBase\Controller;

/**
 * websocket
 * @author weihan
 * @datetime 2018年2月27日下午2:38:55
 *
 */
class Im extends Controller
{
    /**
     * http入口
     *
     * @author weihan
     * @datetime 2018年2月27日下午2:41:02
     */
    public function index(){
        $data = [];
        $template = $this->loader->view('app::Im/index');
        $this->http_output->end($template->render($data));
    }
    
    public function tcp_onConnect()
    {
        $uid = time();
        $this->bindUid($uid);
        $this->send(['type' => 'welcome', 'id' => $uid]);
    }
    
    public function tcp_message()
    {
        $this->sendToAll(
            [
                'type' => 'message',
                'id' => $this->uid,
                'message' => $this->client_data->message,
            ]
        );
    }

    public function tcp_onClose()
    {
        $this->destroy();
    }

}
