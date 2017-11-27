<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/11/4
 * Time: 17:08
 */

namespace app\admin\validate;
use think\Validate;

class User extends Validate
{   protected $rule=[
                    'username'=>'require|max:20',
                    'password'=>'require|min:6',
                    'email' => 'email',
                    "confirm"=>"require|confirm:password"
    ];
    protected $message=['username.require' => "用户名必须填写",
                    'username.max' => '用户名最多不能超过20个字符',
                    'password.require' => '密码必须填写',
                    'password.min' => '密码不得少于6个字符',
                    'email'=>'邮箱格式不正确',
                    'confirm.require'=>'确认密码必须填写',
                    'confirm.confirm'=>'两次密码输入不一致'
    ];

}