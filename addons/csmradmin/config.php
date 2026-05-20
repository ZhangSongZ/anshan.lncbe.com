<?php
use think\Request;

return [
    [
        'name' => 'auditokemailtitle',
        'title' => '帐号审核通知邮件标题',
        'type' => 'string',
        'content' => array(),
        'value' => '恭喜您，您的帐号审核通过',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => ''
    ],
    [
        'name' => 'auditokemailcontent',
        'title' => '帐号审核通知邮件内容',
        'type' => 'text',
        'content' => array(),
        'value' => '%nickname%，您好！
您的帐号%username%已经审核审核通过，请通过以下链接登陆系统。
%loginurl%',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => ''
    ],
    [
        'name' => 'auditreturnemailtitle',
        'title' => '帐号审核退回的通知邮件标题',
        'type' => 'string',
        'content' => array(),
        'value' => '您的帐号审核不通过',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => ''
    ],
    [
        'name' => 'auditreturnemailcontent',
        'title' => '帐号审核退回的通知邮件内容',
        'type' => 'text',
        'content' => array(),
        'value' => '%nickname%，您好！
您的帐号%username%审核不通过原因是：%auditreturn%
',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => ''
    ],
    [
        'name' => '__tips__',
        'title' => '邮件内容配置：',
        'type' => 'string',
        'content' => array(),
        'value' => "%username%:用户名<BR>%username%:姓名<BR>%email%:email地址<BR>%auditreturn%:驳回<BR>%loginurl%:系统登录地址",
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => ''
    ]
];
