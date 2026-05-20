<?php

namespace app\admin\controller\bbexp;

use app\common\controller\Backend;

use app\admin\model\bbexp\Norderdetail; 
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use think\db\exception\BindParamException;
use think\exception\PDOException;
use think\db;
use think\Controller;
use app\admin\model\company\Ecompany; //电商企业表
use app\admin\model\company\Owner; //境内收发货人
use app\admin\model\company\Ebp; //平台企业表
use app\admin\model\company\Agent; //报关企业表
use app\admin\model\datadictionary\Country; //国家表
use app\admin\model\datadictionary\Unitcus; //计量单位表
use app\admin\model\datadictionary\Currency; //币制单位表
use app\common\controller\exorder303;  //9710订单
use app\common\controller\exorderBgd;  //9710报关单

/**
 * 97/98订单管理
 *
 * @icon fa fa-circle-o
 */
class Exorder extends Backend
{

    /**
     * Exorder模型对象
     * @var \app\admin\model\bbexp\Exorder
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\bbexp\Exorder;
        $this->detailModel = new \app\admin\model\bbexp\Exorderdetail;	
        $this->view->assign("confirmList", $this->model->getConfirmList());
        $this->view->assign("orderTypeList", $this->model->getOrderTypeList());
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
		//ck 20260327
		$open = $this->auth->getGroups(); //获取登录管理员的信息
		foreach ($open as $key => $value) {
			$uid = $value['uid'];
		}
		$map['auth_id'] = array('in', $uid);
		$map['order_type'] = 'B'; // 新增条件：order_type等于E
			
		
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
			->where($map)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
    public function import(){
		//$ordertype = $this->request->post('ordertype');
	    set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 3600);
        ini_set('max_input_time', 3600);
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH  . 'public' . DS . $file;///www/wwwroot/anshan.lncbe.com/
		// file_put_contents(__DIR__ .'/timeeerrree.txt',date('Y-m-d H:i:s').base64_encode(serialize($result)).PHP_EOL, FILE_APPEND);
		if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
		/*$list = $this->model
			->with(['details'])
			->select();
		$result = [];ss
		foreach ($list as $item) {
			$result[] = $item->toArray();
		}*/
//file_put_contents(__DIR__ .'/AAdetails1.txt',date('Y-m-d H:i:s').base64_encode(serialize($result)).PHP_EOL, FILE_APPEND);

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
        $table = $this->model->getQuery()->getTable();//表名
        $database = \think\Config::get('database.database');//数据库
        $fieldArr = [];//字段备注
        $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $v['COLUMN_COMMENT'] = explode(':', $v['COLUMN_COMMENT'])[0]; //字段备注有:时截取
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }
		//$this->error(__('No results were found'));

		$detailfieldArr = [];//字段备注
		$detailTable = $this->detailModel->getQuery()->getTable();
		$detaillist = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$detailTable, $database]);
        foreach ($detaillist as $k => $v) {
            if ($importHeadType == 'comment') {
                $v['COLUMN_COMMENT'] = explode(':', $v['COLUMN_COMMENT'])[0]; //字段备注有:时截取
                $detailfieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $detailfieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }
// file_put_contents(__DIR__ .'/time310.txt',date('Y-m-d H:i:s').base64_encode(serialize($fieldArr)).PHP_EOL, FILE_APPEND);		
// file_put_contents(__DIR__ .'/time310.txt',date('Y-m-d H:i:s').base64_encode(serialize($detailfieldArr)).PHP_EOL, FILE_APPEND);		
		// echo "<pre>";
		// print_r($ext);
		//加载文件
		$insertorder = [];
		$insertdetail = [];
		try {
			if (!$PHPExcel = $reader->load($filePath)) {
				$this->error(__('Unknown data format'));
			}
			$currentSheet = $PHPExcel->getSheet(0);  // 读取指定工作表
			$allColumn = $currentSheet->getHighestDataColumn(); // 取得最大的列号
			$allRow = $currentSheet->getHighestRow(); // 取得一共有多少行
			// echo $allRow;
			//$maxColumnNumber = \PHPExcel_Cell::columnIndexFromString($allColumn);
			$maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
			$fields = [];
			// 获取表头
			for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
				for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
					$val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
					$fields[] = $val;
				}
			}
			// 处理数据行
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
					$insertorder[] = $row;
				}
			}
			
		    $currentSheet1 = $PHPExcel->getSheet(1);  // 读取指定工作表
			$allColumn = $currentSheet1->getHighestDataColumn(); // 取得最大的列号
			$allRow = $currentSheet1->getHighestRow(); // 取得一共有多少行
			//$maxColumnNumber = \PHPExcel_Cell::columnIndexFromString($allColumn);
			$maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
			$fields = [];
			// 获取表头
			for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
				for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
					$val = $currentSheet1->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
					$fields[] = $val;
				}
			}
			// 处理数据行
			for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
				$values = [];
				for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
					$val = $currentSheet1->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
					$values[] = is_null($val) ? '' : $val;
				}
				$derow = [];
				$temp = array_combine($fields, $values);
				foreach ($temp as $k => $v) {
					if (isset($detailfieldArr[$k]) && $k !== '') {
						$derow[$detailfieldArr[$k]] = $v;
					}
				}
				if ($derow) {
					$insertdetail[] = $derow;
				}
			}
		} catch (Exception $exception) {
			$this->error($exception->getMessage());
		}
		if(!$insertorder||!$insertdetail){
			$this->error(__('No rows were updated'));
		}
