<?php
namespace app\common\controller;

use think\Db;

use app\admin\model\datadictionary\Country; //国家表
use app\admin\model\datadictionary\Unitcus; //计量单位表
use app\admin\model\datadictionary\Currency; //币制表

/**
 * Fonde licence
 * 公共类-报文发送 三单
 */

class exorderBgd{

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

    public function GenBgd($result,$uid)
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
        if (!is_dir('customsMessage/9710bgd/' . $path)) {
            mkdir('customsMessage/9710bgd/' . $path, 0777, true);
        }
        set_time_limit(0);
		foreach($result as $orderMain){
			$xml = '';
			$i=0;
            foreach ($orderMain['details'] as $value) {
				$itemCurrency = Currency::where('code', $value['item_currency'])->field('name, code,Ecode')->find();
				$i += 1;
				$xml .= "	<DecList>\n";
				$xml .= "        <CodeTS>" . $value['gcode'] . "</CodeTS>\n";
				$xml .= "        <DeclPrice>" . $value['price'] . "</DeclPrice>\n";
				$xml .= "        <DutyMode>" . $value['duty_mode'] . "</DutyMode>\n";
				$value['gmodel'] = htmlspecialchars($value['gmodel'], ENT_QUOTES | ENT_HTML401, 'UTF-8', false);
				$xml .= "        <GModel>" . trim($value['gmodel']) . "</GModel>\n";
				$xml .= "        <GName>" . trim($value['product_name']) . "</GName>\n";
				$xml .= "        <GNo>" . $i . "</GNo>\n";
				$xml .= "        <OriginCountry>" . $value['origin_country'] . "</OriginCountry>\n";//***************BEL 比利时 目的国
				$xml .= "        <TradeCurr>" . $itemCurrency['Ecode'] . "</TradeCurr>\n";//成交币制 USD美元**********
				$xml .= "        <DeclTotal>" . $value['total'] . "</DeclTotal>\n";//申报总价
				$xml .= "        <GQty>" . $value['qty'] . "</GQty>\n";//成交数量
				$xml .= "        <FirstQty>" . $value['qty1'] . "</FirstQty>\n";//第一数量
				if(!empty($value['qty2']) && $value['qty2']!=0){
					$xml .= "        <SecondQty>" . $value['qty2'] . "</SecondQty>\n";//第二法定数量
				}
				$xml .= "        <GUnit>" . $value['unit'] . "</GUnit>\n";//成交计量单位
				$xml .= "        <FirstUnit>" . $value['unit1'] . "</FirstUnit>\n";//第一计量单位
				if(!empty($value['unit2'])){
					$xml .= "        <SecondUnit>" . $value['unit2'] . "</SecondUnit>\n";//第二法定单位
				}
				$xml .= "        <DestinationCountry>CHN</DestinationCountry>\n";//原产国 出口报关单  destinationCountry填的是原产国。***********
				// $xml .= "        <OrigPlaceCode>" . $value['destinationCountry'] . "</OrigPlaceCode>\n";//原产地代码！！！！！！！！！
				$xml .= "        <DistrictCode>" . $value['district_code'] . "</DistrictCode>\n";//境内货源地
				$xml .= "	</DecList>\n";
			}
		    $data = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
			$data .= "<DecMessage xmlns=\"http://www.chinaport.gov.cn/dec\">\n";
			$data .= "    <DecHead>\n";
			$data .= "        <IEFlag>E</IEFlag>\n";//进出口标志
			$data .= "        <AgentCode>".$orderMain['agent_code']."</AgentCode>\n";
			$data .= "        <AgentName>".$orderMain['agent_name']."</AgentName>\n";
			$data .= "        <BillNo>".$orderMain['bill_no']."</BillNo>\n";
			$orderMain['contr_no'] = htmlspecialchars($orderMain['contr_no'], ENT_QUOTES | ENT_HTML401, 'UTF-8', false);
			$data .= "        <ContrNo>".$orderMain['contr_no']."</ContrNo>\n";//合同号
			$data .= "        <CustomMaster>".$orderMain['customs_code']."</CustomMaster>\n";//主管海关
			$data .= "        <CutMode>".$orderMain['cut_mode']."</CutMode>\n";//征免性质分类
			$data .= "        <DistinatePort>".$orderMain['edistinate_port']."</DistinatePort>\n";//指运港
		    //成交方式FOB的，运费杂费都不申报
			if($orderMain['trans_mode']!='3'){
				$data .= "        <FeeCurr>".$orderMain['freight_currency']."</FeeCurr>\n";//运费币制
				$data .= "        <FeeMark>3</FeeMark>\n";//运费标记
				$data .= "        <FeeRate>".$orderMain['freight']."</FeeRate>\n";//运费
				
				//$data .= "        <InsurCurr></InsurCurr>\n";//保险费币制
				//$data .= "        <InsurMark></InsurMark>\n";//保险费标记
				//$data .= "        <InsurRate></InsurRate>\n";//保险费／率
				
				$data .= "        <OtherCurr>".$orderMain['other_curr']."</OtherCurr>\n";//杂费币制
				$data .= "        <OtherMark>3</OtherMark>\n";//杂费标志
				$data .= "        <OtherRate>".$orderMain['other_rate']."</OtherRate>\n";//杂费
			}
			$data .= "        <GrossWet>".$orderMain['weight']."</GrossWet>\n";//毛重
			$data .= "        <IEPort>".$orderMain['port_code']."</IEPort>\n";//出境口岸
			$data .= "        <NetWt>".$orderMain['net_weight']."</NetWt>\n";//净重
			$data .= "        <NoteS>".$orderMain['notes']."</NoteS>\n";//备注
			$data .= "        <OwnerCode>".$orderMain['owner_code']."</OwnerCode>\n";//生产销售单位代码
			$data .= "        <OwnerName>".$orderMain['owner_name']."</OwnerName>\n";//生产销售单位名称
			$data .= "        <PackNo>".$orderMain['pack_no']."</PackNo>\n";//件数
			$data .= "        <TradeCode>".$orderMain['ebc_code']."</TradeCode>\n";//境内收发货人编号，私有通道导入时，必填
			$data .= "        <TradeCountry>".$orderMain['country_code']."</TradeCountry>\n";//出口运抵国 国贸英文代码*********
			$data .= "        <TradeMode>9710</TradeMode>\n";//监管方式
			$data .= "        <TradeName>".$orderMain['ebc_name']."</TradeName>\n";//境内收发货人名称,私有通道导入时，必填
			$data .= "        <TrafMode>".$orderMain['traf_mode']."</TrafMode>\n";//运输方式
		    $data .= "        <TrafName>".$orderMain['traf_name']."</TrafName>\n";//运输工具
			$data .= "        <TransMode>".$orderMain['trans_mode']."</TransMode>\n";//成交方式
			$data .= "        <WrapType>".$orderMain['wrap_type']."</WrapType>\n";//包装种类
			$data .= "        <EntryType>M</EntryType>\n";//  报关单类型 0普通报关单，L为带报关单清单的报关单，W无纸报关类型，D既是清单又是无纸报关的情况，M：无纸化通关
			$data .= "        <DeclTrnRel>0</DeclTrnRel>\n";//报关/转关关系标志。0：一般报关单；1：转关提前报关单
			$data .= "        <ChkSurety>0</ChkSurety>\n";//担保验放标志 担保验放:1:是；0否
			$data .= "        <OwnerCodeScc>".$orderMain['owner_scc']."</OwnerCodeScc>\n";//生产销售单位单位统一编码
			$data .= "        <AgentCodeScc>".$orderMain['agent_scc']."</AgentCodeScc>\n";//申报单位统一编码
			$data .= "        <TradeCoScc>".$orderMain['trade_scc']."</TradeCoScc>\n";//境内收发货人统一代码
			$data .= "        <PromiseItmes>999</PromiseItmes>\n";//承诺事项 1勾选 0-未选 9-空 第一位特殊关系确认 第二位价格影响确认 第三位支付特许权使用费确认
			$data .= "        <TradeAreaCode>".$orderMain['country_code']."</TradeAreaCode>\n";//贸易国别
			$data .= "        <MarkNo>N/M</MarkNo>\n";//标记及号码【本批货物的标记和号码】
			$data .= "        <EntyPortCode>".$orderMain['desp_port_code']."</EntyPortCode>\n";//启运港代码   // 离境口岸  入境口岸代码
			$data .= "        <NoOtherPack>1</NoOtherPack>\n";//无其他包装 勾选 0-未选，有其他包装；1：选中，无其他包装
		    $orderMain['contr_no'] = htmlspecialchars($orderMain['contr_no'], ENT_QUOTES | ENT_HTML401, 'UTF-8', false);
			$data .= "        <OverseasConsigneeEname>".$orderMain['overseas_consignee_ename']."</OverseasConsigneeEname>\n";//境外收货人英文名称
			$data .= "    </DecHead>\n";
			$data .= "    <DecLists>\n";
			$data .= $xml;
			unset($xml);
			$data .= "    </DecLists>\n";
			$data .= "    <DecFreeTxt>\n";
			$data .= "    <VoyNo>".$orderMain['voy_no']."</VoyNo>\n";//航次号
			$data .= "    </DecFreeTxt>\n";
			$data .= "    <DecSign>\n";
			$data .= "        <OperType>G</OperType>\n";//操作类型A:报关单上载B：报关单、转关单上载C:报关单申报D：报关单、转关单申报E：电子手册报关单上载（此种操作类型的报关单上载时不作非空和逻辑校验）G：报关单暂存（转关提前报关单暂存）
			$ClientSeqNo = date('Ymd', time()) . rand(1000000000, 9999999999);
			$data .= "        <ClientSeqNo>".$ClientSeqNo."</ClientSeqNo>\n";//客户端报关单编号 客户端自行编制的编号，唯一识别一票报关单
			$data .= "    </DecSign>\n";
			$data .= "    <EdocRealation>\n";
			$data .= "        <EdocID>".$orderMain['order_number']."</EdocID>\n";//文件名、随附单据编号（文件名命名规则是：申报口岸+随附单据类别代码+IM+18位流水号+.pdf）
			$data .= "        <EdocCode>10000004</EdocCode>\n";//00000001:发票；00000002:装箱单；00000003:提/运单；00000004:合同；10000001：代理报关委托协议（电子）；10000002：减免税货物税款担保证明；10000003：减免税货物税款担保延期证明
			$data .= "        <EdocFomatType>S</EdocFomatType>\n";// 随附单据格式类型 S:结构化 US:非结构化（pdf文件填写US）
			$data .= "    </EdocRealation>\n";
			$data .= "</DecMessage>\n";
			$realTime = date('Hi', time());
			$localfile = 'customsMessage/9710bgd/' . $path . '/9710Message' . $orderMain['order_number'] . '_' . $realTime . '.xml';
			file_put_contents($localfile, $data);//在本地生成xml
			if($order['before']=='RMQ'){
				$datach['host'] = $channel['host'];
				$datach['port'] = $channel['port'];
				$datach['user'] = $channel['user'];
				$datach['pwd'] = $channel['pwd'];
				$datach['exchangeN'] = $channel['exchangeN'];
				$datach['queueNsend'] = $channel['queueNsend'];
				$datach['vhost'] = $channel['vhost'];
				//$items = json_encode($data);
				// 构建查询字符串
				$postFields = [
					'xml' => $data,
					'conf' => $datach,
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
			unset($data);
		}
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
        // file_put_contents(__DIR__ .'/log/api/postresult.txt',' write at '.date('Y-m-d H:i:s').base64_encode(serialize($result)).PHP_EOL, FILE_APPEND);
        return $result;
    }
}