<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/11/1
 * Time: 10:32
 */

namespace app\admin\controller;
use think\Cookie;
use think\Session;
use think\Db;

class Index extends Base {

    function index(){
        $serverInfo = [
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            '主机名'=>$_SERVER['SERVER_NAME'],
            'WEB服务端口'=>$_SERVER['SERVER_PORT'],
            '网站文档目录'=>$_SERVER["DOCUMENT_ROOT"],
            '浏览器信息'=>substr($_SERVER['HTTP_USER_AGENT'], 0, 40),
            '通信协议'=>$_SERVER['SERVER_PROTOCOL'],
            '请求方法'=>$_SERVER['REQUEST_METHOD'],
            'ThinkPHP版本'=>THINK_VERSION,
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '服务器时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '用户的IP地址'=>$_SERVER['REMOTE_ADDR'],
            '剩余空间'=>round((disk_free_space(".")/(1024*1024)),2).'M'
        ];
        $adminUsername = session('adminUsername');
        $loginTimes = session('loginTimes');
        $recentLoginIP = session('recentLoginIP');
        $recentLoginTime = session('recentLoginTime');
        $totalNews = Db::table('news')->count();
        $totalUsers = Db::table('user')->count();
        $totalAdmin = Db::table('admin')->count();
        return $this->fetch('',['adminUsername'=>$adminUsername,'loginTimes'=>$loginTimes,'recLoginIP'=>$recentLoginIP,
            'recLoginTime'=>$recentLoginTime,'serverInfo'=>$serverInfo,
            'totalNews'=>$totalNews,'totalAdmin'=>$totalAdmin,'totalUsers'=>$totalUsers]);
        }

    public  function logout(){
        if($_COOKIE){  //判断客户端的cookie文件是否存在,存在的话将其删除.
            Cookie::delete('adminUsername');
            Cookie::delete('adminPassword');
        }
        //不用session destroy是为了不影响前台退出；
        Session::delete(['adminId','adminUsername']);
        $this->success("退出成功！",'admin/Login/index');
    }
}