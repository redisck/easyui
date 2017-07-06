<?php

namespace app\admin\controller;

use app\common\model\Admin;
use think\Controller;

class Login extends Controller
{
    public function login()
    {
        if(request()->isPost()){
            $res=(new Admin())->login(input('post.'));
            if($res['status']==300){
                $res['token']= request()->token();
            }
            savelogs('登陆后台');
            return json_encode($res);
        }

        if(session(config('auth.ADMIN_AUTH_KEY'))){
            $this->redirect('admin/Index/index');
        }
        return view();
    }

    //退出登陆
    public function logout(){
        session(null);
        $this->redirect('admin/Index/index');
    }
}
