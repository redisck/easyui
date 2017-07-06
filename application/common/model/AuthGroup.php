<?php

namespace app\common\model;

use think\Loader;
use think\Model;

class AuthGroup extends Model
{
    protected $field = true;
    protected $pk='id';
    protected $autoWriteTimestamp = false;  //关闭自动时间

    public function admin()
    {
        return $this->belongsToMany('Admin','authGroupAccess','uid','group_id');
    }

    //添加用户组
    public function add($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        $validate = Loader::validate('AuthGroup');//或者$validate =validate('Admin');
        if(!$validate->scene('create')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }

        //验证用户名重复
        $dd=$this->where('title',$input['title'])->find();
        if($dd){
            $res['message']='角色名已经存在！';
            return $res;
        }
        if(!isset($input['status']) || !$input['status']){
            $input['status']=0;
        }

        //添加数据
        $data=$this->save($input);
        if($data){
            $res['status']=200;
            $res['message']='角色 添加成功！';
        }else{
            $res['message']='角色 添加失败,请重试！';
        }
        return $res;
    }

    //编辑
    public function edit($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];

        $validate = Loader::validate('AuthGroup');//或者$validate =validate('Admin');
        if(!$validate->scene('edit')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }
        //验证规则名重复
        $userInfo=$this->where('title',$input['title'])->where('id','NEQ',$input['id'])->find();
        if($userInfo){
            $res['message']='角色名 已经存在！';
            return $res;
        }

        //添加数据
        if(!isset($input['status']) || !$input['status']){
            $input['status']=0;
        }

        $data=$this->save($input,[$this->pk=>$input['id']]);
        if($data){
            $res['status']=200;
            $res['message']='角色 修改/配置 成功！';
        }else{
            $res['message']='角色 修改/配置 失败,请重试！';
        }
        return $res;
    }

    //删除
    public function del($input){
        $ids=array_unique($input['id']);
        $fids=array_flip($ids);
        unset($fids[session(config('auth.ADMIN_AUTH_KEY'))]);
        $ids=array_flip($fids);
        $id=count($ids)==1?$ids[0]:$ids;

        $res=[
            'status'=>300,
            'message'=>'删除失败',
        ];
        if(count($id)>0) {
            if ($this->destroy($id)) {
                $res['status'] = 200;
                $res['message'] = '删除 角色 成功！';
            } else {
                $res['message'] = '删除 角色 失败,请重试！';
            }
        }else{
            $res['message']='删除 角色 失败,存在不能删除的数据,请重试！';
        }
        return $res;
    }
}
