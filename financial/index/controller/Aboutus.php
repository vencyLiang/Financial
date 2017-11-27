<?php
/**
 * Created by PhpStorm.
 * User: vency
 * Date: 2017/11/12 0012
 * Time: 15:26
 */

namespace app\index\controller;


use think\Controller;

class Aboutus extends Controller{
      function index(){
          return $this->fetch('aboutus');
      }
}