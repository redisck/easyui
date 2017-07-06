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

class Hook extends Validate{
    //重写测试google浏览器bug
    protected  $rule=[
        '__token__|效验数据'=>['token'],
        'name|名称'=>['require','hookname'],
    ];

    protected $message=[
        '__token__.token' => ':attribute 无效！',
        'name.require' => ':attribute 不能为空！',
    ];

    protected $scene=[
        'create'=>['name'],   //创建验证
        'edit'=>['name'],   //创建验证
    ];

    // 自定义验证规则
    protected function hookname($value,$rule,$data)
    {
        $reg="/^[a-zA-Z][a-zA-Z_0-9]{1,100}$/";
        return preg_match($reg,$value) ? true : ':attribute 由字母,数字和下划线组成！';
    }
}