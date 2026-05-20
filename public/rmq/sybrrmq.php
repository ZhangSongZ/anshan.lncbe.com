<?php
require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$countent = file_get_contents('php://input');
file_put_contents(__DIR__ .'/time'.time().'.txt',date('Y-m-d H:i:s').base64_encode(serialize($countent)).PHP_EOL, FILE_APPEND);
$countentArray = json_decode($countent,true);
$conf = $countentArray['conf'];
// $conf = array(
//     'host' => '39.100.243.142',
//     'port' => 5672,
//     'user' => 'aseadmin',
//     'pwd' => 'aseadmin',
//     'vhost' => 'default_vhost',
// );
file_put_contents(__DIR__ .'/timeeerrree.txt',date('Y-m-d H:i:s').base64_encode(serialize($conf)).PHP_EOL, FILE_APPEND);
$exchangeN = $conf['exchangeN']; 
$queueN = $conf['queueNsend']; 
$routingKey = ''; //路由关键字(也可以省略)
$conn1 = new AMQPStreamConnection( //建立生产者与mq之间的连接
    $conf['host'], $conf['port'], $conf['user'], $conf['pwd'], $conf['vhost']
);
$channel1 = $conn1->channel(); //在已连接基础上建立生产者与mq之间的通道
$channel1->exchange_declare($exchangeN, 'direct', false, true, false); //声明初始化交换机
$channel1->queue_declare($queueN, false, true, false, false); //声明初始化一条队列
$channel1->queue_bind($queueN, $exchangeN, $routingKey); //将队列与某个交换机进行绑定，并使用路由关键字

// file_put_contents(__DIR__ .'/log/ds/'.time().'1116031.txt',$countent.PHP_EOL, FILE_APPEND);
//$countent = <<<XML

//XML;
$msgBody = $countentArray['xml'];
$msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain', 'delivery_mode' => 2)); //生成消息
$r = $channel1->basic_publish($msg, $exchangeN, $routingKey); //推送消息到某个交换机
var_dump($msg);
var_dump($r);
$channel1->close();
$conn1->close();

