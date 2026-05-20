<?php
namespace app\common\controller;

use think\Db;

/**
 * Fonde licence
 * 公共类-报文发送 三单
 */

class Popen{

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

    public function Gen303($result,$uid)
    {
        $resuFtp = db::name('merchannel')->where('auth_id',$uid)->where('status',1)->where('category','9610')->find();
        $order = returnMerchannel($resuFtp['order']);
         if($order['before']=='RMQ'){
             $channel = Db::name('rmq')->where('rmq_id',$order['after'])->find();
         }else{
             $channel = Db::name('ftp')->where('id',$order['after'])->find();
        }
        
        $path = date("Ymd", time());
        //如果不存在当天的目录则建立
        if (!is_dir('customsMessage/303/' . $path)) {
            mkdir('customsMessage/303/' . $path, 0777, true);
        }
        set_time_limit(0);
        //构建303XML报文
        $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $item .= "<ceb:CEB303Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";

        foreach ($result as $Detail) {

            $i = 0;
            //循环订单里的多个商品
            $goodsValue = 0;
            $xml = '';
            foreach ($Detail as $value) {
                $i += 1;
                $goodsValue += (float)$value['price'] * (float)$value['qty'];
                $xml .= "    <ceb:OrderList>\n";
                $xml .= "        <ceb:gnum>" . $i . "</ceb:gnum>\n";
                $xml .= "        <ceb:itemNo>" . $value['gcode'] . "</ceb:itemNo>\n";
                $xml .= "        <ceb:itemName>" . $value['product_name'] . "</ceb:itemName>\n";
                $xml .= "        <ceb:itemDescribe>" . $value['product_name'] . "</ceb:itemDescribe>\n";
                $xml .= "        <ceb:barCode></ceb:barCode>\n";
                $xml .= "        <ceb:unit>" . '007' . "</ceb:unit>\n";
                $xml .= "        <ceb:currency>" . $value['item_currency'] . "</ceb:currency>\n";
                $xml .= "        <ceb:qty>" . $value['qty'] . "</ceb:qty>\n";
                $xml .= "        <ceb:price>" . (float)$value['price'] . "</ceb:price>\n";
                $xml .= "        <ceb:totalPrice>" . (float)$value['qty'] * (float)$value['price'] . "</ceb:totalPrice>\n";
                $xml .= "        <ceb:note></ceb:note>\n";
                $xml .= "    </ceb:OrderList>\n";
            }
            $item .= "    <ceb:Order>\n";
            $item .= "    <ceb:OrderHead>\n";
            $item .= "        <ceb:guid>" . $this->GUID() . "</ceb:guid>\n"; //系统唯一序号
            $item .= "        <ceb:appType>" . '1' . "</ceb:appType>\n";
            $item .= "        <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";
            $item .= "        <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";
            $item .= "        <ceb:orderType>" . 'E' . "</ceb:orderType>\n";//电商平台的订单类型 I-进口商品订单；E-出口商品订单；9710，订单类型为B
            $item .= "        <ceb:orderNo>" . $Detail[0]['orderNo'] . "</ceb:orderNo>\n"; //订单编号
            $item .= "        <ceb:ebpCode>" . $Detail[0]['ebpCode'] . "</ceb:ebpCode>\n";
            $item .= "        <ceb:ebpName>" . $Detail[0]['ebpName'] . "</ceb:ebpName>\n";
            $item .= "        <ceb:ebcCode>" . $Detail[0]['ebcCode']. "</ceb:ebcCode>\n";
            $item .= "        <ceb:ebcName>" . $Detail[0]['ebcName'] . "</ceb:ebcName>\n";
            $item .= "        <ceb:goodsValue>" . $goodsValue . "</ceb:goodsValue>\n";
            $item .= "        <ceb:freight>" . (float)$Detail[0]['cost_freight'] . "</ceb:freight>\n";
            $item .= "        <ceb:currency>" . $Detail[0]['item_currency'] . "</ceb:currency>\n";
            $item .= "        <ceb:note>跨境电子商务</ceb:note>\n"; //海关要求必须是这个备注
            $item .= "    </ceb:OrderHead>\n";
            $item .= $xml;
            $item .= "</ceb:Order>\n";
            unset($xml);
            unset($goodsValue);
        }
        $item .= "    <ceb:BaseTransfer>\n";
        $item .= "        <ceb:copCode>" . $result[0][0]['ebcCode'] . "</ceb:copCode>\n";//传输企业代码
        $item .= "        <ceb:copName>" . $result[0][0]['ebcName'] . "</ceb:copName>\n";//传输企业名称
        $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
        $item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
        $item .= "        <ceb:note></ceb:note>\n";
        $item .= "    </ceb:BaseTransfer>\n";
        $item .= "</ceb:CEB303Message>\n";
        $realTime = date('Hi', time());
        //设定文件目录以及生成的文件名称.

        $localfile = 'customsMessage/303/' . $path . '/CEB303Message' . rand(10000,99999) . '_M' . $realTime . '.xml';
        //生成报文
        file_put_contents($localfile, $item);//在本地生成xml
       
      if($order['before']=='RMQ'){
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
    }//End function Gen303

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
        // file_put_contents(__DIR__ .'/log/api/postresult.txt',' write at '.date('Y-m-d H:i:s').base64_encode(serialize($result)).PHP_EOL, FILE_APPEND);
        return $result;
    }
    //收款单报文
    public function Gen403($result,$foun)
    {
        $resuFtp = db::name('ftp')->where('switch',1)->find();
        $path = date("Ymd", time());
        //如果不存在当天的目录则建立
        if (!is_dir('customsMessage/403/' . $path)) {
            mkdir('customsMessage/403/' . $path, 0777, true);
        }
        set_time_limit(0);
        $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $item .= "<ceb:CEB403Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
        //根据复选框得到数据在订单主表的id $ID 是订单号
        foreach ($result as $value) {

            $item .= "<ceb:Receipts>\n";
            $item .= "    <ceb:guid>" . $this->GUID() . "</ceb:guid>\n";
            $item .= "    <ceb:appType>" . '1' . "</ceb:appType>\n";//1-新增 2-变更 3-删除。
            $item .= "    <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";
            $item .= "    <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";
            $item .= "    <ceb:ebpCode>" . $foun['ebpCode'] . "</ceb:ebpCode>\n";
            $item .= "    <ceb:ebpName>" . $foun['ebpName'] . "</ceb:ebpName>\n";
            $item .= "    <ceb:ebcCode>" . $foun['ebcCode'] . "</ceb:ebcCode>\n";
            $item .= "    <ceb:ebcName>" . $foun['ebcName'] . "</ceb:ebcName>\n";
            $item .= "    <ceb:orderNo>" . $value['orderNo'] . "</ceb:orderNo>\n";
            $item .= "    <ceb:payCode></ceb:payCode>\n";//支付企业代码!!!!!!!!!!!!!!!!!!!!!
            $item .= "    <ceb:payName>" . "现金支付" . "</ceb:payName>\n";//支付企业名称!!!!!!!!!!!!!!!!!!!!!!!!
            $item .= "    <ceb:payNo></ceb:payNo>\n";//支付交易编号!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $item .= "    <ceb:charge>" . (float)$value['final_amount'] . "</ceb:charge>\n"; //支付企业收款金额!!!!!!!!!!!!!!!!!!!!!!!!
            $item .= "    <ceb:currency>" . $foun['iCurrency'] . "</ceb:currency>\n";
            $item .= "    <ceb:accountingDate>" . date('YmdHis', time()) . "</ceb:accountingDate>\n";//到账时间!!!!!!!!!!!!!!!!!
            $item .= "    <ceb:note></ceb:note>\n";
            $item .= "</ceb:Receipts>\n";

        }
        $item .= "    <ceb:BaseTransfer>\n";
        $item .= "        <ceb:copCode>" . $foun['ebcCode'] . "</ceb:copCode>\n";
        $item .= "        <ceb:copName>" . $foun['ebcName'] . "</ceb:copName>\n";
        $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
        $item .= "        <ceb:dxpId>". $resuFtp['dxpId'] ."</ceb:dxpId>\n";
        $item .= "        <ceb:note></ceb:note>\n";
        $item .= "    </ceb:BaseTransfer>\n";
        $item .= "</ceb:CEB403Message>\n";
        $realTime = date('Hi', time());
        //设定文件目录以及生成的文件名称.
        $localfile = 'customsMessage/403/' . $path . '/CEB403Message' . rand(10000,99999) . '_M' . $realTime . '.xml';
        //生成报文
        file_put_contents($localfile, $item);//在本地生成xml
        //RMQ上传
//        $postUrl = 'http://101.126.65.161/rmq/sybrrmq.php';
//        $result = $this->posts($postUrl,$item);
        //FTP上传
        //查询FTP信息

        $ftp_host = $resuFtp['ftp_ip'];
        $ftp_port = $resuFtp['ftp_port'];
        $ftp_user = $resuFtp['ftp_username'];
        $ftp_pass = $resuFtp['ftp_password'];
        $remotefile = $resuFtp['ftp_dir'] ."CEB403Message" . rand(10000,99999) . '_M' . $realTime . '.xml';
        $ftp = new ftp($ftp_host,$ftp_port,$ftp_user,$ftp_pass); //连接ftp
        $ftp->up_file($localfile,$remotefile); //上传ftp
        $ftp->close();  //关闭ftp

        unset($item);
    }//End function Gen403
    //运单报文
    public function Gen505($result,$uid)
    {
        $resuFtp = db::name('merchannel')->where('auth_id',$uid)->where('status',1)->where('category','9610')->find();
        $waybill = returnMerchannel($resuFtp['waybill']);
         if($waybill['before']=='RMQ'){
             $channel = Db::name('rmq')->where('rmq_id',$waybill['after'])->find();
        }else{
             $channel = Db::name('ftp')->where('id',$waybill['after'])->find();
        }
        $path = date("Ymd", time());
        //如果不存在当天的目录则建立
        if (!is_dir('customsMessage/505/' . $path)) {
            mkdir('customsMessage/505/' . $path, 0777, true);
        }
        set_time_limit(0);
        $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $item .= "<ceb:CEB505Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
        //根据复选框得到数据在订单主表的id $ID 是订单号
        foreach ($result as $value) {

            $item .= "<ceb:Logistics>\n";
            $item .= "    <ceb:guid>" . $this->GUID() . "</ceb:guid>\n";
            $item .= "    <ceb:appType>" . '1' . "</ceb:appType>\n";
            $item .= "    <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";
            $item .= "    <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";
            // $item .= "    <ceb:ebpCode>" . $ebp['ebpCode'] . "</ceb:ebpCode>\n";
            $item .= "    <ceb:logisticsCode>" . $value['logisticsCode'] . "</ceb:logisticsCode>\n";
            $item .= "    <ceb:logisticsName>" . $value['logisticsName'] . "</ceb:logisticsName>\n";
            $item .= "    <ceb:logisticsNo>" . $value['logisticsNo'] . "</ceb:logisticsNo>\n";
            $item .= "    <ceb:freight>" . (float)$value['cost_freight'] . "</ceb:freight>\n";
            $item .= "    <ceb:insuredFee>0</ceb:insuredFee>\n";
            $item .= "    <ceb:currency>" . $value['freight_currency'] . "</ceb:currency>\n";
            $item .= "    <ceb:grossWeight>" . (float)$value['weight'] . "</ceb:grossWeight>\n";
            $item .= "    <ceb:packNo>" . '1' . "</ceb:packNo>\n";
            $item .= "    <ceb:goodsInfo>" . "商品信息" . "</ceb:goodsInfo>\n";
            $item .= "    <ceb:ebcCode>" . $value['ebcCode'] . "</ceb:ebcCode>\n";
            $item .= "    <ceb:ebcName>" . $value['ebcName'] . "</ceb:ebcName>\n";
            $item .= "    <ceb:ebcTelephone>" . '18041339729' . "</ceb:ebcTelephone>\n";
            $item .= "    <ceb:note></ceb:note>\n";
            $item .= "</ceb:Logistics>\n";
        }
        $item .= "    <ceb:BaseTransfer>\n";
        $item .= "        <ceb:copCode>" . $result[0]['logisticsCode'] . "</ceb:copCode>\n";
        $item .= "        <ceb:copName>" . $result[0]['logisticsName'] . "</ceb:copName>\n";
        $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
        $item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
        $item .= "        <ceb:note></ceb:note>\n";
        $item .= "    </ceb:BaseTransfer>\n";
        $item .= "</ceb:CEB505Message>\n";
        $realTime = date('Hi', time());
        //设定文件目录以及生成的文件名称.
        $localfile = 'customsMessage/505/' . $path . '/CEB505Message' . rand(10000,99999) . '_M' . $realTime . '.xml';
        //生成报文
        file_put_contents($localfile, $item);//在本地生成xml
        
        //选择通道
        if($waybill['before']=='RMQ'){
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
                $remotefile = $channel['ftp_dir'] ."CEB505Message" . rand(10000,99999) . '_M' . $realTime . '.xml';
                $ftp = new ftp($ftp_host,$ftp_port,$ftp_user,$ftp_pass); //连接ftp
                $ftp->up_file($localfile,$remotefile); //上传ftp
                $ftp->close();  //关闭ftp
            }
        unset($item);
    }//End function Gen505
}