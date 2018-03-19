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
    public function  __construct()
    {
        ob_end_clean();
        if(!$_SESSION["user"]){
            $this->jump('index.php?p=show&c=login&a=index','请先登录',3);
        }else{
            self::$userData = $_SESSION["user"];
        }
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
}