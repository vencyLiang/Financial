<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/11/1
 * Time: 11:31
 */

namespace app\admin\controller;
use think\Controller;
use think\captcha\Captcha;
use think\Request;
use think\Session;
use think\Cookie;
use app\common\model\Admin;
use log\Log;

class Login extends  Controller {
    private static $captcha;
    function _initialize(){
        $config = [
            'expire'   => 300,
           //'useImgBg' => true,
            'useZh'    => true,
            'fontSize' => 13,
            'useNoise' => false,
            'imageH'   => 31,
             'imageW'   => 100,
            'length'   => 4,
            //设置验证以后不重置是为了配合ajax；
            'reset'=> false,
        ];
        self::$captcha = new Captcha($config);
    }

    public function index(){
        return $this->fetch();
    }

    public function regAjax(){
        $username = Request::instance()->param('username');
        $password = Request::instance()->param('password');
        if($username === "" ){
            $message = ['msg'=>"<b style='font-weight:bold;color:orangered'>!请先填写用户名</b>"];
            echo json_encode($message);
            exit;
        }
        $admin = new Admin();
        $res = $admin->where('username', 'EQ', $username)->find();
        if ($res) {
            $str ="<b style='font-weight:bold;color:green'>√该用户存在</b>";
            if($password !=""){
                if(md5($password) === $res->password){
                    $str= "<b style='font-weight:bold;color:green'>√用户密码验证通过</b>";
                }else{
                    $str .="&nbsp;<b style='font-weight:bold;color:red'>×请核对密码</b>";
                }
            }
            return json_encode($str);
        } else {
            return json_encode("<b style='font-weight:bold;color:red'>×该用户不存在</b>");
        }
    }
    public function check(){
        $admin = new Admin();
        $username = $this->request->param('username');
        $password = $this->request->param('password');
        $verifyCode = $this->request->param('vcode');
        $remember = $this->request->param('remember');
        if(!$verifyCode){
            $this->error("请通过正常途径登录！",url('admin/Login/index'));
            exit;
        };
        if(!$this->checkVerifyCode($verifyCode)){
            $this->error("验证码输入有误或已超时，请返回重新输入！",url('admin/Login/index'));
            exit;
        };
        $re = $admin->where('username', "=", $username)->where('password', '=', md5($password))->find();
        $config['act'] = 'login';
        $config['operator'] = $username;
        $config['result'] = $re;
        Log::writeLog($config);
        if ($re) {
            SESSION::set('adminId', $re->id);
            SESSION::set('adminUsername', $re->username);
            $loginTimes = $re->loginTimes+1;
            session('recentLoginTime', $re->recLoginTime);
            session('recentLoginIP', $re->recLoginIP);
            session('loginTimes',$loginTimes);
            $recentLoginTime =  time();
            $recentLoginIP = $_SERVER['REMOTE_ADDR'];
            $re->isUpdate()->save(['recLoginTime'=>$recentLoginTime,'recLoginIP'=>$recentLoginIP,'loginTimes'=>$loginTimes]);
            if($remember) {
                Cookie::set('adminUsername',$re->username,3600 * 24 * 365 * 100);
                Cookie::set('adminPassword',$re->password,3600 * 24 * 365 * 100);
            }
            $message = "登录成功！欢迎".session('adminUsername')."回来";
            $this->success($message,url('admin/Index/index'));
        } else {
            $this->error($admin->getError(), url('admin/Login/index'));
        }
    }

    public function verifyCode(){
        return self::$captcha->entry('login');
    }

    public function  checkVerifyCode($vcode,$id='login'){
        return self::$captcha->check($vcode,$id);
    }
}