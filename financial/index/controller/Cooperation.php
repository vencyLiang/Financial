<?php
/**
 * Created by PhpStorm.
 * User: vency
 * Date: 2017/11/12 0012
 * Time: 15:39
 */

namespace app\index\controller;
use think\Controller;


class Cooperation  extends  Controller{

    function index(){

       return $this->fetch('cooperation');
    }

}
