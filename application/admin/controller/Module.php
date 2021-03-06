<?php

namespace app\admin\controller;

use \app\admin\model\Module as ModuleModel;
use \app\admin\model\Plugin as PluginModel;
use \app\common\model\Rule as RuleModel;
use luck\File;
use luck\Sql;
use think\Db;
use think\Hook;

class Module extends Common
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->db=new ModuleModel();
        $this->vname="模块";
        $this->vid="module";
    }
    /**
     * 模块首页
     * @param string $group 分组
     * @param string $type 显示类型
     * @author 史光芒(steven) <289650682@qq.com>
     * @return mixed
     */
    public function index($group = 'local')
    {
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
                        $res['rows']=$data['modules'];
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
            $tab_list[$key]['url']   = url('admin/module/index', ['group' => $key]);
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
     * 安装模块
     * @param string $name 模块标识
     * @param int $confirm 是否确认
     * @author 式神 <289650682@qq.com>
     * @return mixed
     */
    public function install($name = '', $confirm = 0)
    {
        $res=[
            'status'=>300,
            'message'=>$this->vname.'不存在！',
        ];
        if ($name == '') return $res;
        if ($name == 'admin'){
            $res['message']='禁止操作系统核心模块！';
           return $res;
        }
        // 模块配置信息
        $module_info = ModuleModel::getInfoFromFile($name);

        if ($confirm == 0) {
            $need_module = [];
            $need_plugin = [];
            $table_check = [];
            // 检查模块依赖
            if (isset($module_info['need_module']) && !empty($module_info['need_module'])) {
                $need_module = $this->checkDependence('module', $module_info['need_module']);
            }

            // 检查插件依赖
            if (isset($module_info['need_plugin']) && !empty($module_info['need_plugin'])) {
                $need_plugin = $this->checkDependence('plugin', $module_info['need_plugin']);
            }

            // 检查数据表
            if (isset($module_info['tables']) && !empty($module_info['tables'])) {
                foreach ($module_info['tables'] as $table) {
                    if (Db::query("SHOW TABLES LIKE '".config('database.prefix')."{$table}'")) {
                        $table_check[] = [
                            'table' => config('database.prefix')."{$table}",
                            'result' => '<span class="text-danger">存在同名</span>'
                        ];
                    } else {
                        $table_check[] = [
                            'table' => config('database.prefix')."{$table}",
                            'result' => '<i class="fa fa-check text-success"></i>'
                        ];
                    }
                }
            }

            $this->assign('need_module', $need_module);
            $this->assign('need_plugin', $need_plugin);
            $this->assign('table_check', $table_check);
            $this->assign('name', $name);
            $this->assign('page_title', '安装模块：'. $name);
            //return $this->fetch();
            return view();
        }

        // 执行安装文件
        $install_file = realpath(APP_PATH.$name.'/install.php');
        if (file_exists($install_file)) {
            @include($install_file);
        }
        // 执行安装模块sql文件
        $sql_file = realpath(APP_PATH.$name.'/sql/install.sql');
        if (file_exists($sql_file)) {
            if (isset($module_info['database_prefix']) && !empty($module_info['database_prefix'])) {
                $sql_statement = Sql::getSqlFromFile($sql_file, false, [$module_info['database_prefix'] => config('database.prefix')]);
            } else {
                $sql_statement = Sql::getSqlFromFile($sql_file);
            }
            if (!empty($sql_statement)) {
                foreach ($sql_statement as $value) {
                    try{
                        Db::execute($value);
                    }catch(\Exception $e){
                        //$this->error('导入SQL失败，请检查install.sql的语句是否正确');
                        $res['message']='导入SQL失败，请检查install.sql的语句是否正确';
                        return  json_encode($res);
                    }
                }
            }
        }

        // 添加菜单
        $menus = ModuleModel::getMenusFromFile($name);
        if (is_array($menus) && !empty($menus)) {
            if (false === $this->addMenus($menus, $name)) {
                $res['message']='菜单添加失败，请重新安装';
                return  json_encode($res);
            }
        }

        // 检查是否有模块设置信息
        if (isset($module_info['config']) && !empty($module_info['config'])) {
            $module_info['config'] = json_encode(parse_config($module_info['config']));
        }

        // 将模块信息写入数据库
        $ModuleModel = new ModuleModel($module_info);
        $allowField = ['name','title','description','author','author_url','config','access','version','identifier','status'];

        if ($ModuleModel->allowField($allowField)->save()) {
            // 复制静态资源目录
            File::copy_dir(APP_PATH. $name. '/public', ROOT_PATH. 'public');
            // 删除静态资源目录
            File::del_dir(APP_PATH. $name. '/public');
            cache('modules', null);
            cache('module_all', null);
            // 记录行为
            $res['status']=200;
            $res['message']=$this->vname.'安装成功';
            savelogs('安装'.$this->vname,$name.'成功!');
        } else {
            RuleModel::where('module', $name)->delete();
            //return $this->error('模块安装失败');
            $res['message']=$this->vname.'安装失败';
            savelogs('安装'.$this->vname,$name.'失败!');
        }
        return json_encode($res);
    }

    /**
     * 卸载模块
     * @param string $name 模块名
     * @param int $confirm 是否确认
     * @author 式神 <289650682@qq.com>
     * @return mixed|void
     */
    public function uninstall($name = '', $confirm = 0)
    {
        $res=[
            'status'=>300,
            'message'=>$this->vname.'不存在！',
        ];
        if ($name == '') return $res;
        if ($name == 'admin'){
            $res['message']='禁止操作系统核心模块！';
            return $res;
        }

        // 模块配置信息
        $module_info = ModuleModel::getInfoFromFile($name);

        if ($confirm == 0) {
            $this->assign('name', $name);
            $this->assign('page_title', '卸载模块：'. $name);
            return view();
        }

        // 执行卸载文件
        $uninstall_file = realpath(APP_PATH.$name.'/uninstall.php');
        if (file_exists($uninstall_file)) {
            @include($uninstall_file);
        }

        // 执行卸载模块sql文件
        $clear =input('post.clear');
        if ($clear == 1) {
            $sql_file = realpath(APP_PATH.$name.'/sql/uninstall.sql');
            if (file_exists($sql_file)) {
                if (isset($module_info['database_prefix']) && !empty($module_info['database_prefix'])) {
                    $sql_statement = Sql::getSqlFromFile($sql_file, false, [$module_info['database_prefix'] => config('database.prefix')]);
                } else {
                    $sql_statement = Sql::getSqlFromFile($sql_file);
                }

                if (!empty($sql_statement)) {
                    foreach ($sql_statement as $sql) {
                        try{
                            Db::execute($sql);
                        }catch(\Exception $e){
                            $res['message']='卸载失败，请检查uninstall.sql的语句是否正确';
                            return  json_encode($res);
                            //$this->error('卸载失败，请检查uninstall.sql的语句是否正确');
                        }
                    }
                }
            }
        }
        // 删除菜单
        if (false === RuleModel::where('module', $name)->delete()) {
            //return $this->error('菜单删除失败，请重新卸载');
            $res['message']='菜单删除失败，请重新卸载';
            return  json_encode($res);
        }

        // 删除模块信息
        if (ModuleModel::where('name', $name)->delete()) {
            // 复制静态资源目录
            File::copy_dir(ROOT_PATH. 'public/static/'. $name, APP_PATH.$name.'/public/static/'. $name);
            // 删除静态资源目录
            File::del_dir(ROOT_PATH. 'public/static/'. $name);
            cache('modules', null);
            cache('module_all', null);
            // 记录行为
            $res['status']=200;
            $res['message']=$this->vname.'卸载成功';
            savelogs('卸载'.$this->vname,$name.'成功!');
        } else {
            $res['message']=$this->vname.'卸载失败';
            savelogs('卸载'.$this->vname,$name.'失败!');
        }
        return  json_encode($res);
    }

    /**
     * 检查依赖
     * @param string $type 类型：module/plugin
     * @param array $data 检查数据
     * @author 式神 <289650682@qq.com>
     * @return array
     */
    private function checkDependence($type = '', $data = [])
    {
        $need = [];
        foreach ($data as $key => $value) {
            if (!isset($value[3])) {
                $value[3] = '=';
            }
            // 当前版本
            if ($type == 'module') {
                $curr_version = ModuleModel::where('identifier', $value[1])->value('version');
            } else {
                $curr_version = PluginModel::where('identifier', $value[1])->value('version');
            }

            // 比对版本
            $result = version_compare($curr_version, $value[2], $value[3]);
            $need[$key] = [
                $type => $value[0],
                'identifier' => $value[1],
                'version' => $curr_version ? $curr_version : '未安装',
                'version_need' => $value[3].$value[2],
                'result' => $result ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>'
            ];
        }

        return $need;
    }

    /**
     * 添加模型菜单
     * @param array $menus 菜单
     * @param string $module 模型名称
     * @param int $pid 父级ID
     * @author 式神 <289650682@qq.com>
     * @return bool
     */
    private function addMenus($menus = [], $module = '', $pid = 0)
    {
        foreach ($menus as $menu) {
            $data = [
                'pid'         => $pid,
                'module'      => $module,
                'title'       => $menu['title'],
                'name'   => isset($menu['name']) ? $menu['name'] : '',
                'showed' => isset($menu['showed']) ? $menu['showed'] : 1,
                'status'      => isset($menu['status']) ? $menu['status'] : 1,
                'type'      => isset($menu['type']) ? $menu['type'] : 1,
                'sort'      => isset($menu['sort']) ? $menu['sort'] : 100
            ];
            $result = (new RuleModel)->add($data);

            if ($result['status']!=200) return false;

            if (isset($menu['child'])) {
                $this->addMenus($menu['child'], $module, $result['id']);
            }
        }

        return true;
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

    public function export($name = ''){
        $res=[
            'status'=>300,
            'message'=>'功能开发中...',
        ];
        return $res;
    }
}
