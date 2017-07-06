<?php

namespace app\admin\controller;

use app\admin\model\Module;
use app\common\model\Admin;
use app\common\model\Rule;
use think\Request;

class Category extends Common
{
    public function menu(){
        $res = cache('menu_all');
        if(!$res){
            $uid=session(config('auth.ADMIN_AUTH_KEY'));
            $exmenu=Module::where('status',0)->where('name','neq','admin')->field('name')->select();//
            $list=[];
            foreach($exmenu as $v){
                array_push($list,$v->name);
            }

            $cate=Rule::where(['status'=>1,'showed'=>1])->where('module','not in',$list)->order('sort','asc')->select();

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
            $pid=input('post.id');
            $res=[
                'status'=>200,
                'data'=>menu($cates,$pid)
            ];
            if (config('luck.develop_mode') == 0) {
                cache('menu_all', $res);
            }
        }
        return json_encode($res);
    }
}
