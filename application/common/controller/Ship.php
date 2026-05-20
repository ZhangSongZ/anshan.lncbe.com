<?php

namespace app\common\controller;
use app\common\controller\ftp;
use think\db;
/**
 * 507运抵报文
 */
class Ship
{
    public function GUID()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);
        $guid1 = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $guid1;
    }

    public function Gen507($result,$billNo,$uid){
       $resuFtp = db::name('merchannel')->where('auth_id',$uid)->where('status',1)->where('category','9610')->find();
        $arrival = returnMerchannel($resuFtp['arrival']);
         if($arrival['before']=='RMQ'){
             $channel = Db::name('rmq')->where('rmq_id',$arrival['after'])->find();
        }else{
             $channel = Db::name('ftp')->where('id',$arrival['after'])->find();
        }
        
        
        
        $path = date("Ymd", time());
        //如果不存在当天的目录则建立
        if (!is_dir('customsMessage/507/' . $path)) {
            mkdir('customsMessage/507/' . $path, 0777, true);
        }
        set_time_limit(0);

        //构建507XML报文
        $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $item .= "<ceb:CEB507Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
        //根据复选框得到数据在订单主表的id $ID 是订单号


            $item .= "<ceb:Arrival>\n";
            $item .= "  <ceb:ArrivalHead>\n";
            $item .= "    <ceb:guid>" . $this->GUID() . "</ceb:guid>\n";
            $item .= "    <ceb:appType>" . '1' . "</ceb:appType>\n";
            $item .= "    <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";
            $item .= "    <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";
            $item .= "    <ceb:customsCode>" . $result['customsCode'] . "</ceb:customsCode>\n";//申报地海关代码,办理通关手续的 4 位海关编号！！！！！！！！！
            $item .= "    <ceb:copNo>" . $result['copNo'] . "</ceb:copNo>\n";//
            $item .= "    <ceb:operatorCode>" . $result['operatorCode'] . "</ceb:operatorCode>\n";
            $item .= "    <ceb:operatorName>" . $result['operatorName'] . "</ceb:operatorName>\n";
            $item .= "    <ceb:loctNo>".$result['loctNo']."</ceb:loctNo>\n";//监管场所代码
            $item .= "    <ceb:ieFlag>E</ceb:ieFlag>\n";
            $item .= "    <ceb:trafMode>".$result['trafMode']."</ceb:trafMode>\n";//运输方式！！！！！！！！！！！！！！
           if($result['decBillNo']=='否'){ //分单模式
                $item .= "        <ceb:billNo>".$result['billNo']."</ceb:billNo>\n";//提运单号$orderMain['billNo']
            }else{
                 $item .= "        <ceb:billNo></ceb:billNo>\n";//提运单号$orderMain['billNo']
            }
            $item .= "    <ceb:domesticTrafNo>" . $result['voyageNo'] . "</ceb:domesticTrafNo>\n";//境内运输工具编号！！！！！！！！！！！！！！！
            $item .= "    <ceb:logisticsCode>" . $result['logisticsCode'] . "</ceb:logisticsCode>\n";
            $item .= "    <ceb:logisticsName>" . $result['logisticsName'] . "</ceb:logisticsName>\n";
            $item .= "    <ceb:msgCount>" . "1" . "</ceb:msgCount>\n";
            $item .= "    <ceb:msgSeqNo>" . "1" . "</ceb:msgSeqNo>\n";
            $item .= "    <ceb:note></ceb:note>\n";
            $item .= "</ceb:ArrivalHead>\n";
            $item .= "</ceb:Arrival>\n";
        $item .= "    <ceb:BaseTransfer>\n";
        $item .= "        <ceb:copCode>" . $result['logisticsCode'] . "</ceb:copCode>\n";
        $item .= "        <ceb:copName>" . $result['logisticsName'] . "</ceb:copName>\n";
        $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
       
        $item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
      
        $item .= "        <ceb:note></ceb:note>\n";
        $item .= "    </ceb:BaseTransfer>\n";
        $item .= "</ceb:CEB507Message>\n";
        $realTime = date('Hi', time());
        //设定文件目录以及生成的文件名称.
        $localfile = 'customsMessage/507/' . $path . '/CEB507Message' . $billNo . '_M' . $realTime . '.xml';
        //生成报文
        file_put_contents($localfile, $item);//在本地生成xml
        
         //选择通道
        if($total['before']=='RMQ'){
                $data['host'] = $channel['host'];
                $data['port'] = $channel['port'];
                $data['user'] = $channel['user'];
                $data['pwd'] = $channel['pwd'];
                $data['exchangeN'] = $channel['exchangeN'];
                $data['queueNsend'] = $channel['queueNsend'];
                $data['vhost'] = $channel['vhost'];
                //$items = json_encode($data);
                // 构建查询字符串
                $postFields = [
                    'xml' => $item,
                    'conf' => $data,
                ];
				$postFieldsjson = json_encode($postFields);
                $postUrl = 'http://anshan.lncbe.com/rmq/sybrrmq.php';
                $result = $this->posts($postUrl,$postFieldsjson);
         }else{
                //FTP上传
                //查询FTP信息
                $ftp_host = $channel['ftp_ip'];
                $ftp_port = $channel['ftp_port'];
                $ftp_user = $channel['ftp_username'];
                $ftp_pass = $channel['ftp_password'];
                $remotefile = $channel['ftp_dir'] ."CEB507Message" . rand(10000,99999) . '_M' . $realTime . '.xml';
                $ftp = new ftp($ftp_host,$ftp_port,$ftp_user,$ftp_pass); //连接ftp
                $ftp->up_file($localfile,$remotefile); //上传ftp
                $ftp->close();  //关闭ftp
            }
        
        unset($item);

    }

    public function posts($postUrl,$postDate){
        $header = array(
            "content-type: application/json; charset=UTF-8",
        );
        //if (is_array($postDate)) {
            //$postDate = http_build_query($postDate);
        //}
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDate);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}