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
    public function  __construct()
    {
        ob_end_clean();
        if(!$_SESSION["user"]){
            $this->jump('index.php?p=show&c=login&a=index','请先登录',3);
        }else{
            self::$userData = $_SESSION["user"];
        }
    }

    public function listAction(){
        //>>获取所有文章数据
        $model = new ModelNew("article");
        $articleData = $model->findBySql("select * from `sl_article`");
//        var_dump($articleData);exit();
        //>>获取专栏
        $zhuanlanModel = new ModelNew("wzfl");
        $zhuanlanData = $model->findBySql("select * from `sl_wzfl`");
        include CUR_VIEW_PATH."Sarticle" . DS . "article_list.html";
    }

    public function addArticleAction(){
        $model = new ModelNew('article');
        $data["biaoti"] = $_POST["biaoti"]?$_POST["biaoti"]:null;
        $data["shouye"] = $_POST["shouye"]?$_POST["shouye"]:null;
        $data["neirong"] = $_POST["neirong"]?$_POST["neirong"]:null;
        $data["zhuanlan"] = $_POST["zhuanlan"]?$_POST["zhuanlan"]:null;
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
                $this->jump('index.php?p=show&c=article&a=list','编辑文章成功',3);
            }else{
                $this->jump('index.php?p=show&c=article&a=list','编辑文章失败',3);
            }
        }else{
            if($model->insert($data)){
                $this->jump('index.php?p=show&c=article&a=list','发布文章成功',3);
            }else{
                $this->jump('index.php?p=show&c=article&a=list','发布文章失败',3);
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
        $id = $_GET["id"]?$_GET["id"]:null;
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
}