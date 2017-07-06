<?php

namespace app\common\model;

use think\Db;
use think\Loader;
use think\Model;

class Rule extends Model
{
    protected $field = true;    //过滤不存在的字段
    //protected $readonly = ['username'];   //不能修改的字段
    protected $pk='id';
    //protected $autoWriteTimestamp = false;  //关闭自动时间
    protected $table;
    public function __construct()
    {
        $this->table=config('database.prefix').'auth_rule';
    }

    //添加规则
    public function add($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        $validate = Loader::validate('Rules');//或者$validate =validate('Admin');
        if(!$validate->scene('create')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }

        //验证用户名重复
        $rule=$this->where('title',$input['title'])->where('name',$input['name'])->find();
        if($rule){
            $res['message']='规则名已经存在！';
            return $res;
        }
        if(!isset($input['status']) || !$input['status']){
            $input['status']=0;
        }
        if(!isset($input['showed']) || !$input['showed']){
            $input['showed']=0;
        }

        $this->create_time=request()->time();
        $this->update_time=request()->time();
        //添加数据
        $data=$this->save($input);
        if($data){
            $res['status']=200;
            $res['id']=$this->id;
            $res['message']='规则添加成功！';
        }else{
            $res['message']='规则添加失败,请重试！';
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

        $validate = Loader::validate('Rules');//或者$validate =validate('Admin');
        if(!$validate->scene('edit')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }
        //验证规则名重复
        $userInfo=$this->where('title',$input['title'])->where('id','NEQ',$input['id'])->find();
        if($userInfo){
            $res['message']='规则名已经存在！';
            return $res;
        }
        $cate=$this->select();
        $pids=getChildsId($cate,$input['id']);
        if(in_array($input['pid'],$pids) || $input['pid']==$input['id']){
            $res['message']='上级规则 不能为本身或者下级规则！';
            return $res;
        }


        //添加数据
        if(!isset($input['status']) || !$input['status']){
            $input['status']=0;
        }
        if(!isset($input['showed']) || !$input['showed']){
            $input['showed']=0;
        }
        if(!isset($input['system_menu']) || !$input['system_menu']){
            $input['system_menu']=0;
        }
        $this->update_time=request()->time();
        $data=$this->save($input,[$this->pk=>$input['id']]);
        if($data){
            $res['status']=200;
            $res['message']='规则 修改成功！';
        }else{
            $res['message']='规则 修改失败,请重试！';
        }
        return $res;
    }

    //删除
    public function del($input){
        $ids=array_unique($input['id']);
        $id=count($ids)==1?$ids[0]:$ids;
        $res=[
            'status'=>300,
            'message'=>'删除失败',
        ];
        if(empty($id) && count($id)>1){
            $res['message']='删除的数据为空,请重试！';
        }else{
            Db::startTrans();
            try{
                $p=$this->find($id);
                $this->where('pid',$id)->setField('pid',$p['pid']);
                $this->where('id',$id)->delete();
                // 提交事务
                Db::commit();
                $res['status']=200;
                $res['message']='删除规则成功！';
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $res['message']='删除规则失败,请重试！';
            }
        }
        return $res;
    }
}
