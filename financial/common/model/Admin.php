<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/11/1
 * Time: 10:20
 */

namespace app\common\model;
use think\Model;


class Admin extends Model{
    static function getAdminNameById($id){
        return self::get($id)->username;
    }
}