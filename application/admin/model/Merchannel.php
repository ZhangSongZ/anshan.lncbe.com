<?php

namespace app\admin\model;

use think\Model;


class Merchannel extends Model
{

    

    

    // 表名
    protected $name = 'Merchannel';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['5' => __('Status 5')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }




    public function admin()
    {
        return $this->belongsTo('Admin', 'auth_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
