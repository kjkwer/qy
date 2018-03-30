<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/30
 * Time: 11:19
 */

class StatisticsController extends BaseController
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
    public function sendAction(){
        include CUR_VIEW_PATH."Sstatistics" . DS . "statistics_send.html";
    }

    public function acceptAction(){

        include CUR_VIEW_PATH."Sstatistics" . DS . "statistics_accept.html";
    }

    public function sendDataAction(){
        $days = array();
        $num = array();
        for ($i=0;$i<7;$i++){
            $days[] = date("Y-m-d",time()-24*3600*$i);
        }
        $days = array_reverse($days);
        if ($_POST["type"]==1){
            if(self::$userData["guanliyuan"]==1){
                $model = new ModelNew("gw");
                foreach ($days as $v){
                    $count = $model->findBySql("select count(*) as total from sl_gw where Date(dtime) BETWEEN '$v' AND '$v'");
                    $num[] = $count[0]['total'];
                }
            }else{
                $model = new ModelNew("gw_o");
                $zuozhe = self::$userData["id"];
                foreach ($days as $v){
                    $count = $model->findBySql("select count(*) as total from sl_gw_o where  Date(dtime) BETWEEN '$v' AND '$v' AND zuozhe=$zuozhe AND zhuangtai>1");
                    $num[] = $count[0]['total'];
                }
            }

        }else{
            if(self::$userData["guanliyuan"]==1) {
                $model = new ModelNew("gw_o");
                foreach ($days as $v){
                    $count = $model->findBySql("select count(*) as total from sl_gw_o where Date(dtime) BETWEEN '$v' AND '$v' AND zhuangtai>1");
                    $num[] = $count[0]['total'];
                }
            }else{
                $model = new ModelNew("fasong");
                $zuozhe = self::$userData["id"];
                foreach ($days as $v){
                    $count = $model->findBySql("select count(*) as total from sl_fasong where Date(dtime) BETWEEN '$v' AND '$v' AND jieshouren=$zuozhe");
                    $num[] = $count[0]['total'];
                }
            }

        }
        echo json_encode(["days"=>$days,"nums"=>$num]);
    }
}