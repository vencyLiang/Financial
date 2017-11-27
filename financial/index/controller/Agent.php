<?php
/**
 * Created by PhpStorm.
 * User: vency
 * Date: 2017/11/12 0012
 * Time: 20:55
 */

namespace app\index\controller;
use think\Controller;


class Agent  extends  Controller{

    function index(){

        return $this->fetch('agent');
    }


}