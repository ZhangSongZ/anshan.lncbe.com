<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
/**
 * 企业备案统计
 *
 * @icon fa fa-circle-o
 */
class Batj extends Backend
{

    /**
     * Batj模型对象
     * @var \app\admin\model\Batj
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Batj;

    }
    
    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
            
       $owner = Db::name('owner')->count();
       $ecompany = Db::name('ecompany')->count();
       $ebp = Db::name('ebp')->count();
       $agent = Db::name('agent')->count();
       $logistics = Db::name('logistics')->count();
       $operator = Db::name('operator')->count();
       Db::name('batj')->where('id',6)->update(['sum'=>$operator]);
       Db::name('batj')->where('id',5)->update(['sum'=>$logistics]);
         Db::name('batj')->where('id',4)->update(['sum'=>$agent]);
          Db::name('batj')->where('id',3)->update(['sum'=>$ebp]);
           Db::name('batj')->where('id',2)->update(['sum'=>$ecompany]);
            Db::name('batj')->where('id',1)->update(['sum'=>$owner]);
            
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        $this->view->assign('rows',$result);
        return json($result);
    }




    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
