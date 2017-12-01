<?php
namespace app\admin\controller;
use app\common\model\Type as TypeModel;
use app\common\model\News as NewsModel;
use app\common\model\Admin;
use think\db\Query;
use think\Request;
use log\Log;

class News extends Base
{
        protected $searchWhere;

    /**利用给定的关键词索引数组，创建查询条件；
     * @return Query;
     */

        function buildSearchWhere($searchInfo){
            $where = new NewsModel();
            if(isset($searchInfo['typeid']) && $searchInfo['typeid'] !== "0"){
                $typeid = $searchInfo['typeid'];
                $where = $where->where('typeid', '=', $typeid);
            }
            if (isset($searchInfo['searchKeywords']) && $searchInfo['searchKeywords'] !== ""){
                $keywords = $searchInfo['searchKeywords'];
                if ($searchInfo['searchType'] === "title") {
                    $where = $where->where('title', 'like', "%$keywords%");
                }
                if ($searchInfo['searchType'] === "content") {
                    $where = $where->where('content', 'like', "%$keywords%");
                }
            }
            if(isset($searchInfo['status']) && $searchInfo['status'] !== ""){
                $where = $where->where('status','=','1');
            }
            if(isset($searchInfo['from']) && $searchInfo['from'] !== ""){
                $from =strtotime($searchInfo['from']);
                $where = $where->where('pubtime','>',$from);
            }
            if(isset($searchInfo['to']) && $searchInfo['to'] !== ""){
                $endTime = strtotime($searchInfo['to']);
                $where = $where->where('pubtime','<',$endTime+3600*24);
            }
            $this->searchWhere = $where;
            return $this->searchWhere;
        }


    /**调用新闻添加页面
     * @return mixed
     */
        function getAdd(){
            $type = new Type();
            return $this->fetch('',['allTypes'=>$type->getAllTypes()]);
        }

    /**
     * 保存添加的新闻数据
     */
        function addSave(){
            $addNewsInfo = $this->request->param();
            $file = $this->request->file('upload');
            //判断是否有文件上传
            if (!empty($file)) {
                $fileObj = $file->validate(['ext' => 'jpg,png,gif'])->move(IMG_UPLOAD_PATH);
                if (!$fileObj) {
                    echo "<script>alert('图片上传出错，请稍后在修改时重新添加');</script>";
                } else {
                    $addNewsInfo['imgpath'] = $fileObj->getSaveName();
                }
            }
            $curtypeId = $addNewsInfo['typeid'];
            $typepidInfo = TypeModel::getParentById($curtypeId);
            if($typepidInfo) {
                $addNewsInfo['typefid'] = $typepidInfo['id'];
            }else{
                $addNewsInfo['typefid']=0;
            }
            $addNewsInfo['pubtime']=time();
            $addNewsInfo['adminid'] = session('adminId');
            $news = new NewsModel();
            $res = $news->validate(true)->save($addNewsInfo);
            $config = ['act'=>'insert','object'=>'news','name'=>$addNewsInfo['title'],'result'=>$res];
            Log::writeLog($config);
            if ($res) {
                $this->success('新闻添加成功', "news/getAdd");
            } else {
                $this->error($news->getError(), "news/getAdd");
            }
        }

    /**
     * 实现复选框选中的新闻批量删除；
     */
        function delete(){
            $param = Request::instance()->param();
            $id = $param['checkbox'];
            $news = new NewsModel();
            //根据主键删除 $news->delete($id) $id为string 或者 array
            $res = $news->where('id', 'IN', $id)->delete();
            $config = ['act'=>'delete','object'=>'news','result'=>$res];
            Log::writeLog($config);
            if ($res) {
                $this->success(json_encode('新闻删除成功'));
            } else {
                $this->error(json_encode('新闻删除失败'));
            }
        }

    /**
     * 实现复选框选中的新闻批量推荐；
     */
        function propose(){
        $param = Request::instance()->param();
        $id = $param['checkbox'];
        $news = new NewsModel();
        $news->status = '1';
        $res = $news->where('id', 'IN', $id)->setField('status','1');
        if ($res>=0) {
            $this->success(json_encode('新闻推荐成功'));
        } else {
            $this->error(json_encode('新闻推荐失败'));
        }
    }

