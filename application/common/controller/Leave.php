<?php

namespace app\common\controller;

use think\Db;

/**
 * 509离境报文
 */
class Leave
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
    public function Gen509($result,$msgCount,$msgSeqNo,$voyageNo,$copNo,$billNo,$time,$uid)
    {
            set_time_limit(0);
           $resuFtp = db::name('merchannel')->where('auth_id',$uid)->where('status',1)->where('category','9610')->find();
            $departure = returnMerchannel($resuFtp['departure']);
             if($departure['before']=='RMQ'){
                 $channel = Db::name('rmq')->where('rmq_id',$departure['after'])->find();
            }else{
                 $channel = Db::name('ftp')->where('id',$departure['after'])->find();
            }
            
            
            
            $path = date("Ymd", time());
            //如果不存在当天的目录则建立
            if (!is_dir('customsMessage/509/' . $path)) {
                mkdir('customsMessage/509/' . $path, 0777, true);
            }
            $i=1;
            $itemL ='';
            foreach($result as $value){
                $itemL .= "<ceb:DepartureList>\n";
                $itemL .= "    <ceb:gnum>" . $i . "</ceb:gnum>\n";
                $itemL .= "    <ceb:totalPackageNo></ceb:totalPackageNo>\n";
                $itemL .= "    <ceb:logisticsNo>" . $value['logisticsNo'] . "</ceb:logisticsNo>\n";
                $itemL .= "    <ceb:note></ceb:note>\n";
                $itemL .= "</ceb:DepartureList>\n";
                $i++;
            }
            //构建509XML报文
            $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $item .= "<ceb:CEB509Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
            $item .= "<ceb:Departure>\n";
            $item .= "  <ceb:DepartureHead>\n";
            $item .= "    <ceb:guid>" . $this->GUID() . "</ceb:guid>\n";//系统唯一序号
            $item .= "    <ceb:appType>" . '1' . "</ceb:appType>\n";//报送类型 1-新增，2-变更，3-删除
            $item .= "    <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";//报送时间
            $item .= "    <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";//企业报送状态。1-暂存，2-申报。
            $item .= "    <ceb:customsCode>".$result[0]['customsCode']."</ceb:customsCode>\n";//申报地海关代码
            //$item .= "    <ceb:copNo>" . $orderMain['copNo'] . "</ceb:copNo>\n";//企业唯一编号
            $item .= "    <ceb:copNo>" . $copNo . "</ceb:copNo>\n";//企业唯一编号
            $item .= "    <ceb:logisticsCode>" . $result[0]['logisticsCode'] . "</ceb:logisticsCode>\n";
            $item .= "    <ceb:logisticsName>" . $result[0]['logisticsName'] . "</ceb:logisticsName>\n";
            $item .= "    <ceb:trafMode>" . $result[0]['trafMode'] . "</ceb:trafMode>\n";//运输方式!!!!!!!!!!!!!!!!!!!!!!!!
            $item .= "    <ceb:trafName>" . $voyageNo . "</ceb:trafName>\n";//运输工具名称 邮路运输方可为空。
            $item .= "    <ceb:voyageNo>" . $voyageNo . "</ceb:voyageNo>\n";//航班航次号 邮路运输方可为空。
            
            if($result[0]['decBillNo']=='否'){ //分单模式
                $item .= "        <ceb:billNo>".$result[0]['billNo']."</ceb:billNo>\n";//提运单号$orderMain['billNo']
            }else{
                 $item .= "        <ceb:billNo></ceb:billNo>\n";//提运单号$orderMain['billNo']
            }
            
            $item .= "    <ceb:leaveTime>" . $time . "</ceb:leaveTime>\n";
            $item .= "    <ceb:msgCount>" . $msgCount . "</ceb:msgCount>\n";
            $item .= "    <ceb:msgSeqNo>" . $msgSeqNo . "</ceb:msgSeqNo>\n";
            $item .= "    <ceb:note></ceb:note>\n";
            $item .= "</ceb:DepartureHead>\n";
            $item .= $itemL;
            unset($itemL);
            $item .= "</ceb:Departure>\n";
            $item .= "    <ceb:BaseTransfer>\n";
            $item .= "        <ceb:copCode>" . $result[0]['logisticsCode'] . "</ceb:copCode>\n";
            $item .= "        <ceb:copName>" . $result[0]['logisticsName'] . "</ceb:copName>\n";
            $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
           
            $item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
          
            $item .= "        <ceb:note></ceb:note>\n";
            $item .= "    </ceb:BaseTransfer>\n";
            $item .= "</ceb:CEB509Message>\n";
            $realTime = date('His', time());
            //设定文件目录以及生成的文件名称.
            $localfile = 'customsMessage/509/' . $path . '/CEB509Message' . $billNo . '_M' . $realTime .rand(10,99) . '.xml';
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
                $remotefile = $channel['ftp_dir'] ."CEB509Message" . rand(10000,99999) . '_M' . $realTime . '.xml';
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