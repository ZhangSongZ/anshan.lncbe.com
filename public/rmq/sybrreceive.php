<?php

require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
header("Content-Type:text/html;charset=utf-8");
set_time_limit(0);

$conf = array(
    'host' => '39.100.243.142',
    'port' => 5672,
    'user' => 'aseadmin',
    'pwd' => 'aseadmin',
    'vhost' => 'default_vhost',
);
$queueName = 'DXPENT0000024680_E_SEND'; //队列名称 沈阳博瑞跨境电商有限公司
$conn = new AMQPStreamConnection( //建立生产者与mq之间的连接
    $conf['host'], $conf['port'], $conf['user'], $conf['pwd'], $conf['vhost']
);
$channel = $conn->channel(); //在已连接基础上建立生产者与mq之间的通道
$channel->queue_declare($queueName, false, true, false, null); //声明初始化一条队列
//$channel->basic_qos(null, 15, null);  //声明初始化批量读取10条

$callback = function($msg){

    $content = $msg;
    // file_put_contents('log'.time().'.txt',json_encode($content),FILE_APPEND);
    //var_dump($msg->delivery_info);die;
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);//手动应答
    $xmlResult = simplexml_load_string($msg->body);//XML 字符串载入对象中
    $arr=json_encode($xmlResult);//取出的对象转成json 再转成数组
	$path = date('Ymd',time());
	if(!is_dir('log/Alog/'.$path))
	mkdir('log/Alog/'.$path,0777,true);
	file_put_contents('log/Alog/'.$path.'/'.time().'.txt',json_encode($arr),FILE_APPEND);
    $arr= json_decode($arr,TRUE);
   //    echo "<pre>";

    //连接本地数据库
    $servername = "127.0.0.1";
    $username = "root";
    $password = "61ddd2cebbd913c8";
    $con = mysqli_connect($servername, $username, $password, "lnebe");

    if (!$con)
    {
        die("连接错误: " . mysqli_connect_error());
    }

    //出口订单订单回执
	if($arr['OrderReturn']){
		if($arr['OrderReturn']['orderNo']){
			$order = $arr['OrderReturn'];
            $OrderReturnsql = "UPDATE ln_excbe SET order_status = ".$order['returnStatus'].",order_updateTime=".$order['returnTime'].
                ",order_returnInfo= "."'".$order["returnInfo"] ."'". " WHERE orderNo = ". "'".$order["orderNo"] ."'";
			//file_put_contents('log/303/'.date('Ymd',time()).'OrderReturnsql.txt',$OrderReturnsql.PHP_EOL,FILE_APPEND);
			//var_dump($OrderReturnsql);
			$result = mysqli_query($con, $OrderReturnsql);//cbe数据库 123.57.162.187 出口
			unset($OrderReturnsql);
			//var_dump($result);
            echo '订单回执已收到，订单号：'.$order['orderNo'];
		}else{
			foreach($arr['OrderReturn'] as $order){
//				file_put_contents('./log/303/'.date('Ymd',time()).'.txt',json_encode($order).PHP_EOL,FILE_APPEND);
                $OrderReturnsql = "UPDATE ln_excbe SET order_status = ".$order['returnStatus'].",order_updateTime=".$order['returnTime'].
                    ",order_returnInfo= "."'".$order["returnInfo"] ."'". " WHERE orderNo = ". "'".$order["orderNo"] ."'";
				//file_put_contents('log/303/'.date('Ymd',time()).'OrderReturnsql.txt',$OrderReturnsql.PHP_EOL,FILE_APPEND);
				//var_dump($OrderReturnsql);
				$result = mysqli_query($con, $OrderReturnsql);//cbe数据库 23.57.162.187 出口
				unset($OrderReturnsql);
                echo '订单回执已收到，订单号：'.$order['orderNo'];
			}
		}

	}

	//出口收款单回执
	if($arr['ReceiptsReturn']){
		if($arr['ReceiptsReturn']['orderNo']){
			$order = $arr['ReceiptsReturn'];
            $OrderReturnsql = "UPDATE ln_excbe SET receipt_status = ".$order['returnStatus'].",receipt_updateTime=".$order['returnTime'].
                ",receipt_returnInfo= "."'".$order["returnInfo"] ."'". " WHERE orderNo = ". "'".$order["orderNo"] ."'";
//			file_put_contents('./log/403/'.date('Ymd',time()).'OrderReturnsql.txt',$OrderReturnsql.PHP_EOL,FILE_APPEND);
			//var_dump($OrderReturnsql);
			$result = mysqli_query($con, $OrderReturnsql);//cbe数据库 23.57.162.187 出口
//			var_dump($result);
            echo '收款单回执已收到，订单号：'.$order['orderNo'];
		}else{
			foreach($arr['ReceiptsReturn'] as $order){
                $OrderReturnsql = "UPDATE ln_excbe SET receipt_status = ".$order['returnStatus'].",receipt_updateTime=".$order['returnTime'].
                    ",receipt_returnInfo= "."'".$order["returnInfo"] ."'". " WHERE orderNo = ". "'".$order["orderNo"] ."'";
//				file_put_contents('./log/403/'.date('Ymd',time()).'OrderReturnsql.txt',$OrderReturnsql.PHP_EOL,FILE_APPEND);
//				var_dump($OrderReturnsql);
				$result = mysqli_query($con, $OrderReturnsql);//cbe数据库 23.57.162.187 出口
				unset($OrderReturnsql);
//				var_dump($result);
                echo '收款单回执已收到，订单号：'.$order['orderNo'];
			}
		}
	}


    //出口订单运单回执
