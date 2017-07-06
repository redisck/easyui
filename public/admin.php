<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ PHP版本检查 ]
header("Content-type: text/html; charset=utf-8");
if (version_compare(PHP_VERSION, '5.5', '<')) {
    die('PHP版本过低，最少需要PHP5.5，请升级PHP版本！');
}
// 检查是否安装
if(!is_file('./data/install.lock')){
    //define('BIND_MODULE', 'install');
}

define('APP_PATH', __DIR__ . '/../application/');

// 定义入口为admin
define('ENTRANCE', 'admin');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/base.php';
// 关闭路由
\think\App::route(false);

// 执行应用
\think\App::run()->send();

