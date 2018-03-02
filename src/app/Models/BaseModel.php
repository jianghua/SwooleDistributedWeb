<?php
/**
 * model基类
 * @author weihan
 * @datetime 2016年11月15日上午11:32:06
 */
namespace app\Models;

use Web\BaseModel as _BaseModel;

class BaseModel extends _BaseModel
{
    /**
     * 表前缀
     */
    public $tbl_prefix = '';
    
}