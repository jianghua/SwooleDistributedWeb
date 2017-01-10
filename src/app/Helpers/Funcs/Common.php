<?php
use app\Helpers\Libs\Validate;
use Monolog\Logger;
use Server\DataBase\Miner;
use Server\CoreBase\XssClean;
/**
 * 通用函数库
 */
//检查邮箱是否有效
function isemail($email) {
    return Validate::check('email', $email);
}

function ismobile($mobile) {
    return Validate::check('mobile', $mobile);
} 

function xml_unserialize($xml, $isnormal = FALSE) {
    $xml_cls = new app\Helpers\Libs\Xml();
    $data = $xml_cls->xml_unserialize($xml);
    return $data['root']['item'];
}

function xml_serialize($arr, $htmlon = FALSE, $isnormal = FALSE, $level = 1) {
    $s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '';
    $space = str_repeat("\t", $level);
    foreach($arr as $k => $v) {
        if(!is_array($v)) {
            $s .= $space."<item id=\"$k\">".($htmlon ? '<![CDATA[' : '').$v.($htmlon ? ']]>' : '')."</item>\r\n";
        } else {
            $s .= $space."<item id=\"$k\">\r\n".xml_serialize($v, $htmlon, $isnormal, $level + 1).$space."</item>\r\n";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    return $level == 1 ? $s."</root>" : $s;
}

function get_class_name($classname)
{
    if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
    return $pos;
}

/**
 * redis 获取key值，使用时需要加yield
 * @param string $key
 * @return mixed
 *
 * @author weihan
 * @datetime 2016年11月17日下午3:25:10
 */
function redisGet($key) {
    $result = yield get_instance()->redis_pool->coroutineSend('get', $key);
    return unserialize($result);
}

/**
 * redis 设置key、value
 * 不需要加yield，采用的异步
 * @param string $key
 * @param mixed $value
 * @param number $ttl   过期时间，单位秒
 *
 * @author weihan
 * @datetime 2016年11月17日下午3:34:18
 */
function redisSet($key, $value, $ttl=0) {
    $value = serialize($value);
    if ($ttl) {
        get_instance()->redis_pool->setex($key, $ttl, $value, function ($result){});
    }else {
        get_instance()->redis_pool->set($key, $value, function ($result){});
    }
}

/**
 * redis  删除key，可以传入多个
 * @param string $key
 *
 * @author weihan
 * @datetime 2016年11月17日下午3:39:14
 */
function redisDel(...$key) {
    get_instance()->redis_pool->delete($key, function ($result){});
}

/**
 * 生成url
 * @param string $uri
 * @param array $params
 * @return string
 *
 * @author weihan
 * @datetime 2016年11月21日上午10:19:50
 */
function url($uri, $params=[]){
    $query_str = '';
    if ($params){
        $query_str = '?'. http_build_query($params);
    }
    return get_www($uri). $query_str;
}

/**
 * 执行控制器的某个方法
 * @param string $controller_name
 * @param string $method_name
 * @param array $params
 * @param \swoole_http_request $request
 * @return mixed 
 *
 * @author weihan
 * @datetime 2016年11月22日下午3:55:06
 */
function execControllerMethod($controller_name, $method_name, $params, \swoole_http_request $request) {
    $controller_instance = Server\CoreBase\ControllerFactory::getInstance()->getController($controller_name);
    if ($controller_instance != null) {
        if (method_exists($controller_instance, $method_name)) {
            try {
                $controller_instance->setRequestResponse($request, null, $controller_name, $method_name);
                return yield $controller_instance->$method_name($params);
            }catch (\Exception $e) {
                call_user_func([$controller_instance, 'onExceptionHandle'], $e);
            }
        }
    }
}

//字符串解密加密
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

    $ckey_length = 4;	// 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥

    $key = md5($key ? $key : get_instance()->config['auth_key']);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

/**
 * 发送邮件
 * @param $toemail 收件人email
 * @param $subject 邮件主题
 * @param $message 正文
 * @param $from 发件人
 * @param $cfg 邮件配置信息
 * @param $sitename 邮件站点名称
 */

function sendmail($toemail, $subject, $message, $sitename='') {
    $charset = get_instance()->config['charset'];
    $cfg = get_instance()->config->get('email');

    $from = $cfg['mail_from'];
    $mail_type = $cfg['mail_type']; //邮件发送模式
    if($cfg['mail_user']=='' || $cfg['mail_password'] ==''){
        return false;
    }
    $mail= Array (
        'mailsend' => 2,
        'maildelimiter' => 1,
        'mailusername' => 1,
        'server' => $cfg['mail_server'],
        'port' => $cfg['mail_port'],
        'auth' => $cfg['mail_auth'],
        'from' => $cfg['mail_from'],
        'auth_username' => $cfg['mail_user'],
        'auth_password' => $cfg['mail_password']
        );
    //mail 发送模式
    if($mail_type==0) {
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset='.$charset.'' . "\r\n";
        $headers .= 'From: '.$sitename.' <'.$from.'>' . "\r\n";
        mail($toemail, $subject, $message, $headers);
        return true;
    }
    //邮件头的分隔符
    $maildelimiter = $mail['maildelimiter'] == 1 ? "\r\n" : ($mail['maildelimiter'] == 2 ? "\r" : "\n");
    //收件人地址中包含用户名
    $mailusername = isset($mail['mailusername']) ? $mail['mailusername'] : 1;
    //端口
    $mail['port'] = $mail['port'] ? $mail['port'] : 25;
    $mail['mailsend'] = $mail['mailsend'] ? $mail['mailsend'] : 2;

    //发信者
    $email_from = $from == '' ? '=?'.$charset.'?B?'.base64_encode($sitename)."?= <".$from.">" : (preg_match('/^(.+?) \<(.+?)\>$/',$from, $mats) ? '=?'.$charset.'?B?'.base64_encode($mats[1])."?= <$mats[2]>" : $from);

    $email_to = preg_match('/^(.+?) \<(.+?)\>$/',$toemail, $mats) ? ($mailusername ? '=?'.$charset.'?B?'.base64_encode($mats[1])."?= <$mats[2]>" : $mats[2]) : $toemail;;

    $email_subject = '=?'.$charset.'?B?'.base64_encode(preg_replace("/[\r|\n]/", '', '['.$sitename.'] '.$subject)).'?=';
    $email_message = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));

    $headers = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: DZW {$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/html; charset=".$charset."{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";

    if(!$fp = fsockopen($mail['server'], $mail['port'], $errno, $errstr, 30)) {
        runlog('SMTP', "($mail[server]:$mail[port]) CONNECT - Unable to connect to the SMTP server", 0);
        return false;
    }
    stream_set_blocking($fp, true);

    $lastmessage = fgets($fp, 512);
    if(substr($lastmessage, 0, 3) != '220') {
        runlog('SMTP', "$mail[server]:$mail[port] CONNECT - $lastmessage", 0);
        return false;
    }

    fputs($fp, ($mail['auth'] ? 'EHLO' : 'HELO')." phpcms\r\n");
    $lastmessage = fgets($fp, 512);
    if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
        runlog('SMTP', "($mail[server]:$mail[port]) HELO/EHLO - $lastmessage", 0);
        return false;
    }

    while(1) {
        if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
            break;
        }
        $lastmessage = fgets($fp, 512);
    }

    if($mail['auth']) {
        fputs($fp, "AUTH LOGIN\r\n");
        $lastmessage = fgets($fp, 512);
        if(substr($lastmessage, 0, 3) != 334) {
            runlog('SMTP', "($mail[server]:$mail[port]) AUTH LOGIN - $lastmessage", 0);
            return false;
        }

        fputs($fp, base64_encode($mail['auth_username'])."\r\n");
        $lastmessage = fgets($fp, 512);
        if(substr($lastmessage, 0, 3) != 334) {
            runlog('SMTP', "($mail[server]:$mail[port]) USERNAME - $lastmessage", 0);
            return false;
        }

        fputs($fp, base64_encode($mail['auth_password'])."\r\n");
        $lastmessage = fgets($fp, 512);
        if(substr($lastmessage, 0, 3) != 235) {
            runlog('SMTP', "($mail[server]:$mail[port]) PASSWORD - $lastmessage", 0);
            return false;
        }

        $email_from = $mail['from'];
    }

    fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
    $lastmessage = fgets($fp, 512);
    if(substr($lastmessage, 0, 3) != 250) {
        fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
        $lastmessage = fgets($fp, 512);
        if(substr($lastmessage, 0, 3) != 250) {
            runlog('SMTP', "($mail[server]:$mail[port]) MAIL FROM - $lastmessage", 0);
            return false;
        }
    }

    fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail).">\r\n");
    $lastmessage = fgets($fp, 512);
    if(substr($lastmessage, 0, 3) != 250) {
        fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail).">\r\n");
        $lastmessage = fgets($fp, 512);
        runlog('SMTP', "($mail[server]:$mail[port]) RCPT TO - $lastmessage", 0);
        return false;
    }

    fputs($fp, "DATA\r\n");
    $lastmessage = fgets($fp, 512);
    if(substr($lastmessage, 0, 3) != 354) {
        runlog('SMTP', "($mail[server]:$mail[port]) DATA - $lastmessage", 0);
        return false;
    }

    $headers .= 'Message-ID: <'.gmdate('YmdHs').'.'.substr(md5($email_message.microtime()), 0, 6).rand(100000, 999999).'@'.get_instance()->config->get('http.domain').">{$maildelimiter}";

    fputs($fp, "Date: ".gmdate('r')."\r\n");
    fputs($fp, "To: ".$email_to."\r\n");
    fputs($fp, "Subject: ".$email_subject."\r\n");
    fputs($fp, $headers."\r\n");
    fputs($fp, "\r\n\r\n");
    fputs($fp, "$email_message\r\n.\r\n");
    $lastmessage = fgets($fp, 512);
    if(substr($lastmessage, 0, 3) != 250) {
        runlog('SMTP', "($mail[server]:$mail[port]) END - $lastmessage", 0);
    }
    fputs($fp, "QUIT\r\n");
    return true;
}

