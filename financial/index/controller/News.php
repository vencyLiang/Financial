<?php
namespace app\index\controller;
use app\common\model\News as NewsModel;
use app\admin\controller\News as AdminNews;
use app\common\model\Type as TypeModel;
use app\common\model\User as UserModel;
use app\common\model\Comment as CommentModel;
use app\common\model\Admin;
use think\Request;
class News extends  AdminNews{
    use Base;
    protected  $pageSize = 2;
    protected  $pageListInfo;
    protected  $easyNewsList;

    public function _initialize(){
        parent::_initialize();
        $this->pageListInfo = $this->getPageListInfo();
        $this->easyNewsList =  $this->getEasyNewsList();
    }

    /**利用isAjax()方法，配合前端JQ完美解决tp框架下的ajax无刷新分页；
     * 解决逻辑是：先有该页面，然后才有由该页面发出的和接收的ajax请求。
     * @return mixed
     */
    function index(){
        $list = $this->pageListInfo;
        $page = $list->render();
        //生成变量值，渲染模版，返回最终生成的字符串。
        $str = $this->fetch('index', ['list' => $list, 'page' => $page]);
        //是否为Ajax请求；
        if($this->request->isAjax()){
            //如果是，则用正则匹配找出希望改变的区域内容；
            $pattern =  "{<ul class='news' id='news-list'>([\s\S]*?)</ul>[\s\S]*?<div class='Page clearfix' id='pageCodesArea'>([\s\S]*?)</div>}";
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
        $list = $searchWhere->paginate($this->pageSize,false,['query'=>$searchInfo,"type"=>"\page\Pagestyle"]);
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

    /**返回的数组中，每个元素值包含单条新闻的信息，元素键表示名该新闻对应数据库中的主键；
     * 实现了给定了记录的自增ID值可以轻松找出上一条记录与下一条记录的功能。
     * @return array;
     */
    function getEasyNewsList(){
        $searchInfo = Request::instance()->param();
        $searchWhere = $this->buildSearchWhere($searchInfo);
        $searchAllNews = $searchWhere->select();
        $totalNum = count($searchAllNews);
        $easyNewsList = [];
        foreach($searchAllNews as $k=>$newsInfo){
            $newsInfo->adminName = Admin::getAdminNameById($newsInfo->adminid);
            $typename = TypeModel::getTypeNameById($newsInfo->typeid);
            $curTypePid = $newsInfo->typefid;
            if ($curTypePid === 0) {
                $newsInfo->curtypename = "$typename";
            } else {
                $typefname = TypeModel::getTypeNameById($newsInfo->typefid);
                $newsInfo->curtypename = "$typefname>>>$typename";
            }

                if ($k > 0 && $k < $totalNum - 1) {
                    $newsInfo->preNewsId = $searchAllNews[$k - 1]->id;
                    $newsInfo->nextNewsId = $searchAllNews[$k + 1]->id;
                }
                if ($k === 0) {
                    $newsInfo->preNewsId = "";
                    if($totalNum<2){
                        $newsInfo->nextNewsId = "";
                    }else{
                        $newsInfo->nextNewsId = $searchAllNews[1]->id;
                    }
                 }
                if ($k === $totalNum - 1) {
                    $newsInfo->nextNewsId = "";
                    if($totalNum<2){
                        $newsInfo->preNewsId = "";
                    }else{
                        $newsInfo->preNewsId = $searchAllNews[$k - 1]->id;
                    }
                }
            $key  = "id".$newsInfo->id;
            $easyNewsList[$key] = $newsInfo;
        }
        return $easyNewsList;
    }

    function  view(){
        $id = Request::instance()->param("id");
        if(!$id){
            $this->error("请经过正常途径访问！",'news/index');
            exit;
        }
        $easyNewsList = $this->easyNewsList;
        $newsInfo = $easyNewsList["id".$id];
        $preNewsId = $newsInfo->preNewsId;
        $nextNewsId = $newsInfo->nextNewsId;
        if($preNewsId) {
            $preNewsTitle = NewsModel::get($preNewsId)->title;
        }else{
            $preNewsTitle = "这是第一篇";
        }
        if($nextNewsId){
            $nextNewsTitle = NewsModel::get($nextNewsId)->title;
        }else{
            $nextNewsTitle = "没有了";
        }
        $res = NewsModel::get($id);
        if(!$res){
            $this->error("请经过正常途径访问！",'news/index');
            exit;
        }
        $title = $res->title;
        $content = $res->content;
        $pubtime = $res->pubtime;
        $source = $res->source;
        $adminName = Admin::getAdminNameById($res->adminid);
        $clickNum = $res->clicknum+1;
        $res->clicknum = $clickNum;
        $res->save();
        $canComment= $res->cancomment;
        $allCommentList = $this->getComments($id);
        return $this->fetch('', ['id'=>$id,'title' => $title,'content'=>$content, 'pubtime' => $pubtime,
                                            'source'=>$source,'adminName'=>$adminName,'preNewsId'=>$preNewsId,
                                            'preNewsTitle'=>$preNewsTitle,'nextNewsId'=>$nextNewsId,
                                            'nextNewsTitle'=>$nextNewsTitle,'cancomment'=>$canComment,'clickNum'=>$clickNum,'allCommentList'=>$allCommentList]);
    }

    function getComments($articleId){
        $comment = new CommentModel();
        $allCommentList = $comment->where('articleid', '=', $articleId)->select();
        foreach ($allCommentList as $commentItem) {
            $commentItem->commentUser = UserModel::get($commentItem->userid)->username;
            $commentItem->commentUserAvatar = UserModel::get($commentItem->userid)->avatar;
        }
        return $allCommentList;





    }

    function addComment(){
        $this->loginInfo = 1;
        $userId = $this->loginInfo;
        if($userId){
            $commentInfo = Request::instance()->param();
            $commentInfo['userid'] = $userId;
            $commentInfo['publishtime'] = time();
            $commentInfo['osname'] = get_os();
            $commentInfo['browser'] = browse_info();
            $commentInfo['userip'] = $_SERVER['REMOTE_ADDR'];
            $comment = new CommentModel();
            $res = $comment->allowField(true)->save($commentInfo);
            if($res){
                $commentInfoArray = CommentModel::get($comment->id)->toArray();
                $commentInfoArray['commentUser'] = UserModel::get($userId)->username;
                $commentInfoArray['commentUserAvatar'] = UserModel::get($userId)->avatar;
                return json_encode($commentInfoArray);
            }else{
                return "error";
            }
        }else{
            return "0";
        }
    }
}
