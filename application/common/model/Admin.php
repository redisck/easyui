<?php

namespace app\common\model;

use houdunwang\code\Code;
use houdunwang\crypt\Crypt;
use think\Db;
use think\Loader;
use think\Model;
use think\Request;

class Admin extends Model
{
    protected $field = true;    //过滤不存在的字段
    protected $readonly = ['username'];   //不能修改的字段
    protected $pk='id';
    protected $autoWriteTimestamp = false;  //关闭自动时间
    //如果定义table需要加前缀
    //protected $table='';

    public function authGroup()
    {
        return $this->belongsToMany('AuthGroup','authGroupAccess','group_id','uid');
    }

    /**
     * 登录处理
     */
    public function login($data){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        if(strtolower($data['code'])!=strtolower(Code::get())){
            $res['message']='验证码错误!!';
            return $res;
        }
        $validate = Loader::validate('Admin');//或者$validate =validate('Admin');
        if(!$validate->scene('login')->check($data)){
            $res['message']=$validate->getError();
            return $res;
        }
        //检测用户名和密码是否匹配
        $userInfo=$this->where('username',$data['username'])->where('password',Crypt::encrypt($data['password'],sha1(config('crypt.md5'))))->find();
        if(!$userInfo){
            //数据库没有数据
            $res['message']='用户名或者密码不正确!';
            return $res;
        }
        if($userInfo['status']!=1){
            $res['message']='用户被禁用!';
            return $res;
        }

        //更新登陆时间
        $up= $this->save([
            'num'=>['exp','num+1'],
            'last_login_time'=>$_SERVER['REQUEST_TIME'],
            'last_login_ip'=>sprintf("%u",ip2long(request()->ip())),
        ],[$this->pk=>$userInfo['id']]);
        if(!$up){
            $res['message']='登陆失败,请重试！';
            return $res;
        }

        session(config('auth.ADMIN_AUTH_KEY'),$userInfo['id']);
        session(config('auth.ADMIN_NAME'),$userInfo['username']);

        $res['status']=200;
        $res['message']='登录成功!';
        return $res;
    }


    public function pass($data){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        $validate = Loader::validate('Admin');//或者$validate =validate('Admin');
        if(!$validate->scene('pass')->check($data)){
            $res['message']=$validate->getError();
            return $res;
        }
        //2.原始密码是否匹配
        $userInfo=$this->where('id',session(config('auth.ADMIN_AUTH_KEY')))->where('password',Crypt::encrypt($data['password'],sha1(config('crypt.md5'))))->find();
        if(!$userInfo){
            $res['message']='原始密码错误！';
            return $res;
        }

        //3.修改密码
        $data=$this->save([
            'password'=>Crypt::encrypt($data['new_password'],sha1(config('crypt.md5'))),
            'update_time'=>$_SERVER['REQUEST_TIME'],
        ],[$this->pk=>session(config('auth.ADMIN_AUTH_KEY'))]);
        if($data){
            $res['status']=200;
            $res['message']='密码修改成功！';
        }else{
            $res['message']='密码修改失败,请重试！';
        }
        return $res;
    }

    public function add($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        $validate = Loader::validate('Admin');//或者$validate =validate('Admin');
        if(!$validate->scene('create')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }
        //验证用户名重复
        $userInfo=$this->where('username',$input['username'])->find();
        if($userInfo){
            $res['message']='用户名已经存在！';
            return $res;
        }

        //添加数据
        $input['password']=Crypt::encrypt($input['password'],sha1(config('crypt.md5')));
        $input['update_time']= $input['create_time']=$_SERVER['REQUEST_TIME'];


        if(!isset($input['status']) || !$input['status']){
            $input['status']=0;
        }

        $data=$this->save($input);
        if($data){
            $res['status']=200;
            $res['message']='管理员添加成功！';
        }else{
            $res['message']='管理员添加失败,请重试！';
        }
        return $res;
    }

    public function edit($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        $input['edit_password']=$input['password'];
        $validate = Loader::validate('Admin');//或者$validate =validate('Admin');
        if(!$validate->scene('edit')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }
        //验证用户名重复
        $userInfo=$this->where('username',$input['username'])->where('id','NEQ',$input['id'])->find();
        if($userInfo){
            $res['message']='用户名已经存在！';
            return $res;
        }

        //添加数据
        if(!$input['password']){
            unset($input['password']);
        }else{
            $input['password']=Crypt::encrypt($input['password'],sha1(config('crypt.md5')));
        }
        $input['update_time']=$_SERVER['REQUEST_TIME'];

        if(!isset($input['status']) || !$input['status']){
            $input['status']=0;
        }

        $data=$this->save($input,[$this->pk=>$input['id']]);
        if($data){
            $res['status']=200;
            $res['message']='管理员修改成功！';
        }else{
            $res['message']='管理员修改失败,请重试！';
        }
        return $res;
    }

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
                $res['message'] = '删除管理员成功！';
            } else {
                $res['message'] = '删除管理员失败,请重试！';
            }
        }else{
            $res['message']='删除管理员失败,存在不能删除的数据,请重试！';
        }
        return $res;
    }

    public function access($input){
        if(isset($input['group_id'])){
            $group_id=array_unique($input['group_id']);
        }else{
            $group_id=[];
        }
        $uid=$input['uid'];
        $user=$this->where('id',$uid)->field('password',true)->find();
        Db::startTrans();
        try{
            AuthGroupAccess::where('uid',$uid)->delete();
            /*foreach($group_id as $gid){
                $user->authGroup()->attach($uid,['group_id'=>$gid]);
            }*/
            if(count($group_id)>0){
                //$user->authGroup()->save($group_id);
                $user->authGroup()->saveAll($group_id);
            }
            // 提交事务
            Db::commit();
            $res['status']=200;
            $res['message']='授权成功！';
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $res['message']='授权失败,请重试！';
        }

        return $res;
    }
}