function runlog($type, $message, $p, $level=Logger::ERROR){
    get_instance()->log->addRecord($level, $type.' '.$message);
}

/**
 * 创建目录
 * @param string $dir
 * @param number $mode
 * @param string $recursive
 * @return boolean
 *
 * @author weihan
 * @datetime 2016年11月23日下午5:11:40
 */
function mkdirs($dir, $mode = 0777, $recursive = true) {
    if( is_null($dir) || $dir === "" ){
        return false;
    }
    if( is_dir($dir) || $dir === "/" ){
        return true;
    }
    if( mkdirs(dirname($dir), $mode, $recursive) ){
        return mkdir($dir, $mode);
    }
    return false;
}

/**
 * 删除目录下过期的文件
 * @param string $path
 *
 * @author weihan
 * @datetime 2016年11月23日下午5:45:52
 */
function fileUnlink($path){
    if (get_instance()->config->get('server.debug')){
        get_instance()->log->debug('执行fileUnlink '. $path);
    }
    $directoryIterator = new DirectoryIterator($path);
    foreach($directoryIterator as $info){
        if ($info->isDot()){
            continue;
        }
        if ($info->isDir()) {
            fileUnlink($info->getPathname());
        }
        if ($info->isFile() && $info->getMTime() < time()) {
            unlink($info->getPathname());
        }
    }
}

