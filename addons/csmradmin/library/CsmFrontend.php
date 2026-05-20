<?php
// +----------------------------------------------------------------------
// Csmmeet [ CSM系列公共源码 ]
// Author: chensm <chenshiming0802@163.com>
// Create by chensm at 2020-02-26
// +----------------------------------------------------------------------
namespace addons\csmradmin\library;

use app\common\controller\Frontend;
use think\Exception;

/**
 * 前台控制器基类
 */
class CsmFrontend extends Frontend
{

    public function csmasseertnotnull($value, $msg)
    {
        if ($value == null || $value == "") {
            $this->error($msg);
            exit();
        }
    }

    public function tracedao($dao)
    {
        echo $dao->getLastSql();
        die();
    }

    /**
     * 封装了从header获取token，完成登录，如果没有登录则报错
     */
    public function csmassertlogin()
    {
        $token = $this->request->header("csmlogintoken");
        $ff = $this->auth->init($token);
        if ($ff === false) {
            $this->error('请重新登录！', 'pages/csmmeet/login', true);
        }
    }

    /**
     * request封装了必填项校验
     *
     * @throws Exception
     * @return string
     */
    public function csmreq($paramname, $isRequired)
    {
        $paramvalue = $this->request->request($paramname);
        if ($paramvalue == "" || trim($paramvalue) == "") {
            $this->error("缺少参数 : " . $paramname);
        }
        return trim($paramvalue);
    }

    /**
     * 返回json处理，封装了默认code和msn字段
     */
    public function csmjson($result)
    {
        $result2 = [
            "code" => "1",
            "msg" => "",
            "data" => $result
        ];
        $json = json($result2);
        return $json;
    }

    public function csmerrorjson($result, $msg = "")
    {
        $result2 = [
            "code" => "0",
            "msg" => $msg,
            "data" => $result
        ];
        $json = json($result2);
        return $json;
    }
    
}