// file_put_contents(__DIR__ .'/time311.txt',date('Y-m-d H:i:s').base64_encode(serialize($insertorder)).PHP_EOL, FILE_APPEND);		
// file_put_contents(__DIR__ .'/time311.txt',date('Y-m-d H:i:s').base64_encode(serialize($insertdetail)).PHP_EOL, FILE_APPEND);		
		try{
			Db::startTrans();
			//存订单主表
			$this->saveOrder($insertorder);
			//存订单详情表
			$data = $this->saveOrderDetail($insertdetail);
			if($data){
				$this->error($data);
			}
			Db::commit();
		}catch (PDOException $exception) {
            Db::rollback();
			$msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $msg, $matches)) {
                $msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            };
            $this->error($msg);
        } catch (Exception $e) {
			Db::rollback();
            $this->error($e->getMessage());
        }
		
		$this->success();
		
	}
	//存订单主表
	public function saveOrder($insertorder){
		foreach($insertorder as $orderData){
			if(empty($orderData['order_number'])){
				continue;
			}
			$filteredOrderData = array_filter($orderData, function($value) {
				return $value!== '' && $value!== null;
			});			
			//根据电商企业代码，查电商企业ID，名称,统一信用代码
			$filteredOrderData['import_error'] = '';
			$ebcresult = Ecompany::where('ebcCode', $filteredOrderData['ebc_code'])->field('ecID, ebcName,social_code')->find();
			if($ebcresult){
				$filteredOrderData['ebc_id'] = $ebcresult->ecID;//电商企业ID
				$filteredOrderData['ebc_name'] = $ebcresult->ebcName;//电商企业名称
				$filteredOrderData['trade_scc'] = $ebcresult->social_code;//统一信用代码
			}else{
				$filteredOrderData['import_error'] .='电商企业未备案';
			}
			//根据境内收发货人代码 ，查境内收发货人id,名称,统一信用代码
			$ownerresult = Owner::where('ownercode', $filteredOrderData['owner_code'])->field('ownerid,ownercode,ownername,owner_scc')->find();
			if($ownerresult){
				$filteredOrderData['owner_code'] = $ownerresult->ownercode;//境内收发货人代码
				$filteredOrderData['owner_name'] = $ownerresult->ownername;//境内收发货人名称
				$filteredOrderData['owner_scc'] = $ownerresult->owner_scc;//统一信用代码
			}else{
				$filteredOrderData['import_error'] .='境内收发货人未备案';
			}
			//根据平台企业代码，查平台企业ID，名称
			$ebpresult = Ebp::where('ebpCode', $filteredOrderData['ebp_code'])->field('ebpID, ebpName')->find();
			if($ebpresult){
				$filteredOrderData['ebp_id'] =$ebpresult->ebpID;
				$filteredOrderData['ebp_name'] =$ebpresult->ebpName;
			}else{
				$filteredOrderData['import_error'] .='平台企业未备案';
			}
			//根据申报企业代码，查申报企业ID，名称
		    $agentresult = Agent::where('agentCode', $filteredOrderData['agent_code'])->field('agentID, agentName,social_code')->find();
			if($agentresult){
				$filteredOrderData['agent_id'] =$agentresult->agentID;
				$filteredOrderData['agent_name'] =$agentresult->agentName;
				$filteredOrderData['agent_scc'] =$agentresult->social_code;
			}else{
				$filteredOrderData['import_error'] .='申报企业未备案';
			}
			//运费币制，
			if(isset($filteredOrderData['freight_currency'])){
				if($filteredOrderData['freight_currency']=='美金'||$filteredOrderData['freight_currency']=='美元'){
					$filteredOrderData['freight_currency'] = "502";
				}

			}
            //根据国家名称或3位代码查国家中文名称，3位数字代码，3位英文代码
		    $countryresult = Country::where('customsCode', $filteredOrderData['consignee_country'])->field('CName, customsCode,countryCode')->find();
			if($countryresult){
				$filteredOrderData['consignee_country'] =$countryresult->customsCode;//运抵国 3位数字代码
				$filteredOrderData['country_code'] =$countryresult->countryCode;//3位英文代码
				$filteredOrderData['country_cname'] =$countryresult->CName;//中文名称
			}else{
				$countryresult = Country::where('CName', $filteredOrderData['consignee_country'])->field('CName, customsCode,countryCode')->find();
				if($countryresult){
					$filteredOrderData['consignee_country'] =$countryresult->customsCode;//运抵国 3位数字代码
					$filteredOrderData['country_code'] =$countryresult->countryCode;//3位英文代码
					$filteredOrderData['country_cname'] =$countryresult->CName;//中文名称
				}else{
					$filteredOrderData['import_error'] .='国家未匹配到';
				}
			}			
			$match1 = preg_match('/([\x81-\xfe][\x40-\xfe])/',$filteredOrderData['edistinate_port']);
			if(empty($filteredOrderData['edistinate_port'])||$match1){
				$filteredOrderData['edistinate_port'] = $filteredOrderData['countryCode'].'000';
			}
			$filteredOrderData['createtime'] = time();
			$copNo = 'D' . date('Ymd', time()) . rand(100000000, 999999999);
			$filteredOrderData['cop_no'] = $copNo;
	        $open = $this->auth->getGroups(); //获取登录管理员的信息
			foreach ($open as $key => $value) {
				$uid = $value['uid'];
			}
			$filteredOrderData['auth_id'] = $uid;//权限ID
			$filteredOrderData['order_type'] = 'B';//订单类型
			$resultID = Db::name('exorder')->insertGetId($filteredOrderData);			
		}
	}
	//存订单详情表
    public function saveOrderDetail($insertdetail){
		//提取订单号
		$orderNumbers = array_column($insertdetail, 'order_number');
		// 获取本月的开始和结束时间
		$startOfMonth = strtotime(date('Y-m-01 00:00:00'));
		$endOfMonth   = strtotime(date('Y-m-t 23:59:59'));
		// 使用 whereTime 方法进行时间范围查询 [citation:1][citation:6]
		$existingOrders = Db::name('exorderdetail')
			->where('order_number', 'in', $orderNumbers)
			//->whereTime('createtime', '>=', $startOfMonth)
			//->whereTime('createtime', '<=', $endOfMonth)
			->column('order_number');  // 只返回 order_number 列
		if (!empty($existingOrders)){
			$existingOrders = array_unique($existingOrders);
			$orderNoList = implode(',', $existingOrders);
			$msg = '以下订单已存在: ' . $orderNoList;
			return $msg;
		}
	    foreach($insertdetail as $orderData){
			if(empty($orderData['order_number'])){
				continue;
			}
			// 使用 array_filter 函数去除空值
			$orderDetailData = array_filter($orderData, function($value) {
				return $value!== '' && $value!== null;
			});
			$orderDetailData['createtime'] = time();//导入时间
			$open = $this->auth->getGroups(); //获取登录管理员的信息
			foreach ($open as $key => $value) {
				$uid = $value['uid'];
			}
			$orderDetailData['auth_id'] = $uid;//权限ID
			
			//币制
			$currencyresult = Currency::where('code', $orderData['item_currency'])->field('code,Ecode')->find();
			$orderDetailData['item_currency'] = $currencyresult->code;
			$orderDetailData['trade_curr'] = $currencyresult->Ecode;
			
			//成交计量单位
			$unitresult = Unitcus::where('code', $orderData['unit'])->field('code')->find();
			if($unitresult){
				$orderDetailData['unit'] = $unitresult->code;
			}else{
				$unitresult = Unitcus::where('name', $orderData['unit'])->field('code')->find();
				if($unitresult){
					$orderDetailData['unit'] = $unitresult->code;
				}
			}
			//法定第一计量单位
			$unit1result = Unitcus::where('code', $orderData['unit1'])->field('code')->find();
			if($unit1result){
				$orderDetailData['unit1'] = $unit1result->code;
			}else{
				$unit1result = Unitcus::where('name', $orderData['unit1'])->field('code')->find();
				if($unit1result){
					$orderDetailData['unit1'] = $unit1result->code;
				}
			}
			//法定第二计量单位
			$unit2result = Unitcus::where('code', $orderData['unit2'])->field('code')->find();
			if($unit2result){
				$orderDetailData['unit2'] = $unit2result->code;
			}else{
				$unit2result = Unitcus::where('name', $orderData['unit2'])->field('code')->find();
				if($unit2result){
					$orderDetailData['unit2'] = $unit2result->code;
				}
			}
			
			//原产国，判断是不是填的中文，中文名到国家表查代码
			if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $orderDetailData['destination_country']) === 1){
				$countryresult = Country::where('CName', $orderDetailData['destination_country'])->field('CName, customsCode,countryCode')->find();
				if($countryresult){
					//$orderDetailData['destination_country'] =$countryresult->customsCode;//原产国 3位数字代码
					$orderDetailData['destination_country'] =$countryresult->countryCode;//原产国 3位英文代码
				}
			}
			//最终目的国 ，判断是不是填的中文，中文名到国家表查代码
			if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $orderDetailData['origin_country']) === 1){
				$couresult = Country::where('CName', $orderDetailData['origin_country'])->field('CName, customsCode,countryCode')->find();
				if($couresult){
					//$orderDetailData['origin_country'] =$couresult->customsCode;//最终目的国 3位数字代码
					$orderDetailData['origin_country'] =$couresult->countryCode;//最终目的国 3位英文代码
				}
			}
			file_put_contents(__DIR__ .'/time313.txt',date('Y-m-d H:i:s').base64_encode(serialize($orderDetailData)).PHP_EOL, FILE_APPEND);
            $resultID = Db::name('exorderdetail')->insertGetId($orderDetailData);			
		}
	}
	//9710订单申报
	public function customsOrder($ids=null){
		
		//file_put_contents(__DIR__ .'/timeeerrree.txt',date('Y-m-d H:i:s').base64_encode(serialize($result)).PHP_EOL, FILE_APPEND);
		if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
        //$foun = $this->foun(); //基础信息
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        //先查询所有要报关的数据
        $list = $this->model
			->with(['details'])
			->where($where)
			->where('auth_id', $uid)
			->where('order_status', 0)
			->select();
		$result = [];
		foreach ($list as $item) {
			$result[] = $item->toArray();
		}
		file_put_contents(__DIR__ .'/timeeerrree.txt',date('Y-m-d H:i:s').base64_encode(serialize($result)).PHP_EOL, FILE_APPEND);
		if(!$result){
            returnApi('0','暂时没有报关项');
        }

        $exorder =  new exorder303();
        $array = array_chunk($result, 50); //设置xml分组50个
        foreach($array as $datas){
            $exorder->Gen303($datas,$uid);
        }
        $this->model->where($where)->where('auth_id',$uid)->where('order_status',0)->update(['order_status' => '1']);
        returnApi('1','操作完成');
       
	}
	//9710报关单申报
	public function customsBgd($ids=null){
		if($ids){
            $where['id'] = array('in',$ids);
        }else{
            $where =true;
        }
        //$foun = $this->foun(); //基础信息
        $open = $this->auth->getGroups(); //获取登录管理员的信息
        foreach ($open as $key => $value) {
            $uid = $value['uid'];
        }
        //先查询所有要报关的数据
        $list = $this->model
			->with(['details'])
			->where($where)
			->where('auth_id', $uid)
			->where('channel', 0)
			->select();
		$result = [];
		foreach ($list as $item) {
			$result[] = $item->toArray();
		}
		file_put_contents(__DIR__ .'/timeeerrree.txt',date('Y-m-d H:i:s').base64_encode(serialize($result)).PHP_EOL, FILE_APPEND);
		if(!$result){
            returnApi('0','暂时没有报关项');
        }
        $exorderbgd =  new exorderBgd();
		$exorderbgd->GenBgd($result,$uid);
        $this->model->where($where)->where('auth_id',$uid)->where('channel',0)->update(['channel' => '1000']);
        returnApi('1','操作完成');
	}

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
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
        $row = $this->model->get($ids);//根据主键 $ids 获取要编辑的记录
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();//获取有权限的管理员ID数组
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {//GET请求 - 展示编辑页面
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');//POST请求 - 处理数据更新
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);// 排除不需要更新的字段
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
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
	 * 订单详情弹窗
	 */
	public function details($id = null, $order_number = null)
	{
		// 使用 with() 预加载关联数据
		if ($id) {
			$row = $this->model->with(['details'])->where('id', $id)->find();
		} else if ($order_number) {
			$row = $this->model->with(['details'])->where('order_number', $order_number)->find();
		} else {
			$this->error('缺少参数');
		}
		if (!$row) {
			$this->error('订单不存在');
		}
		
		// 获取关联的商品数据
		$details = $row->details;  // 这里已经包含了关联表的所有数据
		// 分配给模板
		$this->view->assign("row", $row);
		$this->view->assign("details", $details);  // 单独传递关联数据
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
		//s20260410ck
		//cop_no 企业内部编号 country_code 运抵国3位英文代码 consignee_country 运抵国3位数字代码
	    // $countryresult = Country::where('CName', $params['country_cname'])->field('CName, customsCode,countryCode')->find();
		// if($countryresult){
			// $params['consignee_country'] =$countryresult->customsCode;//运抵国 3位数字代码
			// $params['country_code'] =$countryresult->countryCode;//3位英文代码
			// $params['country_cname'] =$countryresult->CName;//中文名称
		// }
		$copNo = 'D' . date('Ymd', time()) . rand(100000000, 999999999);
		$params['cop_no'] = $copNo;
		$open = $this->auth->getGroups(); //获取登录管理员的信息
		foreach ($open as $key => $value) {
			$uid = $value['uid'];
		}
		$params['auth_id'] = $uid;//权限ID
	    $params['order_type'] = 'B';//订单类型
		//e20260410ck
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
	//删除到回收站
	public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
				
				// 先删除关联的子表 ck20260416
				$orderNumber = $item->order_number;
				if ($orderNumber) {
					// 根据软删除设置选择方式
					$detailModel = new \app\admin\model\bbexp\Exorderdetail();
					$detailList = $detailModel->where('order_number', $orderNumber)->select();
					foreach ($detailList as $detail) {
						$detail->delete();  // 会根据模型的 SoftDelete 自动处理
					}
				}
				
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }
}
