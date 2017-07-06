<?php

namespace app\admin\controller;


class Rule extends Common
{
    protected function _initialize()
    {
        parent::_initialize();
        $this->db=new \app\common\model\Rule();
    }

    public function index(){
        if(request()->isPost()) {
            $res = [
                'rows' => []
            ];
            $data=ctree($this->db->order('sort','asc')->select());
            $res['rows']=$data;

            return $res;
        }
        return view();
    }

    public function ruletree(){
        $data=ctree($this->db->select(),'pid',0,'open');
        array_unshift($data,['id'=>0,'text'=>'作为顶级规则']);
        return $data;
    }

    //添加规则
    public function add(){
        if(request()->isPost()){
            $res=$this->db->add(input('post.'));
            savelogs('添加规则');
            return json_encode($res);
        }
        return view();
    }
    //修改
    public function edit(){
        if(request()->isPost()){
            $res=$this->db->edit(input('post.'));
            savelogs('修改规则');
            return json_encode($res);
        }
        $id=request()->get('id');
        if($id){
            $data=$this->db->find($id);
            $this->assign('data',$data);
        }
        return view();
    }

    //删除
    public function remove(){
        if(request()->isPost()){
            $res=$this->db->del(input('post.'));
            savelogs('删除规则');
            return json_encode($res);
        }
    }
}
