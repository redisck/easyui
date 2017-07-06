<?php
/**
 * Created by PhpStorm.
 * User: 式神 (luck48.com)
 * Email: 289650682@qq.com
 * Name: ${NAME}Administrator
 * Date: 2017-05-09
 * Time: 16:23
 */
use think\Route;

//登陆路由
Route::any('admin/login/$','admin/Login/login',['method' => 'post|get']);
Route::rule('admin/logout$','admin/Login/logout');

//后台菜单
Route::post('admin/menu','admin/Category/menu');

/*后台不同路由*/
Route::get('admin/info$','admin/Index/info');

//后台主页
Route::rule('admin$','admin/Index/index');

//管理员
Route::any('admin/pass$','admin/Manager/pass',['method' => 'post|get']);    //登陆人员的密码修改
Route::any('admin/manager/access$','admin/Manager/access',['method' => 'post|get']);
Route::any('admin/manager/group$','admin/Manager/group',['method' => 'post|get']);
Route::any('admin/manager$','admin/Manager/index',['method' => 'post|get']);
Route::any('admin/manager/add$','admin/Manager/add',['method' => 'post|get']);
Route::any('admin/manager/edit$','admin/Manager/edit',['method' => 'post|get']);
Route::any('admin/manager/del$','admin/Manager/remove',['method' => 'post']);

//rule菜单
Route::any('admin/ruletree$','admin/Rule/ruletree',['method' => 'post']);
Route::any('admin/rule$','admin/Rule/index',['method' => 'post|get']);
Route::any('admin/rule/add$','admin/Rule/add',['method' => 'post|get']);
Route::any('admin/rule/edit$','admin/Rule/edit',['method' => 'post|get']);
Route::any('admin/rule/del$','admin/Rule/remove',['method' => 'post']);

//管理组菜单
Route::any('admin/group/rule$','admin/Group/rule',['method' => 'post|get']);
Route::any('admin/group$','admin/Group/index',['method' => 'post|get']);
Route::any('admin/group/add$','admin/Group/add',['method' => 'post|get']);
Route::any('admin/group/edit$','admin/Group/edit',['method' => 'post|get']);
Route::any('admin/group/del$','admin/Group/remove',['method' => 'post']);

//日志组菜单
Route::any('admin/log$','admin/log/index',['method' => 'post|get']);
Route::any('admin/log/del$','admin/log/remove',['method' => 'post']);

//模块
Route::any('admin/module$','admin/module/index',['method' => 'post|get']);
Route::any('admin/module/group/<group>$','admin/module/index',['method' => 'post|get'],['group'=>'\w+']);
Route::any('admin/module/install$','admin/module/install',['method' => 'post|get']);
Route::any('admin/module/uninstall$','admin/module/uninstall',['method' => 'post|get']);
Route::any('admin/module/edit$','admin/module/edit',['method' => 'post']);
Route::any('admin/module/export','admin/module/export',['method' => 'post|get']);

//插件
Route::any('admin/plugin$','admin/plugin/index',['method' => 'post|get']);
Route::any('admin/plugin/group/<group>$','admin/plugin/index',['method' => 'post|get'],['group'=>'\w+']);
Route::any('admin/plugin/install$','admin/plugin/install',['method' => 'post']);
Route::any('admin/plugin/uninstall$','admin/plugin/uninstall',['method' => 'post']);
Route::any('admin/plugin/edit$','admin/plugin/edit',['method' => 'post']);
Route::any('admin/plugin/config$','admin/plugin/config',['method' => 'post|get']);
Route::any('admin/plugin/manage$','admin/plugin/manage',['method' => 'post|get']);

//钩子
Route::any('admin/hook$','admin/hook/index',['method' => 'post|get']);
Route::any('admin/hook/add$','admin/hook/add',['method' => 'post|get']);
Route::any('admin/hook/edit$','admin/hook/edit',['method' => 'post|get']);
Route::any('admin/hook/remove$','admin/hook/remove',['method' => 'post|get']);

//404
Route::any('admin/404','admin/error/e404',['method' => 'get']);

Route::any('admin/chosefile/<num>-<ext>-<name>$','admin/All/choseFile',['method' => 'get'],['num'=>'\d?','ext'=>'\w+','name'=>'\w+']);
Route::any('admin/upload/<name>$','admin/All/upload',['method' => 'post'],['name'=>'\w{1,10}']);