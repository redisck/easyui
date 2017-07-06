<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Error extends Controller
{
    public function e404(){
        $err=Request::instance()->param('err')?Request::instance()->param('err'):'404';
        echo "<h2 style='text-align: center;'>{$err}</h2>";
        exit;
    }
}
