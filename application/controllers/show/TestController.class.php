<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/26
 * Time: 14:47
 */

class TestController extends BaseController
{
    public function indexAction(){
        include CUR_VIEW_PATH."Stest" . DS . "index.html";
    }
}