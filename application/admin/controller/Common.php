<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Common extends Controller
{

    protected $db;
    protected $vname='';
    protected $vid='';

    //前置方法
    protected $beforeActionList = [
        'first'=>  ['only'=>'index']
    ];
    protected function first(){
        $this->assign('vname',$this->vname);
        $this->assign('vid',$this->vid);
    }

    public function __construct(Request $request=null){
        parent::__construct($request);

        //登陆验证
        if(!session(config('auth.ADMIN_AUTH_KEY'))){
            $this->redirect('admin/Login/login');
        }

        $module=$this->request->dispatch()['module'];
        $module_name=strtolower($module[0].'/'.$module[1].'/'.$module[2]);

        $auth = new \luck\auth\Auth();
        if(!authcheck($module_name,session(config('auth.ADMIN_AUTH_KEY')),$auth) ){
            //echo '没有权限';
            if(!Request::instance()->isPost()){
                echo '没有权限';
                exit;
            }else{
                $res['message']='你没有权限';
                $res['status']=300;
                exit(json_encode($res));
            }
        }

    }

    protected function getMap($data=[]){
        $where=[];
        foreach($data as $k =>$v){
            if(preg_match('/search_(.*)/',$k,$match)){
                $where[$match[1]]=['like','%'.$v.'%'];
            }
        }
        return $where;
    }

    protected function getOrder($data=[]){
        $sort=$data['sort'];
        $order=$data['order'];
        $orders=$sort." ".$order;
        return $orders;
    }
}
