<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/9
 * Time: 10:11
 */

class AdminController extends BaseController
{
    public static $userData = null;
    public static $isAdmin = [1=>"是",2=>"否"];
    public static $zhuangtai = [1=>"在职",2=>"离职"];
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

    //>>人员管理(列表)
    public function indexAction(){
        $model = new ModelNew('yhlb');
        //>>设置搜索条件
            $seachList = [];
            $seachList[] = 'shanchu=1';
            $urlList=[];
            //>>设置关键字搜索
            if (!empty($_GET['keyWord'])){
                $seachList[] = "xingming like '%".$_GET["keyWord"]."%'";
                $urlList[] = "keyWord=".$_GET['keyWord'];
            }
            $where = "";
            if (count($seachList)>0){
                $where = "where ".implode(" and ",$seachList);
                $url = '&'.implode('&',$urlList);
            }
            //>>设置分页数据
            $count = $model->findBySql("select count(*) as total from `sl_yhlb` $where");
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
            $adminDatas = $model->findBySql("select * from `sl_yhlb` $where $limit");
        //>>查找所有部门
        $jgModel = new ModelNew('zzjg');
        $bumenData = $jgModel->findBySql("select * from sl_zzjg WHERE cengji<=2");
        include CUR_VIEW_PATH."Sadmin" . DS . "admin_index.html";
    }
    //>>添加员工
    public function addAction(){

        $model = new ModelNew('yhlb');

        //>>
        $data["zhanghao"] = $_POST["zhanghao"]?$_POST["zhanghao"]:null;

        $data["mima"] = $_POST["mima"]?$_POST["mima"]:null;
        $data["xingming"] = $_POST["xingming"]?$_POST["xingming"]:null;
        $data["bumen"] = $_POST["bumen"]?self::getBumenIdAction($_POST["bumen"]):null;
        $data["shoujihaoma"] = $_POST["shoujihaoma"]?$_POST["shoujihaoma"]:null;
        $data["dianhua"] = $_POST["dianhua"]?$_POST["dianhua"]:null;
        $data["guanliyuan"] = $_POST["guanliyuan"]?$_POST["guanliyuan"]:null;
        $data["shanchu"] = 1;
        if ($data["guanliyuan"]=="是"){
            $data["guanliyuan"]=1;
        }else{
            $data["guanliyuan"]=2;
        }
        $data["zhuangtai"] = $_POST["zhuangtai"]?$_POST["zhuangtai"]:null;
        if ($data["zhuangtai"]=="在职"){
            $data["zhuangtai"]=1;
        }else{
            $data["zhuangtai"]=2;
        }
//        var_dump($_POST["id"]);exit();
        if($_POST["id"]==""){
            $data["mima"] = md5($_POST["mima"]);
            if ($model->insert($data)){
//                echo "添加成功";
                echo 200;
            }else{
//                echo "添加失败";
                echo 500;
            }
        }else{
            $getMima = $model->where(["id"=>$_POST["id"]])->find("mima")->one();
            if ($data["mima"] == substr($getMima["mima"],0,6)){
                unset($data["mima"]);
            }else{
                $data["mima"] = md5($_POST["mima"]);
            }
//            var_dump($_POST["id"]);exit();
            if ($model->where(["id"=>$_POST["id"]])->update($data)){
//                echo "添加成功";
                echo 200;
            }else{
//                echo "添加失败";
                echo 500;
            }
        }
    }
    //>>修改密码
    public function updatePwdAction(){
        $userId = $_POST["userId"];
        $oldPwd = $_POST["oldPassword"];
        $newPwd = $_POST["newPassword"];
        //>>对比原密码是否正确
        $yhlbModel = new ModelNew("yhlb");
        $data = $yhlbModel->findBySql("select mima from sl_yhlb where id=$userId");
        $srcPwd = $data[0]["mima"];
        if (md5($oldPwd)==$srcPwd){
            $newPwd = md5($newPwd);
            if($yhlbModel->where(["id"=>$userId])->update(["mima"=>$newPwd])){
                echo 200;
            }else{
                echo 502;
            }
        }else{
            echo 501;
        }

    }
    //>>删除人员(单)
    public function deleteSingleAction($id){
        $id = $_POST["id"]?$_POST["id"]:null;
        $model = new ModelNew("yhlb");
        if ($id){
            //>>查询id是否存在
            if ($model->where(["id"=>$id])->update(["shanchu"=>-1])){
                //>>删除成功，修改排序
//                self::updateSortAction($id);
                echo 200;
            }else{
                echo 500;
            }
        }
    }
    public function deletePiliangAction(){
        //>>获取要删除的id
//        var_dump(111,$_POST);exit();
        $result=["code"=>200,"message"=>"删除成功"];
        $ids = $_POST["data"];
        $model = new ModelNew("yhlb");
        //>>删除与公文抄送人信息
        if ($ids){
            foreach ($ids as $id){
                $model->findBySql("update sl_yhlb set shanchu=-1 WHERE id={$id}");
            }
        }
        echo json_encode($result);
    }

    //>>退出登录
    public function quitAction(){
        unset($_SESSION["user"]);
        $this->jump('index.php?p=show&c=login&a=index','退出成功，回到首页',3);
    }

    //>>通过id获取人员数据
    public function getOneAction(){
        $id = $_POST["id"];
        $model = new ModelNew("yhlb");
        $data = $model->where(["id"=>$id])->find()->one();
        $data["bumen"] = self::getBumenMingchengAction($data["bumen"]);
        $data["guanliyuan"] = self::$isAdmin[$data["guanliyuan"]];
        $data["zhuangtai"] = self::$zhuangtai[$data["zhuangtai"]];
        $data["mima"] = substr($data["mima"],0,6);
        echo json_encode($data);
    }

    //>>通过部门名称查找id
    public static function getBumenIdAction($laiyuan){
        $model = new ModelNew("zzjg");
        $data = $model ->where(["mingcheng"=>$laiyuan])->find("id")->one();
        if ($data){
            return $data["id"];
        }
    }

    //>>通过部门id获取部门名称
    public function getBumenMingchengAction($id){
        $model = new ModelNew("zzjg");
        $data = $model ->where(["id"=>$id])->find("mingcheng")->one();
        if ($data){
            return $data["mingcheng"];
        }
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
