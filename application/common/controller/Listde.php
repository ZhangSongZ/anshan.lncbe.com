<?php

namespace app\common\controller;
use app\common\controller\ftp;
use app\common\controller\Sybrrmq;
use think\Db;

/**
 * 603清单报文
 */

class Listde
{
    //函数功能:产生36位的唯一$guid (不规则字符串.)
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

    //出口清单报文
    public function Gen603($result,$uid)
    {
        $resuFtp = db::name('merchannel')->where('auth_id',$uid)->where('status',1)->where('category','9610')->find();
        $list = returnMerchannel($resuFtp['list']);
         if($list['before']=='RMQ'){
             $channel = Db::name('rmq')->where('rmq_id',$list['after'])->find();
        }else{
             $channel = Db::name('ftp')->where('id',$list['after'])->find();
        }
      
        $path = date("Ymd", time());
        //如果不存在当天的目录则建立
        if (!is_dir('customsMessage/603/' . $path)) {
            mkdir('customsMessage/603/' . $path, 0777, true);
        }
        set_time_limit(0);
        //构建603XML报文
        $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $item .= "<ceb:CEB603Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
        //根据复选框得到数据在订单主表的id $ID 是订单号
        foreach ($result as $Detail) {
            $i = 0;
            $copNo = 'B' . date('Ymdhis', time()) . rand(100000000, 999999999);
            $xml = '';
            //多个商品的时候报文连接多次.
            foreach ($Detail as $value) {
                $i += 1;
                $xml .= "	<ceb:InventoryList>\n";
                $xml .= "        <ceb:gnum>" . $i . "</ceb:gnum>\n";
                $xml .= "        <ceb:itemNo>" . $value['gcode'] . "</ceb:itemNo>\n";
                $xml .= "        <ceb:itemRecordNo></ceb:itemRecordNo>\n";//账册备案料号 保税出口模式必填
                $xml .= "        <ceb:itemName>" . $value['product_name'] . "</ceb:itemName>\n";//企业商品名称 企业自定义的商品名称，不必填，汇总后用于退税品名
                $sbuGcode = substr($value['gcode'],0,4);
                $productCode = $sbuGcode."000000";
                $xml .= "        <ceb:gcode>" . $productCode . "</ceb:gcode>\n";
                $xml .= "        <ceb:gname>" . $value['product_name'] . "</ceb:gname>\n";
                $xml .= "        <ceb:gmodel>" . $value['gmodel'] . "</ceb:gmodel>\n";
                // $xml .= "        <ceb:gmodel>" . $value['gmodel'] . "</ceb:gmodel>\n";
                $xml .= "        <ceb:barCode>" . '无' . "</ceb:barCode>\n";
                $xml .= "        <ceb:country>" . $value['country'] . "</ceb:country>\n";
                $xml .= "        <ceb:currency>" . $value['item_currency'] . "</ceb:currency>\n";
                
                $xml .= "        <ceb:qty>" . $value['qty'] . "</ceb:qty>\n";
                if($value['qty1']){
                      $xml .= "        <ceb:qty1>" . (float)$value['qty1'] . "</ceb:qty1>\n"; 
                }else{
                    $xml .= "        <ceb:qty1>" . (float)$value['netWeight']*(float)$value['qty'] . "</ceb:qty1>\n"; 
                }
                
                if($value['qty2']){
                    $xml .= "        <ceb:qty2>" . (float)$value['qty2']. "</ceb:qty2>\n"; 
                }
                
                if($value['unit']){
                      $xml .= "        <ceb:unit>" . (float)$value['unit'] . "</ceb:unit>\n"; 
                }else{
                    $xml .= "        <ceb:unit>" . '007' . "</ceb:unit>\n"; 
                }
                
                if($value['unit1']){
                      $xml .= "        <ceb:unit1>" . (float)$value['unit1'] . "</ceb:unit1>\n"; 
                }else{
                    $xml .= "        <ceb:unit1>" . '035' . "</ceb:unit1>\n"; 
                }
                if($value['unit2']){
                      $xml .= "        <ceb:unit2>" . (float)$value['unit2'] . "</ceb:unit2>\n"; 
                }
                
                $xml .= "        <ceb:price>" . (float)$value['price'] . "</ceb:price>\n";
                $xml .= "        <ceb:totalPrice>" . (float)$value['qty'] * (float)$value['price'] . "</ceb:totalPrice>\n";
                $xml .= "        <ceb:note>" . "" . "</ceb:note>\n";
                $xml .= "	</ceb:InventoryList>\n";
            }
            $item .= "    <ceb:Inventory>\n";
            $item .= "    <ceb:InventoryHead>\n";
            $item .= "        <ceb:guid>" . $this->GUID() . "</ceb:guid>\n";
            $item .= "        <ceb:appType>" . '1' . "</ceb:appType>\n";
            $appTime = date('YmdHis', time());
            $item .= "        <ceb:appTime>" . $appTime . "</ceb:appTime>\n";
            $item .= "        <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";//企业报送状态。1-暂存,2-申报,3-删除。
            $item .= "        <ceb:customsCode>" . $Detail[0]['customsCode'] . "</ceb:customsCode>\n";//办理通关手续的 4 位海关代码
            $item .= "        <ceb:ebpCode>" . $Detail[0]['ebpCode'] . "</ceb:ebpCode>\n";
            $item .= "        <ceb:ebpName>" . $Detail[0]['ebpName'] . "</ceb:ebpName>\n";
            $item .= "        <ceb:orderNo>" .$Detail[0]['orderNo'] . "</ceb:orderNo>\n";
            $item .= "        <ceb:logisticsCode>" . $Detail[0]['logisticsCode'] . "</ceb:logisticsCode>\n";
            $item .= "        <ceb:logisticsName>" . $Detail[0]['logisticsName'] . "</ceb:logisticsName>\n";
            $item .= "        <ceb:logisticsNo>" . $Detail[0]['logisticsNo'] . "</ceb:logisticsNo>\n";
            $item .= "        <ceb:copNo>" . $copNo . "</ceb:copNo>\n"; //企业生成标识唯一编号
            // $item .= "        <ceb:preNo></ceb:preNo>\n"; //电子口岸标识唯一编号
            // $item .= "        <ceb:invtNo></ceb:invtNo>\n"; //海关审结标识唯一编号
            $item .= "        <ceb:ieFlag>" . 'E' . "</ceb:ieFlag>\n";//I-进口,E-出口
            $item .= "        <ceb:portCode>" . $Detail[0]['portCode'] . "</ceb:portCode>\n"; //出口口岸代码
            $item .= "        <ceb:ieDate>" . date('Ymd', time()+86400) . "</ceb:ieDate>\n";//出口日期
            $item .= "        <ceb:statisticsFlag>" .$Detail[0]['statisticsFlag']. "</ceb:statisticsFlag>\n";//A-简化申报;B-汇总申报；
            $item .= "        <ceb:agentCode>" . $Detail[0]['agentCode'] . "</ceb:agentCode>\n";//申报单位的海关登记编号
            $item .= "        <ceb:agentName>" . $Detail[0]['agentName'] . "</ceb:agentName>\n";//申报单位的海关登记名称
            $item .= "        <ceb:ebcCode>" . $Detail[0]['ebcCode'] . "</ceb:ebcCode>\n";//收发货人代码
            $item .= "        <ceb:ebcName>" . $Detail[0]['ebcName'] . "</ceb:ebcName>\n";//收发货人名称
            $item .= "        <ceb:ownerCode>" . $Detail[0]['ownerCode'] . "</ceb:ownerCode>\n";//生产销售企业代
            $item .= "        <ceb:ownerName>" . $Detail[0]['ownerName'] . "</ceb:ownerName>\n";//生产销售企业名
            $item .= "        <ceb:iacCode></ceb:iacCode>\n";//区内企业代码
            $item .= "        <ceb:iacName></ceb:iacName>\n";//区内企业名称
            $item .= "        <ceb:emsNo></ceb:emsNo>\n";//账册编号
            $item .= "        <ceb:tradeMode>" . $Detail[0]['tradeMode'] . "</ceb:tradeMode>\n";//一般出口填 9610，特殊区域出口填 1210
            $item .= "        <ceb:trafMode>" . $Detail[0]['trafMode'] . "</ceb:trafMode>\n";//运输方式
            $item .= "        <ceb:trafName>" . $Detail[0]['voyageNo'] . "</ceb:trafName>\n";//运输工具名称$orderMain['voyageNo']
            $item .= "        <ceb:voyageNo>".$Detail[0]['voyageNo']."</ceb:voyageNo>\n";//航班航次号
            if($Detail[0]['decBillNo']=='否'){ //分单模式
                $item .= "        <ceb:billNo>".$Detail[0]['billNo']."</ceb:billNo>\n";//提运单号$orderMain['billNo']
            }else{
                 $item .= "        <ceb:billNo></ceb:billNo>\n";//提运单号$orderMain['billNo']
            }
            $item .= "        <ceb:totalPackageNo>".$Detail[0]['totalPackageNo']."</ceb:totalPackageNo>\n";//总包号
            $item .= "        <ceb:loctNo>".$Detail[0]['loctNo']."</ceb:loctNo>\n";//监管场所代码
            $item .= "        <ceb:licenseNo></ceb:licenseNo>\n";//许可证号
            $item .= "        <ceb:country>" . $Detail[0]['country'] . "</ceb:country>\n";
            $item .= "        <ceb:POD>" . $Detail[0]['country'] . "</ceb:POD>\n";//指运港代码 ***************************// destinationPort
            $item .= "        <ceb:freight>" . $Detail[0]['cost_freight'] . "</ceb:freight>\n";
            $item .= "        <ceb:fCurrency>" . $Detail[0]['freight_currency'] . "</ceb:fCurrency>\n";
            $item .= "        <ceb:fFlag>3</ceb:fFlag>\n";//运费标志 1-率，2-单价，3-总价
            $item .= "        <ceb:insuredFee>0</ceb:insuredFee>\n";
            $item .= "        <ceb:iCurrency>". $Detail[0]['item_currency'] ."</ceb:iCurrency>\n";
            $item .= "        <ceb:iFlag>3</ceb:iFlag>\n";
            // $item .= "        <ceb:wrapType>" . $channel['wrapType'] . "</ceb:wrapType>\n";
            $item .= "        <ceb:wrapType>6</ceb:wrapType>\n";
            $item .= "        <ceb:packNo>" . '1' . "</ceb:packNo>\n";
            $item .= "        <ceb:grossWeight>" . (float)$Detail[0]['weight'] . "</ceb:grossWeight>\n";
            $item .= "        <ceb:netWeight>" . (float)$Detail[0]['netWeights'] . "</ceb:netWeight>\n";
            $item .= "        <ceb:note>" . '跨境电子商务' . "</ceb:note>\n";
            $item .= "    </ceb:InventoryHead>\n";
            $item .= $xml;
            unset($xml);
            $item .= "    </ceb:Inventory>\n";
        }//End Foreach Loop
        $item .= "    <ceb:BaseTransfer>\n";
        $item .= "        <ceb:copCode>" . $result[0][0]['agentCode'] . "</ceb:copCode>\n";
        $item .= "        <ceb:copName>" . $result[0][0]['agentName'] . "</ceb:copName>\n";
        $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
       
        $item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
      
        $item .= "        <ceb:note></ceb:note>\n";
        $item .= "    </ceb:BaseTransfer>\n";
        //订阅表体END
        $item .= "</ceb:CEB603Message>\n";
        $realTime = date('Hi', time());
        //设定文件目录以及生成的文件名称.
        $localfile = 'customsMessage/603/' . $path . '/CEB603Message' . rand(10000,99999) . '_M' . $realTime . '.xml';
        //生成报文
        file_put_contents($localfile, $item);//在本地生成xml
      
       //选择通道
        if($list['before']=='RMQ'){
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
                $remotefile = $channel['ftp_dir'] ."CEB303Message" . rand(10000,99999) . '_M' . $realTime . '.xml';
                $ftp = new ftp($ftp_host,$ftp_port,$ftp_user,$ftp_pass); //连接ftp
                $ftp->up_file($localfile,$remotefile); //上传ftp
                $ftp->close();  //关闭ftp
            }

        unset($item);

    }//End Function Gen603
    public function posts($postUrl,$postDate){
        $header = array(
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
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