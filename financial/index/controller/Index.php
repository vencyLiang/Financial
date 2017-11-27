<?php
/**
 * Created by PhpStorm.
 * User: vency
 * Date: 2017/11/12 0012
 * Time: 14:24
 */

namespace app\index\controller;

use think\Controller;

class Index extends  Controller{
    function index(){
        return $this->fetch();
    }

}