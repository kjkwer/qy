<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/8
 * Time: 16:48
 */

class IndexController extends Controller
{
    public static $userData = null;
    public function  __construct()
    {
        ob_end_clean();
        if($_SESSION["user"]){
            self::$userData = $_SESSION["user"];
        }
    }


    //>>网站首页
    public function indexAction(){
        $model = new ModelNew('wzfl');
        $articleGlassifyData = $model->where(["type"=>1])->find()->all();
        //>>扶贫专栏
        $articleModel = new ModelNew('article');
        $fpData = $articleModel->where(["zhuanlan"=>17])->limit(0,5)->find()->all();

        include CUR_VIEW_PATH."Sindex" . DS . "index_index.html";
    }

    //>>文章详情
    public function detailAction(){
        $model = new ModelNew('wzfl');
        $articleGlassifyData = $model->find()->limit(0,8)->all();

        $id = $_GET["id"];
        $model = new ModelNew("article");
        //>>增加人气
        $model->query("update sl_article set renqi = renqi+1 WHERE id=$id");
        $article = $model->findOne($id);



        include CUR_VIEW_PATH."Sarticle" . DS . "article_detail.html";
    }

    //>>图文列表
    public function twAction(){
        $model = new ModelNew('wzfl');
        $articleGlassifyData = $model->find()->limit(0,8)->all();

        $wzLxId = $_GET["id"];
        $wzModel = new ModelNew("article");
        $articleData = $wzModel->where(["zhuanlan"=>$wzLxId])->orderBy("id desc")->find()->all();
        include CUR_VIEW_PATH."Sarticle" . DS . "article_tw_list.html";
    }
    //>>文本列表（思想理论、党史故事、入党小提示、报表下载）
    public function wbAction(){
        $model = new ModelNew('wzfl');
        $articleGlassifyData = $model->find()->limit(0,8)->all();

        $wzLxId = $_GET["id"];
        $wzModel = new ModelNew("article");
        $articleData = $wzModel->where(["zhuanlan"=>$wzLxId])->orderBy("id desc")->find()->all();
        include CUR_VIEW_PATH."Sarticle" . DS . "article_wb_list.html";
    }


    //>>通过id获取专栏名称
    public static function getZhuanlanAction($id){
        $model = new ModelNew('wzfl');
        $zhuanlan = $model->where(["id"=>$id])->find("fenleimingcheng")->one();
        return $zhuanlan["fenleimingcheng"];
    }
}