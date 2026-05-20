<?php

namespace app\admin\model\product;

use think\Model;


class Products extends Model
{

    

    

    // 表名
    protected $name = 'products';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'giftFlag_text',
        'returnStatus_text',
        'returnTime_text',
        'disabled_text'
    ];
    

    
    public function getGiftflagList()
    {
        return ['0' => __('GiftFlag 0'), '1' => __('GiftFlag 1')];
    }

    public function getReturnstatusList()
    {
        return ['none' => __('None'), '-1' => __('ReturnStatus -1'), '1' => __('ReturnStatus 1'), '2' => __('ReturnStatus 2'), '3' => __('ReturnStatus 3'), '4' => __('ReturnStatus 4'), '100' => __('ReturnStatus 100'), '120' => __('ReturnStatus 120'), '399' => __('ReturnStatus 399'), '5' => __('ReturnStatus 5')];
    }

    public function getDisabledList()
    {
        return ['0' => __('Disabled 0'), '1' => __('Disabled 1')];
    }


    public function getGiftflagTextAttr($value, $data)
    {
        $value = $value ?: ($data['giftFlag'] ?? '');
        $list = $this->getGiftflagList();
        return $list[$value] ?? '';
    }


    public function getReturnstatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['returnStatus'] ?? '');
        $list = $this->getReturnstatusList();
        return $list[$value] ?? '';
    }


    public function getReturntimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['returnTime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDisabledTextAttr($value, $data)
    {
        $value = $value ?: ($data['disabled'] ?? '');
        $list = $this->getDisabledList();
        return $list[$value] ?? '';
    }

    protected function setReturntimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
