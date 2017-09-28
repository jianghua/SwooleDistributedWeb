<?php
namespace app\Helpers\Libs;
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
}