    function cancelPropose(){
        $param = Request::instance()->param();
        $id = $param['checkbox'];
        $news = new NewsModel();
        //根据主键删除 $news->delete($id) $id为string 或者 array
        $news->status = '1';
        $res = $news->where('id', 'IN', $id)->where('status','=','1')->setField('status','0');
        if ($res>=0) {
            $this->success(json_encode('取消新闻推荐成功'));
        } else {
            $this->error(json_encode('取消新闻推荐失败'));
        }
    }

    function  getUpdate(){
        $curNewsId = Request::instance()->param('id');
        $curNewsInfo = NewsModel::get($curNewsId);
        $type = new TypeModel();
        $allTypes = $type->getAllTypes();
        return $this->fetch('',['curNewsInfo'=>$curNewsInfo,'allTypes'=>$allTypes]);

    }
    function updateSave(){
        $updateNewsInfo = Request::instance()->param();
        $file = $this->request->file('upload');
        if (!empty($file)) {
            $fileObj = $file->validate(['ext' => 'jpg,png,gif'])->move(IMG_UPLOAD_PATH);
            if (!$fileObj) {
                echo "<script>alert('图片上传出错，请稍后在修改时重新添加');</script>";
            } else {
                $updateNewsInfo['imgpath'] = $fileObj->getSaveName();
            }
        }
        $updateTypeId = $updateNewsInfo['typeid'];
        $typepidInfo = TypeModel::getParentById($updateTypeId);
        if($typepidInfo) {
            $updateNewsInfo['typefid'] = $typepidInfo['id'];
        }else{
            $updateNewsInfo['typefid']=0;
        }
        //unset($updateNewsInfo['id']);
        $news = new NewsModel;
        //在用save更新时，如果更新数据中含有主键，则必须用isUpdate()来判断是否为更新操作；
        //在更新时save方法与update方法的不同之处在于，save需要重新指定数据表所有指定为非空的字段值，而update则不需要；
        //where方法后不能使用allowFields方法，因为where方法返回一个Query对象，而allowFields不是该对象的成员方法。
        //用update时validate方法无效；
        $res = $news->validate(true)->isUpdate()->save($updateNewsInfo);
        $config = ['act'=>'update','object'=>'news','oid'=>$updateNewsInfo['id'],'name'=>$updateNewsInfo['title'],'result'=>$res];
        Log::writeLog($config);
        if($res===0){
            $this->success("新闻数据没有改动！","News/operate");
        }elseif($res>=0){
            $this->success("新闻更新成功！","News/operate");
        } else{
            $this->error($news->getError(),"News/operate");
        }
    }

    function operate(){
        $type = new TypeModel();
        $list = $this->getPageListInfo();
        $page = $list->render();
        //生成变量值，渲染模版，返回最终生成的字符串。
        $str = $this->fetch('operate', ['list' => $list, 'page' => $page,'allTypes'=>$type->getAllTypes()]);
        //是否为Ajax请求；
        if($this->request->isAjax()){
            //如果是，则用正则匹配找出希望改变的区域内容；
            $pattern =  "{<tbody id='news-item'>([\s\S]*?)</tbody>[\s\S]*?<div class='Page clearfix' id='pageCodesArea'>([\s\S]*?)</div>}";
            preg_match($pattern,$str,$matches);
            //生成ajax响应内容；
            $data = ['listArea'=>$matches[1],'pageArea'=>$matches[2]];
            $dataInfo = json_encode($data);
            echo $dataInfo;
        }else{
            //如果不是，直接输出渲染后的模板字符串；
            return $this->display($str);
        }
    }

    /**调用新闻管理页面
     * @return mixed
     */
    function getPageListInfo(){
        $searchInfo = $this->request->param();
        $searchWhere = $this->buildSearchWhere($searchInfo);
        //分页保留参数；
        $list = $searchWhere->paginate(2,false,['query'=>$searchInfo,"type"=>"\page\Pagestyle"]);
        foreach ($list as $k => $itemRow) {
            $itemRow->adminName = Admin::getAdminNameById($itemRow->adminid);
            $typename = TypeModel::getTypeNameById($itemRow->typeid);
            $curTypePid = $itemRow->typefid;
            if ($curTypePid === 0) {
                $itemRow->curtypename = "$typename";
            } else {
                $typefname = TypeModel::getTypeNameById($itemRow->typefid);
                $itemRow->curtypename = "$typefname>>>$typename";
            }
        }
        return $list;
    }
}