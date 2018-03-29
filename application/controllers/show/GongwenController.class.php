<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/12
 * Time: 11:25
 */

class GongwenController extends BaseController
{
    public static $userData = null;
    public static $workDatas = null;
    public static $gwStatus = [2=>"未审核",3=>"已退回",4=>"已审核",5=>"已推荐"];
    public static $gwOStatus = [1=>"未发送",2=>"已发送",3=>"已退回",4=>"已审核",5=>"已推荐"];
    public static $isRead = [1=>"未读",2=>"已读"];
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
    //>>发文列表
    public function  listAction(){
        //>>获取分类数据
        $flModel = new ModelNew("wzfl");
        $flData = $flModel->findBySql("select id,fenleimingcheng as mingcheng from sl_wzfl WHERE type=1");
        if (self::$userData["guanliyuan"]==1){   //>>管理员
            //>>设置筛选条件
            $seachList = [];
            $url=[];
            //>>设置默认时间时间段为本月初至今
            $date1 = $_GET["date1"]?$_GET["date1"]:date("Y-m",time())."-01";
            $date2 = $_GET["date2"]?$_GET["date2"]:date("Y-m-d",time());
            $seachList[] = "Date(dtime) BETWEEN '".$date1."' and '".$date2."'";
            $urlList[] = "date1=".$date1;
            $urlList[] = "date2=".$date2;
            if (!empty($_GET['zhuanlan'])){
                if ($_GET['zhuanlan']!="所有"){
                    $zhuanlanId = self::getZlIdAction($_GET["zhuanlan"]);
                    if ($zhuanlanId){
                        $seachList[] = "zhuanlan = ".$zhuanlanId;
                    }
                    $urlList[] = "zhuanlan=".$_GET['zhuanlan'];
                }
            }
            if (!empty($_GET['zhuangtai'])){
                if ($_GET['zhuangtai']!="所有"){
                    if ($_GET['zhuangtai'] == "正常"){
                        $seachList[] = "jiezhishijian > ".time();
                    }else{
                        $seachList[] = "jiezhishijian <= ".time();
                    }
                }
                $urlList[] = "zhuangtai=".$_GET['zhuangtai'];
            }
            if (!empty($_GET['guanjianci'])){
                $seachList[] = "biaoti like '%".$_GET["guanjianci"]."%'";
                $urlList[] = "guanjianci=".$_GET['guanjianci'];
            }
            $where = "";
            if (count($seachList)>0){
                $where = "where ".implode(" and ",$seachList);
                $url = '&'.implode('&',$urlList);
            }
            $gongwenModel = new ModelNew("gw");
            //>设置分页数据
            $count = $gongwenModel->findBySql("select count(*) as total from `sl_gw` $where");
            $totalNum = $count[0]["total"];  //数据总数
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
            $limit=" limit ".($page-1)*$pageSize.",$pageSize";//分页条件
            $gongwenData = $gongwenModel->findBySql("select * from `sl_gw` $where ORDER BY id DESC $limit");
            //>>页码设置
            $pageData = self::pageSetAction($page,$maxPage);
            $init = $pageData["init"];
            $max = $pageData["max"];
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_send_list.html";
        }else{    //>>非管理员
            //>>获取用户发送的公文数据
            $user_id = self::$userData["id"];
            $gongwenModel = new ModelNew("gw_o");
            //>>设置筛选条件
                $seachList = [];
                $urlList=[];
                //设置默认时间时间段为本月初至今
                $date1 = $_GET["date1"]?$_GET["date1"]:date("Y-m",time())."-01";
                $date2 = $_GET["date2"]?$_GET["date2"]:date("Y-m-d",time());
                $seachList[] = "Date(dtime) BETWEEN '".$date1."' and '".$date2."'";
                $urlList[] = "date1=".$date1;
                $urlList[] = "date2=".$date2;
                //工作专栏
                if (!empty($_GET['zhuanlan'])){
                    if ($_GET['zhuanlan']!="所有"){
                        $zhuanlanId = self::getZlIdAction($_GET["zhuanlan"]);
                        if ($zhuanlanId){
                            $seachList[] = "zhuanlan = ".$zhuanlanId;
                        }
                        $urlList[] = "zhuanlan=".$_GET['zhuanlan'];
                    }
                }
                //状态
                if (!empty($_GET['zhuangtai'])){
                    if ($_GET['zhuangtai']!="所有"){
                            $seachList[] = "zhuangtai = ".array_search($_GET['zhuangtai'],self::$gwOStatus);
                    }
                    $urlList[] = "zhuangtai=".$_GET['zhuangtai'];
                }
                //关键词
                if (!empty($_GET['guanjianci'])){
                    $seachList[] = "biaoti like '%".$_GET["guanjianci"]."%'";
                    $urlList[] = "guanjianci=".$_GET['guanjianci'];
                }
                $where = "";
                if (count($seachList)>0){
                    $where = "where ".implode(" and ",$seachList);
                    $url = '&'.implode('&',$urlList);
                }
                //>设置分页数据
                $count = $gongwenModel->findBySql("select count(*) as total from `sl_gw_o` $where and zuozhe=$user_id");
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
//                var_dump($count);exit();
            $gongwenData = $gongwenModel->findBySql("select * from `sl_gw_o` $where and zuozhe=$user_id ORDER BY id DESC $limit");
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_send_list.html";
        }
    }
    //>>接收列表
    public function acceptAction(){
        //>>获取分类数据
        $flModel = new ModelNew("wzfl");
        $flData = $flModel->findBySql("select id,fenleimingcheng as mingcheng from sl_wzfl WHERE type=1");
        if (self::$userData["guanliyuan"]==1){   //>>管理员
            //>>工作专栏id
            $zlId = $_GET["zlId"];
            //>>设置筛选条件
                $seachList = [];
                $seachList[] = "zhuanlan = ".$zlId;
                $urlList=[];
                //>>设置默认乡镇
                $zzjgModel = new ModelNew("zzjg");
                $xiangzhenDatas = $zzjgModel->findBySql("select * from sl_zzjg WHERE cengji=2");
                $defaultXz = $xiangzhenDatas[0]["id"];
                $xzId = $_GET["xiangzhen"]?self::getCunAction($_GET["xiangzhen"]):$defaultXz;
                $seachList[] = "xiangzhen = ".$xzId;
                $urlList[] = "xiangzhen=".self::getCunMingchengAction($xzId);
                //>>设置默认时间时间段为本月初至今
                $date1 = $_GET["date1"]?$_GET["date1"]:date("Y-m",time())."-01";
                $date2 = $_GET["date2"]?$_GET["date2"]:date("Y-m-d",time());
                $seachList[] = "Date(dtime) BETWEEN '".$date1."' and '".$date2."'";
                $urlList[] = "date1=".$date1;
                $urlList[] = "date2=".$date2;
                //>>公文状态
                if (empty($_GET['gwStaus']) || $_GET['gwStaus'] == "所有（完成）"){
                    $seachList[] = "zhuangtai > 1";
                    $urlList[] = "gwStaus=".$_GET['gwStaus'];
                }else{
                    $seachList[] = "zhuangtai = ".array_search($_GET['gwStaus'],self::$gwStatus);
                    $urlList[] = "gwStaus=".$_GET['gwStaus'];
                }
                //>>设置关键字搜索
                if (!empty($_GET['guanjianci'])){
                    $seachList[] = "biaoti like '%".$_GET["guanjianci"]."%'";
                    $urlList[] = "guanjianci=".$_GET['guanjianci'];
                }
                //>>创建where条件
                $where = "";
                if (count($seachList)>0){
                    $where = "where ".implode(" and ",$seachList);
                    $url = '&'.implode('&',$urlList);
                }
            //>>获取所有公文数据
            $user_id = self::$userData["id"];
            $Model = new ModelNew("gw_o");
            //>>设置分页数据
            $count = $Model->findBySql("select count(*) as total from `sl_gw_o` $where");
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
            $gwDate = $Model->findBySql("select * from sl_gw_o ".$where." ORDER BY id DESC $limit");
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_accept_list.html";
        }else{    //>>非管理员
            //>>获取所有公文数据
            $user_id = self::$userData["id"];
            $Model = new ModelNew("fasong");
            //>>设置筛选条件
                $seachList = [];
                $urlList=[];
                //设置默认时间时间段为本月初至今
                $date1 = $_GET["date1"]?$_GET["date1"]:date("Y-m",time())."-01";
                $date2 = $_GET["date2"]?$_GET["date2"]:date("Y-m-d",time());
                $seachList[] = "Date(b.dtime) BETWEEN '".$date1."' and '".$date2."'";
                $urlList[] = "date1=".$date1;
                $urlList[] = "date2=".$date2;
                //工作专栏
                if (!empty($_GET['zhuanlan'])){
                    if ($_GET['zhuanlan']!="所有"){
                        $zhuanlanId = self::getZlIdAction($_GET["zhuanlan"]);
                        if ($zhuanlanId){
                            $seachList[] = "b.zhuanlan = ".$zhuanlanId;
                        }
                        $urlList[] = "zhuanlan=".$_GET['zhuanlan'];
                    }
                }
                //状态
                if (!empty($_GET['zhuangtai'])){
                    if ($_GET['zhuangtai']!="所有"){
                        $seachList[] = "a.zhuangtai = ".array_search($_GET['zhuangtai'],self::$isRead);
                    }
                    $urlList[] = "zhuangtai=".$_GET['zhuangtai'];
                }
                //关键词
                if (!empty($_GET['guanjianci'])){
                    $seachList[] = "biaoti like '%".$_GET["guanjianci"]."%'";
                    $urlList[] = "guanjianci=".$_GET['guanjianci'];
                }
                $where = "";
                if (count($seachList)>0){
                    $where = "where ".implode(" and ",$seachList);
                    $url = '&'.implode('&',$urlList);
                }
                //>设置分页数据
                $count = $Model->findBySql("select COUNT(*) as total from sl_fasong as a join sl_gw as b on a.gongwen=b.id $where and a.jieshouren=$user_id ORDER BY b.id DESC ");
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
            $gwDate = $Model->findBySql("select b.* from sl_fasong as a join sl_gw as b on a.gongwen=b.id $where and a.jieshouren=$user_id ORDER BY b.id DESC $limit");
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_accept_list.html";
        }
    }
    //>>管理员收文----未完成任务列表
    public function acceptOtherAction(){
        //>>获取分类数据
        $flModel = new ModelNew("wzfl");
        $flData = $flModel->findBySql("select id,fenleimingcheng as mingcheng from sl_wzfl WHERE type=1");
        $xiangzhenDatas = $flModel->findBySql("select * from sl_zzjg WHERE cengji=2");
        //>>获取数据
        $zlId = $_GET["zlId"];//>>工作专栏
        $xzId = self::getCunAction($_GET["xiangzhen"]); //>>乡镇
        $date1 = $_GET["date1"]; //>>起始时间
        $date2 = $_GET["date2"]; //>>结束时间
        $guanjianci = $_GET["guanjianci"];//>>关键词
        //>>设置删选条件
        $seachList = [];
        $seachList[] = "b.zhuanlan=".$zlId;
        $seachList[] = "b.xiangzhen=".$xzId;
        $seachList[] = "Date(b.dtime) BETWEEN '".$date1."' and '".$date2."'";
        $urlList=[];
        $urlList[] = "xiangzhen=".self::getCunMingchengAction($xzId);
        $urlList[] = "date1=".$date1;
        $urlList[] = "date2=".$date2;
        $urlList[] = "gwStaus=未完成";
        $where = "";
        if (count($seachList)>0){
            $where = "where ".implode(" and ",$seachList);
            $url = '&'.implode('&',$urlList);
        }
        //>>设置数量查询sql语句
        $sql = "select a.id from sl_zzjg as a join sl_gw_o as b on a.id=b.cun $where AND (b.zhuangtai>=2)";
        $sqlCount = "select COUNT(*) as total from sl_zzjg as a WHERE a.fuid=$xzId and a.id not in  ($sql)";
        $count = $flModel->findBySql($sqlCount);
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
        //>>设置数据查询sql
        $sqlDatas = "select a.id,a.fuid from sl_zzjg as a WHERE a.fuid=$xzId and a.id not in  ($sql) $limit";
        $gwDate = $flModel->findBySql($sqlDatas);
//        var_dump($gwDate);exit();
        include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_accept_list.html";
    }
    //>>编辑文章
    public function editAction(){
        $id = $_GET["id"]?$_GET["id"]:null;
        //>>获取公文类型
        $lxModel = new ModelNew("gwlx");
        $lxData = $lxModel->findBySql("select id,leixing from sl_gwlx");
        //>>获取分类数据
        $flModel = new ModelNew("wzfl");
        $flData = $flModel->findBySql("select id,fenleimingcheng as mingcheng from sl_wzfl WHERE type=1");
//        var_dump($flData);exit();
        //>>获取公文来源
        $model = new ModelNew("zzjg");
        $xiangzhenData = $model->findBySql("select * from sl_zzjg WHERE cengji=2");
        $cunData = $model->findBySql("select * from sl_zzjg WHERE cengji=3");
        if(!$id){
            //>>新增
            if (self::$userData["guanliyuan"]==1){   //>>管理员
                //>>当前登录用户的id
                $user_id = self::$userData["id"];
                //>>获取接收公文人员数据
                $adminModel = new ModelNew("yhlb");
                $jsrys = $adminModel->findBySql("select id,xingming from sl_yhlb WHERE id != $user_id");
                include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_edit.html";
            }else{    //>>非管理员
//                var_dump($cunData);exit();
                include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_edit.html";
            }
        }else{
            //>>编辑
            if (self::$userData["guanliyuan"]==1){   //>>管理员
                //>>当前登录用户的id
                $user_id = self::$userData["id"];
                //>>获取需编辑公文的数据
                $model = new ModelNew("gw");
                $gwData = $model->findOne($id);
                //>>获取接收公文人员数据
                $adminModel = new ModelNew("yhlb");
                $jsrys = $adminModel->findBySql("select id,xingming from sl_yhlb WHERE id != $user_id");
                include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_edit.html";
            }else{    //>>非管理员
                //>>获取需编辑公文的数据
                $model = new ModelNew("gw_o");
                $gwData = $model->findOne($id);
                $laiyuanData = $model->find()->all();
                include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_edit.html";
            }
        }

    }
    //>>管理员编辑发布公文
    public function editAdminAction(){
        $model = new ModelNew('gw');
        $data["biaoti"] = $_POST["biaoti"]?$_POST["biaoti"]:null;
        $data["bianhao"] = $_POST["bianhao"]?$_POST["bianhao"]:null;
        $data["jiezhishijian"] = $_POST["jiezhishijian"]?strtotime($_POST["jiezhishijian"]):null;
        $data["zhuanlan"] = $_POST["zhuanlan"]?self::getZlIdAction($_POST["zhuanlan"]):null;
        $data["neirong"] = $_POST["neirong"]?$_POST["neirong"]:null;
        $data["zuozhe"] = self::$userData["id"];
        if ($_POST["id"] != ''){   //编辑
            if ($rs = $model->where(["id"=>$_POST["id"]])->update($data)){
//                //>>删除原有接收人
//                $fasongModel = new ModelNew("fasong");
//                $fasongModel->where(["fasongren"=>$data["zuozhe"]])->where(["gongwen"=>$_POST["id"]])->delete();
//                //>>设置新的接收人
//                $jsrIds = $_POST["jsrs"];
//                if ($jsrIds){
//                    foreach ($jsrIds as $jsrId){
//                        $model = new ModelNew("fasong");
//                        $data["gongwen"] = $_POST["id"];
//                        $data["fasongren"] = self::$userData["id"];
//                        $data["jieshouren"] = $jsrId;
//                        $data["zhuangtai"] = 1;
//                        $model->insert($data);
//                    }
//                }
                echo $_POST["id"];
            }else{
                echo 500;
            }
        }else{    //新增
            if($rs=$model->insert($data)){
//                //>>设置接收人
//                $jsrIds = $_POST["jsrs"];
//                if ($jsrIds){
//                    foreach ($jsrIds as $jsrId){
//                        $model = new ModelNew("fasong");
//                        $data["gongwen"] = $rs;
//                        $data["fasongren"] = self::$userData["id"];
//                        $data["jieshouren"] = $jsrId;
//                        $data["zhuangtai"] = 1;
//                        $model->insert($data);
//                    }
//                }
                echo $rs;
            }else{
                echo 500;
            }
        }
    }
    //>>设置抄送人
    public function setJsrAction(){
        $gwId = $_POST["gwId"];
        $model = new ModelNew("gw");
        $data = $model->findOne($gwId);
        //>>删除原有接收人
        $fasongModel = new ModelNew("fasong");
        $fasongModel->where(["fasongren"=>$data["zuozhe"]])->where(["gongwen"=>$gwId])->delete();
        //>>设置新的接收人
        $jsrIds = $_POST["jsrs"];
        if ($jsrIds){
            $result = 1;
            foreach ($jsrIds as $jsrId){
                $model = new ModelNew("fasong");
                $arr["gongwen"] = $gwId;
                $arr["fasongren"] = self::$userData["id"];
                $arr["jieshouren"] = $jsrId;
                $arr["zhuangtai"] = 1;
                if (!$model->insert($arr)){
                    $result = 0;
                }
            }
        }
        echo $result;
    }

