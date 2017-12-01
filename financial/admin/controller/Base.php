<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/11/1
 * Time: 11:42
 */

namespace app\admin\controller;
use think\Controller;
use think\Session;
use think\Cookie;
use log\Log;
use app\common\model\Admin;


class Base extends Controller
{
    function _initialize()
    {
        if (!Session::has("adminId")) {
            if (Cookie::has('adminUsername') && Cookie::has('adminPassword')) {
                $username = cookie('adminUsername');
                $password = cookie('adminPassword');
                $admin = new Admin();
                $re = $admin->where('username', "=", $username)->where('password', '=', $password)->find();
                $config['act'] = 'login';
                $config['operator'] = $username;
                $config['result'] = $re;
                Log::writeLog($config);
                if ($re) {
                    $loginTimes = $re->loginTimes+1;
                    session('recentLoginTime', $re->recLoginTime);
                    session('recentLoginIP', $re->recLoginIP);
                    session('loginTimes',$loginTimes);
                    $recentLoginTime =  time();
                    $recentLoginIP = $_SERVER['REMOTE_ADDR'];
                    $re->isUpdate()->save(['recLoginTime'=>$recentLoginTime,'recLoginIP'=>$recentLoginIP,'loginTimes'=>$loginTimes]);
                    session('adminId',$re->id);
                    session('adminUsername',$re->username);
                }else{
                    Cookie::delete('adminUsername');
                    Cookie::delete('adminPassword');
                    $this->error("登录信息已失效，请重新登录！", url('admin/Login/index'));
                    exit;
                }
            } else {
                $this->error('请先登录', "admin/Login/index");
            }
        }
    }
}
