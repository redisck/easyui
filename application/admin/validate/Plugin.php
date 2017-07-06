<?php
/**
 * Created by PhpStorm.
 * User: 式神 (luck48.com)
 * Email: 289650682@qq.com
 * Name: ${NAME}Administrator
 * Date: 2017-06-08
 * Time: 11:40
 */
namespace app\admin\validate;

use think\Validate;

class Plugin extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|插件名称'  => 'require|unique:plugin',
        'title|插件标题' => 'require',
    ];

    protected $scene=[
        'create'=>['name','title'],   //创建验证
        'edit'=>['name'],   //创建验证
    ];
}
