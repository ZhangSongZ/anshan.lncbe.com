<?php

namespace app\common\controller;
use app\common\controller\ftp;
use app\common\controller\Sybrrmq;
use think\Db;

/**
 * 603清单报文
 */

class Summary
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
    public function Gen701($result,$uid)
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
        if (!is_dir('customsMessage/701/' . $path)) {
            mkdir('customsMessage/701/' . $path, 0777, true);
        }
        
       set_time_limit(0);
        //构建XML报文
        $item = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $item .= "<ceb:CEB701Message guid='" . $this->GUID() . "' version='1.0' xmlns:ceb='http://www.chinaport.gov.cn/ceb' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
                $summarycopNo = 'B' . date('Ymd', time()) . rand(100000000, 999999999);
           
                $item .= "<ceb:SummaryApply>\n";
                $item .= "  <ceb:SummaryApplyHead>\n";
                $item .= "    <ceb:guid>" . $this->GUID() . "</ceb:guid>\n";//系统唯一序号
                $item .= "    <ceb:appType>" . '1' . "</ceb:appType>\n";//报送类型
                $item .= "    <ceb:appTime>" . date('YmdHis', time()) . "</ceb:appTime>\n";
                $item .= "    <ceb:appStatus>" . '2' . "</ceb:appStatus>\n";
                $item .= "    <ceb:customsCode>" . $result[0][0]['customsCode'] . "</ceb:customsCode>\n";//申报地海关代码 办理通关手续的 4 位海关编号!!!!!!!!!!!!!!!!!!!!!!!
                //$item .= "    <ceb:copNo>" . $countryOrder[0]['copNo'] . "</ceb:copNo>\n";//企业生成标识唯一编号
                $item .= "    <ceb:copNo>" . $summarycopNo . "</ceb:copNo>\n";//企业生成标识唯一编号
                $item .= "    <ceb:preNo></ceb:preNo>\n";//电子口岸标识唯一编号 不必填
                $item .= "    <ceb:sumNo></ceb:sumNo>\n";//汇总申请编号 ???????? 不必填
                $item .= "    <ceb:agentCode>" . $result[0][0]['agentCode'] . "</ceb:agentCode>\n";//申报企业代码
                $item .= "    <ceb:agentName>" . $result[0][0]['agentName'] . "</ceb:agentName>\n";//申报单位名称
                $item .= "    <ceb:ebcCode>" . $result[0][0]['ebcCode'] . "</ceb:ebcCode>\n";//收发货人代码
                $item .= "    <ceb:ebcName>" . $result[0][0]['ebcName'] . "</ceb:ebcName>\n";//收发货人名称

                $item .= "    <ceb:declAgentCode>" . $result[0][0]['agentCode'] . "</ceb:declAgentCode>\n";//报关单位代码
                $item .= "    <ceb:declAgentName>" . $result[0][0]['agentName'] . "</ceb:declAgentName>\n";//报关单位名称
                
                // $item .= "    <ceb:startTime></ceb:startTime>\n";//汇总开始时间！！！！//date('YmdHis', time())
                // $item .= "    <ceb:endTime></ceb:endTime>\n";//汇总结束时间！！！！
                $item .= "    <ceb:loctNo></ceb:loctNo>\n";//监管场所代码

                $item .= "    <ceb:summaryFlag>" . "1" . "</ceb:summaryFlag>\n";//收发货人汇总标志1:按收发货人单一汇总，2:按收发货人和生产销售单位汇总
                $item .= "    <ceb:itemNameFlag>" . "1" . "</ceb:itemNameFlag>\n";//按商品名汇总标志填 1,按清单原始商品名相同汇总，不填则按商品综合分类名汇总

                $item .= "    <ceb:msgCount>" . "1" . "</ceb:msgCount>\n";//报文总数
                $item .= "    <ceb:msgSeqNo>" . "1" . "</ceb:msgSeqNo>\n";//报文序号
                $item .= "</ceb:SummaryApplyHead>\n";
                foreach($result as $orderInvtNo){
                    $item .= "<ceb:SummaryApplyList>\n";
                    $item .= "    <ceb:invtNo>" . $orderInvtNo[0]['invtNo'] . "</ceb:invtNo>\n";//清单编号
                    $item .= "</ceb:SummaryApplyList>\n";
                    //回写一下汇总申请时候的企业内部编号
                    Db::name('excbe')->where('orderNo',$orderInvtNo[0]['orderNo'])->update(array('summarycopNo'=>$summarycopNo));
                }
                $item .= "</ceb:SummaryApply>\n";
        $item .= "    <ceb:BaseTransfer>\n";
        $item .= "        <ceb:copCode>" . $result[0][0]['agentCode'] . "</ceb:copCode>\n";
        $item .= "        <ceb:copName>" . $result[0][0]['agentName'] . "</ceb:copName>\n";
        $item .= "        <ceb:dxpMode>DXP</ceb:dxpMode>\n";
		$item .= "        <ceb:dxpId>".$channel['dxpId']."</ceb:dxpId>\n";
		$item .= "    </ceb:BaseTransfer>\n";
        $item .= "</ceb:CEB701Message>\n";
        $realTime = date('Hi', time());
        //设定文件目录以及生成的文件名称.
        $localfile = 'customsMessage/701/' . $path . '/CEB701Message' . rand(10000,99999) . '_M' . $realTime . '.xml';
        //生成报文
        file_put_contents($localfile, $item);//在本地生成xml
      
       //选择通道
    //     if($list['before']=='RMQ'){
    //             $data['host'] = $channel['host'];
    //             $data['port'] = $channel['port'];
    //             $data['user'] = $channel['user'];
    //             $data['pwd'] = $channel['pwd'];
    //             $data['exchangeN'] = $channel['exchangeN'];
    //             $data['queueNsend'] = $channel['queueNsend'];
    //             $data['vhost'] = $channel['vhost'];
    //             //$items = json_encode($data);
    //             // 构建查询字符串
    //             $postFields = [
    //                 'xml' => $item,
    //                 'conf' => $data,
    //             ];
				// $postFieldsjson = json_encode($postFields);
    //             $postUrl = 'http://anshan.lncbe.com/rmq/sybrrmq.php';
    //             $result = $this->posts($postUrl,$postFieldsjson);
    //      }else{
    //             //FTP上传
    //             //查询FTP信息
    //             $ftp_host = $channel['ftp_ip'];
    //             $ftp_port = $channel['ftp_port'];
    //             $ftp_user = $channel['ftp_username'];
    //             $ftp_pass = $channel['ftp_password'];
    //             $remotefile = $channel['ftp_dir'] ."CEB701Message" . rand(10000,99999) . '_M' . $realTime . '.xml';
    //             $ftp = new ftp($ftp_host,$ftp_port,$ftp_user,$ftp_pass); //连接ftp
    //             $ftp->up_file($localfile,$remotefile); //上传ftp
    //             $ftp->close();  //关闭ftp
    //         }

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