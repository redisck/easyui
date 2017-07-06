<?php

namespace app\api\controller;


use houdunwang\code\Code;
use think\Controller;

class Common extends Controller
{
    public function code($num,$w,$h,$f){
        Code::num($num)->fontSize($f)->width($w)->height($h)->make();
    }

}
