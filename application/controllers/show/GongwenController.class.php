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
    public function  __construct()
    {
        ob_end_clean();
        if(!$_SESSION["user"]){
            $this->jump('index.php?p=show&c=login&a=index','请先登录',3);
        }else{
            self::$userData = $_SESSION["user"];
        }
    }
    //>>发文列表
    public function  listAction(){
        if (self::$userData["guanliyuan"]==1){   //>>管理员
            //>>获取所有公文数据
            $user_id = self::$userData["id"];
            $gongwenModel = new ModelNew("gw");
            $gongwenData = $gongwenModel->findBySql("select * from `sl_gw` WHERE zuozhe=$user_id ORDER BY dtime DESC ");

            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_list.html";
        }else{    //>>非管理员

        }
    }
    //>>编辑文章
    public function editAction(){
        $id = $_GET["id"]?$_GET["id"]:null;
        //>>获取公文类型
        $lxModel = new ModelNew("gwlx");
        $lxData = $lxModel->findBySql("select id,leixing from sl_gwlx");
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
                echo 111;
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

            }
        }

    }
    //>>管理员编辑发布公文
    public function editAdminAction(){
        $model = new ModelNew('gw');
        $data["biaoti"] = $_POST["biaoti"]?$_POST["biaoti"]:null;
        $data["bianhao"] = $_POST["bianhao"]?$_POST["bianhao"]:null;
        $data["leixing"] = $_POST["leixing"]?$_POST["leixing"]:null;
        $data["jiezhishijian"] = $_POST["jiezhishijian"]?$_POST["jiezhishijian"]:null;
        $data["neirong"] = $_POST["neirong"]?$_POST["neirong"]:null;
        $data["zuozhe"] = self::$userData["id"];
        if ($_POST["id"] != ''){   //编辑
            if ($rs = $model->where(["id"=>$_POST["id"]])->update($data)){
                //>>删除原有接收人
                $fasongModel = new ModelNew("fasong");
                $fasongModel->where(["fasongren"=>$data["zuozhe"]])->where(["gongwen"=>$_POST["id"]])->delete();
                //>>设置新的接收人
                $jsrIds = $_POST["jsrs"];
                if ($jsrIds){
                    foreach ($jsrIds as $jsrId){
                        $model = new ModelNew("fasong");
                        $data["gongwen"] = $_POST["id"];
                        $data["fasongren"] = self::$userData["id"];
                        $data["jieshouren"] = $jsrId;
                        $data["zhuangtai"] = 1;
                        $model->insert($data);
                    }
                }
                $this->jump('index.php?p=show&c=gongwen&a=list','修改公文成功',3);
            }else{
                $this->jump('index.php?p=show&c=gongwen&a=list','修改公文失败',3);
            }
        }else{    //新增
            if($rs=$model->insert($data)){
                //>>设置接收人
                $jsrIds = $_POST["jsrs"];
                if ($jsrIds){
                    foreach ($jsrIds as $jsrId){
                        $model = new ModelNew("fasong");
                        $data["gongwen"] = $rs;
                        $data["fasongren"] = self::$userData["id"];
                        $data["jieshouren"] = $jsrId;
                        $data["zhuangtai"] = 1;
                        $model->insert($data);
                    }
                }
                $this->jump('index.php?p=show&c=gongwen&a=list','发布公文成功',3);
            }else{
                $this->jump('index.php?p=show&c=gongwen&a=list','发布公文失败',3);
            }
        }
    }

    //>>非管理员编辑公文
    public function editNotAdminAction(){

    }


    //>>发送公文详情
    public function sendDetailAction(){
        //>>公文id
        $id = $_GET["id"];
        //>>获取公文内容
        $model = new ModelNew("gw");
        $gwData = $model->findOne($id);
//        var_dump($gwData);exit();
        //>>判断是否为管理员
        if(self::$userData["guanliyuan"]==1){    //管理员
            //>>获取公文内容

            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_detail.html";
        }else{     //非管理员

        }
    }



    //>>判断公文是否过期
    public static function isDateAction($id){
        //>>获取公文截止时间
        $model = new ModelNew("gw");
        $data = $model->find("jiezhishijian")->where(["id"=>$id])->one();
        $data1 = strtotime($data["jiezhishijian"]);
        if ( time()>= $data1){
            echo  "已过期";
        }else{
            echo  "未过期";
        }
    }

    //>>通过接收人的id和公文编号确定是否已发送给改编辑人（管理员公文编辑回显时使用）
    public function isfasongAction($id,$bianhao){
        $model = new ModelNew("fasong");
        $data = $model->where(["jieshouren"=>$id])->where(["gongwen"=>$bianhao])->find()->one();
        if ($data){
            echo "checked";
        }else{
            echo "111";
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
        $names = $model->findBySql("select b.xingming from sl_fasong as a JOIN sl_yhlb as b on a.jieshouren=b.id where a.fasongren=$fsr and a.gongwen=$gwId");
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
        if ($fasongModel->where(["gongwen"=>$id])->delete()){
            $gwModel = new ModelNew("gw");
            if ($gwModel->delete($id)){
                $result = ["code"=>200,"message"=>"删除公文成功"];
            }else{
                $result = ["code"=>502,"message"=>"删除公文失败"];
            }
        }else{
            $result = ["code"=>501,"message"=>"删除抄送人失败"];
        }
        echo json_encode($result);
    }
}