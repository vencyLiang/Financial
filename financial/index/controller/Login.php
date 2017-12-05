<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/12/5
 * Time: 12:20
 */

namespace app\index\controller;


use think\Controller;
use app\common\model\User as UserModel;
use app\common\validate\User as UserValidate;
use think\Loader;

class Login extends Controller{
  function login_reg(){
      return $this->fetch();
  }

  function login(){
      $username = $this->request->param('username');
      $password = $this->request->param('password');
      $user = new UserModel();
      $res = $user->where('username','=',$username)->where('password','=',md5($password))->find();
      if($res){
          session('userId',$res->id);
          return '1';
      }else{
          return '0';
      }
  }

  function register(){
        $regInfo = $this->request->param();
        $user = new UserModel();
        $validate = new UserValidate();
        if(!$validate->check($regInfo)){
          return "-1";
        }else {
            $regInfo['password'] = md5($regInfo['password']);
            $res = $user->allowField(true)->save($regInfo);
            if ($res) {
                return '1';
            } else {
                return '0';
            }
        }
  }
}