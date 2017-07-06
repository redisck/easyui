<?php
/**
 * Created by PhpStorm.
 * User: 式神 (luck48.com)
 * Email: 289650682@qq.com
 * Name: ${NAME}Administrator
 * Date: 2017-05-10
 * Time: 13:42
 */
namespace app\admin\validate;

use think\Validate;

class Admin extends Validate{
    /*protected $rule = [
        '__token__' => 'token',
        'username'  =>  'require',
        'password' =>  'require',
    ];

    protected $message  =   [
        'username.require' => '用户名必须填写',
        'password.require' => '密码必须填写',
        '__token__.token' => '通行令无效，请重试！'
    ];*///我的validate

    //重写测试google浏览器bug
    protected  $rule=[
        '__token__|效验数据'=>['token'],
        'username|用户名'=>['require'],
        'password|密码'=>['require','min:6','max:20'],
        'edit_password|密码'=>['min:6','max:20'],
        'new_password|新密码'=>['require','min:6','max:20'],
        'confirm_password|确认密码'=>['require','confirm:new_password'],
        'nickname|昵称'=>['require'],
        'email|电子邮件'=>['email'],
    ];

    protected $message=[
        '__token__.token' => ':attribute 无效！',
        'username.require' => ':attribute 不能为空！',
        'password.require' => ':attribute 不能为空！',
        'new_password.require' => ':attribute 不能为空！',
        'confirm_password.require' => ':attribute 不能为空！',
        'confirm_password.confirm' => ':attribute 和 新密码不一致！',
        'nickname.require' => ':attribute 不能为空！',
        'email.email' => ':attribute 电子邮件格式不正确！',
    ];

    protected $scene=[
        'create'=>['username','password','nickname','email'],   //创建验证
        'login'=>['username','password','__token__'],   //登陆验证
        'pass'=>['password','new_password','confirm_password'], //修改密码验证
        'edit'=>['username','edit_password','nickname','email'],
    ];
}