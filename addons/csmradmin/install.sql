--
-- 表的结构 `__PREFIX__csmradmin_adminapply`
--
CREATE TABLE IF NOT EXISTS `__PREFIX__csmradmin_adminapply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(100) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(100) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `ip` varchar(50) DEFAULT NULL COMMENT '登录IP',
  `description` text COMMENT '备注',
  `relate_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `auth_group_ids` varchar(100) DEFAULT NULL COMMENT '对应管理员角色',
  `auditstatus` enum('-1','0','1','-2') NOT NULL DEFAULT '0' COMMENT '审核状态:-2=申请撤销,-1=审核退回,0=待审核,1=审核通过',
  `auditreturn` varchar(255) DEFAULT NULL COMMENT '审核退回原因',
  `audituser_id` varchar(100) DEFAULT NULL COMMENT '审核人',
  `audituser` varchar(100) DEFAULT NULL COMMENT '审核人姓名',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `b1` varchar(100) DEFAULT NULL COMMENT '备用字段1',
  `b2` varchar(100) DEFAULT NULL COMMENT '备用字段2',
  `b3` varchar(100) DEFAULT NULL COMMENT '备用字段3',
  `b4` varchar(100) DEFAULT NULL COMMENT '备用字段4',
  `b5` varchar(100) DEFAULT NULL COMMENT '备用字段5',
  `b6` varchar(100) DEFAULT NULL COMMENT '备用字段6',
  `b7` varchar(100) DEFAULT NULL COMMENT '备用字段7',
  `b8` varchar(100) DEFAULT NULL COMMENT '备用字段8',
  `b9` varchar(100) DEFAULT NULL COMMENT '备用字段9',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='管理员注册申请表';
