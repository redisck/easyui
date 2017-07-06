<?php

namespace app\admin\controller;

use app\admin\model\Plugin as PluginModel;
use app\admin\model\HookPlugin as HookPluginModel;
use app\common\builder\LBuilder;
use luck\Sql;
use think\Db;
use think\Loader;

class Plugin extends Common
{
    protected  function _initialize()
    {
        parent::_initialize();
        $this->db=new PluginModel();
        $this->vname="插件";
        $this->vid="plugin";
    }

    /**
     * 首页
     * @param string $group 分组
     * @param string $type 显示类型
     * @author 史光芒 <289650682@qq.com>
     * @return mixed
     */
    public function index($group = 'local', $type = ''){
        if(request()->isPost()) {

            switch ($group) {
                case 'local':
                    $status='';
                    $keyword='';
                    foreach(input("post.") as $k =>$v){
                        if(preg_match('/search_(.*)/',$k,$match)){
                            if($match[1]=='status'){
                                $status=$v;
                            }else if($match[1]=='name'){
                                $keyword=$v;
                            }
                        }
                    }
                    $data=$this->db->getAll($keyword,$status);
                    if($data['res']['status']!=200){
                        $res['rows']=[];
                    }else{
                        $res['rows']=$data['plugins'];
                        $res['total']=$data['total']['all'];
                    }
                    break;
                case 'online':
                    $res['total']=[];
                    $res['rows']=[];
                    break;
                default:
                    $res['total']=[];
                    $res['rows']=[];
                    return;
            }
            return $res;
        }
        $list_group = ['local' => '本地模块','online'=>'在线模板'];
        $tab_list=[];
        foreach ($list_group as $key => $value) {
            $tab_list[$key]['title'] = $value;
            $tab_list[$key]['url']   = url('admin/plugin/index', ['group' => $key]);
        }
        $this->assign('tab_list',$tab_list);

        switch ($group) {
            case 'local':
                $data = $this->db->getAll();
                $status=[
                    "全部"=>['value'=>'','num'=>$data['total']['all']],
                    "已启用"=>['value'=>'1','num'=>$data['total']['1']],
                    "未启用"=>['value'=>'0','num'=>$data['total']['0']],
                    "未安装"=>['value'=>'-1','num'=>$data['total']['-1']],
                    "已损坏"=>['value'=>'-2','num'=>$data['total']['-2']],
                ];

                $this->assign('status',$status);
                break;
        }
        return view();
    }

    /**
     * 安装插件
     * @param string $name 插件标识
     * @author 史光芒 <289650682@qq.com>
     */
    public function install(){
        if(request()->isPost()){
            $name=request()->get('name');
            $plug_name = trim($name);
            $res=[
                'status'=>300,
                'message'=>'插件不存在！',
            ];
            if ($plug_name == '') return $res;
            $plugin_class = get_plugin_class($plug_name);

            if (!class_exists($plugin_class)) {
                return $res;
            }
            // 实例化插件
            $plugin = new $plugin_class;

            //插件预安装
            if(!$plugin->install()) {
                $res['message']='插件预安装失败!原因：'. $plugin->getError();
                return $res;
            }

            // 插件配置信息
            $plugin_info = $plugin->info;
            // 验证插件信息
            $validate = Loader::validate('Plugin');
            // 验证失败 输出错误信息
            if(!$validate->scene('create')->check($plugin_info)){
                $res['message']=$validate->getError();
                return $res;
            }

            // 添加钩子
            if (isset($plugin->hooks) && !empty($plugin->hooks)) {
                if (!HookPluginModel::addHooks($plugin->hooks, $name)) {
                    $res['message']='安装插件钩子时出现错误，请重新安装';
                    return $res;
                }
                cache('hook_plugins', null);
            }

            // 执行安装插件sql文件
            $sql_file = realpath(config('plugin_path').$name.'/install.sql');
            if (file_exists($sql_file)) {
                if (isset($plugin->database_prefix) && $plugin->database_prefix != '') {
                    $sql_statement = Sql::getSqlFromFile($sql_file, false, [$plugin->database_prefix => config('database.prefix')]);
                } else {
                    $sql_statement = Sql::getSqlFromFile($sql_file);
                }

                if (!empty($sql_statement)) {
                    foreach ($sql_statement as $value) {
                        Db::execute($value);
                    }
                }
            }
            // 并入插件配置值
            $plugin_info['config'] = $plugin->getConfigValue();

            // 将插件信息写入数据库
            if ($this->db->create($plugin_info)) {
                cache('plugin_all', null);
                $res['status']=200;
                $res['message']='插件安装成功';
                savelogs('安装'.$this->vname,$plug_name.'成功!');
            } else {
                $res['message']='插件安装失败';
                savelogs('安装'.$this->vname,$plug_name.'失败!');
            }
            return $res;
        }
        $this->redirect('admin/error/e404',['err'=>'非法操作']);
        return;
    }


