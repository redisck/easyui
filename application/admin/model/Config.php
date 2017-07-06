<?php

namespace app\admin\model;

use think\Loader;
use think\Model;

class Config extends Model
{
    protected $field = true;    //过滤不存在的字段
    protected $pk='id';
    protected $vname='配置';

    public function add($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        $validate = Loader::validate('Config');//或者$validate =validate('Admin');
        if(!$validate->scene('create')->check($input)){
            $res['message']=$validate->getError();
            return $res;
        }
        //验证用户名重复
        $info=$this->where('name',$input['name'])->find();
        if($info){
            $res['message']=$this->vname.'已经存在！';
            return $res;
        }

        if(!isset($input['status']) || !$input['status']){
            $input['status']=0;
        }
        $data=$this->save($input);
        if($data){
            $res['status']=200;
            $res['message']=$this->vname.'添加成功！';
        }else{
            $res['message']=$this->vname.'添加失败,请重试！';
        }
        return $res;
    }

    public function edit($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];

        $validate = Loader::validate('Config');//或者$validate =validate('Admin');
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

        if(!isset($input['status']) || !$input['status']){
            $input['status']=0;
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

    public function del($input){
        $ids=array_unique($input['id']);
        $id=$ids;//count($ids)==1?$ids[0]:$ids;

        $res=[
            'status'=>300,
            'message'=>'删除失败',
        ];
        if(count($id)>0) {
            if ($this->where('id','in',$id)->delete()) {
                $res['status'] = 200;
                $res['message'] =$this->vname.'删除成功！';
            } else {
                $res['message'] = $this->vname.'删除失败,请重试！';
            }
        }else{
            $res['message']=$this->vname.'删除失败,存在不能删除的数据,请重试！';
        }
        return $res;
    }

    public function getConfig($name=''){
        $configs = self::column('value,type', 'name');

        $result = [];
        foreach ($configs as $config) {
            switch ($config['type']) {
                case 'array':
                    $result[$config['name']] = parse_attr($config['value']);
                    break;
                case 'checkbox':
                    if ($config['value'] != '') {
                        $result[$config['name']] = explode(',', $config['value']);
                    } else {
                        $result[$config['name']] = [];
                    }
                    break;
                default:
                    $result[$config['name']] = $config['value'];
                    break;
            }
        }

        return $name != '' ? $result[$name] : $result;
    }
}
