<?php
/**
 * Created by PhpStorm.
 * User: 式神 (luck48.com)
 * Email: 289650682@qq.com
 * Name: ${NAME}Administrator
 * Date: 2017-06-07
 * Time: 19:33
 */
namespace app\common\behavior;

use app\admin\model\Hook as HookModel;
use app\admin\model\HookPlugin as HookPluginModel;
use app\admin\model\Plugin as PluginModel;

class Hook
{
    public function run(&$params)
    {
        if(defined('BIND_MODULE') && BIND_MODULE === 'install') return;
        $hook_plugins= cache('hook_plugins');
        $hooks= cache('hooks');
        $plugins= cache('plugins');
        if (!$hook_plugins) {
            // 所有钩子
            $hooks = HookModel::where('status', 1)->column('status', 'name');
            // 所有插件
            $plugins = PluginModel::where('status', 1)->column('status', 'name');
            // 钩子对应的插件
            $hook_plugins = HookPluginModel::where('status', 1)->order('hook,sort')->select();

            if (config('luck.develop_mode') == 0) {
                cache('hook_plugins', $hook_plugins);
                cache('hooks', $hooks);
                cache('plugins', $plugins);
            }
        }

        // 行为逻辑
        if ($hook_plugins) {
            foreach ($hook_plugins as $value) {
                if (isset($hooks[$value['hook']]) && isset($plugins[$value['plugin']])) {
                    \think\Hook::add($value['hook'], get_plugin_class($value['plugin']));
                }
            }
        }
    }

}