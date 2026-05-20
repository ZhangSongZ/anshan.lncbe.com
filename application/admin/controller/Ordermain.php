<?php

namespace app\admin\controller;

use app\admin\library\Auth;
use app\common\controller\Backend;
use app\common\controller\Popen;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use think\db\exception\BindParamException;
use think\exception\PDOException;
use think\db;
use think\Process;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Ordermain extends Backend
{

    /**
     * Ordermain模型对象
     * @var \app\admin\model\Ordermain
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Ordermain;

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
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['orderNo','createtime','copNo','ie_date','totalPackageNo','pack_no','cost_item','cost_freight']);
                
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }
    //报关数据
    public function order(){


        $sql = "load data local infile './uploads/20240305/4f02daea2b96b3fe8314d6c146439e3b.csv' ignore into table name character set gbk fields terminated by '_' lines terminated by '|'(`name`);";

       $tt =  Db::execute($sql);
       var_dump($tt);die;



//        for ($i=0; $i<1000000; $i++) {
//            // 构造 SQL INSERT 语句
//            $sql = "INSERT INTO ln_tests (id) VALUES ('5555555');";
//
//            if (db::query($sql) === TRUE) {
//                echo "Record inserted successfully\n";
//            } else {
//                echo "Error: " . $sql . "<br>" . db::->error;
//            }
//        }


//        $process1 = new Process(function(){
//            echo "11";
//        });
//        $process2 = new Process(function(){
//
//        });
//
//        $process1->start();
//        $process2->start();
//
//
//        $process1->wait();
//        $process2->wait();

//
//        $obj = new Popen();
//        $pp =  $obj->getUsersByTime();


    }

    /**
     * 导入
     *
     * @return void
     * @throws PDOException
     * @throws BindParamException
     */
    /**
     * 创建一个pdo连接
     * [create_pdo description]
     * @return {[type]} [description]
     */
    public function create_pdo()
    {
        $ip = config('database.hostname');
        $user = config('database.username');
        $pwd = config('database.password');
        $port = config('database.hostport');
        $dbname = config('database.database');

        $dsn = 'mysql:dbname=' . $dbname . ';host=' . $ip . ';port=' . $port;

        $options = [\PDO::MYSQL_ATTR_LOCAL_INFILE => true];
        $db = new \PDO($dsn, $user, $pwd, $options);

        return $db;

    }
    /**
     * 真正执行的部分
     * [create_new_data description]
     * @param  {[type]} $arr   [description]
     * @param  {[type]} $table [description]
     * @return {[type]}        [description]
     */
    public function create_new_data($arr, $table)
    {
        // 将所有的数据写入到文件中
        $myFile   = './data/db/'.$table.'_'.date('YmdHis').'.txt';
        $handler  = fopen($myFile,'a+');
        $content  = implode("\n" ,$arr);
        fwrite($handler ,$content . "\r\n");

        $prefix    = config('database.prefix');
        $tableName = $prefix . $table;

        $sql = "LOAD DATA LOCAL INFILE '".$myFile."' INTO TABLE ".$tableName." CHARACTER SET utf8 FIELDS TERMINATED BY ',' LINES TERMINATED BY '\\n' (`id`,`name`,`add_time`)";

        // tp5自带的db执行报错 query 和 execute 都不行 只能重新new一个pdo 顺便说一句 傻逼tp
        $pdo = $this->create_pdo();
        $res = $pdo->exec($sql);
        var_dump($res);die;

        return $res;
    }



    public function import1()
    {
        $file = $this->request->request('file');



        // 将所有的数据写入到文件中

//        $prefix    = config('database.prefix');
//
//        $tableName = $prefix.'tests' ;
//        $sql = "load data local infile '/uploads/20240305/4f02daea2b96b3fe8314d6c146439e3b.csv' ignore into table ln_tests character set gbk fields terminated by '_' lines terminated by '|'(`id`);";
//        $pdo = $this->create_pdo();
//        $res = $pdo->exec($sql);
//        return $res;
        $options = [\PDO::MYSQL_ATTR_LOCAL_INFILE => true];
       // $sql = "load data local infile '/uploads/20240305/4f02daea2b96b3fe8314d6c146439e3b.csv' ignore into table ln_tests character set gbk fields terminated by '_' lines terminated by '|'(`id`);";
        $sql = "LOAD DATA LOCAL INFILE '.$file' INTO TABLE name CHARACTER SET gbk FIELDS TERMINATED BY ',' LINES TERMINATED BY '\\n' (`name`)";
        $tt =  Db::execute($sql);
        var_dump($tt);die;
    }









}
