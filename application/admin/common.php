<?php
/**
 * Created by PhpStorm.
 * User: 式神 (luck48.com)
 * Email: 289650682@qq.com
 * Name: ${NAME}Administrator
 * Date: 2017-05-24
 * Time: 13:57
 */

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