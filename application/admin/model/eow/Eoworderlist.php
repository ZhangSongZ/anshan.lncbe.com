<?php

namespace app\admin\model\eow;

use think\Model;
use traits\model\SoftDelete;

class Eoworderlist extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'exorder';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'confirm_text',
        'order_type_text',
        'order_updateTime_text',
        'inventory_updateTime_text'
    ];
    

    
    public function getConfirmList()
    {
        return ['draft' => __('Draft'), 'ok' => __('Ok'), 'cancel' => __('Cancel')];
    }

    public function getOrderTypeList()
    {
        return ['E' => __('E'), 'B' => __('B'), 'W' => __('W')];
    }


    public function getConfirmTextAttr($value, $data)
    {
        $value = $value ?: ($data['confirm'] ?? '');
        $list = $this->getConfirmList();
        return $list[$value] ?? '';
    }


    public function getOrderTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['order_type'] ?? '');
        $list = $this->getOrderTypeList();
        return $list[$value] ?? '';
    }


    public function getOrderUpdatetimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['order_updateTime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getInventoryUpdatetimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['inventory_updateTime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setOrderUpdatetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setInventoryUpdatetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
