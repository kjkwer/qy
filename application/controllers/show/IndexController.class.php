<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/8
 * Time: 16:48
 */

class IndexController extends Controller
{
    public function  __construct()
    {
        ob_end_clean();
    }


    //>>网站首页
    public function indexAction(){
        $model = new ModelNew('wzfl');
        $articleGlassifyData = $model->find()->limit(0,8)->all();
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
        $article = $model->findOne($id);
        include CUR_VIEW_PATH."Sarticle" . DS . "article_detail.html";
    }
}