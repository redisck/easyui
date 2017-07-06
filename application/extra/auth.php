<?php
/**
 * Created by PhpStorm.
 * User: 式神 (luck48.com)
 * Email: 289650682@qq.com
 * Name: ${NAME}Administrator
 * Date: 2017-05-08
 * Time: 17:21
 */
/**
 * auth权限配置文件
 */
return [
	'AUTH_CONFIG'		=>	array(
		'auth_on'	=>	12, //认证开关
		'auth_type'	=>	1, // 认证方式，1为时时认证；2为登录认证。
		'auth_group'=>	'auth_group', //用户组数据表名
		'auth_group_access'	=>	'auth_group_access', //用户组明细表
		'auth_rule'	=>	'auth_rule', //权限规则表
		'auth_user'	=>	'user'//用户信息表
	),

	'ADMIN_AUTH_KEY' => 'admin.id', // 认证key
	'ADMIN_NAME' => 'admin.username', // 认证key
	//超级管理员id,拥有全部权限,只要用户uid在这个角色组里的,就跳出认证.可以设置多个值,如array('1','2','3')
	'ADMINISTRATOR'=>array('1'),
	'NO_AUTH_RULES'=>array(
		'admin/index/index',//后台首页
		'admin/index/info',//控制台
		'admin/category/menu',//菜单
		'admin/rule/ruletree',//上级规则
	),
];