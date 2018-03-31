<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/10
 * Time: 9:55
 */

class ArticleController  extends BaseController
{
    public static $userData = null;
    public static $shouye1 = [1=>"是",2=>"否"];
    public static $workDatas = null;
    public static $title = null;//>>网站标题
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
        //>>获取网站标题
        self::$title = $zlModel->findBySql("select * from sl_wzbt limit 1")[0]["title"];
    }

    public function listAction(){
        //>>获取所有文章数据
        $articleModel = new ModelNew("article");
            //>>设置筛选条件
            $seachList = [];
            $urlList=[];
            //设置默认时间时间段为本月初至今
            $date1 = !empty($_GET["date1"])?$_GET["date1"]:date("Y-m",time())."-01";
            $date2 = !empty($_GET["date2"])?$_GET["date2"]:date("Y-m-d",time());
            $seachList[] = "Date(dtime) BETWEEN '".$date1."' and '".$date2."'";
            $urlList[] = "date1=".$date1;
            $urlList[] = "date2=".$date2;
            //工作专栏
            if (!empty($_GET['zhuanlan'])){
                if ($_GET['zhuanlan']!="所有"){
                    $zhuanlanId = self::getfenleiIdAction($_GET["zhuanlan"]);
                    if ($zhuanlanId){
                        $seachList[] = "zhuanlan = ".$zhuanlanId;
                    }
                    $urlList[] = "zhuanlan=".$_GET['zhuanlan'];
                }
            }
            //关键词
            if (!empty($_GET['keyword'])){
                $seachList[] = "biaoti like '%".$_GET["keyword"]."%'";
                $urlList[] = "keyword=".$_GET['keyword'];
            }
            $where = "";
            if (count($seachList)>0){
                $where = "where ".implode(" and ",$seachList);
                $url = '&'.implode('&',$urlList);
            }
            //>设置分页数据
            $count = $articleModel->findBySql("select COUNT(*) as total from sl_article $where  ORDER BY id DESC ");
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
        $articleData = $articleModel->findBySql("select * from `sl_article` $where ORDER BY id DESC $limit");
        //>>获取专栏
        $zhuanlanModel = new ModelNew("wzfl");
        $zhuanlanData = $zhuanlanModel->findBySql("select * from `sl_wzfl`");
        include CUR_VIEW_PATH."Sarticle" . DS . "article_list.html";
    }

    public function addArticleAction(){
        $model = new ModelNew('article');
        $data["biaoti"] = $_POST["biaoti"]?$_POST["biaoti"]:null;
        $data["shouye"] = $_POST["shouye"]?$_POST["shouye"]:null;
        if ($data["shouye"]=="是"){
            $data["shouye"] = 1;
        }else{
            $data["shouye"] = 2;
        }
        $data["neirong"] = $_POST["neirong"]?$_POST["neirong"]:null;
        $data["zhuanlan"] = $_POST["zhuanlan"]?self::getfenleiIdAction($_POST["zhuanlan"]):null;
        $data["paixu"] = $_POST["paixu"]?$_POST["paixu"]:null;
        $data["gengxinshijian"] = $_POST["gengxinshijian"]?$_POST["gengxinshijian"]:null;
        //>>保存图片
        $imageArray = $_FILES["tupian"]?$_FILES["tupian"]:null;
        $data["tupian"] = null;
        if ($imageArray["error"]==0){
            //>>文件上传正常,设置保存文件路径
            $this->library("Upload");
            $upload = new upload;
            if (!$data["tupian"]=$upload->up($imageArray)){
                //>>文件上传失败
                $this->jump('index.php?p=show&c=article&a=add','文件上传失败',3);
            }
        }
        if ($_POST["id"] != ''){
            if ($data["tupian"]==null){
                unset($data["tupian"]);
            }
            if ($model->where(['id'=>$_POST["id"]])->update($data)){
                echo $_POST["id"];
            }else{
                echo 500;
            }
        }else{
            if($rs = $model->insert($data)){
                echo $rs;
            }else{
                echo 500;
            }
        }
    }
    //>>单个删除
    public function deleteAction(){
        $id = $_GET['id'];
        $model = new ModelNew('article');
        if ($model->where(["id"=>$id])->delete()){
            $this->jump('index.php?p=show&c=article&a=list','删除文章成功',3);
        }else{
            $this->jump('index.php?p=show&c=article&a=list','删除文章失败',3);
        }
    }
    //>>批量删除
    public function deletePiliangAction(){
        //>>获取要删除的id
        $result=["code"=>500,"message"=>"删除失败"];
        $ids = $_POST["data"];
        $model = new ModelNew("article");
        if ($model->delete($ids)){
            $result=["code"=>200,"message"=>"删除成功"];
        }
        echo json_encode($result);
    }

    public function addAction(){
        //>>获取所有专栏
        $fenleiModel = new ModelNew("wzfl");
        $fenleiData = $fenleiModel->findBySql("select * from sl_wzfl");
        $id = !empty($_GET["id"])?$_GET["id"]:null;
        if (!$id){
            include CUR_VIEW_PATH."Sarticle" . DS . "article_add.html";
        }else{
            //>>获取文章数据
            $article = new ModelNew("article");
            $articleData = $article->findOne($id);
//            var_dump($articleData);exit();
            include CUR_VIEW_PATH."Sarticle" . DS . "article_add.html";
        }
    }

    public static function getShouye($id){
        return self::$shouye1[$id];
    }

    public static function getfenleiAction($id){
        $fenleiModel = new ModelNew("wzfl");
        $data = $fenleiModel->where(["id"=>$id])->find("fenleimingcheng")->one();
        return $data["fenleimingcheng"];
    }

    public static function getfenleiIdAction($mingcheng){
        $fenleiModel = new ModelNew("wzfl");
        $data = $fenleiModel->where(["fenleimingcheng"=>$mingcheng])->find("id")->one();
        return $data["id"];
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