<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2017/11/1
 * Time: 14:26
 */

namespace app\common\model;
use think\Model;


class Type extends Model{

    /**功能：通过分类id得到对应的分类名称
     * @param $id
     * @return mixed
     */
    static function getTypeNameById($id){
        return self::get($id)->tname;
    }

    /**功能：通过本类id找出直接父分类信息的关联数组；
     * @param $id
     * @return bool|mixed
     */
    static function getParentById($id)
    {
        $fid = self::get($id)->parentId;
        if($fid) {
            return self::get($fid)->toArray();
        }
        else{
            return false;
        }
    }

    /**给定一个分类数组，在该数组中找出某个给定分类下的所有分类数组，该结果中的每个分类元素以固定格式表示:
     * typeArr 为给定的分类数组，id为欲查找其下级分类的分类,level 表示该分类所在的层级，exclusiveId表示不找该分类的下级分类。
     * @param array $config
     * @return array
     */
    function getAllSubTypesById($config=[]){
        $typesArr = $config['typesArr'];
        $level = $config['level'];
        $id = $config['id'];
        $exclusiveId = $config['exclusiveId'];
        //必须声明为static，否则在递归的时候，该数组就会被重置。
        //同时，特别注意：该函数的返回值是该静态变量的值，因此特别注意，若在同一文件中多次调用该方法，会导致最终结果与预期不符合！！！
        static  $resultTypesArr = [];
        foreach ( $typesArr as $typeItem) {
                if ($typeItem->parentId == $id) {
                    if ($typeItem->id != $exclusiveId) {
                    $typeItem['level'] = $level;
                    $resultTypesArr[] = $typeItem;
                    $this->getAllSubTypesById(['typesArr'=>$typesArr,'level'=>$level+1,'id'=>$typeItem->id,'exclusiveId'=>$exclusiveId]);
                }
            }
        }
        return $resultTypesArr;
    }

    /**功能：从根分类开始，遍历出所有的分类，并构造对应的层次结构。
     * @param array $config
     * @return array
     */
    function getAllTypes($config=[]){
        $config['typesArr'] = $this->select();
        $config['level'] = 0;
        //假定所有顶级分类的父级分类的id=0
        $config['id'] = 0;
        //由于数据库里没有id = 0的分类，所以该参数表明，找所有分类的下级分类；
        $config['exclusiveId'] = 0  ;
        $allTypes = $this->getAllSubTypesById($config);
        return $allTypes;
    }

    /**功能：给定分类id,找出所有上层分类；
     * @param $id
     * @return array
     */
    function  getAllUpLevelTypesById($id){
            $typesArr = $this->select();
            $allUpTypes = [];
            foreach ($typesArr as $typeItem) {
                $level = $this->getLevel($typeItem->id);
                if ($level<$this->getLevel($id)) {
                    $typeItem['level']= $level;
                    $allUpTypes[] = $typeItem;
                }
            }
            return $allUpTypes;
    }

    /**功能：把指定分类的所有上级分类（包括叔伯级分类），按对应的层次结构构造出来。
     * @param $id
     * @return array
     */
    function getAllUpLevelTypesByIdInOrder($id){
        $typesArr = $this->getAllUpLevelTypesById($id);
        $config['typesArr'] = $typesArr;
        $exclusiveId =  $id;
        if( $exclusiveId !== 0){
            $config['exclusiveId'] = $exclusiveId;
            $config['id'] = 0;
            $config['level'] = 0;
            $allUpLevelTypes = $this->getAllSubTypesById($config);
        }else{
            $allUpLevelTypes=[];
        }
        return $allUpLevelTypes;
    }

    /**功能：给定分类id,找出所在的分类层级；
     * @param $id
     * @return int
     */
    function  getLevel($id){
        $level = 0;
        while($id){
            $id = self::get($id)->parentId;
            $level++;
        }
        return $level;
    }

    /**功能：获得指定分类的所有上级父分类id数组；
     * @param $id
     * @return array
     */
    function getParentsId($id){
        $parentsId = [];
        while($id){
            $id = self::get($id)->parentId;
            $parentsId[]=$id;
        }
        return $parentsId;
    }
}