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
            //>>获取用户发送的公文数据
            $user_id = self::$userData["id"];
            $gongwenModel = new ModelNew("gw");
            $gongwenData = $gongwenModel->findBySql("select * from `sl_gw` WHERE zuozhe=$user_id ORDER BY dtime DESC ");
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_send_list.html";
        }else{    //>>非管理员
            //>>获取用户发送的公文数据
            $user_id = self::$userData["id"];
            $gongwenModel = new ModelNew("gw");
            $gongwenData = $gongwenModel->findBySql("select * from `sl_gw` WHERE zuozhe=$user_id ORDER BY dtime DESC ");
//            var_dump($gongwenData);exit();
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_send_list.html";
        }
    }
    //>>接收列表
    public function acceptAction(){
        if (self::$userData["guanliyuan"]==1){   //>>管理员
            //>>获取所有公文数据
            $user_id = self::$userData["id"];
            $Model = new ModelNew("fasong");
            $gwDate = $Model->findBySql("select b.* from sl_fasong as a join sl_gw as b on a.gongwen=b.id where a.jieshouren=1 ORDER BY b.dtime");
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_accept_list.html";
        }else{    //>>非管理员
            //>>获取所有公文数据
            $user_id = self::$userData["id"];
            $Model = new ModelNew("fasong");
            $gwDate = $Model->findBySql("select b.* from sl_fasong as a join sl_gw as b on a.gongwen=b.id where a.jieshouren=$user_id ORDER BY b.dtime DESC ");
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_accept_list.html";
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
                //>>获取公文来源
                $model = new ModelNew("gwlxy");
                $laiyuanData = $model->find()->all();
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
                $model = new ModelNew("gw");
                $gwData = $model->findOne($id);
                //>>获取公文来源
                $model = new ModelNew("gwlxy");
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
        $data["leixing"] = $_POST["leixing"]?self::getLeixingIdAction($_POST["leixing"]):null;
        $data["jiezhishijian"] = $_POST["jiezhishijian"]?$_POST["jiezhishijian"]:null;
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
        $model = new ModelNew("gw");
        $data["biaoti"] = $_POST["biaoti"]?$_POST["biaoti"]:null;
        $data["bianhao"] = $_POST["bianhao"]?$_POST["bianhao"]:null;
        $data["leixing"] = $_POST["leixing"]?self::getLeixingIdAction($_POST["leixing"]):null;
        $data["laiyuan"] = $_POST["laiyuan"]?self::getLaiyuanIdAction($_POST["laiyuan"]):null;
        $data["neirong"] = $_POST["neirong"]?$_POST["neirong"]:null;
        $data["zuozhe"] = self::$userData["id"];
        //>>保存图片
        $imageArray = $_FILES["tupian"]?$_FILES["tupian"]:null;
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
            if($rs = $model->insert($data)){
                //>>设置接收数据
                $fsModel = new ModelNew("fasong");
                $array["gongwen"] = $rs;
                $array["fasongren"] = self::$userData["id"];
                $array["jieshouren"] = 1;
                $array["zhuangtai"] = 0;
                $fsModel->insert($array);
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
        //>>获取公文内容
        $model = new ModelNew("gw");
        $gwData = $model->findOne($id);
//        var_dump($gwData);exit();
        //>>判断是否为管理员
        if(self::$userData["guanliyuan"]==1){    //管理员
            //>>获取公文内容
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_send_detail.html";
        }else{     //非管理员
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_send_detail.html";
        }
    }
    //>>接收公文详情
    public function acceptDetailAction(){
        //>>公文id
        $id=$_GET["id"];
        //>>获取公文内容
        $model = new ModelNew("gw");
        $gwData = $model->findOne($id);
//        var_dump($gwData);exit();
        if(self::$userData["guanliyuan"]==1){    //管理员
            //>>获取公文内容
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_admin_accept_detail.html";
        }else{     //非管理员
            include CUR_VIEW_PATH."Sgongwen" . DS . "gongwen_huiyuan_accept_detail.html";
        }
    }
    //>>发送公文
    public function fasongAction(){
        $data["gongwen"] = $_POST["gwId"];
        $data["fasongren"] = self::$userData["id"];
        $data["jieshouren"] = 1;
        $data["zhuangtai"] = 1;
        $model = new ModelNew("fasong");
        $zhuangtai = self::getGwStatusAction(self::$userData["id"],$data["gongwen"]);
        if ($zhuangtai == 0){
            if ($model->where(["gongwen"=>$data["gongwen"]])->where(["fasongren"=>$data["fasongren"]])->where(["jieshouren"=>$data["jieshouren"]])->update($data)){
                echo 200;
            }else{
                echo 500;
            }
        }elseif ($zhuangtai == 1){
            echo 200;
        }
    }
    //>>获取发送公文状态
    public static function getGwStatusAction($fsr,$gwId){
        $model = new ModelNew("fasong");
        $zhuangtai = $model->where(["gongwen"=>$gwId])->where(["fasongren"=>$fsr])->find("zhuangtai")->one();
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
}