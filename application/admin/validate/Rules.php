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

class Rules extends Validate{

    //重写测试google浏览器bug
    protected  $rule=[
        'title|规则名'=>['require'],
        'name|路径'=>['require'],
    ];

    protected $message=[
        '__token__.token' => ':attribute 无效！',
        'title.require' => ':attribute 不能为空！',
        'name.require' => ':attribute 不能为空！',
    ];

    protected $scene=[
        'create'=>['title'],   //创建验证
        'edit'=>['title'],
    ];
}