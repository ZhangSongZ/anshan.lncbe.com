<?php

namespace app\admin\controller;
use app\common\controller\Backend;
use think\db;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Mood extends Backend
{

    /**
     * Excbe模型对象
     * @var \app\admin\model\Excbe
     */
    protected $model = null;


    public function index(){

        $params = $this->request->post("row/a");
        if($params){
            $billNo = $params['billNo'];
            $status = $params['status'];
            Db::table('ln_excbe')->where('billNo', $billNo)->update(['status' => $status]);
            return   $this->success();
        }
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        $map['auth_id'] = array('in', $uid);
        $list = Db::table('ln_excbe')
            ->field('billNo')
            ->where($map)
            ->group('billNo')
            ->select();
        $this->view->assign('list', $list);
        return $this->view->fetch();
    }



}
