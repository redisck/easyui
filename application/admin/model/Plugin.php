<?php

namespace app\admin\model;

use think\Loader;
use think\Model;

class Plugin extends Model
{
    protected $field = true;    //过滤不存在的字段
    protected $pk='id';
    protected $vname='插件';

    // 写入时处理config
    public function setConfigAttr($value)
    {
        return !empty($value) ? json_encode($value) : '';
    }

    /**
     * 获取所有插件信息
     * @param string $keyword 查找关键词
     * @param string $status 查找状态
     * @author 史光芒 <289650682@qq.com>
     * @return array|bool
     */
    public function getAll($keyword = '', $status = '')
    {
        $res = [
            'status' => 300,
            'message' => '数据加载出错',
        ];

        $result = cache('plugin_all');
        if (!$result) {
            // 获取插件目录下的所有插件目录
            $dirs = array_map('basename', glob(config('plugin_path').'*', GLOB_ONLYDIR));
            if ($dirs === false || !file_exists(config('plugin_path'))) {
                $res['message'] = $this->vname.'目录不可读或者不存在';
                return $res;
            }

            // 读取数据库插件表
            $plugins = $this->order('sort asc,id desc')->column(true, 'name');

            // 读取未安装的插件
            foreach ($dirs as $plugin) {
                if (!isset($plugins[$plugin])) {
                    $plugins[$plugin]['name'] = $plugin;

                    // 获取插件类名
                    $class = get_plugin_class($plugin);

                    // 插件类不存在则跳过实例化
                    if (!class_exists($class)) {
                        // 插件的入口文件不存在！
                        $plugins[$plugin]['status'] = '-2';
                        continue;
                    }

                    // 实例化插件
                    $obj = new $class;

                    // 插件插件信息缺失
                    if (!isset($obj->info) || empty($obj->info)) {
                        // 插件信息缺失！
                        $plugins[$plugin]['status'] = '-3';
                        continue;
                    }

                    // 插件插件信息不完整
                    if (!$this->checkInfo($obj->info)) {
                        $plugins[$plugin]['status'] = '-4';
                        continue;
                    }

                    // 插件未安装
                    $plugins[$plugin] = $obj->info;
                    $plugins[$plugin]['status'] = '-1';

                }
            }

            // 数量统计
            $total = [
                'all' => count($plugins), // 所有插件数量
                '-2'  => 0,               // 错误插件数量
                '-1'  => 0,               // 未安装数量
                '0'   => 0,               // 未启用数量
                '1'   => 0,               // 已启用数量
            ];

            // 过滤查询结果和统计数量
            $data=[];
            foreach ($plugins as $key => $value) {
                // 统计数量
                if (in_array($value['status'], ['-2', '-3', '-4'])) {
                    // 已损坏数量
                    $total['-2']++;
                } else {
                    $total[(string)$value['status']]++;
                }

                // 过滤查询
                if ($status != '') {
                    if ($status == '-2') {
                        // 过滤掉非已损坏的插件
                        if (!in_array($value['status'], ['-2', '-3', '-4'])) {
                            unset($plugins[$key]);
                            continue;
                        }
                    } else if ($value['status'] != $status) {
                        unset($plugins[$key]);
                        continue;
                    }
                }
                if ($keyword != '') {
                    if (stristr($value['name'], $keyword) === false && (!isset($value['title']) || stristr($value['title'], $keyword) === false) && (!isset($value['author']) || stristr($value['author'], $keyword) === false)) {
                        unset($plugins[$key]);
                        continue;
                    }
                }
                $data[]=$value;
            }

            // 处理状态及插件按钮
            foreach ($data as &$plugin) {
                switch ($plugin['status']) {
                    case '-4': // 插件信息不完整
                        $plugin['title'] = '插件信息不完整';
                        $plugin['version'] = '无版本号';
                        $plugin['description'] = '暂无简介';
                        $plugin['status'] = '<span class="easyui-linkbutton button-line-red button-sm l-btn luck-pd5">已损坏</span>';
                        break;
                    case '-3': // 插件信息缺失
                        $plugin['title'] = '插件信息缺失';
                        $plugin['version'] = '无版本号';
                        $plugin['description'] = '暂无简介';
                        $plugin['status'] = '<span class="easyui-linkbutton button-line-red button-sm l-btn luck-pd5">已损坏</span>';
                        break;
                    case '-2': // 入口文件不存在
                        $plugin['title'] = '入口文件不存在';
                        $plugin['version'] = '无版本号';
                        $plugin['description'] = '暂无简介';
                        $plugin['status'] = '<span class="easyui-linkbutton button-line-red button-sm l-btn luck-pd5">已损坏</span>';
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

            $result = ['total' => $total, 'plugins' => $data];

            $res['status'] = 200;
            $res['message'] = $this->vname.'目录加载完成';

            $result['res']=$res;
            // 非开发模式，缓存数据
            if (config('luck.develop_mode') == 0) {
                cache('plugin_all', $result);
            }
        }
        return $result;
    }

    /**
     * 检查插件插件信息是否完整
     * @param string $info 插件插件信息
     * @author 史光芒 <289650682@qq.com>
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
     * 获取插件配置
     * @param string $name 插件名称
     * @param string $item 指定返回的插件配置项
     * @author 史光芒 <289650682@qq.com>
     * @return array|mixed
     */
    public static function getConfig($name = '', $item = '')
    {
        $config = cache('plugin_config_'.$name);
        if (!$config) {
            $config = self::where('name', $name)->value('config');
            if (!$config) {
                return [];
            }

            $config = json_decode($config, true);
            // 非开发模式，缓存数据
            if (config('luck.develop_mode') == 0) {
                cache('plugin_config_'.$name, $config);
            }
        }

        if (!empty($item)) {
            $items = explode(',', $item);
            if (count($items) == 1) {
                return isset($config[$item]) ? $config[$item] : '';
            }

            $result = [];
            foreach ($items as $item) {
                $result[$item] = isset($config[$item]) ? $config[$item] : '';
            }
            return $result;
        }
        return $config;
    }

    /**
     * 设置插件配置
     * @param string $name 插件名.配置名
     * @param string $value 配置值
     * @author 史光芒 <289650682@qq.com>
     * @return bool
     */
    public static function setConfig($name = '', $value = '')
    {
        $item = '';
        if (strpos($name, '.')) {
            list($name, $item) = explode('.', $name);
        }

        // 获取缓存
        $config = cache('plugin_config_'.$name);

        if (!$config) {
            $config = self::where('name', $name)->value('config');
            if (!$config) {
                return false;
            }

            $config = json_decode($config, true);
        }

        if ($item === '') {
            // 批量更新
            if (!is_array($value) || empty($value)) {
                // 值的格式错误，必须为数组
                return false;
            }

            $config = array_merge($config, $value);
        } else {
            // 更新单个值
            $config[$item] = $value;
        }

        if (false === self::where('name', $name)->setField('config', json_encode($config))) {
            return false;
        }

        // 非开发模式，缓存数据
        if (config('luck.develop_mode') == 0) {
            cache('plugin_config_'.$name, $config);
        }

        return true;
    }

    public function edit($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];

        $validate = Loader::validate('Plugin');//或者$validate =validate('Admin');
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
}
