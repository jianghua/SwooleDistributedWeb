<?php
namespace app\Helpers\Libs;

/**
 * 表单处理器
 * 用于生成HTML表单项
 * @author Tianfeng.Han
 * @package SwooleSystem
 * @subpackage HTML
 * @link http://www.swoole.com/
 *
 */
class Form
{
    static $checkbox_value_split = ',';
    static $default_help_option = '请选择';

    /**
     * 根据数组，生成表单
     * @param array $form_array
     * @param array $data   默认值
     * @return array
     *
     * @author weihan
     * @datetime 2016年11月18日上午11:23:04
     */
	static function autoform($form_array, $data)
	{
	    return [
	        'label' => self::autoLabel($form_array),   //表单名字
	        'tips' => self::autoTips($form_array),     //表单介绍
	        'input' => self::autoInput($form_array, $data),   //表单输入项
	        'js' => self::autoJs($form_array),         //formValidator验证js
	    ];
	    
	}
	
	/**
	 * 根据数组，生成表单
	 * @param array $form_array
	 * @param array $data  默认值
     * @return array
	 *
	 * @author weihan
	 * @datetime 2016年11月18日上午11:23:27
	 */
	static function autoInput($form_array, $data){
	    $forms = array();
	    foreach($form_array as $k=>$v)
	    {
	        //去掉
	        if (isset($v['label']))unset($v['label']);
	        if (isset($v['tips']))unset($v['tips']);
	        //去掉表单验证部分，
	        if (isset($v['validates']))unset($v['validates']);
	        //表单类型
	        $func = $v['type'];
	        //默认值
	        if (isset($data[$k])){
	            $v['value'] = $data[$k]; 
	        }
	        //表单值
	        $value = $v['value'] ?? '';
	        unset($v['type'],$v['value']);
	    
	        if(in_array($func, ['input', 'password', 'text', 'checkcode', 'upload', 'multi_upload', 'cascade_select', 'editor']))
	        {
	            $forms[$k] = self::$func($k,$value,$v, $data);
	        }
	        else
	        {
	            $option = $v['option'] ?? [];
	            $self = $v['self'] ?? false;
	            $label_class = $v['label_class'] ?? '';
	            unset($v['option'],$v['self'],$v['label_class']);
	            $forms[$k] = self::$func($k,$option,$value,$self,$v,$label_class);
// 	            if($func=='radio' and isset($v['empty']))
// 	                $forms[$k].= "\n<script language='javascript'>add_filter('{$k}','{$v['empty']}',function(){return getRadioValue('{$k}');});</script>";
// 	                elseif($func=='checkbox' and isset($v['empty']))
// 	                $forms[$k].= "\n<script language='javascript'>add_filter('{$k}[]','{$v['empty']}',function(){return getCheckboxValue('{$k}[]');});</script>";
	        }
	        //
	        if (!is_array($forms[$k])){
	            $forms[$k] = ['input'=>$forms[$k]];
	        }
	        $forms[$k]['tips_container'] = "<span id=\"{$k}Tip\" class=\"tip\"></span>";
	        //表单类型
	        $forms[$k]['type'] = $func;
	        //表单值
	        $forms[$k]['value'] = $forms[$k]['value'] ?? $value;
	    }
	    return $forms;
	}
	
	/**
	 * 根据数组，生成表单验证js
	 * @param array $form_array
     * @return array
	 *
	 * @author weihan
	 * @datetime 2016年11月18日上午11:23:41
	 */
	static function autoJs($form_array){
	    $forms = array();
	    //对于一些特殊的类型进行处理
	    foreach($form_array as $k=>$v)
	    {
	        //级联选择
	        if ($v['type'] == 'cascade_select'){
	            foreach ($v['selects'] as $_k=>$_v){
	                $_v['validates']['tipID'] = $k.'Tip';
	                $form_array[$_k] = $_v;
	            }
	        }
	    }
	    foreach($form_array as $k=>$v)
	    {
	        //表单验证部分，
	        if (isset($v['validates']) && $validates = $v['validates']){
    	        $validates_str_pre = ".formValidator({
        	                    empty: true
        	                })";
    	        $min = $max = $validates_str= '';
    	        $tipID = isset($validates['tipID']) && $validates['tipID'] ? "tipID:'{$validates['tipID']}'," : '';
    	        foreach ($validates as $validate=>$rule){
    	            // 为空的情况 -required
    	            if($validate == 'required')
    	            {
    	                if (!empty($rule)){
    	                    $validates_str_pre = ".formValidator({
    	                    {$tipID}
    	                    empty: false,
    	                    onEmpty: \"{$rule}\",
    	                    onFocus: \"{$rule}\"
    	                    })";
    	                }
    	                
    	            }
    	            //检测字符串最大长度
    	            if($validate == 'maxlen')
    	            {
    	                $qs = explode('|',$rule);
    	                $max = $qs[0];
    	                $max_msg = $qs[1];
    	            }
    	            //检测字符串最小长度
    	            if($validate == 'minlen')
    	            {
    	                $qs = explode('|',$rule);
    	                $min = $qs[0];
    	                $min_msg = $qs[1];
    	            }
    	            
