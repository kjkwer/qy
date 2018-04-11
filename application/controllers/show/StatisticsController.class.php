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
    public static $dayNum = array(7=>'最近7天',15=>'最近15天',30=>'最近30天');
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

    public function sendDataAction(){
        $dayNum = $_POST["dayNum"];
        $n = array_search($dayNum,self::$dayNum);
        $days = array();
        $num = array();
        $sendNum = array();
        $acceptNum = array();
        for ($i=0;$i<$n;$i++){
            $days[] = date("Y-m-d",time()-24*3600*$i);
        }
        $days = array_reverse($days);
        $model = new ModelNew("gw");
        if ($_POST["type"]==1){
            if(self::$userData["guanliyuan"]==1){
                foreach ($days as $v){
                    $count = $model->findBySql("select count(*) as total from sl_gw where Date(dtime) BETWEEN '$v' AND '$v'");
                    $sendNum[] = $count[0]['total'];
                }
                foreach ($days as $v){
                    $count = $model->findBySql("select count(*) as total from sl_gw_o where Date(sendTime) BETWEEN '$v' AND '$v' AND zhuangtai>1");
                    $acceptNum[] = $count[0]['total'];
                }

            }else{
                $zuozhe = self::$userData["id"];
                foreach ($days as $v){
                    $count = $model->findBySql("select count(*) as total from sl_gw_o where  Date(sendTime) BETWEEN '$v' AND '$v' AND zuozhe=$zuozhe AND zhuangtai>1");
                    $sendNum[] = $count[0]['total'];
                }
                $zuozhe = self::$userData["id"];
                foreach ($days as $v){
                    $count = $model->findBySql("select count(*) as total from sl_fasong where Date(dtime) BETWEEN '$v' AND '$v' AND jieshouren=$zuozhe");
                    $acceptNum[] = $count[0]['total'];
                }
            }

        }
        echo json_encode(["days"=>$days,"sendNum"=>$sendNum,"acceptNum"=>$acceptNum]);
    }
}