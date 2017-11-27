<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/11/4
 * Time: 17:08
 */

namespace app\admin\validate;
use think\Validate;

class News extends Validate
{   protected $rule=[
                    'title'=>'require|max:50',
                    'content'=>'require|min:100',
    ];
    protected $message=['title.require' => "标题必须填写",
                    'title.max' => '标题最多不能超过50个字符',
                    'content.require' => '内容必须填写',
                    'content.min' => '内容不得少于100个字符',
    ];

}