    	            //检查对象相等的情况 -equalo
    	            if($validate == 'equalo')
    	            {
    	                $qs = explode('|',$rule);
    	                $validates_str .= ".compareValidator({
        					desID: \"{$qs[0]}\",
        					operateor: \"=\",
        					onError: \"{$qs[1]}\"
        				})";
    	            }
    	            //检查对象相等的情况 -equalo
    	            if($validate == 'ctype')
    	            {
    	                $qs = explode('|',$rule);
    	                if($regx = Validate::getRegx($qs[0], true))
    	                {
    	                    $regx = substr($regx, 1, strrpos($regx, '/')-1);
    	                    $regx = addslashes($regx);
    	                    $validates_str .= ".regexValidator({
            					regExp: \"{$regx}\",
            					onError: \"{$qs[1]}\"
            				})";
    	                }
    	            }
    	            //检查值的类型 -regx，自定义正则检查
    	            if($validate == 'regx')
    	            {
    	                $qs = explode('|',$rule);
    	                $regx = $qs[0];
    	                $regx = substr($regx, 1, strrpos($regx, '/'));
    	                $regx = addslashes($regx);
    	                $validates_str .= ".regexValidator({
        	                regExp: \"{$regx}\",
        	                onError: \"{$qs[1]}\"
    	                })";
    	            }
    	            
    	            //ajax
    	            if ($validate == 'ajax'){
    	                $rule['onWait'] = $rule['onWait'] ?? '验证中...';
    	                $validates_str .= ".ajaxValidator({
                    	    type : \"{$rule['type']}\",
                    		url : \"".url($rule['url'])."\",
                    		data :\"{$rule['data']}\",
                    		datatype : \"html\",
                    		async:'false',
                    		success : function(data){	
                                if(data == 1)
                    			{
                                    return true;
                    			}
                                else
                    			{
                                    return false;
                    			}
                    		},
                    		buttons: {$rule['buttons']},
                    		onError : \"{$rule['onError']}\",
                    		onWait : \"{$rule['onWait']}\"
                    	})";
    	            }
    	        }
    	        
    	        if ($min || $max) {
    	            $validates_str_pre .= ".inputValidator({";
    	            $has_min = false;
    	            if ($min){
    	                $has_min = true;
    	                $validates_str_pre .= "min: {$min},onErrorMin: '{$min_msg}'";
    	            }
    	            if ($max){
    	                $has_min && $validates_str_pre .= ',';
    	                $validates_str_pre .= "max: {$max},onErrorMax: '{$max_msg}'";
    	            }
	                $validates_str_pre .= "})";
    	        }
    	        $validates_str = $validates_str_pre. $validates_str;
    	        if ($validates_str){
    	            $forms[$k] = "$(\"#{$k}\"){$validates_str};";
    	        }
    	        
	        }
	    }
	    return $forms;
	}
	
	/**
	 * 表单名字
	 * @param array $form_array
	 *
	 * @author weihan
	 * @datetime 2016年11月21日下午3:37:59
	 */
	static function autoLabel($form_array){
	    $forms = array();
	    foreach($form_array as $k=>$v)
	    {
            $forms[$k] = $v['label'] ?? '';
	    }
	    return $forms;
	}
	
	/**
	 * 表单项提示语
	 * @param array $form_array
	 *
	 * @author weihan
	 * @datetime 2016年11月21日下午4:28:31
	 */
	static function autoTips($form_array){
	    $forms = array();
	    foreach($form_array as $k=>$v)
	    {
            $forms[$k] = $v['tips'] ?? '';
	    }
	    return $forms;
	}
	
	/**
	 * 检查表单项
	 * @param \swoole_http_request $request
	 * @param array $input 提交的表单项
	 * @param unknown $form    表单form，以及验证规则
	 * @param unknown $error   错误信息
	 * @param array $form_val  验证后的表单项，以及对应的input值
	 *
	 * @author weihan
	 * @datetime 2016年12月6日上午10:23:06
	 */
	static function checkInput(\swoole_http_request $request, $input, $form, &$error, &$form_val=[])
	{
	    //特殊类型进行处理，
	    foreach($form as $name=>$v)
	    {
            $type = $v['type'];
            //多图上传
            if ($type == 'multi_upload'){
                foreach ($input as $_p=>$_v){
                    //以name开头，并且数字结尾
                    if (strpos($_p, $name) !== false && is_numeric(str_replace($name, '', $_p))) {
                        $input[$name][] = $_v;
                        unset($input[$_p]);
                    }
                }
                //过滤掉重复的，并且重置键
                $input[$name] = array_values(array_filter($input[$name])) ? : '';
            }
            //级联选择
            if ($type == 'cascade_select'){
                foreach ($v['selects'] as $_k=>$_v){
                    $form[$_k] = $_v;
                }
                unset($form[$name]);
            }
	    }
	    
	    foreach($form as $name=>$v)
	    {
	        $value = $input[$name]??'';
	        $f = $v['validates'];
        	// 为空的情况 -required
        	if(isset($f['required']) and empty($value))
        	{
        	    $error = $f['required'];
        	    return false;
        	}
        	//为空的情况，并且允许为空，不再往下判断
        	if(!isset($f['required']) and empty($value))
        	{
        	    $form_val[$name] = $value;
        	    continue;
        	}
        	//检测字符串最大长度
        	if(isset($f['maxlen']))
        	{
        	    $qs = explode('|',$f['maxlen']);
        	    if(strLength($value)>$qs[0])
        	    {
        	        $error = $qs[1];
        		    return false;
        	    }
        	}
	        //检测字符串最小长度
        	if(isset($f['minlen']))
        	{
        	    $qs = explode('|',$f['minlen']);
        	    if(strLength($value)<$qs[0])
        	    {
        	        $error = $qs[1];
        		    return false;
        	    }
        	}
	        //检查数值相等的情况 -equal
        	if(isset($f['equal']))
        	{
        	    $qs = explode('|',$f['equal']);
        	    if($value!=$qs[0])
        	    {
        	        $error = $qs[1];
        		    return false;
        	    }
        	}
	        //检查数值相等的情况 -noequal
        	if(isset($f['noequal']))
        	{
        	    $qs = explode('|',$f['noequal']);
        	    if($value==$qs[0])
        	    {
        	        $error = $qs[1];
        		    return false;
        	    }
        	}
	        //检查对象相等的情况 -equalo
        	if(isset($f['equalo']))
        	{
        	    $qs = explode('|',$f['equalo']);
        	    if($value!=$input[$qs[0]])
        	    {
        	        $error = $qs[1];
        		    return false;
        	    }
        	}
        	//检查对象不相等的情况 -noequalo
        	if(isset($f['noequalo']))
        	{
        	    $qs = explode('|',$f['noequalo']);
        	    if($value==$input[$qs[0]])
        	    {
        	        $error = $qs[1];
        	        return false;
        	    }
        	}
	        //检查对象相等的情况 -equalo
        	if(isset($f['ctype']))
        	{
        	    $qs = explode('|',$f['ctype']);
        	    if (is_array($value)){
            	    foreach ($value as $_vv)
            	    {
            	       if(!Validate::check($qs[0],$_vv))
                	    {
                	        $error = $qs[1];
                		    return false;
                	    }
            	    }
        	    }else {
        	        if(!Validate::check($qs[0],$value))
        	        {
        	            $error = $qs[1];
        	            return false;
        	        }
        	    }
        	}
	        //检查值的类型 -regx，自定义正则检查
        	if(isset($f['regx']))
        	{
        	    $qs = explode('|',$f['regx']);
        	    if (is_array($value)){
            	    foreach ($value as $_vv)
            	    {
                	    if(!Validate::regx($qs[0],$_vv))
                	    {
                	        $error = $qs[1];
                		    return false;
                	    }
            	    }
        	    }else {
        	        if(!Validate::regx($qs[0],$value))
        	        {
        	            $error = $qs[1];
        	            return false;
        	        }
        	    }
        	}
        	//ajax
        	if (isset($f['ajax'])){
        	    $uri = $f['ajax']['url'];
        	    $uri = trim($uri, '/');
        	    $uri_arr = explode('/', $uri);
        	    $params = ['email'=>$value, 'is_return'=>true];
        	    //
        	    $result = yield execControllerMethod($uri_arr[0], $uri_arr[1], $params, $request);
        	    if ($result == 0) {
        	        $error = $f['ajax']['onError'];
        	        return false;
        	    }
        	}
        	$_value = is_array($value) ? json_encode($value) : $value;
        	$form_val[$name] = $_value;
	    }
        return true;
	}
	/**
	 * 元素选项处理
	 * @param $attr
	 * @return unknown_type
	 */
	static function input_attr(&$attr)
	{
	    $str = " ";
        if(!empty($attr) && is_array($attr))
        {
            foreach($attr as $key=>$value)
            {
                $str .= "$key=\"$value\" ";
            }
        }
        return $str;
	}
	/**
     * 下拉选择菜单
     * $name  此select 的 name 标签
     * $array 要制作select 的数
     * $default 如果要设定默认选择哪个数据 就在此填入默认的数据的值
     * $self 设置为true，option的值等于$value
     * $attrArray html标签的熟悉  就是这个select的属性标签 例如  class="x1"
     * $add_help 增加一个值为空的 请选择 项
     */
    static function select($name, $option, $default = null, $self = null, $attrArray = null, $add_help = true)
	{
		$htmlStr = "<select name=\"$name\" id=\"$name\"";
		$htmlStr .= self::input_attr($attrArray) . ">\n";

        if ($add_help) {
            if ($add_help === true) {
                $htmlStr .= "<option value=\"\">" . self::$default_help_option . "</option>\n";
            } else {
                $htmlStr .= "<option value=\"\">$add_help</option>\n";
            }
        }
        foreach ($option as $key => $value) {
            if ($self) {
                $key = $value;
            }
            if ($key == $default) {
                $htmlStr .= "<option value=\"{$key}\" selected=\"selected\">{$value}</option>\n";
            } else {
                $htmlStr .= "<option value=\"{$key}\">{$value}</option>\n";
            }
        }
        $htmlStr .= "</select>\n";

		return $htmlStr;
	}

	/**
	 * 多选下拉选择菜单
	 * $name  此select 的 name 标签
	 * $array 要制作select 的数
	 * $default 如果要设定默认选择哪个数据 就在此填入默认的数据的值
	 * $self 设置为ture，option的值等于$value
	 * $attrArray html标签的熟悉  就是这个select的属性标签 例如  class="x1"
	 * $add_help 增加一个值为空的 请选择 项
	 */
	static function muti_select($name,$option,$default=array(),$self=null,$attrArray=null,$add_help=true)
	{
		$htmlStr = "<select name=\"$name\" id=\"$name\"";
		$htmlStr .= self::input_attr($attrArray) . ">\n";

		if($add_help)
		{
			if($add_help===true)
				$htmlStr .= "<option value=\"\">".self::$default_help_option."</option>\n";
			else $htmlStr .= "<option value=\"\">$add_help</option>\n";
		}
		foreach($option as $key => $value)
		{
			if($self) $key=$value;
			if (in_array($key,$default))
			{
				$htmlStr .= "<option value=\"{$key}\" selected=\"selected\">{$value}</option>\n";
			}
			else
			{
				$htmlStr .= "<option value=\"{$key}\">{$value}</option>\n";
			}
		}
		$htmlStr .= "</select>\n";

		return $htmlStr;
	}

	/**
	 * 单选按钮
	 *	$name  此radio 的 name 标签
	 *	$array 要制作radio 的数
	 *	$default 如果要设定默认选择哪个数据 就在此填入默认的数据的值
	 *	$self 设置为ture，option的值等于$value
	 *	$attrArray html的属性 例如  class="x1"
     **/
    static function radio($name, $option, $default = null, $self = false, $attrArray = null, $label_class = '')
	{
		$htmlStr = "";
	    $attrStr = self::input_attr($attrArray);

		foreach($option as $key => $value)
		{
			if($self) $key=$value;
			if ($key == $default)
			{
				$htmlStr .= "<label class='$label_class'><input type=\"radio\" name=\"$name\" id=\"{$name}_{$key}\" value=\"$key\" checked=\"checked\" {$attrStr} />".$value."</label>";
			}
			else
			{
				$htmlStr .= "<label class='$label_class'><input type=\"radio\" name=\"$name\" id=\"{$name}_{$key}\" value=\"$key\"  {$attrStr} />&nbsp;".$value."</label>";
			}
		}
		return $htmlStr;
	}
	/**
	 * 多选按钮
	 * @param string $name  此radio 的 name 标签
	 * @param array $option 要制作radio 的数
	 * @param string $default 如果要设定默认选择哪个数据 就在此填入默认的数据的值
	 * @param bool $self 设置为ture，option的值等于$value
	 * @param array $attrArray html的属性 例如  class="x1"
	 * @param string $label_class
	 * @return string
	 */
	static function checkbox($name, $option, $default = null, $self = false, $attrArray = null, $label_class = '')
	{
		$htmlStr = "";
		$attrStr = self::input_attr($attrArray);
		$default = array_flip(explode(self::$checkbox_value_split, $default));

		foreach ($option as $key => $value)
		{
			if ($self)
			{
				$key = $value;
			}
			if (isset($default[$key]))
			{
				$htmlStr
					.=
					"<label class='$label_class'><input type=\"checkbox\" name=\"{$name}[]\" id=\"{$name}_$key\" value=\"$key\" checked=\"checked\" {$attrStr} />"
					. $value . '</label>';
			}
			else
			{
				$htmlStr
					.=
					"<label class='$label_class'><input type=\"checkbox\" name=\"{$name}[]\" id=\"{$name}_$key\" value=\"$key\"  {$attrStr} />"
					. $value . '</label>';
			}
		}
		return $htmlStr;
	}

    /**
     * 文件上传表单
     * @param $name 表单名称
     * @param $value 值
     * @param $attrArray html的属性 例如  class="x1"
     * @return unknown_type
     */
    static function upload($name, $value = '', $attrArray = null, $data=[])
    {
        $ret['value'] = $value = $value ?? '';
    	
    	$file_name = $name. 'File';
    	$img_name = $name. 'Img';
    	$width = $attrArray['upload']['width'] ?? 0;
    	$height = $attrArray['upload']['height'] ?? 0;
    	
    	$file_type = $attrArray['upload']['type'] ?? '';
    	switch($file_type){
    	    case 'image': $upload_uri = 'ajax/image';break;
    	    default: $upload_uri = 'ajax/image';
    	}
    	$upload_maxsize= get_instance()->config->get('upload_maxsize');
    	
    	$form = "<input type='hidden' name='$name' id='$name' value='$value'>";
    	$form .= '<script src="'.url('js/jquery.ajaxfileupload.js').'" type="text/javascript"></script>';
    	$form .= "<script type=\"text/javascript\">
    	       function {$file_name}AjaxFileUpload(){
    	            $('#{$img_name}').attr('src', '".url('/images/loading.gif')."');
        	        $.ajaxFileUpload({
                        url: '".url($upload_uri)."?r='+Math.random(), //用于文件上传的服务器端请求地址
                        secureuri: false, //是否需要安全协议，一般设置为false
                        fileElementId: '{$file_name}', //文件上传域的ID
                        maxFileSize: {$upload_maxsize},
                        maxFileSizeErr: '文件太大',
                        data: {name: '{$file_name}', height: {$height}, width: {$width}},
                        dataType: 'text', //返回值类型 
                        success: function (data, status)  //服务器成功响应处理函数
                        {
                            data = jQuery.parseJSON(data);
                            if (data.status == 1){
                                $('#{$name}').val(data.url);
                                $('#{$img_name}').attr('src', data.url);
                                {$attrArray['upload']['succ_js']}
                            }else{
                                alert(data.msg);
                            }
                        },
                        error: function (data, status, e)//服务器响应失败处理函数
                        {
                            alert(e);
                        }
                    });
                };
                </script>";
        unset($attrArray['upload']);
        $attrStr = self::input_attr($attrArray);
        $form .= "<img id='$img_name' src='".($value ? : url('/images/add-photo.gif'))."'>";
        $form .= "<input type='file' name='$file_name' id='{$file_name}' {$attrStr} onchange=\"{$file_name}AjaxFileUpload();\"/>";
        $ret['upload'] = $form;
        return $ret;
    }
    
    /**
     * 多文件上传表单
     * @param $name 表单名称
     * @param $value 值
     * @param $attrArray html的属性 例如  class="x1"
     * @return unknown_type
     */
    static function multi_upload($name, $value = [], $attrArray = null, $data=[])
    {
        $ret['value'] = $value ?? [];
        if ($ret['value']){
            $ret['value'] = json_decode($ret['value'], true);
        }
        if (!is_array($ret['value'])){
            $ret['value'] = [$ret['value']];
        }
         
        $file_name = $name. 'File';
        $width = $attrArray['upload']['width'] ?? 0;
        $height = $attrArray['upload']['height'] ?? 0;
         
        $file_type = $attrArray['upload']['type'] ?? '';
        switch($file_type){
            case 'image': $upload_uri = 'ajax/image';break;
            default: $upload_uri = 'ajax/image';
        }
        //最多上传几张图片
        $max_nums = $file_type = $attrArray['upload']['max_nums'] ?? 1;
         
        $form = '';
        for ($i=0; $i<=$max_nums; $i++){
            $_value = $ret['value'][$i] ?? '';
    	   $form .= "<input type='hidden' name='{$name}{$i}' id='{$name}{$i}' value='{$_value}'>";
        }
        $input_hidden_str = "$('#{$name}'+{$file_name}_num).val(data.url);";
        //已上传数量
        $num = max(0, max(array_keys($ret['value']))+1);
        $upload_maxsize= get_instance()->config->get('upload_maxsize');
        
        $form .= '<script src="'.url('js/jquery.ajaxfileupload.js').'" type="text/javascript"></script>';
        $form .= "<script type=\"text/javascript\">
            var {$file_name}_max_nums = {$max_nums};
            var {$file_name}_num = {$num};
            function {$file_name}AjaxFileUpload(){
                if ({$file_name}_num >= {$file_name}_max_nums){
                    alert('最多上传{$max_nums}张！');
                    return; 
                }
                $('#{$file_name}_photo_show').append('<div id=\"{$file_name}_loading\" class=\"delete\"><div><img src=\"".url('/images/loading.gif')."\"></div><a id=\"'+{$file_name}_num+'\" class=\"hide\"></a></div>');
                $.ajaxFileUpload({
                    url: '".url($upload_uri)."?r='+Math.random(), //用于文件上传的服务器端请求地址
                    secureuri: false, //是否需要安全协议，一般设置为false
                    fileElementId: '{$file_name}', //文件上传域的ID
                    maxFileSize: {$upload_maxsize},
                    maxFileSizeErr: '文件太大',
                    data: {name: '{$file_name}', height: {$height}, width: {$width}},
                    dataType: 'text', //返回值类型 
                    success: function (data, status)  //服务器成功响应处理函数
                    {
                        $('#{$file_name}_loading').remove();
                        data = jQuery.parseJSON(data);
                        if (data.status == 1){
                            {$input_hidden_str}
                            $('#{$file_name}_photo_show').append('<div class=\"delete\"><div><img src=\"'+data.url+'\"></div><a id=\"'+{$file_name}_num+'\" class=\"hide\"></a></div>');
                            {$attrArray['upload']['succ_js']}
                            {$file_name}_num += 1;
                        }else{
                            alert(data.msg);
                        }
                    },
                    error: function (data, status, e)//服务器响应失败处理函数
                    {
                        $('#{$file_name}_loading').remove();
                        alert(e);
                    }
                });
            };
            $(function(){
                $(\"#{$file_name}_photo_show\").off(\"mouseenter\", \"div\").on(\"mouseenter\", \"div\", function() {
                    var that = this;
                    var dom = $(that).children(\"a\");
                    dom.removeClass(\"hide\");
                    dom.off(\"click\");
                    dom.on(\"click\", function() {
                        {$file_name}_max_nums += 1;
                        var _id = $(this).attr('id');
                        $('#{$name}'+_id).val('');
                        var _input = $('<input type=\"hidden\" name=\"{$name}'+{$file_name}_max_nums+'\" id=\"{$name}'+{$file_name}_max_nums+'\">');
                        _input.appendTo($(\"#{$file_name}_photo_show\"));
                        dom.parent().remove();
                    });
                }).off(\"mouseleave\", \"div\").on(\"mouseleave\", \"div\", function() {
                    var that = this;
                    $(that).children(\"a\").addClass(\"hide\");
                });
         });
        </script>";
    unset($attrArray['upload']);
    $attrStr = self::input_attr($attrArray);
    $form .= "<input type='file' style='height:40px' name='$file_name' id='{$file_name}' {$attrStr} onchange=\"{$file_name}AjaxFileUpload();\"/>";
    $form .= "<div id=\"{$file_name}_photo_show\" class=\"img-cont\">";
    if ($ret['value']){
        foreach ($ret['value'] as $_k=>$_img){
            if ($_img){
                $form .= '<div class="delete"><div><img src="'.$_img.'"></div><a id="'.($_k).'" class="hide"></a></div>';
            }
        }
    }
    $form .= "</div>";
    $ret['upload'] = $form;
    return $ret;
    }
    
    
    /**
     * 单行文本输入框
     * @param $name
     * @param $value
     * @param $attrArray
     * @return string
     */
    static function input($name, $value = '', $attrArray = null, $data=[])
	{
		$attrStr = self::input_attr($attrArray);
		return "<input type='text' name='{$name}' id='{$name}' value='{$value}' {$attrStr} />";
	}
	/**
     * 按钮
     * @param $name
     * @param $value
     * @param $attrArray
     * @return unknown_type
     */
	static function button($name,$value='',$attrArray=null, $data=[])
	{
		if(empty($attrArray['type'])) $attrArray['type'] = 'button';
	    $attrStr = self::input_attr($attrArray);
		return "<input name='{$name}' id='{$name}' value='{$value}' {$attrStr} />";
	}
	/**
	 * 密码输入框
	 * @param $name
	 * @param $value
	 * @param $attrArray
	 * @return unknown_type
	 */
    static function password($name,$value='',$attrArray=null, $data=[])
	{
		$attrStr = self::input_attr($attrArray);
		return "<input type='password' name='{$name}' id='{$name}' value='{$value}' {$attrStr} />";
	}
	/**
	 * 多行文本输入框
	 * @param $name
	 * @param $value
	 * @param $attrArray
	 * @return string
	 */
    static function text($name,$value='',$attrArray=null, $data=[])
	{
		if(!isset($attrArray['cols'])) $attrArray['cols'] = 60;
		if(!isset($attrArray['rows'])) $attrArray['rows'] = 3;
		$attrStr = self::input_attr($attrArray);
		$forms = "<textarea name='{$name}' id='{$name}' $attrStr>$value</textarea>";
		return $forms;
	}

	/**
     * 隐藏项
     * @param $name
     * @param $value
     * @param $attrArray
     * @return string
     */
	static function hidden($name,$attrArray=null, $value='',$data=[])
	{
	    $attrStr = self::input_attr($attrArray);
		return "<input type='hidden' name='{$name}' id='{$name}' value='{$value}' {$attrStr} />";
	}

	/**
	 * 表单头部
	 * @param $form_name
	 * @param $method
	 * @param $action
	 * @param $if_upload
	 * @param $attrArray
	 * @return string
	 */
	static function head($form_name,$method='post',$action='',$if_upload=false,$attrArray=null)
	{
	    if($if_upload) $attrArray['enctype'] = "multipart/form-data";
	    $attrStr = self::input_attr($attrArray);
	    return "action='$action' method='$method' name='$form_name' id='$form_name' $attrStr";
	}
	/**
	 * 设置Form Secret防止，非当前页面提交数据
	 * @param $length
	 * @return string
	 */
    static function secret($length = 32)
    {
        return uniqid(RandomKey::string($length));
    }
    
    /**
     * 验证码
     * @param string $name
     * @param string $value
     * @param array $attrArray
     * @return string[]
     *
     * @author weihan
     * @datetime 2016年11月22日上午10:14:44
     */
    static function checkcode($name, $value='', $attrArray=[], $data=[]) {
        $ret = [];
        $ret['input'] = self::input($name, $value, $attrArray);
        
        $code_len = $attrArray['code_len']??4;
        $font_size = $attrArray['font_size']??15;
        $width = $attrArray['width']??100;
        $height = $attrArray['height']??30;
        $font = $attrArray['font']??'';
        $font_color = $attrArray['font_color']??'';
        $background = $attrArray['background']??'';
        $ret['img'] = "<img id='{$name}img' onclick='this.src=this.src+\"&\"+Math.random()' src='".url('pub/checkcode')."?code_len=$code_len&font_size=$font_size&width=$width&height=$height&font_color=".urlencode($font_color)."&background=".urlencode($background)."'>";
        return $ret;
    }
    
    /**
     * 级联select
     * @param string $name  无用
     * @param string $value 无用
     * @param array $attrArray
     * @param array $data   默认值
     *
     * @author weihan
     * @datetime 2016年12月2日下午3:16:13
     */
    static function cascade_select($name, $value = '', $attrArray = null, $data=[]){
        $ret = [];
        $selects_classname = [];
        //select多级配置
        $selects_configs = $attrArray['selects'];
        foreach ($selects_configs as $_name=>$configs){
            //默认值
            if (isset($data[$_name])){
                $configs['data-value'] = $data[$_name];
                $ret['value'][$_name] = $data[$_name];
            }
            
            unset($configs['validates']);
            $data_url = $configs['data-url'];
            if (is_array($data_url)){
                $data_url = url($data_url[0], $data_url[1]);
            }else {
                $data_url = url($data_url);
            }
            $configs['data-url'] = $data_url;
            
            $selects_classname[] = $configs['class'];
            $attrStr = self::input_attr($configs);
            $ret[$_name] = "<select id=\"{$_name}\" name=\"{$_name}\" {$attrStr}></select>";
        }
        
        $ret['js'] = '<script src="'.url('js/jquery.cxselect.min.js').'" type="text/javascript"></script>';
        $cxSelect_str = "selects: ". json_encode($selects_classname). ",jsonName: '{$attrArray['jsonName']}',jsonValue: '{$attrArray['jsonValue']}'"; 
        $ret['js'] .= '<script type="text/javascript">$(function(){$("#'.$name.'").cxSelect({'.$cxSelect_str.'});});</script>';
        return $ret;
    }
    
    /**
     * 编辑器
     * @param unknown $name
     * @param string $value
     * @param unknown $attrArray
     *
     * @author weihan
     * @datetime 2016年12月7日下午2:48:24
     */
    static function editor($name, $value = '', $attrArray = null, $data=[]) {
        $textareaid = 'content';
        $toolbar = 'full';
        $module = '';
        $catid = '';
        $color = '';
        $allowupload = 1;
        $allowbrowser = 0;
        $alowuploadexts = '';
        $height = 400;
        $disabled_page = 0;
        $allowuploadnum = '10';
        
        $str ='<textarea name="'.$name.'" id="'.$name.'" boxid="'.$name.'">'.$value.'</textarea>';
		$str .= '<script type="text/javascript" src="'.url('js/ckeditor/ckeditor.js').'"></script>';
		if($toolbar == 'basic') {
			$toolbar = "['Source', 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink' ],['Maximize'],\r\n";
		} elseif($toolbar == 'full') {
			$toolbar = "['Source',";
		    $toolbar .= "'-','Templates'],
		    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print'],
		    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],['ShowBlocks'],['Image','Capture','Flash','MyVideo'],['Maximize'],['autoformat'],
		    '/',
		    ['Bold','Italic','Underline','Strike','-'],
		    ['Subscript','Superscript','-'],
		    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
		    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		    ['Link','Unlink','Anchor'],
		    ['Table','HorizontalRule','Smiley','SpecialChar','PageBreak']";
		    /* $toolbar .= "'/',
		    ['Styles','Format','Font','FontSize'],
		    ['TextColor','BGColor'],
		    ['attachment']\r\n"; */
		} elseif($toolbar == 'desc') {
			$toolbar = "['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'Image', '-','Source'],['Maximize'],\r\n";
		} else {
			$toolbar = '';
		}
		$str .= "<script type=\"text/javascript\">\r\n";
		$str .= "var editor=CKEDITOR.replace( '$textareaid',{";
		$str .= "height:{$height},";
	
		$show_page = ($module == 'content' && !$disabled_page) ? 'true' : 'false';
		$str .="pages:$show_page,subtitle:$show_page,textareaid:'".$textareaid."',module:'".$module."',catid:'".$catid."',\r\n";
		if($allowupload) {
			$authkey = '';
			$str .="flashupload:true,alowuploadexts:'".$alowuploadexts."',allowbrowser:'".$allowbrowser."',allowuploadnum:'".$allowuploadnum."',authkey:'".$authkey."',\r\n";
		}
		//ssq todo APP_PATH
        if($allowupload) $str .= "filebrowserUploadUrl : '".url('ajax/ckeditorImage')."',\r\n";
		if($color) {
			$str .= "extraPlugins : 'uicolor',uiColor: '$color',";
		}
		$str .= "toolbar :\r\n";
		$str .= "[\r\n";
		$str .= $toolbar;
		$str .= "]\r\n";
		//$str .= "fullPage : true";
		$str .= "});\r\n";
		$str .= '</script>';
// 		if(is_ie()) $ext_str .= "<div style='display:none'><OBJECT id='PC_Capture' classid='clsid:021E8C6F-52D4-42F2-9B36-BCFBAD3A0DE4'><PARAM NAME='_Version' VALUE='0'><PARAM NAME='_ExtentX' VALUE='0'><PARAM NAME='_ExtentY' VALUE='0'><PARAM NAME='_StockProps' VALUE='0'></OBJECT></div>";
// 		$str .= $ext_str;
		return $str;
    }
}