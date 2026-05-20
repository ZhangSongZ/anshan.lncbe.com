<?php

namespace app\admin\controller;
use app\common\controller\Backend;

use think\paginator\driver\Bootstrap;

use think\Db;

class Tongji extends Backend
{
    public function index()
    {
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        $excbe = db::name('excbe');
        //提运单号
        $billNo = $excbe
            ->where('auth_id',$uid)
            ->field('billNo,createtime')
            ->group('billNo')
            ->order('id desc')
            ->select();

        $order = array();
        $int =0;
        foreach($billNo as $key=>$value){
            //大包数
            $countpack = $this->groupByQuery($value['billNo'],'totalPackageNo');
            //小件数
            $countlogis= $this->groupByQuery($value['billNo'],'logisticsNo');
            //总金额，总毛重
             $all = $excbe
                ->where('billNo',$value['billNo'])
                ->field('weight,final_amount')
                ->group('orderNo')
                ->select();


            $moeny = array_column($all,'final_amount');
            $weight = array_column($all,'weight');
            $allWeight = array_sum($weight);
            $allmoeny = array_sum($moeny);
            $int++;
            $order[] = array('id'=>$int,'time'=>$value['createtime'],'billNo'=>$value['billNo'], 'countpack'=>$countpack, 'countlogis'=>$countlogis,'allWeight'=>$allWeight,'allmoeny'=>$allmoeny);

        }
        // 使用paginate进行分页，每页显示10条数据
        $page = input('get.page', 1); // 获取当前页数，默认为第1页
        $perPage = 15; // 每页显示数量
        $offset = ($page - 1) * $perPage; // 计算偏移量

        // 对数组进行分页
        $currentPageList = array_slice($order, $offset, $perPage);

        // 实例化分页类，传入总数和每页数量
        $paginator = new Bootstrap($order, $perPage, $page,count($order),false,[
            'var_page' => 'page',
            'path'     => '/lncbe.php/tongji',
            'query'    => [],
            'fragment' => '',
        ]);

        // 将分页数据赋值给模板
        $this->view->assign('order', $currentPageList);
        $this->view->assign('paginator', $paginator);



      // $this->view->assign("order", $order);
        return $this->view->fetch();
    }

    public function groupByQuery($where,$field) {
        return DB::name('excbe')
            ->where('billNo',$where)
            ->group($field)
            ->count();
    }

    public function tongji(){
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        $excbe = db::name('excbe');
        //提运单号
        $billNo = db::name('excbe')
            ->field('billNo')
            ->where('auth_id',$uid)
            ->group('billNo')
            ->order('id desc')
            ->select();
        $tongji = db::name('census')->field('billNo')->select();

        $billNos=array_column($billNo,'billNo');  //获取运单号
        $tongjis=array_column($tongji,'billNo');
        $diff = array_diff($billNos,$tongjis);   //比对数据
        $order = array();
        $int =0;
        // 使用
        foreach($diff as &$value){
            //大包数
            $countpack = $this->groupByQuery($value,'totalPackageNo');
            //小件数
            $countlogis= $this->groupByQuery($value,'logisticsNo');
            //总金额，总毛重
            $all = $excbe
                ->where('billNo',$value)
                ->field('weight,final_amount,createtime')
                ->group('orderNo')
                ->select();

            $moeny = array_column($all,'final_amount');
            $weight = array_column($all,'weight');
            $createtime = array_column($all,'createtime');
            $allWeight = array_sum($weight);
            $allmoeny = array_sum($moeny);
           $res = db::name('census')->insert(['auth_id'=>$uid,'billNo'=>$value,'countpack'=>$countpack,'countlogis'=>$countlogis,'allWeight'=>$allWeight,'allmoeny'=>$allmoeny,'createtime'=>$createtime[0]]);
           if(!$res){
                 $path = 'TongjiLog_' . date("Ymd", time());
               if (!is_dir('notify_log/' . $path)) {
                   mkdir('notify_log/' . $path, 0777, true);
               }
               file_put_contents("tongji_log/" . $path . "/" . $path . ".txt", "--" . json_encode($res) . "--" . PHP_EOL, FILE_APPEND);
           }

        }

    }


}


