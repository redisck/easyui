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

//后台
include_once 'route/admin.php';

return [
    '/' => 'index', // 首页访问路由
    'code/<num>-<w>-<h>-<f>$'=>['api/Common/code',['get'],['num'=>'\d+','w'=>'\d+','h'=>'\d+']],
    'token$'=>['api/Common/getToken',['get']],

    //后台
    /*'admin$'=>'admin/Index/index',
    'admin/login$'=>['admin/Login/login',['get']],*/

    //'__miss__'  => 'public/miss',

];