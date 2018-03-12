<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/8
 * Time: 17:21
 */

class LoginController extends Controller
{
    public function  __construct()
    {
        ob_end_clean();

    }

    //>>登录页
    public function indexAction(){
        include CUR_VIEW_PATH."Slogin" . DS . "login_index.html";
    }

    //>>验证登陆信息
    public function checkAction(){
        $model = new ModelNew("yhlb");
        $zhanghao=$_POST['zhanghao'];
        $rs=$model->where(['zhanghao'=>$zhanghao])->find()->one();
        if ($rs){
            $mima=md5($_POST['mima']);
            if ($rs['mima']==$mima){
                if ($rs['status']==2){
                    //>>已离职
                    $this->jump('index.php?p=show&c=login&a=index','已离职，禁止登录',3);
                }
                $_SESSION["user"]['id']=$rs['id'];
                $_SESSION["user"]['zhanghao']=$rs['zhanghao'];
                $_SESSION["user"]['xingming']=$rs['xingming'];
                $_SESSION["user"]['bumen']=$rs['bumen'];
                $_SESSION["user"]['shoujihaoma']=$rs['shoujihaoma'];
                $_SESSION["user"]['zhicheng']=$rs['zhicheng'];
                $_SESSION["user"]['guanliyuan']=$rs['guanliyuan'];
                $this->jump('index.php?p=show&c=admin&a=index','登录成功',3);
            }else{
                $this->jump('index.php?p=show&c=login&a=index','密码错误',3);
            }
        }else{
            $this->jump('index.php?p=show&c=login&a=index','账号不存在',3);
        }
    }
}