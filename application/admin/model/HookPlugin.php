<?php

namespace app\admin\model;

use think\Loader;
use think\Model;
use app\admin\model\Hook as HookModel;

class HookPlugin extends Model
{
    protected $field = true;    //过滤不存在的字段
    protected $pk='id';
    protected $vname='插件钩子';

    public function add($input){
        //1.执行验证
        $res=[
            'status'=>300,
            'message'=>'验证失败',
        ];
        $validate = Loader::validate('Hook');//或者$validate =validate('Admin');
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
        if(!isset($input['system']) || !$input['system']){
            $input['system']=0;
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

        $validate = Loader::validate('Hook');//或者$validate =validate('Admin');
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
        if(!isset($input['system']) || !$input['system']){
            $input['system']=0;
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
        $id=count($ids)==1?$ids[0]:$ids;

        $res=[
            'status'=>300,
            'message'=>'删除失败',
        ];
        if(count($id)>0) {
            if ($this->where('id',$id)->delete()) {
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

    /**
     * 下面是安装需要的方法
     */

    /**
     * 添加钩子-插件对照
     * @param array $hooks 钩子
     * @param string $plugin_name 插件名称
     * @author 史光芒 <289650682@qq.com>
     * @return bool
     */
    public static function addHooks($hooks = [], $plugin_name = '')
    {
        if (!empty($hooks) && is_array($hooks)) {
            // 添加钩子
            if (!HookModel::addHooks($hooks, $plugin_name)) {
                return false;
            }

            foreach ($hooks as $name => $description) {
                if (is_numeric($name)) {
                    $name = $description;
                }
                $data[] = [
                    'hook'        => $name,
                    'plugin'      => $plugin_name,
                    'create_time' => request()->time(),
                    'update_time' => request()->time(),
                ];
            }

            return self::insertAll($data);
        }
        return false;
    }

    /**
     * 删除钩子
     * @param string $plugin_name 钩子名称
     * @author 史光芒 <289650682@qq.com>
     * @return bool
     */
    public static function deleteHooks($plugin_name = '')
    {
        if (!empty($plugin_name)) {
            // 删除钩子
            if (!HookModel::deleteHooks($plugin_name)) {
                return false;
            }
            if (false === self::where('plugin', $plugin_name)->delete()) {
                return false;
            }
        }
        return true;
    }
}
