<?php

namespace app\admin\model;

use think\Loader;
use think\Model;

class Log extends Model
{
    protected $field = true;    //过滤不存在的字段
    protected $readonly = ['username'];   //不能修改的字段
    protected $pk='id';
    protected $autoWriteTimestamp = false;  //关闭自动时间

    //添加用户组
    public function add($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        $validate = Loader::validate('Log');//或者$validate =validate('Admin');
        if(!$validate->scene('create')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }

        //添加数据
        $data=$this->save($input);
        if($data){
            $res['status']=200;
            $res['message']='日志 添加成功！';
        }else{
            $res['message']='日志 添加失败,请重试！';
        }
        return $res;
    }
}
