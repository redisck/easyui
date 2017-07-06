<?php

namespace app\admin\controller;

use app\common\builder\LBuilder;
use app\admin\model\Config as ConfigModel;
use app\admin\model\Module as ModuleModel;
use think\Db;

class System extends Common
{
    protected function _initialize()
    {
        parent::_initialize();
        $this->vname="系统设置";
        $this->vid="system";
    }

    public function index(){
        if ($this->request->isPost()) {
            $res=[
                'status'=>300,
                'message'=>'验证失败',
            ];
            $module=$data = $this->request->post();

            $items = ConfigModel::where('status', 1)->column('name,type');
            foreach ($items as $name => $type) {
                if (!isset($data[$name])) {
                    switch ($type) {
                        // 开关
                        case 'switch':
                            $data[$name] = 0;
                            break;
                        case 'checkbox':
                            $data[$name] = '';
                            break;
                    }
                } else {
                    // 如果值是数组则转换成字符串，适用于复选框等类型
                    if (is_array($data[$name])) {
                        $data[$name] = implode(',', $data[$name]);
                    }
                    switch ($type) {
                        // 开关
                        case 'switch':
                            $data[$name] = 1;
                            break;
                        // 日期时间
                        case 'date':
                        case 'time':
                        case 'datetime':
                            $data[$name] = strtotime($data[$name]);
                            break;
                    }
                }
                ConfigModel::where('name', $name)->update(['value' => $data[$name]]);
                unset($module[$name]);
            }
            $modules = ModuleModel::where('config', 'neq', '')
                ->where('status', 1)
                ->column('config,title,name', 'name');

            Db::startTrans();
            try{
                foreach($modules as $k=>$v){
                    foreach($module as $kk=>$r){
                        if(preg_match('/'.$k.'_(.*)/',$kk,$match)){
                            $dd[$k][$match[1]]=$r;
                        }
                    }
                    if(false===ModuleModel::where('name', $k)->update(['config' => json_encode($dd[$k])])){
                        $res['message']='更新失败,请重试！';
                        return json_encode($res);
                    }
                    // 非开发模式，缓存数据
                    if (config('luck.develop_mode') == 0) {
                        cache('module_config_'.$k, $data);
                    }
                }
                // 提交事务
                Db::commit();
                savelogs('更新'.$this->vname);
                cache('system_config', null);
                $res['status']=200;
                $res['message']='更新成功！';
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $res['message']='更新失败,请重试！';
            }

            return json_encode($res);
        }else{
            // 配置分组信息
            $list_group = config('config_group');

            // 读取模型配置
            $modules = ModuleModel::where('config', 'neq', '')
                ->where('status', 1)
                ->column('config,title,name', 'name');
            foreach ($modules as $name => $module) {
                $list_group[$name] = $module['title'];
            }

            // 查询条件
            $map['status'] = 1;

            // 数据列表
            $data_list = ConfigModel::where($map)
                ->order('sort asc,id asc')
                ->column('name,title,group,tips,type,value,options,ak');

            foreach ($data_list as &$value) {
                // 解析options
                if ($value['options'] != '') {
                    $value['options'] = parse_attr($value['options']);
                }
            }

            // 默认模块列表
            if (isset($data_list['home_default_module'])) {
                $list_module['index'] = '默认';
                $data_list['home_default_module']['options'] = array_merge($list_module, ModuleModel::getModule());
            }
            $config[0][0]='group';
            $db_config=[];
            $db_c=[];
            foreach ($list_group as $k =>$item) {
                $num=0;
                if(isset(config('config_group')[$k])){
                    foreach($data_list as $v) {
                        if($k==$v['group']){

                            if($v['type']=='checkbox'){
                                $v['value']=explode(',',$v['value']);
                            }

                            $config[0][1][$item][$num]=[$v['type'], $v['name'], $v['title'], $v['tips'], $v['options'], $v['value']];
                            $db_config[$v['name']]=$v['value'];
                            $num++;
                        }
                    }
                }else{
                    // 模块配置
                    $module_info = ModuleModel::getInfoFromFile($k);
                    foreach($module_info['config'] as &$r){
                        $r[1]=$k.'_'.$r[1];
                    }
                    $config[0][1][$item]= $module_info['config'];
                    // 数据库内的模块信息
                    $db_config_json = ModuleModel::where('name', $k)->value('config');
                    $db_config_json = json_decode($db_config_json, true);

                    foreach($db_config_json as $kk=>$v){
                        $db_c[$k.'_'.$kk]=$v;
                    }
                }
            }
            $db_config=array_merge($db_config,$db_c);
            return LBuilder::make('form')
                ->setPageTitle('系统设置')
                ->setFormId('sys')
                ->addSubmit('提交',url('admin/system/index'))
                ->addFormItems($config)
                ->setFormdata($db_config)
                ->fetch();
        }
    }
}
