<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/19
 * Time: 17:37
 */

class organizationController
{
    public static $userData = null;
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

    //>>页面
    public function indexAction(){
        include CUR_VIEW_PATH."Sorganization" . DS . "organization_index.html";
    }

    //>>查询下一级
    public static function getNextDataAction($id=0){
        $model = new ModelNew("sl_zzjg");
        $datas = $model->where(["fuid"=>$id])->find()->all();
        return $datas;
    }
    //>>查询下一级
    public static function getNextDataOtherAction(){
        $id = $_POST["id"];
        $model = new ModelNew("sl_zzjg");
        $datas = $model->where(["fuid"=>$id])->find()->all();
        echo json_encode($datas);
    }
    //>>添加部门
    public function addNextAction(){
        $data["fuid"] = $_POST["fuid"]?$_POST["fuid"]:0;
        $data["cengji"] = $_POST["peed"]?$_POST["peed"]:"";
        $data["mingcheng"] = $_POST["mingcheng"]?$_POST["mingcheng"]:"";
        $model = new ModelNew("zzjg");
        if ($model->insert($data)){
            echo 200;
        }else{
            echo 500;
        }
    }
}