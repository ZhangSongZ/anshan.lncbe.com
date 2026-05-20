<?php

namespace app\admin\controller;

use app\admin\library\Auth;
use app\api\controller\pc\CartController;
use app\common\controller\Backend;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\db\exception\BindParamException;
use think\exception\PDOException;
use think\db;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use app\common\controller\Popen;  //三单
use app\common\controller\Listde; //清单
use app\common\controller\Total;  //总分单
use app\common\controller\Ship;   //运抵单
use app\common\controller\Leave;  //离境单
use app\common\controller\Cancel;  //撤销单
use app\common\controller\Summary;  //汇总单
/**
 *
 *
 * @icon fa fa-circle-o
 */
class Excbe extends Backend
{

    /**
     * Excbe模型对象
     * @var \app\admin\model\Excbe
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Excbe;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    public function xun(){
        set_time_limit(0);
        $dataCount = 2000;
        // 创建空数组存放生成的数据
        $dataArray = [];
        $time_start = microtime(true);
        for ($i=0; $i<$dataCount; $i++) {
            $dataArray[] = "(111,333,2019,'大包数',7321315267097137,'CA','1',1.29,'T恤',6109100010,1,2.4,1,0.248,20522056860,1,'8502',1,1)";
        }
        $valuesString = implode(",",$dataArray);
        $sql = "Insert into ln_excbe (orderNo,createtime,totalPackageNo,logisticsNo,country,logisticsCode,weight,product_name,gcode,qty,price,item_currency,netWeight,billNo,voyageNo,cost_freight,freight_currency,gmodel) VALUES $valuesString";
        db::query($sql);
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo "执行耗时：".$time." 秒。" ;
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            
			$open = $this->auth->getGroups(); //获取登录管理员的信息
            foreach ($open as $key => $value) {
                $uid = $value['uid'];
            }
			$map = [];
			if($uid!='1'&&$uid!='13'){
				$map['auth_id'] = array('in', $uid);
			}
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->where($map)
                ->order($sort, $order)
                ->paginate($limit);

            //小件数
            $countlogis= $this->model
                ->where($where)
                ->where($map)
                ->group('logisticsNo')
                ->count();
            //先查询所有要报关的数据
            $count= $this->model->where($where)->where($map)->where('status',0)->count();
            $counts= $this->model->where($where)->where($map)->where('status',0)->group('logisticsNo')->count();//代报关包裹数
            $result = array("total" => $list->total(), "rows" => $list->items(),"extend" => ['count' => $count,'counts'=>$counts,'countlogis'=>$countlogis]);
            return json($result);
        }

        return $this->view->fetch();
    }



    //多进程测试

    function swoole(){


        for ($i = 0; $i < 10; $i++){

            $pid = pcntl_fork();

            if ($pid == -1) {

                die("开启进程失败");

            } elseif ($pid) {

                echo "启动子进程 $pid \n";

            } else {
                echo $pid;
                echo "子进程 ".getmypid()." 正在处理任务\n";

                sleep(rand(5,10));

                exit;

            }

        }


    }


    public function swooles(){

        for ($i = 0; $i < 10; $i++){

            $pid = pcntl_fork();


            if ($pid == -1) {

                die("开启进程失败");

            } elseif ($pid) {

                echo "启动子进程 $pid \n";

            } else {
                echo $pid;
                echo "子进程 ".getmypid()." 正在处理任务\n";
                sleep(rand(5,10));
                exit;
            }
        }
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 导入
     *
     * @return void
     * @throws PDOException
     * @throws BindParamException
     */
    public function import()
    {
        set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 3600);
        ini_set('max_input_time', 3600);
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }
        if ($ext === 'csv') {
            $file = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, 'w');
            $n = 0;
            while ($line = fgets($file)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding !== 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = isset($this->importHeadType) ? $this->importHeadType : 'comment';

        $table = $this->model->getQuery()->getTable();
        $database = \think\Config::get('database.database');
        $fieldArr = [];
        $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $v['COLUMN_COMMENT'] = explode(':', $v['COLUMN_COMMENT'])[0]; //字段备注有:时截取
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }

        //加载文件
        $insert = [];
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                $this->error(__('Unknown data format'));
            }
            $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
            $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
            $fields = [];
            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = $val;
                }
            }

            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $values[] = is_null($val) ? '' : $val;
                }
                $row = [];
                $temp = array_combine($fields, $values);
                foreach ($temp as $k => $v) {
                    if (isset($fieldArr[$k]) && $k !== '') {
                        $row[$fieldArr[$k]] = $v;
                    }
                }
                if ($row) {
                    $insert[] = $row;
                }
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
        if (!$insert) {
            $this->error(__('No rows were updated'));
        }

        try {
            //是否包含admin_id字段
            $has_admin_id = false;
            foreach ($fieldArr as $name => $key) {
                if ($key == 'admin_id') {
                    $has_admin_id = true;
                    break;
                }
            }
            if ($has_admin_id) {
                $auth = Auth::instance();
                foreach ($insert as &$val) {
                    if (!isset($val['admin_id']) || empty($val['admin_id'])) {
                        $val['admin_id'] = $auth->isLogin() ? $auth->id : 0;
                    }
                }
            }
            //只为没有回执时匹配订单号和清单号时使用
//            foreach($insert as $k=>&$v){
//                $names = Db::name('excbe')->where('orderNo', $v['orderNo'])->update(['preNo' => $v['preNo'],'invtNo'=>$v['invtNo']]);
//            }



            // 导入表格程序开始
            $result = array();
            $open = $this->auth->getGroups(); //获取登录管理员的信息
            foreach ($open as $key => $value) {
                $uid = $value['uid'];
            }
            foreach ($insert as $v)
            {
                //先按运单号分组
                $result[$v['logisticsNo']][] =$v;
            }

            $time = time();
            foreach ($result as $key=>$v) {
                $final_amount=0;
                $weights=0;
                $netWeights=0;
                foreach($v as $p=>$va){
                    $final_amount +=  (float)$va['qty']*(float)$va['price'];

                   // $weights +=  (float)$va['qty']*(float)$va['weight'];
                    $weights  =  (float)$va['weight'];

                    $netWeights +=  (float)$va['qty']*(float)$va['netWeight'];
                }

                $orderNo = 'NO' . date('YmdHis', time()) . rand(100000000,999999999); //订单号

                $copNo = 'B' . date('Ymdhis', time()) . rand(100000000, 999999999);  //企业内部号
                foreach($v as $q=>$val){

                    if(!$val['logisticsNo']){ //去除空数据
                        continue;
                    }

                    $declare = db::name('declare')->where('CName',$val['country'])->find();

                    $val['country'] = $declare['customsCode'];

                    if($val['item_currency']=='美元'||$val['item_currency']=='USD'){ //商品币制
                        $val['item_currency'] = '502';
                    }
                    if($val['item_currency']=='人民币'){ //商品币制
                        $val['item_currency'] = '142';
                    }
                    if($val['freight_currency']=='美元'||$val['freight_currency']=='USD'){ //运费币制
                        $val['freight_currency'] = '502';
                    }
                    if($val['freight_currency']=='人民币'){ //运费币制
                        $val['freight_currency'] = '142';
                    }
                    if($val['item_currency']=='美元'||$val['item_currency']=='USD'){ //商品币制
                        $val['item_currency'] = '502';
                    }

                    //删除提运单号中带有“-”的字符
                    if (strpos($val['billNo'], "-") !== false) {
                        // 去除"-"
                        $val['billNo'] = str_replace("-", "", $val['billNo']);
                    }


                    $val['logisticsNo'] = trim($val['logisticsNo']); //过滤运单号的空格
                    
                  //  $orderNo =  $val['orderNo'];

                  
                    /*后加*/

                    if($final_amount>700){  //判断订单有没有不合规
                        $status = -1; //不合格
                    }else{
                        $status = 0;  //合格
                    }
                    if($netWeights>$weights){
//                   
                            $status = -1;
//                    
//                   
                    }
                    
                    //汇总申报导入表
                    if(empty($val['unit'])){
                        $unit = '';
                        $unit1 = '';
                        $qty1 = '';
                        $unit2 = '';
                        $qty2 = '';
                       
                     }else{
                          $unit = $val['unit'];
                          $unit1 = $val['unit1'];
                          $qty1 = $val['qty1'];
                          $unit2 = $val['unit2'];
                          $qty2 = $val['qty2'];
                      }
                    
                    
                    $foun = $this->foun(); //基础信息 将报关信息放到订单表中
                    
                    if(!$foun){
                            returnApi('0','请先完善报关的基本信息');
                        }
                    $dataArray[] = "('{$uid}','{$orderNo}','{$time}','{$val['totalPackageNo']}','{$val['logisticsNo']}','{$val['country']}','{$weights}','{$val['product_name']}','{$val['gcode']}','{$val['qty']}','{$val['price']}','{$val['item_currency']}','{$val['netWeight']}','{$val['billNo']}','{$val['voyageNo']}','{$val['cost_freight']}','{$val['freight_currency']}','{$val['gmodel']}','{$status}','{$final_amount}','{$weights}','{$netWeights}','{$copNo}','{$foun['ebpCode']}','{$foun['ebpName']}','{$foun['ebcCode']}','{$foun['ebcName']}','{$foun['logisticsCode']}','{$foun['logisticsName']}','{$foun['customsCode']}','{$foun['portCode']}','{$foun['statisticsFlag']}','{$foun['agentCode']}','{$foun['agentName']}','{$foun['ownerCode']}','{$foun['ownerName']}','{$foun['tradeMode']}','{$foun['trafMode']}','{$foun['operatorCode']}','{$foun['operatorName']}','{$foun['loctNo']}','{$foun['declagAgentCode']}','{$foun['declAgentName']}','{$foun['decBillNo']}','{$unit}','{$unit1}','{$qty1}','{$unit2}','{$qty2}')";

                }

            }

            //把生产的数组分成每份一万个
            $arrays = array_chunk($dataArray, 10000);
            //开始循环导入
            foreach($arrays as $k=>$v){
                $valuesString = implode(",",$v);
                $sql = "Insert into ln_excbe (auth_id,orderNo,createtime,totalPackageNo,logisticsNo,country,weight,product_name,gcode,qty,price,item_currency,netWeight,billNo,voyageNo,cost_freight,freight_currency,gmodel,status,final_amount,weights,netWeights,copNo,ebpCode,ebpName,ebcCode,ebcName,logisticsCode,logisticsName,customsCode,portCode,statisticsFlag,agentCode,agentName,ownerCode,ownerName,tradeMode,trafMode,operatorCode,operatorName,loctNo,declagAgentCode,declAgentName,decBillNo,unit,unit1,qty1,unit2,qty2) VALUES $valuesString";
                db::query($sql);
            }
            //完成此次导入程序
        } catch (PDOException $exception) {
            $msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $msg, $matches)) {
                $msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            };
            $this->error($msg);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success();
    }


        //加密
    public function encryption($character,$i,$y){

        $replacedString = substr_replace($character, "****", $i, $y); // 从位置3开始，替换1个字符
       return $replacedString; // 输出: Helo, world! （如果原始字符串长度允许）

    }

    public function encryptions($characters){
        $length = mb_strlen($characters, 'UTF-8');
        $character = mb_substr($characters,0,1,'UTF-8');
        $charact = mb_substr($characters,$length-1,1,'UTF-8');
        return $character.'**'.$charact;
    }

    public function encryptionss($characters){
        $length = mb_strlen($characters, 'UTF-8');
        $character = mb_substr($characters,0,4,'UTF-8');
        $charact = mb_substr($characters,$length-4,4,'UTF-8');
        return $character.'******'.$charact;
    }


    /**
     *  打印面单 批量
     */

    public function letian($ids=null,$billNo)
    {
        if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        //先查询所有要报关的数据
        $result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->select();
        if(!$result){
            returnApi('0','暂时没有需要打印的面单');
        }

        $this->view->assign('row', $result);
        $this->view->assign('billNo', $billNo);
        return $this->view->fetch();

    }
    //单独打印
    public function letians($ids=null)
    {
        if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        //先查询所有要报关的数据
        $result = $this->model->where('auth_id',$uid)->where($where)->select();
        if(!$result){
            returnApi('0','暂时没有需要打印的面单');
        }

        $this->view->assign('row', $result);

        return $this->view->fetch();

    }


    //查询出企业的报关基础信息
    public function foun(){
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        $result = db::name('basic')->where('auth_id',$uid)->where('status',1)->find();
        return $result;
    }



         //报关三单方法
        public  function customsOne($ids=null,$billNo,$status)
        {
            if ($ids) {
                $where['id'] = array('in', $ids);
            } else {
                $where = true;
            }
            
            $open = $this->auth->getGroups(); //获取登录管理员的信息
            foreach ($open as $key => $value) {
                $uid = $value['uid'];
            }
            //先查询所有要报关的数据
            $result = $this->model->where($where)->where('billNo',$billNo)->where('auth_id',$uid)->where('status',0)->select();
            //按运单查询
            $results = $this->model->where($where)->where('billNo',$billNo)->where('auth_id',$uid)->where('status',0)->field('orderNo,item_currency,logisticsCode,cost_freight,freight_currency,weight,logisticsNo,final_amount,ebpCode,ebpName,ebcCode,ebcName,logisticsCode,logisticsName,customsCode,portCode,statisticsFlag,agentCode,agentName,ownerCode,ownerName,tradeMode,trafMode')->group('logisticsNo')->select();
            if (!$result || !$results) {
                returnApi('0', '暂时没有报关项');
            }
            $arrdata = array();
            foreach ($result as $v) {
                //先按运单号分组
                $arrdata[$v['logisticsNo']][] = $v;
            }
            //引用三单类
            $popen = new Popen();
            $array = array_chunk($arrdata, 50); //设置xml分组50个
            $arrays = array_chunk($results, 50);
            //报303
            if ($status == 'all' || $status == '303'){
                foreach ($array as $datas) {
                    $popen->Gen303($datas,$uid);
                }
             }
            //报403和505
           if ($status == 'all' || $status != '303') {
               foreach ($arrays as $datas) {
//                    if($status == 'all' || $status == '403'){
//                        $popen->Gen403($datas,$foun);
//                    }
                    if($status == 'all' || $status == '505'){
                        $popen->Gen505($datas,$uid);
                   }
               }
           }

           $this->model->where($where)->where('billNo',$billNo)->where('auth_id',$uid)->where('status',0)->update(['status' => '1']);
            returnApi('1','操作完成');
      }

    /**
     *603清单报关
     */
    public function customsTwo($ids=null,$billNo)
    {
        if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
       
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        //先查询所有要报关的数据
        //$result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',1)->select();
        $result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',1)->field('orderNo,item_currency,logisticsCode,cost_freight,freight_currency,weight,logisticsNo,final_amount,gcode,product_name,gmodel,country,qty,netWeight,price,totalPackageNo,copNo,preNo,invtNo,voyageNo,billNo,ebpCode,ebpName,ebcCode,ebcName,logisticsCode,netWeights,agentCode,logisticsName,customsCode,portCode,statisticsFlag,agentCode,agentName,ownerCode,ownerName,tradeMode,trafMode,loctNo,decBillNo,,unit,unit1,qty1,unit2,qty2')->group('logisticsNo')->select();
        if(!$result){
            returnApi('0','暂时没有报关项');
        }
        $arrdata = array();
        foreach ($result as $v)
        {
            //先按运单号分组
            $arrdata[$v['logisticsNo']][] =$v;
        }
        $listde =  new Listde();
        $array = array_chunk($arrdata, 50); //设置xml分组50个
        foreach($array as $datas){
            $listde->Gen603($datas,$uid);
        }
        $this->model->where($where)->where('billNo',$billNo)->where('auth_id',$uid)->where('auth_id',$uid)->where('status',1)->update(['status' => '2']);
        returnApi('1','操作完成');
    }


    /**
     *607总分单报关
     */
    public function customsThree($ids=null,$billNo)
    {
        if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
       
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }

        //先查询所有要报关的数据