/**
 * 数据库表清理器
 * @param string $tbl_name
 *
 * @author weihan
 * @datetime 2016年11月24日下午2:12:12
 */
function tableCleaner($tbl_name){
    if (get_instance()->config->get('server.debug')){
        get_instance()->log->debug('执行tableCleaner '. $tbl_name);
    }
    
    get_instance()->mysql_pool->dbQueryBuilder->delete()->from($tbl_name)->where('expire', time(), Miner::LESS_THAN);
    get_instance()->mysql_pool->query(function (){});
}

/**
 * 获取邮箱登录地址
 * @param string $email_host
 * @return string
 *
 * @author weihan
 * @datetime 2016年11月25日上午10:41:35
 */
function emailAddress($email) {
    $email_arr = [
        'gmail.com' => 'https://mail.google.com/',
        '163.com' => 'http://mail.163.com/',
        '126.com' => 'http://mail.126.com/',
        'qq.com' => 'http://mail.qq.com/',
        'sina.com' => 'http://mail.sina.com/',
        'sohu.com' => 'http://mail.sohu.com/',
        'yahoo.com.cn' => 'http://mail.yahoo.com.cn/',
        'yahoo.com' => 'http://mail.yahoo.com/',
        'yahoo.cn' => 'http://mail.cn.yahoo.com/',
        '21.cn' => 'http://mail.21cn.com/',
        '139.com' => 'http://mail.139.com/',
        '263.net' => 'http://mail.263.net/',
    ];
    $email_host = '';
    $arr = explode('@', $email, 2);
    isset($arr[1]) && $email_host = $arr[1];
    return isset($email_arr[$email_host]) ? $email_arr[$email_host] : '';
}

/**
 * 安全过滤函数
 *
 * @param $string
 * @return string
 */
function safe_replace($string) {
    if (is_array($string)){
        foreach ($string as &$v){
            $v = safe_replace($v);
        }
        return $string;
    }
    $string = str_replace('%20','',$string);
    $string = str_replace('%27','',$string);
    $string = str_replace('%2527','',$string);
    $string = str_replace('*','',$string);
    $string = str_replace('"','&quot;',$string);
    $string = str_replace("'",'',$string);
    $string = str_replace('"','',$string);
    $string = str_replace(';','',$string);
    $string = str_replace('<','&lt;',$string);
    $string = str_replace('>','&gt;',$string);
    $string = str_replace("{",'',$string);
    $string = str_replace('}','',$string);
    $string = str_replace('\\','',$string);
    return $string;
}
/**
 * xss过滤函数
 *
 * @param $string
 * @return string
 */
