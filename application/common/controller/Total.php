<?php

namespace app\common\controller;

use think\Db;

/**
 * 607总分单报文
 */
class Total
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
    public function Gen607($result,$msgCount,$msgSeqNo,$voyageNo,$copNo,$billNo,$allWeight,$uid)
    {
        $resuFtp = db::name('merchannel')->where('auth_id',$uid)->where('status',1)->where('category','9610')->find();
        $total = returnMerchannel($resuFtp['total']);
         if($total['before']=='RMQ'){
             $channel = Db::name('rmq')->where('rmq_id',$total['after'])->find();
        }else{
             $channel = Db::name('ftp')->where('id',$total['after'])->find();
        }
        
        
        set_time_limit(0);
        $path = date("Ymd", time());
        //如果不存在当天的目录则建立
        if (!is_dir('customsMessage/607/' . $path)) {
            mkdir('customsMessage/607/' . $path, 0777, true);
        }

            $i=1;
          //  $allWeight=0;
            $itemLog = '';
            foreach($result as $value){
             //   $allWeight +=  $value['weight'];
                $itemLog .= "<ceb:WayBillList>\n";
                $itemLog .= "    <ceb:gnum>" . $i . "</ceb:gnum>\n";
                $itemLog .= "    <ceb:totalPackageNo></ceb:totalPackageNo>\n";//总包号 不必填
                $itemLog .= "    <ceb:logisticsNo>" . $value['logisticsNo'] . "</ceb:logisticsNo>\n";//物流运单编号
                $itemLog .= "    <ceb:note></ceb:note>\n";
                $itemLog .= "</ceb:WayBillList>\n";
                $i++;
            }

            //构建607XML报文
            $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $item .= "<ceb:CEB607Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
            $item .= "<ceb:WayBill>\n";
            $item .= "  <ceb:WayBillHead>\n";
            $item .= "    <ceb:guid>" . $this->GUID() . "</ceb:guid>\n";
            $item .= "    <ceb:appType>" . '1' . "</ceb:appType>\n";
            $item .= "    <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";
            $item .= "    <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";
            $item .= "    <ceb:customsCode>".$result[0]['customsCode']."</ceb:customsCode>\n";//申报地海关代码 办理通关手续的 4 位海关编号
            $item .= "    <ceb:copNo>" . $copNo . "</ceb:copNo>\n";//企业生成标识唯一编号
            $item .= "    <ceb:agentCode>" .$result[0]['agentCode'] . "</ceb:agentCode>\n";//申报企业代码
            $item .= "    <ceb:agentName>" . $result[0]['agentName'] . "</ceb:agentName>\n";
            $item .= "    <ceb:loctNo>".$result[0]['loctNo']."</ceb:loctNo>\n";//监管场所代码
            $item .= "    <ceb:trafMode>" . $result[0]['trafMode'] . "</ceb:trafMode>\n";//运输方式代码!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $item .= "    <ceb:trafName>" . $voyageNo . "</ceb:trafName>\n";//运输工具名称!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $item .= "    <ceb:voyageNo>" . $voyageNo . "</ceb:voyageNo>\n";//航班航次号!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $billNo = str_replace(array("?", "&", "？", "＆", " ", "<null>", "\r", "\n", "\t", " ", " "),"", trim($billNo));//替换掉各种不同空格符号
            $item .= "    <ceb:billNo>" . $billNo . "</ceb:billNo>\n";//提运单号
            $item .= "    <ceb:domesticTrafNo></ceb:domesticTrafNo>\n";//境内运输工具编号 不必填
            //$item .= "    <ceb:grossWeight>" . $oM['weight'] . "</ceb:grossWeight>\n";//货物及其包装材料的重量之和,千克
            $item .= "    <ceb:grossWeight>" . round($allWeight,2) . "</ceb:grossWeight>\n";//货物及其包装材料的重量之和,千克
            $item .= "    <ceb:logisticsCode>" . $result[0]['logisticsCode'] . "</ceb:logisticsCode>\n";//物流企业代码
            $item .= "    <ceb:logisticsName>" . $result[0]['logisticsName'] . "</ceb:logisticsName>\n";//物流企业名称
            $item .= "    <ceb:msgCount>" . $msgCount . "</ceb:msgCount>\n";//报文总数
            $item .= "    <ceb:msgSeqNo>" . $msgSeqNo . "</ceb:msgSeqNo>\n";//报文序号
            $item .= "    <ceb:note></ceb:note>\n";
            $item .= "</ceb:WayBillHead>\n";
            $item .= $itemLog;
            unset($itemLog);
            $item .= "</ceb:WayBill>\n";
            $item .= "    <ceb:BaseTransfer>\n";
            $item .= "        <ceb:copCode>" . $result[0]['agentCode'] . "</ceb:copCode>\n";
            $item .= "        <ceb:copName>" . $result[0]['agentName'] . "</ceb:copName>\n";
            $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
     
            $item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
      

            $item .= "        <ceb:note></ceb:note>\n";
            $item .= "    </ceb:BaseTransfer>\n";
            $item .= "</ceb:CEB607Message>\n";
            $realTime = date('His', time());
            //设定文件目录以及生成的文件名称.
            $localfile = 'customsMessage/607/' . $path . '/CEB607Message' . $billNo . '_M' . $realTime .rand(10,99) . '.xml';
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
                $remotefile = $channel['ftp_dir'] ."CEB607Message" . rand(10000,99999) . '_M' . $realTime . '.xml';
                $ftp = new ftp($ftp_host,$ftp_port,$ftp_user,$ftp_pass); //连接ftp
                $ftp->up_file($localfile,$remotefile); //上传ftp
                $ftp->close();  //关闭ftp
            }

            unset($item);
     } //End function Gen607

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