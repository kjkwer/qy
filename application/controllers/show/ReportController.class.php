<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/27
 * Time: 17:47
 */

class ReportController extends BaseController
{
    public static $userData = null;
    public static $workDatas = null;
    public function  __construct()
    {
        ob_end_clean();
        if(!$_SESSION["user"]){
            $this->jump('index.php?p=show&c=login&a=index','请先登录',3);
        }else{
            self::$userData = $_SESSION["user"];
        }
        //>>获取工作专栏
        $zlModel = new ModelNew("wzfl");
        self::$workDatas = $zlModel->where(["type"=>1])->find()->all();
    }
    //>>添加举报信息
    public function addAction(){
        $data["name"] = $_POST["name"];
        $data["phone"] = $_POST["phone"];
        $data["mail"] = $_POST["mail"];
        $data["qq"] = $_POST["qq"];
        $data["title"] = $_POST["title"];
        $data["content"] = $_POST["content"];
//        var_dump($data);exit();
        $model = new ModelNew("report");
        if ($model->insert($data)){
            echo 200;
        }else{
            echo 500;
        }
    }
    //>>举报信息列表
    public function listAction(){
        $reportModel = new ModelNew('report');
        //>>设置删选条件
            $seachList = [];
            $urlList=[];
            //设置默认时间时间段为本月初至今
            $date1 = $_GET["date1"]?$_GET["date1"]:date("Y-m",time())."-01";
            $date2 = $_GET["date2"]?$_GET["date2"]:date("Y-m-d",time());
            $seachList[] = "Date(dtime) BETWEEN '".$date1."' and '".$date2."'";
            $urlList[] = "date1=".$date1;
            $urlList[] = "date2=".$date2;
            //关键词
            if (!empty($_GET['keyword'])){
                $seachList[] = "title like '%".$_GET["keyword"]."%'";
                $urlList[] = "keyword=".$_GET['keyword'];
            }
            $where = "";
            if (count($seachList)>0){
                $where = "where ".implode(" and ",$seachList);
                $url = '&'.implode('&',$urlList);
            }
            //>>设置分页数据
            $count = $reportModel->findBySql("select count(*) as total from `sl_report` $where");
            $totalNum = $count[0]["total"];//数据总数
            $pageSize = 10;  //每页数量
            $maxPage=$totalNum==0?1:ceil($totalNum/$pageSize); //总共有的页数
            $page=isset($_GET['page'])?$_GET['page']:1; //当前页
            if($page < 1)
            {
                $page=1;
            }
            if($page > $maxPage)
            {
                $page=$maxPage;
            }
            $limit=" limit ".($page-1)*$pageSize.",$pageSize"; //分页条件
            //>>页码设置
            $pageData = self::pageSetAction($page,$maxPage);
            $init = $pageData["init"];
            $max = $pageData["max"];
        $reportDatas = $reportModel->findBySql("select * from sl_report $where ORDER BY id DESC $limit");
        include CUR_VIEW_PATH."Sreport" . DS . "report_list.html";
    }
    //>>举报详情页
    public function detailAction(){
        $id = $_GET["id"];
        $reportModel = new ModelNew('report');
        $reportData = $reportModel->findOne($id);

        include CUR_VIEW_PATH."Sreport" . DS . "report_detail.html";
    }
    //>>页码设置
    public static function pageSetAction($page,$maxPage){
        $pageNum = 5;//页码个数
        $pageOffer = ($pageNum-1)/2;//页码偏移量
        if($maxPage<=$pageNum){
            $init=1;
            $max = $maxPage;
        }
        if($maxPage>$pageNum){
            if($page<=$pageOffer){
                $init=1;
                $max = $pageNum;
            }else{
                if($page+$pageOffer>=$maxPage+1){
                    $init = $maxPage-$pageNum+1;
                    $max = $pageNum;
                }else{
                    $init = $page-$pageOffer;
                    $max = $page+$pageOffer;
                }
            }
        }
        return ["init"=>$init,'max'=>$max];
    }
}