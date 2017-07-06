<?php

namespace app\admin\model;

use think\Loader;
use think\Model;

class Module extends Model
{
    protected $field = true;    //过滤不存在的字段
    protected $pk='id';
    protected $vname='模块';

    public function getAll($keyword = '', $status = '')
    {
        $res = [
            'status' => 300,
            'message' => '数据加载出错',
        ];
        $result = cache('module_all');
        if(!$result){
            $dirs = array_map('basename', glob(APP_PATH . '*', GLOB_ONLYDIR));

            if ($dirs === false || !file_exists(APP_PATH)) {
                $res['message'] = '模块目录不可读或者不存在';
                return $res;
            }

            // 不读取模块信息的目录
            $except_module = ['common', 'admin', 'index', 'extra', 'route', 'install', 'api'];
            // 正常模块(包括已安装和未安装)
            $dirs = array_diff($dirs, $except_module);
            $modules = $this->order('sort asc,id desc')->column(true, 'name');
            foreach ($dirs as $module) {
                if (!isset($modules[$module])) {
                    $info = self::getInfoFromFile($module);
                    $modules[$module]['name'] = $module;
                    // 模块模块信息缺失
                    if (empty($info)) {
                        $modules[$module]['status'] = '-2';
                        continue;
                    }
                    // 模块模块信息不完整
                    if (!$this->checkInfo($info)) {
                        $modules[$module]['status'] = '-3';
                        continue;
                    }

                    // 模块未安装
                    $modules[$module] = $info;
                    $modules[$module]['status'] = '-1'; // 模块未安装
                }
            }

            // 数量统计
            $total = [
                'all' => count($modules), // 所有模块数量
                '-2'  => 0,               // 已损坏数量
                '-1'  => 0,               // 未安装数量
                '0'   => 0,               // 已禁用数量
                '1'   => 0,               // 已启用数量
            ];
            // 过滤查询结果和统计数量
            $data=[];
            foreach ($modules as $key => $value) {
                // 统计数量
                if (in_array($value['status'], ['-2', '-3'])) {
                    // 已损坏数量
                    $total['-2']++;
                } else {
                    $total[(string)$value['status']]++;
                }

                // 过滤查询
                if ($status != '') {
                    if ($status == '-2') {
                        // 过滤掉非已损坏的模块
                        if (!in_array($value['status'], ['-2', '-3'])) {
                            unset($modules[$key]);
                            continue;
                        }
                    } else if ($value['status'] != $status) {
                        unset($modules[$key]);
                        continue;
                    }
                }
                if ($keyword != '') {
                    if (stristr($value['name'], $keyword) === false && (!isset($value['title']) || stristr($value['title'], $keyword) === false) && (!isset($value['author']) || stristr($value['author'], $keyword) === false)) {
                        unset($modules[$key]);
                        continue;
                    }
                }
                $data[]=$value;
            }
            // 处理状态及模块按钮
            foreach ($data as &$module) {
                // 系统核心模块
                if (isset($module['system_module']) && $module['system_module'] == '1') {
                    $module['status'] = '<span class="easyui-linkbutton button-line-red button-sm l-btn luck-pd5">不可操作</span>';
                    continue;
                }

                switch ($module['status']) {
                    case '-4': // 插件信息不完整
                        $module['title'] = '插件信息不完整';
                        $module['version'] = '无版本号';
                        $module['description'] = '暂无简介';
                        $module['status'] = '<span class="easyui-linkbutton button-line-red button-sm l-btn luck-pd5">已损坏</span>';
                        break;
                    case '-3': // 插件信息缺失
                        $module['title'] = '插件信息缺失';
                        $module['version'] = '无版本号';
                        $module['description'] = '暂无简介';
                        $module['status'] = '<span class="easyui-linkbutton button-line-red button-sm l-btn luck-pd5">已损坏</span>';
                        break;
                    case '-2': // 入口文件不存在
                        $module['title'] = '入口文件不存在';
                        $module['version'] = '无版本号';
                        $module['description'] = '暂无简介';
                        $module['status'] = '<span class="easyui-linkbutton button-line-red button-sm l-btn luck-pd5">已损坏</span>';
                        break;
                    case '-1': // 未安装
                        break;
                    case '0': // 禁用
                        break;
                    case '1': // 启用
                        break;
                    default: // 未知
                        $plugin['title'] = '未知';
                        break;
                }
            }

            $result = ['total' => $total, 'modules' => $data];


            $res['status'] = 200;
            $res['message'] = '模块目录加载完成';

            $result['res']=$res;
            // 非开发模式，缓存数据
            if (config('luck.develop_mode') == 0) {
                cache('module_all', $result);
            }
        }
        return $result;
    }


    /**
     * 从文件获取模块信息
     * @param string $name 模块名称
     * @author 史光芒(steven) <289650682@qq.com>
     * @return array|mixed
     */
    public static function getInfoFromFile($name = '')
    {
        $info = [];
        if ($name != '') {
            // 从配置文件获取
            if (is_file(APP_PATH. $name . '/info.php')) {
                $info = include APP_PATH. $name . '/info.php';
            }
        }
        return $info;
    }

    /**
     * 检查模块模块信息是否完整
     * @param string $info 模块模块信息
     * @author 史光芒(steven) <289650682@qq.com>
     * @return bool
     */
    private function checkInfo($info = '')
    {
        $default_item = ['name','title','author','version'];
        foreach ($default_item as $item) {
            if (!isset($info[$item]) || $info[$item] == '') {
                return false;
            }
        }
        return true;
    }

    /**
     * 从文件获取模块菜单
     * @param string $name 模块名称
     * @author 史光芒(steven) <289650682@qq.com>
     * @return array|mixed
     */
    public static function getMenusFromFile($name = '')
    {
        $menus = [];
        if ($name != '' && is_file(APP_PATH. $name . '/menus.php')) {
            // 从菜单文件获取
            $menus = include APP_PATH. $name . '/menus.php';
        }
        return $menus;
    }


    public function edit($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];

        $validate = Loader::validate('Module');//或者$validate =validate('Admin');
        if(!$validate->scene('edit')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }
        //验证用户名重复
        $info=$this->where('name',$input['name'])->where('id','NEQ',$input['id'])->find();
        if($info){
            $res['message']=$this->vname.'已经存在！';
            return $res;
        }

        $data=$this->save($input,[$this->pk=>$input['id']]);
        if($data){
            $res['status']=200;
            $res['message']=$this->vname.'修改成功！';
        }else{
            $res['message']=$this->vname.'修改失败,请重试！';
        }
        return $res;
    }

    /**
     * 获取所有模块的名称和标题
     * @author 史光芒(steven) <289650682@qq.com>
     * @return mixed
     */
    public static function getModule()
    {
        $modules = cache('modules');
        if (!$modules) {
            $modules = self::where('status', '>=', 0)->order('id')->column('name,title');
            // 非开发模式，缓存数据
            if (config('luck.develop_mode') == 0) {
                cache('modules', $modules);
            }
        }
        return $modules;
    }
}
