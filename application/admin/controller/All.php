<?php

namespace app\admin\controller;

use think\Image;

class All extends Common
{
    public function choseFile($num,$ext,$name){
        $this->assign('ext',$ext);
        $this->assign('name',$name);
        if($num!=0){
            return view('chosefile');//多文件上传
        }else{
            return view('chosefile-only');//单文件上传
        }
    }

    //上传文件
    public function upload($name){
        $file = request()->file('file');
        $filename1=ROOT_PATH . 'public' . DS . 'uploads'.DS;
        $filename2=$filename1.$name;
        $time=date("Ymd",$_SERVER['REQUEST_TIME'] );
        $hash=date("YmdHis",$_SERVER['REQUEST_TIME'] ).rand(100000,999999);
        $info = $file->rule('uniqid')->validate(['size'=>1024*1024,'ext'=>'jpg,png,gif'])->move($filename2.DS.$time,$hash);
        $res=[
            'status'=>300,
            'message'=>'上传失败!'
        ];
        if($info){
            /*//后缀
            echo $info->getExtension();
            //带时间文件名
            echo $info->getSaveName();
            //文件名
            echo $info->getFilename();*/
            $names=$info->getFilename();
            $image=Image::open($filename2.DS.$time.DS.$names);
            $image->thumb(150,150,Image::THUMB_CENTER)->save($filename2.DS.$time.DS.'thumb_150_150_'.$names);
            $res['status']=200;
            $res['message']='上传成功';
            $res['url']=$time.DS.'thumb_150_150_'.$names;
        }else{
            // 上传失败获取错误信息
            $res['message']=$file->getError();
        }
        return json_encode($res);
    }

}
