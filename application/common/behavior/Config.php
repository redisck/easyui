<?php
/**
 * Created by PhpStorm.
 * User: 式神 (luck48.com)
 * Email: 289650682@qq.com
 * Name: ${NAME}Administrator
 * Date: 2017-06-23
 * Time: 15:01
 */
namespace app\common\behavior;

use app\admin\model\Config as ConfigModel;
use app\admin\model\Module as ModuleModel;

class Config{
    public function run(&$params){
        // 如果是安装操作，直接返回
        if(defined('BIND_MODULE') && BIND_MODULE === 'install') return;
        // 获取当前模块名称
        $module = '';
        $dispatch = request()->dispatch();
        if (isset($dispatch['module'])) {
            $module = $dispatch['module'][0];
        }
        // 获取入口目录
        $base_file = request()->baseFile();
        $base_dir  = preg_replace(['/index.php$/', '/admin.php$/'], ['', ''], $base_file);
        define('PUBLIC_PATH', $base_dir);

        $view_replace_str = [
            '__STATIC__'=>PUBLIC_PATH.'static/admin',
            '__PUBLIC__'=>PUBLIC_PATH.'public',
            '__UPLOADS__'=>PUBLIC_PATH.'uploads',
            '__ROOT__' => PUBLIC_PATH,
        ];
        config('view_replace_str', $view_replace_str);
        // 如果定义了入口为admin，则修改默认的访问控制器层
        if(defined('ENTRANCE') && ENTRANCE == 'admin') {
            if ($dispatch['type'] == 'module' && $module == '') {
                header("Location: ".$base_dir.'admin.php/admin', true, 302);exit();
            }
            if ($module != '' && $module != 'admin' && $module != 'common' && $module != 'index' && $module != 'extra' && $module != 'api') {
                // 修改默认访问控制器层
                config('url_controller_layer', 'admin');
                // 修改视图模板路径
                config('template.view_path', APP_PATH. $module. '/view/admin/');
            }
            // 插件静态资源目录
            //config('view_replace_str.__PLUGINS__', '/plugins');
        } else {
            if ($dispatch['type'] == 'module' && $module == 'admin') {
                header("Location: ".$base_dir.'admin.php/admin', true, 302);exit();
            }
            // 修改默认访问控制器层
            if ($module != '' && $module != 'index') {
                config('url_controller_layer', 'home');
            }
        }

        // 定义模块资源目录
        config('view_replace_str.__MODULE_CSS__', PUBLIC_PATH. 'static/'. $module .'/css');
        config('view_replace_str.__MODULE_JS__', PUBLIC_PATH. 'static/'. $module .'/js');
        config('view_replace_str.__MODULE_IMG__', PUBLIC_PATH. 'static/'. $module .'/img');
        config('view_replace_str.__MODULE_LIBS__', PUBLIC_PATH. 'static/'. $module .'/libs');
        // 静态文件目录
        config('public_static_path', PUBLIC_PATH. 'static/');

        // 读取系统配置
        $system_config = cache('system_config');
        if (!$system_config) {
            $ConfigModel   = new ConfigModel();
            $system_config = $ConfigModel->getConfig();
            // 所有模型配置
            $module_config = ModuleModel::where('config', 'neq', '')->column('config', 'name');
            foreach ($module_config as $module_name => $config) {
                $system_config[strtolower($module_name).'_config'] = json_decode($config, true);
            }
            // 非开发模式，缓存系统配置
            if (config('luck.develop_mode') == 0) {
                cache('system_config', $system_config);
            }
        }
        // 设置配置信息
        config($system_config);
    }
}