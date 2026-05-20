<?php

namespace app\admin\model\csmradmin;

use think\Model;


class Adminapply extends Model
{

    

    

    // 表名
    protected $name = 'csmradmin_adminapply';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'auditstatus_text'
    ];
    

    
    public function getAuditstatusList()
    {
        return ['-2' => __('Auditstatus -2'), '-1' => __('Auditstatus -1'), '0' => __('Auditstatus 0'), '1' => __('Auditstatus 1')];
    }


    public function getAuditstatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['auditstatus']) ? $data['auditstatus'] : '');
        $list = $this->getAuditstatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
