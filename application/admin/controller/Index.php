<?php

namespace app\admin\controller;

use app\admin\model\Module;
use app\common\model\Admin;
use app\common\model\Rule;
use think\Cache;
use think\Controller;
use think\Request;
use luck\auth\Auth;

class Index extends Common
{
    public function index(){
        $uid=session(config('auth.ADMIN_AUTH_KEY'));
        //当前登录用户
        $admin=new Admin();
        $userInfo=$admin->where('id',$uid)->find();
        $this->assign('admin',$userInfo);

        //模块是否禁用
        $exmenu=Module::where('status',0)->where('name','neq','admin')->field('name')->select();
        $list=[];
        foreach($exmenu as $v){
            array_push($list,$v->name);
        }

        //顶级菜单
        $cate=Rule::where(['status'=>1,'showed'=>1,'pid'=>0])->where('module','not in',$list)->order('sort','asc')->select();
        $cates=[];
        if(in_array($uid,config('auth.ADMINISTRATOR'))){
            $cates=$cate;
        }else{
            $user=Admin::field('password',true)->find($uid);
            $group=$user->authGroup;
            $rid='';
            foreach($group as $g){
                $rid.=$g['rules'].',';
            }
            $rids=array_filter(array_unique(explode(',',$rid)));

            foreach($cate as $v){
                if(in_array($v['id'],$rids)){
                    $cates[]=$v;
                }
            }
        }
        $this->assign('topmenu',$cates);
        return view();
    }

    /**
     * 清空系统缓存
     * @author 式神 <289650682@qq.com>
     */
    public function clearCache()
    {
        if (!empty(config('wipe_cache_type'))) {
            foreach (config('wipe_cache_type') as $item) {
                if ($item == 'LOG_PATH') {
                    $dirs = (array) glob(constant($item) . '*');
                    foreach ($dirs as $dir) {
                        array_map('unlink', glob($dir . '/*.log'));
                    }
                    array_map('rmdir', $dirs);
                } else {
                    array_map('unlink', glob(constant($item) . '/*.*'));
                }
            }
            Cache::clear();
            $this->assign('desc','缓存清除成功!');
            return view();
        } else {
            $this->assign('desc','请在系统设置中选择需要清除的缓存类型!');
            return view();
        }
    }

    //首页控制台信息
    public function info(){
        print_r(date('Y-m-d H:i:s',THINK_START_TIME));
        echo '<br>';
        echo THINK_START_MEM;
        echo '<br>';
        echo ENV_PREFIX ;
        echo '<br>';
        echo IS_CLI;
        echo '<br>';
        echo IS_WIN;
        echo '<br>';
        echo THINK_VERSION;
        echo '<br>';
        print_r($this->request->dispatch());
        print_r(session(''));
        return view();
    }
}
