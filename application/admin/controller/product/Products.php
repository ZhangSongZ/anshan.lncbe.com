<?php

namespace app\admin\controller\product;
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
/**
 * 商品备案管理
 *
 * @icon fa fa-circle-o
 */
class Products extends Backend
{

    /**
     * Products模型对象
     * @var \app\admin\model\product\Products
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\product\Products;
        $this->view->assign("giftflagList", $this->model->getGiftflagList());
        $this->view->assign("returnstatusList", $this->model->getReturnstatusList());
        $this->view->assign("disabledList", $this->model->getDisabledList());
    }
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

            // 导入表格程序开始
            //$result = array();
            $open = $this->auth->getGroups(); //获取登录管理员的信息
            foreach ($open as $key => $value) {
                $uid = $value['uid'];
            }
            $time = time();
			$units = db::name('dic_unitcus')
				->field('name,code')
				->select();

			$unitMap = [];
			foreach ($units as $u) {
				$unitMap[$u['name']] = $u['code'];
				$unitMap[$u['code']] = $u['code']; // name / code 都能命中
			}
			$dataArray = [];
            foreach ($insert as $val) {
				$unit  = $unitMap[$val['unit']]  ?? '';
				$unit1 = $unitMap[$val['unit1']] ?? '';
				$unit2 = $unitMap[$val['unit2']] ?? '';
				// 如果 unit2 为空，qty2 也置空
				if ($unit2 === '') {
					$val['qty2'] = '';
				}
				$dataArray[] = "(
					'{$uid}',
					'{$val['itemNo']}',
					'{$val['itemName']}',
					'{$val['gcode']}',
					'{$val['gmodel']}',
					'{$val['price']}',
					'{$val['product_currency']}',
					'{$unit}',
					'{$unit1}',
					'{$val['qty1']}',
					'{$unit2}',
					'{$val['qty2']}',
					'{$val['netWeight']}',
					'{$val['weight']}'
				)";
			}

            //把生产的数组分成每份一万个
            $arrays = array_chunk($dataArray, 10000);
            //开始循环导入
            foreach($arrays as $k=>$v){
                $valuesString = implode(",",$v);
                $sql = "Insert into ln_products (auth_id,itemNo,itemName,gcode,gmodel,price,product_currency,unit,unit1,qty1,unit2,qty2,netWeight,weight) VALUES $valuesString";
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



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
