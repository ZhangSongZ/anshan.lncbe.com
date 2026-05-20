<?php

namespace app\common\controller;
use app\common\controller\Sybrrmq;
use think\Db;

/**
 * 605撤单报文
 */
class Cancel
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

    //撤单报文
    public function Gen605($result,$uid)
    {
       
            $resuFtp = db::name('merchannel')->where('auth_id',$uid)->where('status',1)->where('category','9610')->find();
            $revoke = returnMerchannel($resuFtp['revoke']);
             if($revoke['before']=='RMQ'){
                 $channel = Db::name('rmq')->where('rmq_id',$revoke['after'])->find();
            }else{
                 $channel = Db::name('ftp')->where('id',$revoke['after'])->find();
            }
       
        $path = date("Ymd", time());
        //如果不存在当天的目录则建立
        if (!is_dir('customsMessage/605/' . $path)) {
            mkdir('customsMessage/605/' . $path, 0777, true);
        }
        set_time_limit(0);
        //构建603XML报文
        $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $item .= "<ceb:CEB605Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
        //根据复选框得到数据在订单主表的id $ID 是订单号
        foreach ($result as $Detail) {
            $copNo = 'B' . date('Ymdhis', time()) . rand(100000000, 999999999);

            $item .= "<ceb:InvtCancel>\n";
            $item .= "    <ceb:guid>" . $this->GUID() . "</ceb:guid>\n";
            $item .= "    <ceb:appType>" . '1' . "</ceb:appType>\n";
            $item .= "    <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";
            $item .= "    <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";
            $item .= "    <ceb:customsCode>" . $Detail['customsCode'] . "</ceb:customsCode>\n";
            $item .= "    <ceb:copNo>" . $copNo . "</ceb:copNo>\n";
            $item .= "    <ceb:preNo>" . $Detail['preNo'] . "</ceb:preNo>\n";
            $item .= "    <ceb:invtNo>" . $Detail['invtNo'] . "</ceb:invtNo>\n";
            $item .= "    <ceb:reason>取消发货</ceb:reason>\n";
            // $item .= "    <ceb:agentCode>" . "210166K009" . "</ceb:agentCode>\n";
            $item .= "    <ceb:agentCode>" . $Detail['agentCode'] . "</ceb:agentCode>\n";
            // $item .= "    <ceb:agentName>" . "辽宁跨境物流科技有限公司" . "</ceb:agentName>\n";
            $item .= "    <ceb:agentName>" . $Detail['agentName'] . "</ceb:agentName>\n";
            $item .= "    <ceb:ebcCode>" . $Detail['ebcCode']. "</ceb:ebcCode>\n";
            $item .= "    <ceb:ebcName>" . $Detail['ebcName'] . "</ceb:ebcName>\n";
            $item .= "    <ceb:note></ceb:note>\n";
            $item .= "</ceb:InvtCancel>\n";
        }
        $item .= "    <ceb:BaseTransfer>\n";
        $item .= "        <ceb:copCode>" . $Detail['agentCode'] . "</ceb:copCode>\n";
        $item .= "        <ceb:copName>" . $Detail['agentName'] . "</ceb:copName>\n";
        $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
       
        $item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
      
        $item .= "        <ceb:note></ceb:note>\n";
        $item .= "    </ceb:BaseTransfer>\n";
        $item .= "</ceb:CEB605Message>\n";
        $realTime = date('Hi', time());
        //设定文件目录以及生成的文件名称.
        $localfile = 'customsMessage/605/' . $path . '/CEB605Message' . date('Ymdhis', time()) . rand(1000, 9999) . '_M' . $realTime . '.xml';
        file_put_contents($localfile, $item);//在本地生成xml
        
        if($revoke['before']=='RMQ'){
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

    }//End Function Gen605
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