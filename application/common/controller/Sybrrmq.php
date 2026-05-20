<?php

namespace app\common\controller;


class Sybrrmq{

    public function rmq($countent){

         $conf = array(
            'host' => '39.100.243.142',
            'port' => 5672,
            'user' => 'aseadmin',
            'pwd' => 'aseadmin',
            'vhost' => 'default_vhost',
        );

        //$exchangeN = ' '; //交换机名
        $exchangeN = 'DXPENT0000528811'; //交换机名 沈阳博瑞跨境电商有限公司
        $queueN = 'DXPENT0000528811_E_SEND'; //队列名称 沈阳博瑞跨境电商有限公司
        $routingKey = ''; //路由关键字(也可以省略)
        $conn1 = rmqs( //建立生产者与mq之间的连接
            $conf['host'], $conf['port'], $conf['user'], $conf['pwd'], $conf['vhost']
        );
        $channel1 = $conn1->channel(); //在已连接基础上建立生产者与mq之间的通道
        $channel1->exchange_declare($exchangeN, 'direct', false, true, false); //声明初始化交换机
        $channel1->queue_declare($queueN, false, true, false, false); //声明初始化一条队列
        $channel1->queue_bind($queueN, $exchangeN, $routingKey); //将队列与某个交换机进行绑定，并使用路由关键字
       //  $countent = file_get_contents('php://input');
        // file_put_contents(__DIR__ .'/log/ds/'.time().'1116031.txt',$countent.PHP_EOL, FILE_APPEND);
        //$countent = <<<XML

        //XML;
        $msgBody = $countent;
        $msg = rmq($msgBody, array('content_type' => 'text/plain', 'delivery_mode' => 2)); //生成消息
        $r = $channel1->basic_publish($msg, $exchangeN, $routingKey); //推送消息到某个交换机
        var_dump($msg);
        var_dump($r);
        $channel1->close();
        $conn1->close();
    }
}