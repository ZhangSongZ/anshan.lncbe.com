<?php

namespace app\admin\model;

use think\Model;


class Excbe extends Model
{

    

    

    // 表名
    protected $name = 'excbe';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function getStatusList()
    {
        return ['-1' => __('数据有误'),'0' => __('待报关'),'1' => __('三单完成'),'2' => __('清单完成'),'3' => __('总分单完成'),'4' => __('运抵完成'),'5' => __('离境完成')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }











}