    /**
     * 卸载插件
     * @param string $name 插件标识
     * @author 史光芒 <289650682@qq.com>
     */
    public function uninstall($name = '')
    {
        if(request()->isPost()) {
            $name=request()->get('name');
            $plug_name = trim($name);
            $res=[
                'status'=>300,
                'message'=>'插件不存在！',
            ];
            if ($plug_name == '') return $res;

            $class = get_plugin_class($plug_name);
            if (!class_exists($class)) {
                return $res;
            }

            // 实例化插件
            $plugin = new $class;
            // 插件预卸
            if (!$plugin->uninstall()) {
                $res['message']='插件预卸载失败!原因：' . $plugin->getError();
                return $res;
            }

            // 卸载插件自带钩子
            if (isset($plugin->hooks) && !empty($plugin->hooks)) {
                if (false === HookPluginModel::deleteHooks($plug_name)) {
                    $res['message']='卸载插件钩子时出现错误，请重新卸载';
                    return $res;
                }
                cache('hook_plugins', null);
            }

            // 执行卸载插件sql文件
            $sql_file = realpath(config('plugin_path') . $plug_name . '/uninstall.sql');
            if (file_exists($sql_file)) {
                if (isset($plugin->database_prefix) && $plugin->database_prefix != '') {
                    $sql_statement = Sql::getSqlFromFile($sql_file, true, [$plugin->database_prefix => config('database.prefix')]);
                } else {
                    $sql_statement = Sql::getSqlFromFile($sql_file, true);
                }

                if (!empty($sql_statement)) {
                    Db::execute($sql_statement);
                }
            }

            // 删除插件信息
            if (PluginModel::where('name', $plug_name)->delete()) {
                cache('plugin_all', null);
                $res['status']=200;
                $res['message']='插件卸载成功';
                savelogs('卸载'.$this->vname,$plug_name.'成功!');
                return $res;
            } else {
                $res['message']='插件卸载失败';
                savelogs('卸载'.$this->vname,$plug_name.'失败!');
                return $res;
            }
        }

        $this->redirect('admin/error/e404',['err'=>'非法操作']);
        return;
    }

    //修改插件状态
    public function edit(){
        if(request()->isPost()){
            $data=input('post.');
            $res=$this->db->edit($data);

            if($res['status']==200){
                savelogs('编辑'.$this->vname,$data['name'].' id:'.$data['id']);
            }else{
                savelogs('编辑'.$this->vname,$data['name'].' id:'.$data['id'].' 失败');
            }
            return json_encode($res);
        }
    }

    /**
     * 插件参数设置
     * @param string $name 插件名称
     * @author 史光芒 <289650682@qq.com>
     */
    public function config($name = '')
    {
        // 更新配置
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data = json_encode($data);
            $res=[
                'status'=>300,
                'message'=>'更新失败',
            ];
            if (false !== PluginModel::where('name', $name)->update(['config' => $data])) {
                $res['status']=200;
                $res['message']=$this->vname.'更新成功！';
            }
            return json_encode($res);
        }
        $name=request()->get('name');
        /*$plugin_class = get_plugin_class($name);
        // 实例化插件
        $plugin  = new $plugin_class;
        $trigger = isset($plugin->trigger) ? $plugin->trigger : [];*/

        // 插件配置值
        $info      = PluginModel::where('name', $name)->field('id,name,config')->find();
        $db_config = json_decode($info['config'], true);
        // 插件配置项
        $config    = include config('plugin_path'). $name. '/config.php';
        // 使用ZBuilder快速创建表单
        return LBuilder::make('form')
            ->setPageTitle('设置插件')
            ->addFormItems($config)
            ->setFormData($db_config)
            ->fetch();
    }

    public function manage($name = ''){
        $res=[
            'status'=>300,
            'message'=>'功能开发中...',
        ];
        return $res;
    }
}
