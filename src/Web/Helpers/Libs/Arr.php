<?php
namespace Web\Helpers\Libs;
/**
 * 数组相关
 * @author weihan
 * @datetime 2016年12月20日上午10:03:56
 *
 */
class Arr
{
    /**
     * 二维数组，针对特定的列求和
     * @param [] $arr
     * @param string $sub_key
     *
     * @author weihan
     * @datetime 2016年12月20日上午10:05:57
     */
    public static function sum($arr, $sub_key) {
        $sum = 0;
        if ($arr && is_array($arr)) {
            foreach($arr as $v){
                if (isset($v[$sub_key])) {
                    $sum += intval($v[$sub_key]);
                }
            }
        }
        return $sum;
    }
    

    /**
     * 合并两个多维数组
     * @param [] $arr1
     * @param [] $arr2
     * @return []
     *
     * @author weihan
     * @datetime 2018年3月19日下午3:16:44
     */
    public static function arrayMergeDimensional($arr1, $arr2){
        $new_arr = array();
        foreach ($arr1 as $k=>$v){
            if (is_array($v) && isset($arr2[$k])) {
                $v = self::arrayMergeDimensional($v, $arr2[$k]);
            }else {
                if (!is_numeric($k) && isset($arr2[$k])) {
                    $v = $arr2[$k];
                }
            }
            if (isset($arr2[$k])) {
                unset($arr2[$k]);
            }
            $new_arr[$k] = $v;
        }
        if ($arr2) {
            $new_arr = array_merge($new_arr, $arr2);
        }
        return $new_arr;
    }
}
