<?php

namespace app\admin\controller\datadictionary;

use app\common\controller\Backend;

/**
 * 国家代码管理
 *
 * @icon fa fa-circle-o
 */
class Country extends Backend
{

    /**
     * Country模型对象
     * @var \app\admin\model\datadictionary\Country
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datadictionary\Country;

    }

    public function search(){
		// 设置返回格式为JSON
        $this->request->filter(['strip_tags', 'trim']);
        // 获取参数
        $country = $this->request->post('country_data');
		if(empty($country)) {
            $this->error('国家名称不能为空');
        }
		$countryData = $this->model
            ->where('CName', $country)
            ->order('couID', 'desc')
            ->find();
		if(!$countryData){
			$countryData = $this->model
				->where('customsCode', $country)
				->order('couID', 'desc')
				->find();
		}
		if(!$countryData){
			$countryData = $this->model
				->where('countryCode', $country)
				->order('couID', 'desc')
				->find();
		}
		if($countryData){
            $this->success('查询成功', null, [
                'country_cname' => $countryData->CName,
                'consignee_country' => $countryData->customsCode,
                'country_code' => $countryData->countryCode
            ]);
		}else{
			$this->error('该国家未匹配到');
		}
	}

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
