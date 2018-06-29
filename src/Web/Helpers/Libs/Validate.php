<?php
namespace Web\Helpers\Libs;

/**
 * 数值验证类，类中的方法都是静态的，用于检测一个变量是否符合某种规则，不符合返回false，符合返回原值
 * @author tianfeng.han
 * @package SwooleSystem
 * @subpackage Validate
 * @link http://www.swoole.com/
 */
class Validate
{
    static $regx = array(
        //邮箱
        'email'=>'/^[\w-\.]+@[\w-]+(\.(\w)+)*(\.(\w){2,4})$/',
        //手机号码
        'mobile'=>'/^(13|15|16|17|18|19)[0-9]{9}$/',
        //固定电话带分机号
        'tel'=>'/^((0\d{2,3})-)(\d{7,8})(-(\d{1,4}))?$/',
        //固定电话不带分机号
        'phone'=>'/^\d{3}-?\d{8}|\d{4}-?\d{7}$/',
        //域名
        'domain'=>'/@([0-9a-z-_]+.)+[0-9a-z-_]+$/i',
        //日期
        'date'=>'/^[1-9][0-9][0-9][0-9]-[0-9]{1,2}-[0-9]{1,2}$/',
        //日期时间
        'datetime'=>'/^[1-9][0-9][0-9][0-9]-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}(:[0-9]{1,2}){1,2}$/',
        //时间
        'time'=>'/^[0-9]{1,2}(:[0-9]{1,2}){1,2}$/',
        /*--------- 数字类型 --------------*/
        'int'=>'/^\d{1,11}$/', //十进制整数
        'hex'=>'/^0x[0-9a-f]+$/i', //16进制整数
        'bin'=>'/^[01]+$/', //二进制
        'oct'=>'/^0[1-7]*[0-7]+$/', //8进制
        'float'=>'/^\d+\.[0-9]+$/', //浮点型
        /*---------字符串类型 --------------*/
        //utf-8中文字符串
        'chinese'=>[
            'js' => '/^[\u4e00-\u9fa5]+$/',
            'php' => '/^[\x{4e00}-\x{9fa5}]+$/u',
        ],
        /*---------常用类型 --------------*/
        //英文  
        'english' => '/^[A-Za-z0-9_\.]+$/', 
        //昵称，可以带英文字符和数字
        'nickname' => [     
            'js' => '/^[a-z0-9_\.\u4e00-\u9fa5]+$/',
            'php' => '/^[\x{4e00}-\x{9fa5}a-z0-9_\.]+$/ui',
        ], 
        //真实姓名
        'realname' => [     
            'js' => '/^[\u4e00-\u9fa5]+$/',
            'php' => '/^[\x{4e00}-\x{9fa5}]+$/u',
        ], 
        //密码
        'password' => '/^[a-z0-9]{6,32}$/i', 
        //区号
        'area' => '/^0\d{2,3}$/', 
        //版本号
        'version' => '/^\d+\.\d+\.\d+$/',      
        //URL
        'url' => '/^((https?):\/\/(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)$/',
        //通用，中文，英文、数字
        'common' => [     
            'js' => '/^[a-z0-9_\.\u4e00-\u9fa5\s]+$/',
            'php' => '/^[\x{4e00}-\x{9fa5}a-z0-9_\.\s]+$/ui',
        ], 
        //文本输入，范围更广，\u4e00-\u9fa5表示中文，后面的unicode是中文符号。 ？ ！ ， 、 ； ： “ ” ‘ ’ （ ） 《 》 〈 〉 【 】 『 』 「 」 ﹃ ﹄ 〔 〕 … — ～ ﹏ ￥
        'text' => [     
            'js' => '/^[A-za-z0-9\u4e00-\u9fa5\s_\.\-,\u3002\uff1f\uff01\uff0c\u3001\uff1b\uff1a\u201c\u201d\u2018\u2019\uff08\uff09\u300a\u300b\u3008\u3009\u3010\u3011\u300e\u300f\u300c\u300d\ufe43\ufe44\u3014\u3015\u2026\u2014\uff5e\ufe4f\uffe5]+$/',
            'php' => '/^[\x{4e00}-\x{9fa5}a-z0-9\s_\.\-,\x{3002}\x{ff1f}\x{ff01}\x{ff0c}\x{3001}\x{ff1b}\x{ff1a}\x{201c}\x{201d}\x{2018}\x{2019}\x{ff08}\x{ff09}\x{300a}\x{300b}\x{3008}\x{3009}\x{3010}\x{3011}\x{300e}\x{300f}\x{300c}\x{300d}\x{fe43}\x{fe44}\x{3014}\x{3015}\x{2026}\x{2014}\x{ff5e}\x{fe4f}\x{ffe5}]+$/ui',
        ], 
        //身份证号码
        'idcard' => '/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/',
    );
    /**
     * 正则验证
     * @param $regx
     * @param $input
     * @return bool|string
     */
    static function regx($regx, $input)
    {
        $n = preg_match($regx, $input, $match);
        if ($n === 0)
        {
            return false;
        }
        else
        {
            return $match[0];
        }
    }