//    if($arr['WayBillReturn']){
//        if($arr['WayBillReturn']['orderNo']){
//            $order = $arr['WayBillReturn'];
//            $OrderReturnsql = "UPDATE ln_excbe SET waybill_status = ".$order['returnStatus'].",waybill_updateTime=".$order['returnTime'].
//                ",waybill_returnInfo= "."'".$order["returnInfo"] ."'". " WHERE orderNo = ". "'".$order["orderNo"] ."'";
//            //file_put_contents('log/303/'.date('Ymd',time()).'OrderReturnsql.txt',$OrderReturnsql.PHP_EOL,FILE_APPEND);
//            //var_dump($OrderReturnsql);
//            $result = mysqli_query($con, $OrderReturnsql);//cbe数据库 123.57.162.187 出口
//            unset($OrderReturnsql);
//            //var_dump($result);
//            echo '订单回执已收到，订单号：'.$order['orderNo'];
//        }else{
//            foreach($arr['WayBillReturn'] as $order){
////				file_put_contents('./log/303/'.date('Ymd',time()).'.txt',json_encode($order).PHP_EOL,FILE_APPEND);
//                $OrderReturnsql = "UPDATE ln_excbe SET waybill_status = ".$order['returnStatus'].",waybill_updateTime=".$order['returnTime'].
//                    ",waybill_returnInfo= "."'".$order["returnInfo"] ."'". " WHERE orderNo = ". "'".$order["orderNo"] ."'";
//                //file_put_contents('log/303/'.date('Ymd',time()).'OrderReturnsql.txt',$OrderReturnsql.PHP_EOL,FILE_APPEND);
//                //var_dump($OrderReturnsql);
//                $result = mysqli_query($con, $OrderReturnsql);//cbe数据库 23.57.162.187 出口
//                unset($OrderReturnsql);
//                echo '订单回执已收到，订单号：'.$order['orderNo'];
//            }
//        }
//
//    }





    //出口清单回执
	if($arr['InventoryReturn']['orderNo']&&$arr['InventoryReturn']['statisticsFlag']=='A'){
	    // file_put_contents('./log/603/'.time().'.txt',json_encode($arr),FILE_APPEND);
//	    file_put_contents('./log/603/'.date('Ymd',time()).'.txt',json_encode($arr),FILE_APPEND);
		$orderInventory = $arr['InventoryReturn'];
//		$sql = "UPDATE ln_excbe SET inventory_status = " . $orderInventory['returnStatus'].
//         ",preNo=".$orderInventory['preNo'].",invtNo=".$orderInventory['invtNo'].",inventory_updateTime=".$orderInventory['returnTime'].
//        ",inventory_returnInfo= concat(inventory_returnInfo,"."-".$orderInventory['returnInfo'].")";
//		$sql .= " WHERE orderNo = '" . $arr['InventoryReturn']['orderNo']. "'";

        $sql = "UPDATE ln_excbe SET inventory_status = ".$orderInventory['returnStatus'].",inventory_updateTime=".$orderInventory['returnTime'].
            ",preNo="."'".$orderInventory['preNo']."'" .",invtNo="."'".$orderInventory['invtNo']."'".
            ",inventory_returnInfo= "."'".$orderInventory["returnInfo"] ."'". " WHERE orderNo = ". "'".$orderInventory['orderNo'] ."'";


//		var_dump($sql);
		$result = mysqli_query($con, $sql);//cbe数据库
//	    file_put_contents('./log/603/'.date('Ymd',time()).'orderNo603.txt',$sql.PHP_EOL,FILE_APPEND);
//		var_dump($result);
        echo '出口清单回执已收到，订单号：'.$arr['InventoryReturn']['orderNo'];
	}

    //撤单回执
    if($arr['InvtCancelReturn']&&$arr['InventoryReturn']['statisticsFlag']!='A'){
        $cancelInventory = $arr['InvtCancelReturn'];
        $sql = "UPDATE ln_excbe SET cancel_status = ".$cancelInventory['returnStatus'].",cancel_updateTimae=".$cancelInventory['returnTime'].
        ",cancel_retrunInfo= "."'".$cancelInventory["returnInfo"] ."'". " WHERE invtNo = ". "'".$orderInventory['invtNo'] ."'";
		$result = mysqli_query($con, $sql);//cbe数据库
        echo '撤销单回执已收到，清单号：'.$arr['InvtCancelReturn']['invtNo'];
	}


  //  echo "<pre>";
  //  print_r($arr);
  //  echo json_encode($arr);
  //  echo 'success1';
  //  $con->close();

};


//$channel->basic_qos(null, 2, null);
  $channel->basic_consume($queueName, "", false, false, false, false, $callback);
//var_dump($channel->callbacks);

while(count($channel->callbacks)) {
    $channel->wait();
}

//$channel->wait();
$channel->close();
$conn->close();

?>