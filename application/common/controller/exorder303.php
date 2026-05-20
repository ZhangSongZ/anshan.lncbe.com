<?php
namespace app\common\controller;

use think\Db;

/**
 * Fonde licence
 * 公共类-报文发送 三单
 */

class exorder303{

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
        $resuFtp = db::name('merchannel')->where('auth_id',$uid)->where('status',1)->where('category','9710')->find();
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
            foreach ($Detail['details'] as $value) {
                $i += 1;
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
				$goodsValue+=(float)$value['qty'] * (float)$value['price'];
            }
            $item .= "    <ceb:Order>\n";
            $item .= "    <ceb:OrderHead>\n";
            $item .= "        <ceb:guid>" . $this->GUID() . "</ceb:guid>\n"; //系统唯一序号
            $item .= "        <ceb:appType>" . '1' . "</ceb:appType>\n";
            $item .= "        <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";
            $item .= "        <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";
            $item .= "        <ceb:orderType>" . 'B' . "</ceb:orderType>\n";//电商平台的订单类型 I-进口商品订单；E-出口商品订单；9710，订单类型为B
            $item .= "        <ceb:orderNo>" . $Detail['order_number'] . "</ceb:orderNo>\n"; //订单编号
			if (preg_match('/^[a-zA-Z0-9]{10}$/', $Detail['ebp_code'])) {
				$Detail['ebp_code'] = $Detail['ebp_code'];
			} else {
				$Detail['ebp_code'] = '无';
			}
            $item .= "        <ceb:ebpCode>" . $Detail['ebp_code'] . "</ceb:ebpCode>\n";
            $item .= "        <ceb:ebpName>" . $Detail['ebp_name'] . "</ceb:ebpName>\n";
            $item .= "        <ceb:ebcCode>" . $Detail['ebc_code']. "</ceb:ebcCode>\n";
            $item .= "        <ceb:ebcName>" . $Detail['ebc_name'] . "</ceb:ebcName>\n";
            // $item .= "        <ceb:goodsValue>" . $Detail['cost_item'] . "</ceb:goodsValue>\n";
            $item .= "        <ceb:goodsValue>" . $goodsValue . "</ceb:goodsValue>\n";
            $item .= "        <ceb:freight>" . (float)$Detail['cost_freight'] . "</ceb:freight>\n";
            $item .= "        <ceb:currency>" . $value['item_currency'] . "</ceb:currency>\n";
            $item .= "        <ceb:note>跨境电子商务</ceb:note>\n"; //海关要求必须是这个备注
            $item .= "    </ceb:OrderHead>\n";
            $item .= $xml;
            $item .= "</ceb:Order>\n";
$exorder = db::name('exorder')->where('auth_id',$uid)->where('order_number',$Detail['order_number'])->update(['cost_item' => $goodsValue]);		
            unset($xml);
            unset($goodsValue);
        }
        $item .= "    <ceb:BaseTransfer>\n";
        $item .= "        <ceb:copCode>" . $result[0]['ebc_code'] . "</ceb:copCode>\n";//传输企业代码
        $item .= "        <ceb:copName>" . $result[0]['ebc_name'] . "</ceb:copName>\n";//传输企业名称
        $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
        //if($uid==9||$uid==11||$uid==12||$uid==13||$uid==15){
           // $item .= "        <ceb:dxpId>".$foun['dxpId']."</ceb:dxpId>\n";
        //}else{
            $item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
        //}
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
			var_dump($ftp);
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
}