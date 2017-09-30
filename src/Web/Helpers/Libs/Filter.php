<?php
namespace Web\Helpers\Libs;
/**
 * 过滤类
 * 用于过滤过外部输入的数据，过滤数组或者变量中的不安全字符，以及HTML标签
 * 
 * @author weihan
 * @datetime 2017年1月6日上午10:24:46
 *
 */
class Filter
{
    CONST INT = 'INT';
    CONST STRING = 'STRING';
    CONST FLOAT = 'FLOAT';
    /**
     * @var 对应Validate中的ctype
     */
    CONST CTYPE = 'CTYPE';
    /**
     * 自定义正则
     */
    CONST REGX = 'REGX';
    /**
     * 过滤
     * @param string $var   
     * @param string $type 类型
     * @param string $default
     *
     * @author weihan
     * @datetime 2017年1月6日上午10:38:49
     */
    public static function filter($var, $type, $default='') {
        if (is_array($var)){
            return self::filterArray($var, $type, $default);
        }
        return self::filterVar($var, $type, $default);
    }
    
    /**
     * 类型转换
     * @param $var
     * @param $type
     * @return bool|float|int|string
     */
    private static function filterVar($var, $type, $default=''){
        //部分类型需要额外参数
        if (is_array($type)){
            $t = $type[0];
            $param = $type[1];
        }else {
            $t = $type;
        }
        switch($t){
            case self::INT:
                return intval($var) ? : intval($default);
            case self::STRING:
                return self::escape($var) ? : self::escape($default);
            case self::FLOAT:
                return floatval($var) ? : floatval($default);
            case self::CTYPE:
                return Validate::check($param, $var) ? : $default;
            case self::REGX:
                return Validate::regx($param, $var) ? : $default;
            default:
                return false;
        }
    }

    /**
     * 过滤数组
     * @param $array
     * @return array
     */
    private static function filterArray($array, $type, $default=''){
        $clean = array();
        foreach ($array as $key => $string){
            if (is_array($string)){
                self::filterArray($string, $type, $default);
            }else{
                $string = self::filterVar($string, $type, $default);
                $key = self::escape($key);
            }
            $clean[$key] = $string;
        }
        return $clean;
    }

    /**
     * 使输入的代码安全
     * @param $string
     * @return string
     */
    private static function escape($string){
        if (is_numeric($string)){
            return $string;
        }
        //HTML转义
        $string = htmlspecialchars($string, ENT_QUOTES, charset());
        return $string;
    }
}