//        $allWeight = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',2)->sum('weight');
        //按订单去重求出总毛重
        $allWeights = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',2)
            ->field('weight')
            ->group('orderNo')
            ->select();
        $sums = array_column($allWeights,'weight');
        $allWeight = array_sum($sums);

        $result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',2)->field('orderNo,item_currency,logisticsCode,cost_freight,freight_currency,weight,logisticsNo,final_amount,copNo,preNo,invtNo,voyageNo,billNo,ebpCode,ebpName,ebcCode,ebcName,logisticsCode,logisticsName,customsCode,portCode,statisticsFlag,agentCode,agentName,ownerCode,ownerName,tradeMode,trafMode,loctNo')->group('logisticsNo')->select();
        if(!$result){
            returnApi('0','暂时没有报关项');
        }
        $voyageNo = array_column($result, 'voyageNo');
        $copNo = array_column($result,'copNo');
      //  $billNo = array_column($result,'billNo');
        $array = array_chunk($result, 5000); //设置xml分组10000个
        $i=0;  //页数
        $total =  new Total();
        foreach($array as $datas){
            $i++;
            $total->Gen607($datas,count($array),$i,$voyageNo[0],$copNo[0],$billNo,$allWeight,$uid);
        }
        $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',2)->update(['status' => '3']);
        returnApi('1','操作完成');
    }




    /**
     *507运抵
     */
    public function customsFour($ids=null,$billNo)
    {
        if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
       
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        //先查询所有要报关的数据
        $result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',3)->field('copNo,voyageNo,billNo,ebpCode,ebpName,ebcCode,ebcName,logisticsCode,logisticsName,customsCode,portCode,statisticsFlag,agentCode,agentName,ownerCode,ownerName,tradeMode,trafMode,loctNo,operatorCode,operatorName,decBillNo')->find();
        if(!$result){
            returnApi('0','暂时没有报关项');
        }
        $ship =  new Ship();
        $ship->Gen507($result,$billNo,$uid);

        $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',3)->update(['status' => '4']);
        returnApi('1','操作完成');
    }



    /**
     *509离境
     */
    public function customsFive($ids=null,$billNo)
    {
        if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
      
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }

        //先查询所有要报关的数据
        $result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',4)->field('orderNo,item_currency,logisticsCode,cost_freight,freight_currency,weight,logisticsNo,final_amount,copNo,preNo,invtNo,voyageNo,billNo,ebpCode,ebpName,ebcCode,ebcName,logisticsCode,logisticsName,customsCode,portCode,statisticsFlag,agentCode,agentName,ownerCode,ownerName,tradeMode,trafMode,decBillNo')->group('logisticsNo')->select();
        if(!$result){
            returnApi('0','暂时没有报关项');
        }
        $voyageNo = array_column($result, 'voyageNo');
        $copNo = array_column($result,'copNo');
       // $billNo = array_column($result,'billNo');
        $array = array_chunk($result, 5000); //设置xml分组15000个
        $i=0;  //页数
        $time = date('YmdHis', time()); //离境时间
        $leave =  new Leave();
        foreach($array as $datas){
            $i++;
            $leave->Gen509($datas,count($array),$i,$voyageNo[0],$copNo[0],$billNo,$time,$uid);
        }
        $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',4)->update(['status' => '5']);
        returnApi('1','操作完成');
    }
    
    
     /**
     *701 汇总申报
     */
    public function summary($ids=null,$billNo)
    {
         if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
       
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
      
        //先查询所有要报关的数据
        $result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',5)->field('copNo,voyageNo,billNo,orderNo,ebpCode,ebpName,ebcCode,ebcName,logisticsNo,logisticsCode,logisticsName,customsCode,portCode,statisticsFlag,agentCode,agentName,ownerCode,ownerName,tradeMode,trafMode,loctNo,operatorCode,operatorName,decBillNo,declagAgentCode,declAgentName,invtNo')->group('logisticsNo')->select();
        
         
        if(!$result){
            returnApi('0','暂时没有报关项');
        }
        $arrdata = array();
        foreach ($result as $v)
        {
            //先按运单号分组
            $arrdata[$v['logisticsNo']][] =$v;
        }
        
        $array = array_chunk($arrdata, 50); //设置xml分组50个
        
        $summary =  new Summary();
        foreach($array as $datas){
            
            $summary->Gen701($datas,$uid);
        }
       

        $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',5)->update(['status' => '7']);
        returnApi('1','操作完成');
    }


    /**
     *605撤销单报关
     */
    public function customsEight($ids=null,$billNo)
    {
        if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
      
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        //先查询所有要报关的数据
        $result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',5)->field('orderNo,item_currency,logisticsCode,cost_freight,freight_currency,weight,logisticsNo,final_amount,preNo,copNo,invtNo,ebpCode,ebpName,ebcCode,ebcName,logisticsCode,logisticsName,customsCode,portCode,statisticsFlag,agentCode,agentName,ownerCode,ownerName,tradeMode,trafMode')->where($where)->group('logisticsNo')->select();
        if(!$result){
            returnApi('0','暂时没有报关项');
        }

        $cancel =  new Cancel();
        $array = array_chunk($result, 50); //设置xml分组50个
        foreach($array as $datas){
            $cancel->Gen605($datas,$uid);
        }
        $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',5)->update(['status' => '6']);
        returnApi('1','操作完成');
    }

    //导出汇总
    public function export()
    {
        if ($this->request->isPost()) {
            set_time_limit(0);
            ini_set ('memory_limit', '2048M');
            $search = $this->request->post('search');
            $ids = $this->request->post('ids');
            $filter = $this->request->post('filter');
            $op = $this->request->post('op');
            $columns = $this->request->post('columns');

            $whereIds = $ids == 'all' ? '1=1' : ['id' => ['in', explode(',', $ids)]];

            $this->request->get(['search' => $search, 'ids' => $ids, 'filter' => $filter, 'op' => $op]);
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            // $gtTable   = $this->model->getTable();
            // $fTable    = $this->fModel->getTable();


            $newExcel = new Spreadsheet();  //创建一个新的excel文档
            $objSheet = $newExcel->getActiveSheet();  //获取当前操作sheet的对象
            $objSheet->setTitle('数据汇总');  //设置当前sheet的标题

            $newExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $newExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $newExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $newExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $newExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);

            $objSheet->setCellValue('A1', '提单号')
                ->setCellValue('B1', '大包数')
                ->setCellValue('C1', '小件数')
                ->setCellValue('D1', '总金额（美元）')
                ->setCellValue('E1', '总毛重（KG）');

            //提运单号
            $billNo= $this->model
                ->field('billNo')
                ->where($where)
                ->where($whereIds)
                ->find();
            //大包数
            $countpack= $this->model
                ->where($where)
                ->where($whereIds)
                ->group('totalPackageNo')
                ->count();
            //小件数
            $countlogis= $this->model
                ->where($where)
                ->where($whereIds)
                ->group('logisticsNo')
                ->count();

            //总金额，总毛重

            $all = $this->model
                ->where($where)
                ->where($whereIds)
                ->field('weight,final_amount')
                ->group('orderNo')
                ->select();

            $moeny = array_column($all,'final_amount');
            $weight = array_column($all,'weight');
            $allWeight = array_sum($weight);
            $allmoeny = array_sum($moeny);

            /*--------------开始从数据库提取信息插入Excel表中------------------*/

            $objSheet->setCellValue('A' . 2, $billNo['billNo'])
                ->setCellValue('B' . 2, $countpack)
                ->setCellValue('C' . 2, $countlogis)
                ->setCellValue('D' . 2, $allmoeny)
                ->setCellValue('E' . 2, $allWeight);



            /*--------------下面是设置其他信息------------------*/

            $title = $billNo['billNo']."-数据汇总";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = IOFactory::createWriter($newExcel, 'Xlsx');
            $objWriter->save('php://output');

            return;

        }
    }


    //导出数据
    public function exports()
    {
        if ($this->request->isPost()) {
            set_time_limit(0);
            ini_set ('memory_limit', '2048M');
            $search = $this->request->post('search');
            $ids = $this->request->post('ids');
            $filter = $this->request->post('filter');
            $op = $this->request->post('op');
            $columns = $this->request->post('columns');

            $whereIds = $ids == 'all' ? '1=1' : ['id' => ['in', explode(',', $ids)]];

            $this->request->get(['search' => $search, 'ids' => $ids, 'filter' => $filter, 'op' => $op]);
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            // $gtTable   = $this->model->getTable();
            // $fTable    = $this->fModel->getTable();
            $sql=$this->model
                ->table("ln_excbe")
                ->where($where)
                ->where($whereIds)
                ->select();
            $sql = collection($sql)->toArray();

            $newExcel = new Spreadsheet();  //创建一个新的excel文档
            $objSheet = $newExcel->getActiveSheet();  //获取当前操作sheet的对象
            $objSheet->setTitle('数据');  //设置当前sheet的标题

            $newExcel->getactivesheet()->getColumnDimension('A')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('B')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('C')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('D')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('E')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('F')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('G')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('H')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('I')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('J')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('K')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('L')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('M')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('N')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('O')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('P')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('Q')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('R')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('S')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('T')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('U')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('V')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('W')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('X')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('Y')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('Z')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('AA')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('AB')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('AC')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('AD')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('AE')->setAutoSize(true);
            $newExcel->getactivesheet()->getColumnDimension('AF')->setAutoSize(true);

            $objSheet->setcellvalue('A1', "客户代码")
                ->setcellvalue('B1', "电商平台名称")
                ->setcellvalue('C1', "电商平台海关编码")
                ->setcellvalue('D1', "电商企业名称")
                ->setcellvalue('E1', "电商企业海关编码")
                ->setcellvalue('F1', "生产销售企业名称")
                ->setcellvalue('G1', "生产销售企业代码")
                ->setcellvalue('H1', "进出口日期")
                ->setcellvalue('I1', "进出口标志")
                ->setcellvalue('J1', "申报地海关代码")
                ->setcellvalue('K1', "进出口地海关代码")
                ->setcellvalue('L1', "运输方式")
                ->setcellvalue('M1', "包装种类")
                ->setcellvalue('N1', "申报业务类型(A/B)")
                ->setcellvalue('O1', "订单编号")
                ->setcellvalue('P1', "企业商品货号")
                ->setcellvalue('Q1', "数量单位")
                ->setcellvalue('R1', "大包号")
                ->setcellvalue('S1', "运单号")
                ->setcellvalue('T1', "目的国")
                ->setcellvalue('U1', "订单毛重")
                ->setcellvalue('V1', "商品名称")
                ->setcellvalue('W1', "海关编码")
                ->setcellvalue('X1', "数量")
                ->setcellvalue('Y1', "申报单价")
                ->setcellvalue('Z1', "商品币制")
                ->setcellvalue('AA1', "净重（单个商品净重kg）")
                ->setcellvalue('AB1', "提运单号")
                ->setcellvalue('AC1', "航班班次号")
                ->setcellvalue('AD1', "运费")
                ->setcellvalue('AE1', "运费币制")
                ->setcellvalue('AF1', "申报要素");
        //写入到第一个sheet

            //提运单号
            $billNo= $this->model
                ->field('billNo')
                ->where($where)
                ->where($whereIds)
                ->find();

            $open = $this->auth->getGroups(); //获取登录管理员的信息
            foreach ($open as $key => $value) {
                $uid = $value['uid'];
            }

            $orderDetails = $this->model->table("ln_excbe")->where('auth_id',$uid)->where($where)->where($whereIds)->select();
            $founcontent = $this->foun();

            $k = 2;
            foreach ($orderDetails as $val){
                    $objSheet->setCellValue('A'.$k, '');
                    $objSheet->setCellValue('B'.$k, $founcontent['ebpName']);
                    $objSheet->setCellValue('C'.$k, $founcontent['ebpCode']);
                    $objSheet->setCellValue('D'.$k, $founcontent['ebcName']);
                    $objSheet->setCellValue('E'.$k, $founcontent['ebcCode']);
                    $objSheet->setCellValue('F'.$k, $founcontent['ownerName']);
                    $objSheet->setCellValue('G'.$k, $founcontent['ownerCode']);
                    $objSheet->setCellValue('H'.$k, date('Ymd', $val['createtime']));
                    $objSheet->setCellValue('I'.$k, 'E');
                    $objSheet->setCellValue('J'.$k, '0912');
                    $objSheet->setCellValue('K'.$k, '0912');
                    $objSheet->setCellValue('L'.$k, '5');
                    $objSheet->setCellValue('M'.$k, '6');
                    $objSheet->setCellValue('N'.$k, 'A');
                    $objSheet->setCellValue('O'.$k, $val['orderNo']);
                    $objSheet->setCellValue('P'.$k, $val['gcode']);
                    $objSheet->setCellValue('Q'.$k, '007');
                    $objSheet->setCellValue('R'.$k, ''.$val['totalPackageNo']);
                    $objSheet->setCellValue('S'.$k, $val['logisticsNo']);
                    $objSheet->setCellValue('T'.$k, $val['country']);
                    $objSheet->setCellValue('U'.$k, $val['weight']);
                    $objSheet->setCellValue('V'.$k, $val['product_name']);
                    $objSheet->setCellValue('W'.$k, $val['gcode']);
                    $objSheet->setCellValue('X'.$k, $val['qty']);
                    $objSheet->setCellValue('Y'.$k, $val['price']);
                    $objSheet->setCellValue('Z'.$k, '502');
                    $objSheet->setCellValue('AA'.$k, $val['netWeight']);
                    $objSheet->setCellValue('AB'.$k, $val['billNo']);
                    $objSheet->setCellValue('AC'.$k, $val['voyageNo']);
                    $objSheet->setCellValue('AD'.$k, $val['cost_freight']);
                    $objSheet->setCellValue('AE'.$k, $val['freight_currency']);
                    $objSheet->setCellValue('AF'.$k, $val['gmodel']);
                $objSheet->setCellValueExplicitByColumnAndRow(19, $k,$val['logisticsNo'],DataType::TYPE_STRING);
                    $k = $k+1;

            }


            /*--------------下面是设置其他信息------------------*/

            $title = $billNo['billNo']."-数据汇总";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = IOFactory::createWriter($newExcel, 'Xlsx');
            $objWriter->save('php://output');

            return;

        }
    }


    //批量删除
    public function dels($ids=null,$billNo){
        if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        if(!$billNo){
            returnApi('0','请填写提运单号再删除');
        }
        //先查询所有要报关的数据
        $result = $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',0)->whereor('status',-1)->select();

        if(!$result){
            returnApi('0','没有符合删除条件的记录');
        }else{
            $this->model->where($where)->where('auth_id',$uid)->where('billNo',$billNo)->where('status',0)->whereor('status',-1)->delete();
        }
        returnApi('1','操作完成');
    }







}
