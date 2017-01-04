<?php
namespace app\Helpers\Libs;
use app\Controllers\User;
class Lib{
    static function getTypeName($typeId){
        switch ($typeId){
            case User::TYPE_PERSONAL:
                return "个人";
            case User::TYPE_GOV:
                return "政府";
            case User::TYPE_MEDIA:
                return "媒体";
            case User::TYPE_COMPANY:
                return "企业";
            default:
                return "其他";
        }
    }  
}
