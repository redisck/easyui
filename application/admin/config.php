<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Env;
return [
    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    //'default_return_type'    => 'json',

    //输出替换
    /*'view_replace_str'  =>  [
        '__STATIC__'=>'/static/admin',
        '__PUBLIC__'=>'/public',
        '__UPLOADS__'=>'/uploads',
        '__ROOT__' => '/',
    ],*/

    /**
     * 后台系统相关配置
     * @param  string $name 插件名
     * @author 史光芒 <289650682@qq.com>
     * @return object
     */
    // 插件目录路径
    'plugin_path'        => ROOT_PATH. 'plugins'. DS,
    // 文件上传路径
    'upload_path'        => ROOT_PATH . 'public' . DS . 'uploads',
    // 文件上传临时目录
    'upload_temp_path'   => ROOT_PATH . 'public' . DS . 'uploads' . DS . 'temp/',

];