    static function isVersion($ver)
    {
        return self::check('version', $ver);
    }

    static function check($ctype, $input)
    {
        if (isset(self::$regx[$ctype]))
        {
            return self::regx(self::getRegx($ctype), $input);
        }
        else
        {
            return self::$ctype($input);
        }
    }

    /**
     * 检查数组是否缺少某些Key
     * @param array $array
     * @param array $keys
     *
     * @return bool
     */
    static function checkLacks(array $array, array $keys)
    {
        foreach($keys as $k)
        {
            if (empty($array[$k]))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * 验证字符串格式
     * @param $str
     * @return false or $str
     */
    static function string($str)
    {
        return filter_var($str, FILTER_DEFAULT);
    }
    /**
     * 验证是否为URL
     * @param $str
     * @return false or $str
     */
    static function url($str)
    {
        return filter_var($str, FILTER_VALIDATE_URL);
    }
    /**
     * 过滤HTML，使参数为纯文本
     * @param $str
     * @return false or $str
     */
    static function text($str)
    {
        return filter_var($str, FILTER_SANITIZE_STRING);
    }
    /**
     * 检测是否为gb2312中文字符串
     * @param $str
     * @return false or $str
     */
    static function chinese_gb($str)
    {
        $n =  preg_match("/^[".chr(0xa1)."-".chr(0xff)."]+$/",$str,$match);
        if($n===0) return false;
        else return $match[0];
    }
    /**
     * 检测是否为自然字符串（可是中文，字符串，下划线，数字），不包含特殊字符串，只支持utf-8或者gb2312
     * @param $str
     * @return false or $str
     */
    static function realstring($str,$encode='utf8')
    {
        if($encode=='utf8') $n = preg_match('/^[\x{4e00}-\x{9fa5}|a-z|0-9|A-Z]+$/u',$str,$match);
        else $n = preg_match("/^[".chr(0xa1)."-".chr(0xff)."|a-z|0-9|A-Z]+$/",$str,$match);
        if($n===0) return false;
        else return $match[0];
    }
    /**
     * 检测是否一个英文单词，不含空格和其他特殊字符
     * @param $str
     * @return false or $str
     */
    static function word($str, $other='')
    {
        $n = preg_match("/^([a-zA-Z_{$other}]*)$/",$str,$match);
        if($n===0) return false;
        else return $match[0];
    }

    /**
     * 检查是否ASSIC码
     * @param $value
     * @return true or false
     */
    static function assic($value)
    {
        $len = strlen($value);
        for ($i = 0; $i < $len; $i++)
        {
            $ord = ord(substr($value, $i, 1));
            if ($ord > 127) return false;
        }
        return $value;
    }

    /**
     * IP地址
     * @param $value
     * @return bool
     */
    static function ip($value)
    {
        $arr = explode('.', $value);
        if (count($arr) != 4)
        {
            return false;
        }
        //第一个和第四个不能为0或255
        if (($arr[0] < 1 or $arr[0] > 254) or ($arr[3] < 1 or $arr[3] > 254))
        {
            return false;
        }
        //中间2个可以为0,但不能超过254
        if (($arr[1] < 0 or $arr[1] > 254) or ($arr[2] < 0 or $arr[2] > 254))
        {
            return false;
        }
        return true;
    }

    /**
     * 检查值如果为空则设置为默认值
     * @param $value
     * @param $default
     * @return unknown_type
     */
    static function value_default($value,$default)
    {
        if(empty($value)) return $default;
        else return $value;
    }
    
    /**
     * 获取正则
     * @param string $ctype
     * @param bool $is_js   true：js的正则，false：php的正则
     *
     * @author weihan
     * @datetime 2016年12月1日上午11:21:38
     */
    static function getRegx($ctype, $is_js=false)
    {
        $regx = '';
        if (isset(self::$regx[$ctype]))
        {
            if (is_array(self::$regx[$ctype])){
                if ($is_js && isset(self::$regx[$ctype]['js'])){
                    $regx = self::$regx[$ctype]['js'];
                }elseif (! $is_js && isset(self::$regx[$ctype]['php'])) {
                    $regx = self::$regx[$ctype]['php'];
                }
            }else{
                $regx = self::$regx[$ctype];
            }
        }
        return $regx;
    }
}
