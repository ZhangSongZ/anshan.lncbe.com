<?php

namespace app\admin\controller\company;

use app\common\controller\Backend;

/**
 * 报关企业管理
 *
 * @icon fa fa-circle-o
 */
class Agent extends Backend
{

    /**
     * Agent模型对象
     * @var \app\admin\model\company\Agent
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\company\Agent;

    }

    /**
     * 检查单位代码是否存在并获取最新记录
     */
    public function checkCode()
    {
        // 设置返回格式为JSON
        $this->request->filter(['strip_tags', 'trim']);
        
        // 获取参数
        $agentCode = $this->request->post('agent_code');
        
        if(empty($agentCode)) {
            $this->error('单位代码不能为空');
        }
        
        $agent = $this->model
            ->where('agentCode', $agentCode)
            ->order('agentID', 'desc')
            ->find();
        
        if($agent) {
            // 查询成功，返回单位名称和社会信用代码
            $this->success('查询成功', null, [
                'agent_name' => $agent->agentName,
                'social_code' => $agent->social_code
            ]);
        } else {
            // 未找到记录
            $this->error('该单位未备案，请先进行备案');
        }
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