    //>>非管理员编辑公文
    public function editHuiyuanAction(){
        $model = new ModelNew("gw_o");
        $data["biaoti"] = $_POST["biaoti"]?$_POST["biaoti"]:null;
        $data["bianhao"] = $_POST["bianhao"]?$_POST["bianhao"]:null;
        $data["zhuanlan"] = $_POST["zhuanlan"]?self::getZlIdAction($_POST["zhuanlan"]):null;
        $data["xiangzhen"] = $_POST["xiangzhen"]?self::getCunAction($_POST["xiangzhen"]):null;
        $data["cun"] = $_POST["cun"]?self::getCunAction($_POST["cun"]):null;
        $data["laiyuan"] = $_POST["laiyuan"]?self::getLaiyuanIdAction($_POST["laiyuan"]):null;
        $data["neirong"] = $_POST["neirong"]?$_POST["neirong"]:null;
        $data["zuozhe"] = self::$userData["id"];
        //>>保存图片
        $imageArray = $_FILES["tupian"]?$_FILES["tupian"]:null;
        $data["zhuangtai"] = 1;
        $data["tupian"] = null;
        if ($imageArray["error"]==0){
            //>>文件上传正常,设置保存文件路径
            $this->library("Upload");
            $upload = new upload;
            if (!$data["tupian"]=$upload->up($imageArray)){
                //>>文件上传失败
                $this->jump('index.php?p=show&c=gongwen&a=edit','文件上传失败',3);
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
            $data["yuefen"] = date("m",time());
            if($rs = $model->insert($data)){
                echo $rs;
            }else{
                echo 500;
            }
        }
    }

    //>>发送公文详情
    public function sendDetailAction(){
        //>>公文id
        $id = $_GET["id"];
//        var_dump($gwData);exit();
        //>>判断是否为管理员
        if(self::$userData["guanliyuan"]==1){    //管理员
            //>>获取公文内容
            $model = new ModelNew("gw");
            $gwData = $model->findOne($id);
            //>>获取公文内容
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_send_detail.html";
        }else{     //非管理员
            //>>获取公文内容
            $model = new ModelNew("gw_o");
            $gwData = $model->findOne($id);
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_send_detail.html";
        }
    }
    //>>接收公文详情
    public function acceptDetailAction(){
        //>>公文id
        $id=$_GET["id"];
//        var_dump($gwData);exit();
        if(self::$userData["guanliyuan"]==1){    //管理员
            //>>获取公文内容
            $model = new ModelNew("gw_o");
            $gwData = $model->findOne($id);
            //>>获取分类数据
            $flModel = new ModelNew("wzfl");
            $flData = $flModel->findBySql("select id,fenleimingcheng as mingcheng from sl_wzfl WHERE type=1");
            //>>获取公文内容
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_accept_detail.html";
        }else{     //非管理员
            //>>获取公文内容
            $model = new ModelNew("gw");
            $gwData = $model->findOne($id);
            //>>设置为已读
            $fsModel = new ModelNew("fasong");
            $fsModel->where(["jieshouren"=>self::$userData["id"]])->where(["gongwen"=>$id])->update(["zhuangtai"=>2]);
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_accept_detail.html";
        }
    }
    //>>发送公文
    public function fasongAction(){
        $id = $_POST["gwId"];
        $model = new ModelNew("gw_o");
        if ($model->where(["id"=>$id])->update(["zhuangtai"=>2])){
            echo 200;
        }else{
            echo 500;
        }
    }
    //>>获取发送公文状态
    public static function getGwStatusAction($jsr,$gwId){
        $model = new ModelNew("fasong");
        $zhuangtai = $model->where(["gongwen"=>$gwId])->where(["jieshouren"=>$jsr])->find("zhuangtai")->one();
        return $zhuangtai["zhuangtai"];
    }

    //>>判断公文是否过期
    public static function isDateAction($id){
        //>>获取公文截止时间
        $model = new ModelNew("gw");
        $data = $model->find("jiezhishijian")->where(["id"=>$id])->one();
        $data1 = strtotime($data["jiezhishijian"]);
        return $data1;
    }

    //>>通过接收人的id和公文编号确定是否已发送给改编辑人（管理员公文编辑回显时使用）
    public function isfasongAction($id,$gwId){
        $model = new ModelNew("fasong");
        $data = $model->where(["jieshouren"=>$id])->where(["gongwen"=>$gwId])->find()->one();
        if ($data){
            echo "em-add";
        }else{
            echo "";
        }
    }
    //>>获取公文类型
    public static function getGwLxAction($id){
        $model = new ModelNew("gwlx");
        $data = $model ->where(["id"=>$id])->find("leixing")->one();
        if ($data){
            return $data["leixing"];
        }
    }

    //>>获取抄送人
    public static function getJsrAction($gwId){
        $fsr = self::$userData["id"];
//        var_dump($gwId,$fsr);exit();
        $model = new ModelNew("fasong");
        $names = $model->findBySql("select b.xingming from sl_fasong as a JOIN sl_yhlb as b on a.jieshouren=b.id where a.gongwen=$gwId");
        if ($names){
            $str = "";
            foreach ($names as $name){
                $str .= $name["xingming"]."，";
            }
        }
        $str = trim($str,"，");
        return $str;
    }
    //>>删除公文<dan>
    public function deleteSingleAction(){
        $id = $_POST["id"];
        $result = ["code"=>501,"message"=>"删除失败"];
        $fasongModel = new ModelNew("fasong");  //>>删除与公文有关的抄送人信息
        $fasongModel->where(["gongwen"=>$id])->delete();
        $gwModel = new ModelNew("gw");
        if ($gwModel->delete($id)){
            $result = ["code"=>200,"message"=>"删除公文成功"];
        }else{
            $result = ["code"=>502,"message"=>"删除公文失败"];
        }
        echo json_encode($result);
    }
    //>>删除公文O
    public function deleteSingleOAction(){
        $id = $_POST["id"];
        $result = ["code"=>501,"message"=>"删除失败"];
        $gwModel = new ModelNew("gw_o");
        if ($gwModel->delete($id)){
            $result = ["code"=>200,"message"=>"删除公文成功"];
        }else{
            $result = ["code"=>502,"message"=>"删除公文失败"];
        }
        echo json_encode($result);
    }
    //>>删除公文批量
    public function deletePiliangAction(){
        //>>获取要删除的id
        $result=["code"=>500,"message"=>"删除失败"];
        $ids = $_POST["data"];
        $model = new ModelNew("gw");
        //>>删除与公文抄送人信息
        $fasongModel = new ModelNew("fasong");
        if ($ids){
            foreach ($ids as $id){
                $fasongModel->where(["gongwen"=>$id])->delete();
                if ($model->delete($id)){
                    $result = ["code"=>200,"message"=>"删除公文成功"];
                }else{
                    $result = ["code"=>502,"message"=>"删除公文失败"];
                }
            }
        }
        echo json_encode($result);
    }
    //>>删除公文批量o
    public function deletePiliangOAction(){
        //>>获取要删除的id
        $result=["code"=>200,"message"=>"删除成功"];
        $ids = $_POST["data"];
        $model = new ModelNew("gw_o");
        if ($ids){
            foreach ($ids as $id){
                $model->findBySql("delete from sl_gw_o WHERE id=$id AND (zhuangtai=1 or zhuangtai=3)");
            }
        }
        echo json_encode($result);
    }

    //>>审核公文
    public function sheheGwAction(){
        $id = $_POST["gwId"];
        $model = new ModelNew("gw_o");
        if ($model->where(["id"=>$id])->update(["zhuangtai"=>4])){
            echo 200;
        }else{
            echo 500;
        }
    }
    //>>推荐公文
    public function tuijianGwAction(){
        $id = $_POST["gwId"];
        $gwModel = new ModelNew("gw_o");
        if ($gwModel->where(["id"=>$id])->update(["zhuangtai"=>5])){
            $data = $gwModel->findOne($id);
//            var_dump($data);exit();
            $wzModel = new ModelNew("article");
            $wz["biaoti"] = $data["biaoti"];
            $wz["tupian"] = $data["tupian"];
            $wz["neirong"] = $data["neirong"];
            $wz["zhuanlan"] = $data["zhuanlan"];
            $wz["xiangzhen"] = $data["xiangzhen"];
            $wz["cun"] = $data["cun"];
            $wz["shouye"] = 1;
            if ($wzModel->insert($wz)){
                echo 200;
            }else{
                echo 502;
            }
        }else{
            echo 501;
        }
    }

    //>>获取公文来源
    public static function getlaiyuanAction($id){
        $model = new ModelNew("gwlxy");
        $data = $model ->where(["id"=>$id])->find("laiyuan")->one();
        if ($data){
            return $data["laiyuan"];
        }
    }
    //>>获取发送人
    public static function getfsrAction($id){
        $model = new ModelNew("yhlb");
        $data = $model ->where(["id"=>$id])->find("xingming")->one();
        if ($data){
            return $data["xingming"];
        }
    }

    //>>获取类型ID
    public static function getLeixingIdAction($leixing){
        $model = new ModelNew("gwlx");
        $data = $model ->where(["leixing"=>$leixing])->find("id")->one();
        if ($data){
            return $data["id"];
        }
    }

    //>>获取类型ID
    public static function getLaiyuanIdAction($laiyuan){
        $model = new ModelNew("gwlxy");
        $data = $model ->where(["laiyuan"=>$laiyuan])->find("id")->one();
        if ($data){
            return $data["id"];
        }
    }
    //>>获取专栏id
    public static function getZlIdAction($name){
        $model = new ModelNew("wzfl");
        $data = $model ->where(["fenleimingcheng"=>$name])->find("id")->one();
        if ($data){
            return $data["id"];
        }
    }
    //>>获取专栏名称
    public static function getZlNameAction($id){
        $model = new ModelNew("wzfl");
        $data = $model ->where(["id"=>$id])->find("fenleimingcheng")->one();
        if ($data){
            return $data["fenleimingcheng"];
        }
    }
    //>>获取乡镇id
    public function getCunAction($name){
        $model = new ModelNew("zzjg");
        $data = $model ->where(["mingcheng"=>$name])->find("id")->one();
        return $data["id"];
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
}