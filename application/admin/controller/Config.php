<?php

namespace app\admin\controller;

use app\admin\model\Config as ConfigModel;

class Config extends Common
{
    protected function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->db=new ConfigModel();
        $this->vname="配置";
        $this->vid="config";
    }

    public function index(){
        if(request()->isPost()) {
            $data=input('post.');
            $map = $this->getMap($data);
            $order = $this->getOrder($data);
            // 数据列表
            $res['total'] = $this->db->where($map)->order($order)->limit($data['rows'])->page($data['page'])->count();
            $d=$this->db->where($map)->order($order)->limit($data['rows'])->page($data['page'])->select();
            foreach($d as $v){
                $v['group_name']=config('config_group')[$v['group']];
                $v['type_name']=config('form_item_type')[$v['type']];
            }
            $res['rows'] = $d;
            return $res;
        }
        $this->assign('config_group',config('config_group'));
        return view();
    }

    public function add(){
        if(request()->isPost()){
            $data=input('post.');
            $res=$this->db->add($data);

            if($res['status']==200){
                savelogs('添加'.$this->vname,$data['name']);
            }else{
                savelogs('添加'.$this->vname,$data['name'].' 失败');
            }

            return json_encode($res);
        }
        $this->assign('config_group',config('config_group'));
        $this->assign('form_item_type',config('form_item_type'));
        return view();
    }
    //修改
    public function edit(){
        if(request()->isPost()){
            $data=input('post.');
            $res=$this->db->edit($data);

            if($res['status']==200){
                savelogs('编辑'.$this->vname,$data['name'].' id:'.$data['id']);
            }else{
                savelogs('编辑'.$this->vname,$data['name'].' id:'.$data['id'].' 失败');
            }
            return json_encode($res);
        }
        $id=request()->get('id');
        if($id){
            $data=$this->db->find($id);
            $this->assign('data',$data);
        }
        $this->assign('config_group',config('config_group'));
        $this->assign('form_item_type',config('form_item_type'));
        return view();
    }

    //删除
    public function remove(){
        if(request()->isPost()){
            $data=input('post.');
            $res=$this->db->del($data);
            if($res['status']==200){
                savelogs('删除'.$this->vname,' id:'.implode(',',$data['id']));
            }else{
                savelogs('删除'.$this->vname,' id:'.implode(',',$data['id']).' 失败');
            }
            return json_encode($res);
        }
    }
}
