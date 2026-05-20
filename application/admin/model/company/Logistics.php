<?php

namespace app\admin\model\company;

use think\Model;


class Logistics extends Model
{

    

    

    // 表名
    protected $name = 'logistics';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'createTime_text'
    ];
    

    



    public function getCreatetimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['createTime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreatetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