function remove_xss($string) {
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);

    //$parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound');
    $parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

    $parm = array_merge($parm1, $parm2);

    for ($i = 0; $i < sizeof($parm); $i++) {
        $pattern = '/';
        for ($j = 0; $j < strlen($parm[$i]); $j++) {
            if ($j > 0) {
                $pattern .= '(';
                $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                $pattern .= '|(&#0([9][10][13]);?)?';
                $pattern .= ')?';
            }
            $pattern .= $parm[$i][$j];
        }
        $pattern .= '/i';
        $string = preg_replace($pattern, '', $string);
    }
    return $string;
}
function filter_illegal($val){
    if ($val){
        $val = XssClean::getXssClean()->xss_clean($val);
        $val = safe_replace($val);
    }
    return $val;
}

/**
 * 获取当前的编码，默认utf-8
 * @return mixed
 *
 * @author weihan
 * @datetime 2016年12月6日下午1:42:29
 */
function charset(){
    return get_instance()->config->get('charset','utf-8');
}

/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 */
function strCut($string, $length, $dot = '...') {
    if(function_exists('mb_strlen')){
        if (mb_strlen($string, 'utf-8') <= $length){
            return $string;
        }
        return mb_substr($string, 0, $length, 'utf-8'). $dot;
    }
    
    $strlen = strlen($string);
    if($strlen <= $length) return $string;
    //     $string = str_replace(array(' ','&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵',' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strcut = '';
    if(strtolower(charset()) == 'utf-8') {
        $length = intval($length-strlen($dot)-$length/3);
        $n = $tn = $noc = 0;
        while($n < strlen($string)) {
            $t = ord($string[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t <= 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $length) {
                break;
            }
        }
        if($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
        //         $strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
    } else {
        $dotlen = strlen($dot);
        $maxi = $length - $dotlen - 1;
        $current_str = '';
        $search_arr = array('&',' ', '"', "'", '“', '”', '—', '<', '>', '·', '…','∵');
        $replace_arr = array('&amp;','&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;',' ');
        $search_flip = array_flip($search_arr);
        for ($i = 0; $i < $maxi; $i++) {
            $current_str = ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
            if (in_array($current_str, $search_arr)) {
                $key = $search_flip[$current_str];
                $current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
            }
            $strcut .= $current_str;
        }
    }
    return $strcut.$dot;
}
/**
 * 获取字符串长度，一个汉字当一个
 * @param string $str
 * @param string $charset
 *
 * @author weihan
 * @datetime 2016年12月6日下午1:44:04
 */
function strLength($str, $charset='utf-8'){
    if(function_exists('mb_strlen')){
        return mb_strlen($str,'utf-8');
    }
    
    if($charset=='utf-8') $str = iconv('utf-8','gb2312',$str);
    $num = strlen($str);
    $cnNum = 0;
    for($i=0;$i<$num;$i++){
        if(ord(substr($str,$i+1,1))>127){
            $cnNum++;
            $i++;
        }
    }
    $enNum = $num-($cnNum*2);
    $number = $enNum+ $cnNum;
    return ceil($number);
}
/**
 * 得到头像地址
 * @param int $userid
 * @param string $size
 * @return string
 *
 * @author weihan
 * @datetime 2016年12月19日下午3:08:56
 */
function avatar($userid, $size='middel', $timestamp='') {
    return get_instance()->config['uc.UC_API']. "/avatar.php?uid=$userid&size=$size&f=$timestamp";
}

/**
 * 用户个人主页地址
 * @param int $userid
 * @return string
 *
 * @author weihan
 * @datetime 2016年12月22日下午1:32:55
 */
function userUrl($userid){
    return "";
}

/**
 * 获取显示名字
 * @param array $userinfo
 *
 * @author weihan
 * @datetime 2016年12月22日下午3:47:53
 */
function showname($userinfo){
    $showname = '';
    if ($userinfo){
        $showname = $userinfo['username'];
    }
    return $showname;
}

/**
 * 手机号隐藏中间
 * @param number $mobile
 *
 * @author weihan
 * @datetime 2016年12月23日下午4:51:53
 */
function mobileShow($mobile){
    return substr($mobile, 0, 3). '****'. substr($mobile, -4, 4);
}

/**
 * 邮箱隐藏部分
 * @param string $email
 * @return string
 *
 * @author weihan
 * @datetime 2016年12月23日下午4:53:20
 */
function emailShow($email){
    $arr = explode('@', $email);
    $pos = intval(strlen($arr[0])/2);
    return substr($arr[0], 0, $pos). str_repeat('*', strlen($arr[0])-$pos). '@'. $arr[1];
}