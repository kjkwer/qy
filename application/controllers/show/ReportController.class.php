<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/27
 * Time: 17:47
 */

class ReportController extends BaseController
{
    public function addAction(){
        $data["name"] = $_POST["name"];
        $data["phone"] = $_POST["phone"];
        $data["mail"] = $_POST["mail"];
        $data["qq"] = $_POST["qq"];
        $data["title"] = $_POST["title"];
        $data["content"] = $_POST["content"];
        var_dump($data);exit();
        $model = new ModelNew("report");
        if ($model->insert($data)){
            echo 200;
        }else{
            echo 500;
        }
    }
}