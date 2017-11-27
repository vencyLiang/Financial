<?php
/**
 * Created by PhpStorm.
 * User: vency
 * Date: 2017/11/13 0013
 * Time: 21:23
 */

namespace app\index\controller;
use app\admin\controller\News as AdminNews;
use think\Session;


class Base extends  AdminNews{
//因为AdminNews里有继承自其Base类的_initialize()方法；
//该方法用以判断管理员是否登录，所以要把他覆盖掉；
//同时前端也有要判断访问用户是否登录的需求，
//所以定义一个成员属性用来访问
    protected $loginInfo;
    public function _initialize(){
        if(Session::has('userId')){
            $this->loginInfo = session('userId');
        }else{
            $this->loginInfo = null;
        }
    }

    public function getLoginInfo(){
        return $this->loginInfo;
    }
}