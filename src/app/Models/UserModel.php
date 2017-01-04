<?php
/**
 * 个人用户表
 * @author weihan
 * @datetime 2016年11月15日上午11:32:06
 */
namespace app\Models;

class UserModel extends BaseModel
{
    public $tbl_name = 'user';
    
    public $_form = [
        'email' => [
            'label' => '邮箱',
            'tips' => '请填写本人常用邮箱，该邮箱将作为登录帐号',
            'type' => 'input',
            'value' => '',
            'placeholder' => '',
            'class' => 'input w1',
            'validates' => [
                'required' => '请输入邮箱',
                'maxlen' => '32|邮箱最长32位',
                'ctype' => 'email|请输入正确的邮箱地址',
            ]
        ],
        'username' => [              //登录使用
            'label' => '用户名',
            'tips' => '',
            'type' => 'input',
            'value' => '',
            'placeholder' => '请输入用户名',
            'class' => 'input w1',
            'validates' => [
                'required'=>'请输入用户名',
                'minlen' => '6|用户名最少6位',
                'maxlen' => '32|用户名最多32位',
                'ctype' => 'common|请输入正确的用户名',
            ]
        ],
        'username_reg' => [             //注册使用
            'label' => '用户名',
            'tips' => '',
            'type' => 'input',
            'value' => '',
            'placeholder' => '请输入用户名',
            'class' => 'input w1',
            'validates' => [
                'required'=>'请输入用户名',
                'minlen' => '6|用户名最少6位',
                'maxlen' => '32|用户名最多32位',
                'ctype' => 'common|请输入正确的用户名',
                'ajax' =>[
                    'type' => 'post',
                    'url' => 'ajax/checkUsername',
                    'data' => '',
                    'buttons' => '$("#dosubmit")',
                    'onError' => '用户名已注册',
                    'onWait' => '验证中...',
                ],
            ]
        ],
        'password' => [
            'label' => '密码',
            'tips' => '建议密码由字母、数字组合、至少6位',
            'type' => 'password',
            'placeholder' => '',
            'class' => 'input w1',
            'validates' => [
                'required'=>'请输入密码',
                'ctype' => 'password|密码格式不正确',
                'minlen' => '6|密码格式不正确',
                'maxlen' => '32|密码格式不正确',
            ]
        ],
        'repassword' => [
            'label' => '确认密码',
            'tips' => '请再次输入密码',
            'type' => 'password',
            'placeholder' => '',
            'class' => 'input w1',
            'validates' => [
                'required'=>'请输入确认密码',
                'minlen' => '6|确认密码格式不正确',
                'equalo' => 'password|两次密码不一致,请重输',
            ]
        ],
        'opassword' => [
            'label' => '请输入原密码',
            'tips' => '输入当前密码',
            'type' => 'password',
            'placeholder' => '',
            'class' => 'input w1',
            'validates' => [
                'required'=>'请输入密码',
                'ctype' => 'password|密码格式不正确',
                'minlen' => '6|密码格式不正确',
                'maxlen' => '32|密码格式不正确',
            ]
        ],
        'checkcode' => [
            'label' => '验证码',
            'tips' => '',
            'type' => 'checkcode',
            'value' => '',
            'placeholder' => '',
            'class' => 'input w2',
            'validates' => [
                'required' => '请输入验证码',
                'ajax' =>[
                    'type' => 'post',
                    'url' => 'ajax/checkcode',
                    'data' => '',
                    'buttons' => '$("#dosubmit")',
                    'onError' => '验证码不正确',
//                     'onWait' => '验证中...',
                ],
            ]
        ],
        //用户资料
        'realname' => [
            'label' => '姓名',
            'tips' => '您的姓名',
            'type' => 'input',
            'maxlength' => 10,
            'value' => '',
            'placeholder' => '',
            'class' => 'input w1',
            'validates' => [
                'required'=>'请输入姓名',
                'minlen' => '2|最少2位',
                'maxlen' => '10|最多10位',
                'ctype' => 'chinese|仅限中文',
            ]
        ],
        'thumb' => [
            'label' => '个人头像',
            'tips' => '尺寸200X200像素，能代表个人形象，请勿使用低俗图片、二维码、其他机构企业等涉嫌误导用户的头像',
            'type' => 'upload',
            'upload' => [
                'type' => 'image',   //上传文件类型，image：图片，
                'width' => '200',   //上传图片宽度，超过的部分会截取
                'height' => '200',  //上传图片高度，超过的部分会截取
                'succ_js' => "",//上传成功后执行的js，回调参数是data
            ],
            'validates' => [
                'required'=>'请上传个人头像',
                'ctype' => 'url|请上传个人头像',
            ]
        ],
        'city_select' => [  //select所在容器的id
            'label' => '所在地',
            'type' => 'cascade_select',
            'jsonName' => 'name',
            'jsonValue' => 'val',
            'selects' => [
                'province' => [
                    'data-first-title' => '选择省',
                    'data-first-value' => '0',
                    'data-required'=>'true',
                    'data-url' => 'ajax/linkage',
                    'data-value' => '17',
                    'class' => 'prov',
                    'data-query-name' => 'linkage_id', //ajax参数名
                    'validates' => [
                        'required'=>'请选择省份',
                        'ctype' => 'int|仅限数字',
                    ]
                ],
                'city' => [
                    'data-first-title' => '选择地市',
                    'data-first-value' => '0',
                    'data-url' => 'ajax/linkage',
                    'data-required'=>'true',
                    'data-value' => '0',
                    'class' => 'city',
                    'data-query-name' => 'linkage_id', //ajax参数名
                    'validates' => [
                        'required'=>'请选择地市',
                        'ctype' => 'int|仅限数字',
                    ]
                ],
                'county' => [
                    'data-first-title' => '选择区县',
                    'data-first-value' => '0',
                    'data-url' => 'ajax/linkage',
                    'data-empty-style'=>"none",
                    'data-required'=>'true',
                    'data-value' => '0',
                    'class' => 'dist',
                    'data-query-name' => 'linkage_id', //ajax参数名
                    'validates' => [
                        'ctype' => 'int|仅限数字',
                    ]
                ],
            ],
        ],
        'pics' => [
            'label' => '多图上传',
            'tips' => '',
            'type' => 'multi_upload',
            'upload' => [
                'type' => 'image',   //上传文件类型，image：图片，
                'max_nums' => 5,    //最多上传几张图片，默认一张
                'width' => '0',   //上传图片宽度，超过的部分会截取
                'height' => '0',  //上传图片高度，超过的部分会截取
                'succ_js' => '',//上传成功后执行的js，回调参数是data
            ],
            'validates' => [
                'ctype' => 'url|请上传照片',
            ]
        ],
    ];
    
