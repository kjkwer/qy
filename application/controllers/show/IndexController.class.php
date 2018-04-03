<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/8
 * Time: 16:48
 */

class IndexController extends Controller
{
    public static $userData = null;
    public static $hotArticle = null; //>>热门文章
    public static $newNotice = null; //>>热门公告
    public static $phone = null;//>>联系电话
    public static $mail = null;//>>联系邮箱
    public static $title = null;//>>网站标题
    public static $articleGlassifyData = null;
    public function  __construct()
    {
        ob_end_clean();
        if(!empty($_SESSION["user"])){
            self::$userData = $_SESSION["user"];
        }
        //>>设置热门推荐文章
        $model = new ModelNew("article");
        self::$hotArticle = $model->findBySql("select a.* from sl_article as a join sl_wzfl as b on a.zhuanlan=b.id WHERE b.type!=3 ORDER BY a.renqi DESC limit 7");
        //>>获取最新公告
        self::$newNotice = $model->findBySql("select a.* from sl_article as a join sl_wzfl as b on a.zhuanlan = b.id where b.type=3 ORDER BY id DESC limit 8");
        //>>获取联系电话
        self::$phone = $model->findBySql("select * from sl_phone limit 1")[0]["phone"];
        //>>获取联系邮箱
        self::$mail = $model->findBySql("select * from sl_mail limit 1")[0]["mail"];
        //>>获取网站标题
        self::$title = $model->findBySql("select * from sl_wzbt limit 1")[0]["title"];
        //>>获取工作专栏列表
        self::$articleGlassifyData = $model->findBySql("select * from sl_wzfl WHERE type=1 ORDER BY sort ASC ");
    }


    //>>网站首页
    public function indexAction(){
        //>>获取所有专栏
        $zhuanlanModel = new ModelNew('wzfl');
        $zhuanlanDatas = $zhuanlanModel->findBySql("select * from sl_wzfl WHERE type=1 ORDER by sort asc ");
//        var_dump($zhuanlanDatas);exit();
        if(self::isWapAction()) {
            include CUR_VIEW_PATH."Sindex" . DS . "wap_index_index.html";
        }else{
            include CUR_VIEW_PATH."Sindex" . DS . "index_index.html";
        }
    }

    //>>文章详情
    public function detailAction(){

        $id = $_GET["id"];
        $model = new ModelNew("article");
        //>>增加人气
        $model->query("update sl_article set renqi = renqi+1 WHERE id=$id");
        $article = $model->findOne($id);
        include CUR_VIEW_PATH."Sarticle" . DS . "article_detail.html";
    }

    //>>图文列表
    public function twAction(){
        $wzLxId = $_GET["id"];
        $wzModel = new ModelNew("article");
            //>>设置分页数据
            $count = $wzModel->findBySql("select count(*) as total from sl_article WHERE zhuanlan=$wzLxId");
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
            $articleData = $wzModel->findBySql("select * from sl_article WHERE zhuanlan=$wzLxId ORDER BY id DESC $limit");
        include CUR_VIEW_PATH."Sarticle" . DS . "article_tw_list.html";
    }
    //>>文本列表（思想理论、党史故事、入党小提示、报表下载）
    public function wbAction(){
        $wzLxId = $_GET["id"];
        $wzModel = new ModelNew("article");
            //>>设置分页数据
            $count = $wzModel->findBySql("select count(*) as total from sl_article WHERE zhuanlan=$wzLxId");
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
            $articleData = $wzModel->findBySql("select * from sl_article WHERE zhuanlan=$wzLxId ORDER BY id DESC $limit");
        include CUR_VIEW_PATH."Sarticle" . DS . "article_wb_list.html";
    }


    //>>通过id获取专栏名称
    public static function getZhuanlanAction($id){
        $model = new ModelNew('wzfl');
        $zhuanlan = $model->where(["id"=>$id])->find("fenleimingcheng")->one();
        return $zhuanlan["fenleimingcheng"];
    }

    //>>通过专栏id获取改专栏数据
    public static function getArticleDatasByZlIdAction($id,$offer){
        $articleModel = new ModelNew("article");
        $articleDatas = $articleModel->findBySql("select * from sl_article where zhuanlan=$id order by id DESC limit $offer");
        return $articleDatas;
    }
    //>>获取乡镇名称
    public function getCunMingchengAction($id){
        $model = new ModelNew("zzjg");
        $data = $model ->where(["id"=>$id])->find("mingcheng")->one();
        return $data["mingcheng"];
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
    //>>检测用户是否为手机登录
    public static function isWapAction(){
        // 先检查是否为wap代理，准确度高
        if(!empty($_SERVER['HTTP_VIA'])){
            if(stristr($_SERVER['HTTP_VIA'],"wap")){
                return true;
            }
        }

        // 检查浏览器是否接受 WML.
        elseif(strpos(strtoupper($_SERVER['HTTP_ACCEPT']),"VND.WAP.WML") > 0){
            return true;
        }
        //检查USER_AGENT
        elseif(preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])){
            return true;
        }
        else{
            return false;
        }
    }
}