<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\db;
/**
 * 企业报关通道
 *
 * @icon fa fa-circle-o
 */
class Merchannel extends Backend
{

    /**
     * Merchannel模型对象
     * @var \app\admin\model\Merchannel
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Merchannel;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['admin'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('admin')->visible(['nickname']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }
    
    
    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
           
            $parent_id1 = $params['parent_id1'];
            $children_id1 = $params['children_id1'];
            $params['order'] = $parent_id1.'-'.$children_id1;
             $parent_id2 = $params['parent_id2'];
            $children_id2 = $params['children_id2'];
            $params['waybill'] = $parent_id2.'-'.$children_id2;
             $parent_id3 = $params['parent_id3'];
            $children_id3 = $params['children_id3'];
            $params['list'] = $parent_id3.'-'.$children_id3;
             $parent_id4 = $params['parent_id4'];
            $children_id4 = $params['children_id4'];
            $params['total'] = $parent_id4.'-'.$children_id4;
             $parent_id5 = $params['parent_id5'];
            $children_id5 = $params['children_id5'];
            $params['arrival'] = $parent_id5.'-'.$children_id5;
             $parent_id6 = $params['parent_id6'];
            $children_id6 = $params['children_id6'];
            $params['departure'] = $parent_id6.'-'.$children_id6;
             $parent_id7 = $params['parent_id7'];
            $children_id7 = $params['children_id7'];
            $params['revoke'] = $parent_id7.'-'.$children_id7;
            
            
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
       
        
        
        $this->success();
    }

    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        $order = returnMerchannel($row['order']);
         if(!$order['before']||!$order['after']){
             $names1 = '';
        }else{
            if($order['before']=='RMQ'){
                 $name = Db::name('rmq')->where('rmq_id',$order['after'])->find();
                 $names1 = $name['ebcname'];
            }else{
                 $name = Db::name('ftp')->where('id',$order['after'])->find();
                 $names1 = $name['ftp_name'];
            }
        }
        $waybill = returnMerchannel($row['waybill']);
        if(!$waybill['before']||!$waybill['after']){
             $names2 = '';
            }else{
                
             if($waybill['before']=='RMQ'){
                 $name = Db::name('rmq')->where('rmq_id',$waybill['after'])->find();
                 $names2 = $name['ebcname'];
            }else{
                 $name = Db::name('ftp')->where('id',$waybill['after'])->find();
                 $names2 = $name['ftp_name'];
            }
        }
        $list = returnMerchannel($row['list']);
         if(!$list['before']||!$list['after']){
             $names3 = '';
        }else{
             if($list['before']=='RMQ'){
                 $name = Db::name('rmq')->where('rmq_id',$list['after'])->find();
                 $names3 = $name['ebcname'];
            }else{
                 $name = Db::name('ftp')->where('id',$list['after'])->find();
                 $names3 = $name['ftp_name'];
            }
        }
        $total = returnMerchannel($row['total']);
          if(!$total['before']||!$total['after']){
             $names4 = '';
             }else{
             if($total['before']=='RMQ'){
                 $name = Db::name('rmq')->where('rmq_id',$total['after'])->find();
                 $names4 = $name['ebcname'];
            }else{
                 $name = Db::name('ftp')->where('id',$total['after'])->find();
                 $names4 = $name['ftp_name'];
            }
        }
        $arrival = returnMerchannel($row['arrival']);
          if(!$arrival['before']||!$arrival['after']){
             $names5 = '';
          }else{
             if($arrival['before']=='RMQ'){
                 $name = Db::name('rmq')->where('rmq_id',$arrival['after'])->find();
                 $names5 = $name['ebcname'];
            }else{
                 $name = Db::name('ftp')->where('id',$arrival['after'])->find();
                 $names5 = $name['ftp_name'];
            }
        }
        
        $departure = returnMerchannel($row['departure']);
         if(!$departure['before']||!$departure['after']){
             $names6 = '';
        }else{
              if($departure['before']=='RMQ'){
             $name = Db::name('rmq')->where('rmq_id',$departure['after'])->find();
             $names6 = $name['ebcname'];
            }else{
                 $name = Db::name('ftp')->where('id',$departure['after'])->find();
                 $names6 = $name['ftp_name'];
            }  
        }
        
        $revoke = returnMerchannel($row['revoke']);
         if(!$revoke['before']||!$revoke['after']){
             $names7 = '';
         }else{
             if($revoke['before']=='RMQ'){
             $name = Db::name('rmq')->where('rmq_id',$revoke['after'])->find();
             $names7 = $name['ebcname'];
           }else{
             $name = Db::name('ftp')->where('id',$revoke['after'])->find();
              $names7 = $name['ftp_name'];
           }
        }
        
        
         $this->view->assign('order1', $order['before']);
         $this->view->assign('order2', $order['after']);
         $this->view->assign('name1', $names1);
         
         $this->view->assign('waybill1', $waybill['before']);
         $this->view->assign('waybill2', $waybill['after']);
         $this->view->assign('name2', $names2);
         
         $this->view->assign('list1', $list['before']);
         $this->view->assign('list2', $list['after']);
         $this->view->assign('name3', $names3);
         
         $this->view->assign('total1', $total['before']);
         $this->view->assign('total2', $total['after']);
         $this->view->assign('name4', $names4);
         
         $this->view->assign('arrival1', $arrival['before']);
         $this->view->assign('arrival2', $arrival['after']);
         $this->view->assign('name5', $names5);
         
         $this->view->assign('departure1', $departure['before']);
         $this->view->assign('departure2', $departure['after']);
         $this->view->assign('name6', $names6);
         
         $this->view->assign('revoke1', $revoke['before']);
         $this->view->assign('revoke2', $revoke['after']);
         $this->view->assign('name7', $names7);
        
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
             $parent_id1 = $params['parent_id1'];
            $children_id1 = $params['children_id1'];
            $params['order'] = $parent_id1.'-'.$children_id1;
             $parent_id2 = $params['parent_id2'];
            $children_id2 = $params['children_id2'];
            $params['waybill'] = $parent_id2.'-'.$children_id2;
             $parent_id3 = $params['parent_id3'];
            $children_id3 = $params['children_id3'];
            $params['list'] = $parent_id3.'-'.$children_id3;
             $parent_id4 = $params['parent_id4'];
            $children_id4 = $params['children_id4'];
            $params['total'] = $parent_id4.'-'.$children_id4;
             $parent_id5 = $params['parent_id5'];
            $children_id5 = $params['children_id5'];
            $params['arrival'] = $parent_id5.'-'.$children_id5;
             $parent_id6 = $params['parent_id6'];
            $children_id6 = $params['children_id6'];
            $params['departure'] = $parent_id6.'-'.$children_id6;
             $parent_id7 = $params['parent_id7'];
            $children_id7 = $params['children_id7'];
            $params['revoke'] = $parent_id7.'-'.$children_id7;
            
            
            
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
       
        
   
        
        
        
        $this->success();
    }

    
  
    /**
     * 根据父级ID获取子级选项
     * @ApiParams   (name="pid", type="integer", required=true, description="父级ID")
     */
    public function getChildrenOptions()
    {
        // 1. 获取请求参数
        $pid = $this->request->get('pid', 0);
        // 2. 权限判断（可选）
        // 可根据$pid从数据库查询，或处理自定义逻辑
        // 这里假设一个简单场景
        $childrenList = [];
        if ($pid == 'RMQ') {
           $rmq = Db::name('rmq')->select();
            foreach ($rmq as $k=>$v){
                  $childrenList[] =['value' => $v['rmq_id'], 'text' => $v['ebcname']];
               }
                
         
        } elseif ($pid == 'FTP') {
            $ftp = Db::name('ftp')->select();
            foreach ($ftp as $k=>$v){
                  $childrenList[] =['value' => $v['id'], 'text' => $v['ftp_name']];
               }
        }
        // 如果是数据库查询，可以这样：
        // $list = $this->model->where('parent_id', $pid)->field('id as value, name as text')->select();
        // $childrenList = collection($list)->toArray();
        // 3. 返回标准化数据
        $result = [
            'code'  => 1, // FastAdmin通常用1表示成功
            'msg'   => '获取成功',
            'list'  => $childrenList,
            'total' => count($childrenList)
        ];
        return json($result); // 必须返回JSON格式[ref_3]
    }
    
    
    
}
