<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Loader;

// 应用公共文件
if (!function_exists('get_plugin_class')) {
    /**
     * 获取插件类名
     * @param  string $name 插件名
     * @author 史光芒 <289650682@qq.com>
     * @return string
     */
    function get_plugin_class($name)
    {
        return "plugins\\{$name}\\{$name}";
    }
}

if (!function_exists('plugin_config')) {
    /**
     * 获取或设置某个插件配置参数
     * @param string $name 插件名.配置名
     * @param string $value 设置值
     * @author steven(式神) <289650682@qq.com>
     * @return mixed
     */
    function plugin_config($name = '', $value = '')
    {
        if ($value === '') {
            // 获取插件配置
            if (strpos($name, '.')) {
                list($name, $item) = explode('.', $name);
                return model('admin/plugin')->getConfig($name, $item);
            } else {
                return model('admin/plugin')->getConfig($name);
            }
        } else {
            return model('admin/plugin')->setConfig($name, $value);
        }
    }
}

if (!function_exists('model')) {
    /**
     * 实例化Model
     * @param string    $name Model名称
     * @param string    $layer 业务层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @return \think\Model
     */
    function model($name = '', $layer = 'model', $appendSuffix = false)
    {
        return Loader::model($name, $layer, $appendSuffix);
    }
}

if (!function_exists('parse_config')) {
    /**
     * 解析配置，返回配置值
     * @param array $configs 配置
     * @author steven(式神) <289650682@qq.com>
     * @return array
     */
    function parse_config($configs = []) {
        $type = [
            'hidden'      => 5,
            'date'        => 4,
            'ckeditor'    => 4,
            'daterange'   => 4,
            'datetime'    => 4,
            'editormd'    => 4,
            'file'        => 4,
            'colorpicker' => 5,
            'files'       => 4,
            'icon'        => 4,
            'image'       => 4,
            'images'      => 5,
            'jcrop'       => 4,
            'range'       => 4,
            'number'      => 4,
            'password'    => 4,
            'sort'        => 4,
            'static'      => 4,
            'summernote'  => 4,
            'switch'      => 5,
            'tags'        => 4,
            'text'        => 5,
            'array'       => 5,
            'textarea'    => 5,
            'time'        => 5,
            'ueditor'     => 5,
            'wangeditor'  => 4,
            'radio'       => 5,
            'bmap'        => 5,
            'masked'      => 5,
            'select'      => 5,
            'linkage'     => 5,
            'checkbox'    => 5,
            'linkages'    => 6
        ];
        $result = [];
        foreach ($configs as $item) {
            // 判断是否为分组
            if ($item[0] == 'group') {
                foreach ($item[1] as $option) {
                    foreach ($option as $group => $val) {
                        $result[$val[1]] = isset($val[$type[$val[0]]]) ? $val[$type[$val[0]]] : '';
                    }
                }
            } else {
                $result[$item[1]] = isset($item[$type[$item[0]]]) ? $item[$type[$item[0]]] : '';
            }
        }
        return $result;
    }
}

//权限验证
if(!function_exists('authcheck')){
    function authcheck($name, $uid,$auth='', $type=1, $mode='url', $relation='or'){
        if(in_array($uid,config('auth.ADMINISTRATOR'))){
            return true;
        }else if(in_array($name, config('auth.NO_AUTH_RULES'))){
            return true;
        }else{
            if(!$auth){
                $auth=new \luck\auth\Auth();
            }
            return $auth->check($name, $uid, $type, $mode, $relation)?true:false;
        }
    }
}

if(!function_exists('savelogs')){
    //日志方法
    function savelogs($name,$description=''){
        $data=[];
        $logs=new \app\admin\model\Log();
        $module=request()->dispatch()['module'];
        $data['module']=strtolower($module[0].'/'.$module[1].'/'.$module[2]);
        $data['modulename']=$name;
        $data['username']=session(config('auth.ADMIN_NAME'));
        $data['description']=$description?$data['username'].' '.$name.': '.$description:'';
        $data['time']=request()->time();
        $data['ip']=sprintf("%u",ip2long(request()->ip()));
        return $logs->add($data);
    }
}


/**
 * 多级分类树形
 * @param
 * @acthor:式神 289650682@qq.com
 * @return 2016-10-12
 */
if(!function_exists('ctree')) {
    function ctree($cate, $pidname = 'pid', $pid = 0, $open = 'open', $rule = [])
    {
        $arr = array();
        foreach ($cate as $k => $v) {
            if ($v[$pidname] == $pid) {
                $v['text'] = $v['title'];//下拉tree菜单名字
                if (count($rule) > 0) {
                    if (in_array($v['id'], $rule) && count(getChildsId($cate, $v['id'])) == 0) {
                        $v['checked'] = true;
                    }
                }
                if ($open != 'open' && count(getChildsId($cate, $v['id'])) == 0) {
                    $v['state'] = 'open';
                } else {
                    $v['state'] = $open;//关闭菜单
                }
                $v['children'] = ctree($cate, $pidname, $v['id'], $open, $rule);
                $arr[] = $v;
            }
        }
        return $arr;
    }
}

if(!function_exists('menu')) {
    function menu($cate, $pid = 0, $pidname = 'pid')
    {
        $arr = array();
        foreach ($cate as $k => $v) {
            if ($v[$pidname] == $pid) {
                $v['url'] = url($v['name']);//下拉tree菜单名字
                $v['child'] = menu($cate, $v['id'], $pidname);
                $arr[] = $v;
            }
        }
        return $arr;
    }
}

/**
 * 查询子栏目id
 * @acthor:式神 2896506822qq.com
 * @time: 2016-10-12
 */
//子栏目ID
if(!function_exists('getChildsId')) {
    function getChildsId($cate, $pid = 0, $pidname = 'pid')
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$pidname] == $pid) {
                $arr[] = $v['id'];
                $arr = array_merge($arr, getChildsId($cate, $v['id'], $pidname));
            }
        }
        return $arr;
    }
}

//父栏目ID
if(!function_exists('getCateId')) {
    function getCateId($cate, $id)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['id'] == $id) {
                $arr[] = $v['pid'];
                $arr = array_merge(getCateId($cate, $v['pid']), $arr);
            }
        }
        return $arr;
    }
}
//父栏目
if(!function_exists('getCate')) {
    function getCate($cate, $id)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['id'] == $id) {
                $arr[] = $v;
                $arr = array_merge(getCate($cate, $v['pid']), $arr);
            }
        }
        return $arr;
    }
}

if (!function_exists('parse_attr')) {
    /**
     * 解析配置
     * @param string $value 配置值
     * @return array|string
     */
    function parse_attr($value = '') {
        $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
        if (strpos($value, ':')) {
            $value  = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k]   = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}