    /**
     * 注册表单项目
     *
     * @author weihan
     * @datetime 2016年11月21日下午3:30:53
     */
    public function regForm() {
        return ['username_reg', 'password', 'repassword', 'checkcode'];
    }
    
    /**
     * 登录表单
     * @return string[]
     *
     * @author weihan
     * @datetime 2017年1月4日上午10:22:06
     */
    public function loginForm() {
        return ['username', 'password'];
    }
    
    /**
     * 个人资料
     * @return string[]
     *
     * @author weihan
     * @datetime 2017年1月3日下午4:39:47
     */
    public function profileForm() {
        return ['realname', 'email', 'thumb', 'city_select', 'pics'];
    }
    
    /**
     * 获取用户信息
     * @param int $userid
     *
     * @author weihan
     * @datetime 2016年11月15日下午4:03:59
     */
    public function userinfo($userid, $return_result=true) {
        $contidions_arr = [
            'userid' => $userid,
        ];
        return yield $this->getOne($contidions_arr, '*', $return_result);
    }    
    
    /**
     * 判断用户名是否存在
     * @param string $username
     * @return int 0:不存在，1：存在
     *
     * @author weihan
     * @datetime 2017年1月4日上午9:48:55
     */
    public function isUsernameExist($username) {
        $contidions_arr = [
            'username' => $username,
        ];
        $nums = yield $this->count($contidions_arr);
        return $nums ? 1 : 0;
    }
}