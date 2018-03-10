<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/9
 * Time: 17:10
 */

class cotentController extends Controller
{
    public function  __construct()
    {
        ob_end_clean();
        //>>验证用户是否登录
        if(!$_SESSION){
            $this->jump('index.php?p=show&c=login&a=index','密码错误',3);
        }
    }
    //>>发文
    public function fawenAction(){

    }
}