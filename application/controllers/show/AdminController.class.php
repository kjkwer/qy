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
    public function  __construct()
    {
        ob_end_clean();
        if(!$_SESSION["user"]){
            $this->jump('index.php?p=show&c=login&a=index','请先登录',3);
        }else{
            self::$userData = $_SESSION["user"];
        }
    }

    //>>人员管理(列表)
    public function indexAction(){
        $model = new ModelNew('yhlb');
//        $adminDatas = $model->orderBy('{$this->paixu} desc')->all();
        $adminDatas = $model->findBySql('select * from `sl_yhlb`');
//        var_dump($adminDatas);exit();
        include CUR_VIEW_PATH."Sadmin" . DS . "admin_index.html";
    }
    //>>添加员工
    public function addAction(){

        $model = new ModelNew('yhlb');

        //>>
        $data["zhanghao"] = $_POST["zhanghao"]?$_POST["zhanghao"]:null;

        $data["mima"] = $_POST["mima"]?md5($_POST["mima"]):null;
        $data["xingming"] = $_POST["xingming"]?$_POST["xingming"]:null;
        $data["bumen"] = $_POST["bumen"]?$_POST["bumen"]:null;
        $data["shoujihaoma"] = $_POST["shoujihaoma"]?$_POST["shoujihaoma"]:null;
        $data["zhicheng"] = $_POST["zhicheng"]?$_POST["zhicheng"]:null;
        $data["dianhua"] = $_POST["dianhua"]?$_POST["dianhua"]:null;
        $data["guanliyuan"] = $_POST["guanliyuan"]?$_POST["guanliyuan"]:null;
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
        $data["paixu"] = self::getSortAction();
        if($data){
            if ($model->insert($data)){
//                echo "添加成功";
                $this->jump('index.php?p=show&c=admin&a=index','添加成功',3);
            }else{
//                echo "添加失败";
                $this->jump('index.php?p=show&c=admin&a=index','添加失败',3);
            }
        }else{
//            echo "数据错误";
            $this->jump('index.php?p=show&c=admin&a=index','数据错误',3);
        }
    }
    //>>删除人员
    public function deleteAction($id){
        $id = $_GET["id"]?$_GET["id"]:null;
        $model = new ModelNew("yhlb");
        if ($id){
            //>>查询id是否存在
            if ($model->delete($id)){
                //>>删除成功，修改排序
//                self::updateSortAction($id);
                $this->jump('index.php?p=show&c=admin&a=index','删除成功',3);
            }else{
                $this->jump('index.php?p=show&c=admin&a=index','删除失败，请刷新重试',3);
            }
        }
    }
    //>>退出登录
    public function quitAction(){
        unset($_SESSION["user"]);
        $this->jump('index.php?p=show&c=index&a=index','退出成功，回到首页',3);
    }

    //>>获取当前做大排序
    public static function getSortAction(){
        $model = new ModelNew('admin');
        $sort = $model->findBySql('select paixu from sl_yhlb');
        if ($sort){
            for ($i=0;$i<count($sort);$i++){
                $data[] = $sort[$i]["paixu"];
            }
            return max($data)+1;
        }else{
            return 1;
        }
    }


}
