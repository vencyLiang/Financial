<?php
/**
 * Created by PhpStorm.
 * User: vency
 * Date: 2017/11/22 0022
 * Time: 1:47
 */

namespace app\admin\controller;
use app\common\model\User as UserModel;
use think\db\Query;
use think\Request;
use think\Db;
use log\Log;


class User extends Base{

    protected $searchWhere;

    /**利用给定的关键词索引数组，创建查询条件；
     * @return Query;
     */
    function buildSearchWhere($searchInfo){
        $where = new UserModel();
        if(isset($searchInfo['searchKeywords']) && $searchInfo['searchKeywords'] !== ""){
            $keywords = $searchInfo['searchKeywords'];
            $where = $where->where('username','like',"%$keywords%")->whereOr('phone','like',"%$keywords%")->whereOr('email','like',"%$keywords%");
        }
        if(isset($searchInfo['status']) && $searchInfo['status'] !== ""){
            $where = $where->where('status','=','1');
        }
        if(isset($searchInfo['from']) && $searchInfo['from'] !== ""){
            $from =strtotime($searchInfo['from']);
            $where = $where->where('regtime','>',$from);
        }
        if(isset($searchInfo['to']) && $searchInfo['to'] !== ""){
            $endTime = strtotime($searchInfo['to']);
            $where = $where->where('regtime','<',$endTime+3600*24);
        }
        return $where;
    }

    function operate(){
        $searchInfo = Request::instance()->param();
        $searchWhere = $this->buildSearchWhere($searchInfo);
        $allUserList = $searchWhere->paginate(10,false,['query'=>$searchInfo,"type"=>"\page\Pagestyle"]);
        $total = $allUserList->total();
        $page = $allUserList->render();
        return $this->fetch('',['allUserList'=>$allUserList,'page'=>$page,'total'=>$total]);
    }

    function getAdd(){
        return $this->fetch();
    }

    function delete(){
        $param = Request::instance()->param();
        $id = $param['checkbox'];
        $user = new UserModel();
        //根据主键删除 $news->delete($id) $id为string 或者 array
        $res = $user->where('id', 'IN', $id)->delete();
        $config = ['act'=>'delete','object'=>'user','result'=>$res];
        Log::writeLog($config);
        if ($res) {
            $this->success(json_encode('用户删除成功'));
        } else {
            $this->error(json_encode('用户删除失败'));
        }
    }

    function singleDelete(){
        $id = Request::instance()->param('id');
        //根据主键删除 $news->delete($id) $id为string 或者 array
        $res = Db::table('user')->delete($id);
        $config = ['act'=>'delete','object'=>'user','name'=>UserModel::get($id)->username,'oid'=>$id,'result'=>$res];
        Log::writeLog($config);
        if ($res) {
            $this->success(json_encode('用户删除成功'));
        } else {
            $this->error(json_encode('用户删除失败'));
        }
    }

    function addSave(){
        $userInfo = Request::instance()->param();
        $userInfo['regtime'] = time();
        $user = new UserModel();
        $res = $user->validate(true)->allowField(true)->save($userInfo);
        $config = ['act'=>'insert','object'=>'user','name'=>$userInfo['username'],'result'=>$res];
        Log::writeLog($config);
        if($res){
            $this->success('用户添加成功',"user/operate");
        }else{
            $this->error($user->getError(),"user/operate");
        }
    }

    function getChangePWD(){
        $id = Request::instance()->param('id');
        $username = UserModel::get($id)->username;
        return $this->fetch("",['username'=>$username,'id'=>$id]);

    }

    function  saveChangePWD(){
        $id = Request::instance()->param('id');
        $password = Request::instance()->param('password');
        if(!$password){
            $this->success('密码没有改动','user/operate');
            exit;
        }
        $res = UserModel::get($id)->isUpdate()->save(['password'=>md5($password)]);
        if($res){
            $this->success('密码修改成功','user/operate');
        }else{
            $this->error('密码修改失败','user/operate');
        }
    }


    function getUpdate(){
        $id = Request::instance()->param('id');
        $url = url('user/operate');
        if(!$id){
            $this->error('请指定要修改的用户',$url);
            exit;
        }
        $user = UserModel::get($id);
        if(!$user){
            $this->error('请通过合法途径修改用户！',$url);
            exit;
        }
        return $this->fetch('',['user'=>$user]);
    }

    function updatesave(){
        $userInfo = Request::instance()->param();
        $url = url('user/operate');
        if(!$userInfo){
            $this->error('请通过合法途径修改用户！',$url);
            exit;
        }
        //注释掉的代码实现的功能是：空出不填的部分，不更新。
        //foreach ($userInfo as $key=>$value){
        //    if($value===""){
        //        unset($userInfo[$key]);
        //    }
        //}
        if(!$userInfo['password']){
            unset($userInfo['password']);
        }
        $id = $userInfo['id'];
        $user = new UserModel();
        $res = $user->allowField(true)->isUpdate()->save($userInfo);
        $config = ['act'=>'update','object'=>'user','name'=>UserModel::get($id)->username,'oid'=>$id,'result'=>$res];
        Log::writeLog($config);
        if($res===0){
            $this->success("用户数据没有改动！",$url);
        }elseif($res>0){
            $this->success("用户数据更新成功！",$url);
        } else{
            $this->error($user->getError(),$url);
        }
    }


    function stop(){
        $id = Request::instance()->param('id');
        $user = new UserModel();
        $re= $user->where('id',$id)->setField('status','0');
        return $re;
    }

    function start(){
        $id = Request::instance()->param('id');
        $user = new UserModel();
        $re= $user->where('id',$id)->setField('status','1');
        return $re;
    }
}