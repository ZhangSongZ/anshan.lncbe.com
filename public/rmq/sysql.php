<?php


//连接本地数据库
$servername = "127.0.0.1";
$username = "root";
$password = "150034c0228ea8bd";
$con = mysqli_connect($servername, $username, $password, "lncbe");

if (!$con)
{
    die("连接错误: " . mysqli_connect_error());
}
$arr = file_get_contents("php://input");

$arrs= json_decode(json_decode($arr,TRUE),True);


$orderInventory = $arrs['WayBillReturn'];
//		$sql = "UPDATE ln_excbe SET inventory_status = " . $orderInventory['returnStatus'].
//         ",preNo=".$orderInventory['preNo'].",invtNo=".$orderInventory['invtNo'].",inventory_updateTime=".$orderInventory['returnTime'].
//        ",inventory_returnInfo= concat(inventory_returnInfo,"."-".$orderInventory['returnInfo'].")";
//		$sql .= " WHERE orderNo = '" . $arr['InventoryReturn']['orderNo']. "'";
var_dump($orderInventory);die;
$sql = "UPDATE ln_excbe SET inventory_status = ".$orderInventory['returnStatus'].",inventory_updateTime=".$orderInventory['returnTime'].
    ",preNo="."'".$orderInventory['preNo']."'" .",invtNo="."'".$orderInventory['invtNo']."'".
    ",inventory_returnInfo= "."'".$orderInventory["returnInfo"] ."'". " WHERE orderNo = ". "'".$orderInventory['orderNo'] ."'";


//		var_dump($sql);
$result = mysqli_query($con, $sql);//cbe数据库
//	    file_put_contents('./log/603/'.date('Ymd',time()).'orderNo603.txt',$sql.PHP_EOL,FILE_APPEND);
//		var_dump($result);
echo '出口清单回执已收到，订单号：'.$orderInventory['orderNo'];



//$order = $arrs['OrderReturn'];
//
//$OrderReturnsql = "UPDATE ln_excbe SET order_status = ".$order['returnStatus'].",order_updateTime=".$order['returnTime'].
//    ",order_returnInfo= "."'".$order["returnInfo"] ."'". " WHERE orderNo = ". "'".$order["orderNo"] ."'";

//$order = $arrs['ReceiptsReturn'];
//
//
//$OrderReturnsql = "UPDATE ln_excbe SET receipt_status = ".$order['returnStatus'].",receipt_updateTime=".$order['returnTime'].
//    ",receipt_returnInfo= "."'".$order["returnInfo"] ."'". " WHERE orderNo = ". "'".$order["orderNo"] ."'";
////			file_put_contents('./log/403/'.date('Ymd',time()).'OrderReturnsql.txt',$OrderReturnsql.PHP_EOL,FILE_APPEND);
////var_dump($OrderReturnsql);
//$result = mysqli_query($con, $OrderReturnsql);//cbe数据库 23.57.162.187 出口
////			var_dump($result);
//echo '收款单回执已收到，订单号：'.$order['orderNo'];
//
////$OrderReturnsql = "UPDATE ln_excbe SET order_status = 11,order_updateTime=11,order_returnInfo= 11 WHERE orderNo = 'NO202409224375183'";
////file_put_contents('log/303/'.date('Ymd',time()).'OrderReturnsql.txt',$OrderReturnsql.PHP_EOL,FILE_APPEND);
////var_dump($OrderReturnsql);
//$result = mysqli_query($con, $OrderReturnsql);//cbe数据库 123.57.162.187 出口
//var_dump($result);die;
////unset($OrderReturnsql);
////var_dump($result);
//echo '订单回执已收到，订单号：'.$order['orderNo'];