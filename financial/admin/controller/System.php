<?php

namespace app\admin\controller;
use app\common\model\Log as LogModel;
use think\Request;


class System extends Base
{

    function buildSearchWhere($searchInfo)
    {
        $where = new LogModel();
        if (isset($searchInfo['operator']) && $searchInfo['operator'] !== "") {
            $operator = $searchInfo['operator'];
            $where = $where->where('operator', 'like', "%$operator%");
        }
        if (isset($searchInfo['from']) && $searchInfo['from'] !== "") {
            $from = strtotime($searchInfo['from']);
            $where = $where->where('time', '>', $from);
        }
        if (isset($searchInfo['to']) && $searchInfo['to'] !== "") {
            $endTime = strtotime($searchInfo['to']);
            $where = $where->where('time', '<', $endTime + 3600 * 24);
        }
        return $where;
    }


    function systemLog(){
        $searchInfo = Request::instance()->param();
        $logList = $this->buildSearchWhere($searchInfo)->select();
        //生成变量值，渲染模版，返回最终生成的字符串。
        $str = $this->fetch('', ['logList' => $logList]);
        //是否为Ajax请求；
        if ($this->request->isAjax()) {
            //如果是，则用正则匹配找出希望改变的区域内容；
            $pattern = "{<tbody id='tbody'>([\s\S]*?)</tbody>}";
            preg_match($pattern, $str, $matches);
            //生成ajax响应内容；
            $data = ['tbody' => $matches[1]];
            $dataInfo = json_encode($data);
            return $dataInfo;
        } else {
            return $this->fetch('', ['logList' => $logList]);
        }
    }

    function test(){
       return $this->fetch();
    }
}