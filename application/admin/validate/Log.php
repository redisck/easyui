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

class Log extends Validate{
    //重写测试google浏览器bug
    protected  $rule=[
        '__token__|效验数据'=>['token'],
        'username|用户名'=>['require'],
        'module|模块路径'=>['require'],
        'modulename|模块名'=>['require'],
        'time|时间'=>['require'],
        'ip|IP'=>['require'],
    ];

    protected $message=[
        '__token__.token' => ':attribute 无效！',
        'username.require' => ':attribute 不能为空！',
        'module.require' => ':attribute 不能为空！',
        'modulename.require' => ':attribute 不能为空！',
        'time.confirm' => ':attribute 不能为空！',
        'ip.require' => ':attribute 不能为空！',
    ];

    protected $scene=[
        'create'=>['username','module','modulename','time','ip'],   //创建验证
    ];
}