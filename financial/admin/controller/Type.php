<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/11/1
 * Time: 14:17
 */

namespace app\admin\controller;
use app\common\model\Type as TypeModel;
use log\Log;

class Type extends Base{

    function  getAllTypes(){
        $type = new TypeModel();
        $allTypes = $type->getAllTypes();
        return $allTypes;
    }

    function  getAdd(){
        return $this->fetch('',['allTypes'=>$this->getAllTypes()]);
    }
    function addform(){
        return $this->fetch('',['allTypes'=>$this->getAllTypes()]);
    }

    function addSave(){
        $type = new TypeModel();
        $arr = $this->request->param();
        $res=$type->allowField(true)->save($arr);
        $config = ['act'=>'insert','object'=>'type','name'=>$arr['tname'],'result'=>$res];
        Log::writeLog($config);
        if($res){
            $this->success("分类添加成功！",'Type/addform');
        } else{
            $this->error("分类添加失败！",'Type/addform');
        }
    }
    function operate(){
        return $this->fetch('',['allTypes'=>$this->getAllTypes()]);
    }

    function delete(){
        $id = $this->request->param('id');
        $type = new TypeModel();
        $sonNum = $type->where('parentId',"=",$id)->count();
        if($sonNum>0){
            $this->error("请先删除该分类下的子类",'Type/operate');
        }else{
            $res = $type->where('id','=',$id)->delete();
            $config = ['act'=>'delete','object'=>'type','oid'=>$id,'name'=>TypeModel::get($id)->tname,'result'=>$res];
            Log::writeLog($config);
            if($res) {
                $this->success("分类删除成功！",'Type/operate');
            }else{
                $this->error($type->getError(),'Type/operate');
            }
        }
    }

    function getUpdate(){
        $id = $this->request->param('id');
        $type = new TypeModel();
        $tname = $type->get($id)->tname;
        $parentId = $type->get($id)->parentId;
        $allUpTypes = $type-> getAllUpLevelTypesByIdInOrder($id);
        return $this->fetch('',['tname'=>$tname,'allUpTypes'=>$allUpTypes,'id'=>$id,'parentId'=>$parentId]);
    }


    function updateSave(){
        $updateTypeInfo = $this->request->param();
        $id = $updateTypeInfo['id'];
        $res = TypeModel::get($id)->isUpdate()->allowField(true)->save($updateTypeInfo);
        $config = ['act'=>'update','object'=>'type','oid'=>$id,'name'=>$updateTypeInfo['tname'],'result'=>$res];
        Log::writeLog($config);
        if($res===0){
            $this->success("分类信息没有变化！","Type/operate");
        }else if($res>=0){
            $this->success("分类信息更新成功！","Type/operate");
        }else{
            $this->error("分类更新失败！","Type/operate");
        }
    }
}