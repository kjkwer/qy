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
        $articleGlassifyData = $model->find()->all();
//        var_dump($articleGlassifyData);exit;
        include CUR_VIEW_PATH."Sindex" . DS . "index_index.html";
    }
}