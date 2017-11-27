<?php
namespace app\index\controller;
use think\Controller;
class Newslist extends  Controller
{
    function index()
    {
        return $this->fetch('');
    }
}