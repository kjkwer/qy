<?php
/**
 * Created by PhpStorm.
 * User: ZYQ
 * Date: 2018/3/8
 * Time: 17:21
 */

class LoginController extends Controller
{
    public static $title = null;//>>网站标题
    public function  __construct()
    {
        ob_end_clean();
        //>>获取网站标题
        $model = new ModelNew("article");
        self::$title = $model->findBySql("select * from sl_wzbt limit 1")[0]["title"];
    }

    //>>登录页
    public function indexAction(){
        if(self::isWapAction()) {
            include CUR_VIEW_PATH."Slogin" . DS . "wap_login_index.html";
        }else{
            include CUR_VIEW_PATH."Slogin" . DS . "login_index.html";
        }
    }

    //>>验证登陆信息
    public function checkAction(){
        $model = new ModelNew("yhlb");
        $zhanghao=$_POST['zhanghao'];
        $rs=$model->where(['zhanghao'=>$zhanghao])->find()->one();
        if ($rs){
            $mima=md5($_POST['mima']);
            if ($rs['mima']==$mima){
                if ($rs['zhuangtai']==2){
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
                $_SESSION["user"]['personnel']=$rs['personnel'];

                $this->jump('index.php?p=show&c=gongwen&a=list','登录成功',3);
            }else{
                $this->jump('index.php?p=show&c=login&a=index','密码错误',3);
            }
        }else{
            $this->jump('index.php?p=show&c=login&a=index','账号不存在',3);
        }
    }
    //>>检测用户是否为手机登录
    public static function isWapAction(){
        // 先检查是否为wap代理，准确度高
        if(!empty($_SERVER['HTTP_VIA'])){
            if(stristr($_SERVER['HTTP_VIA'],"wap")){
                return true;
            }
        }

        // 检查浏览器是否接受 WML.
        elseif(strpos(strtoupper($_SERVER['HTTP_ACCEPT']),"VND.WAP.WML") > 0){
            return true;
        }
        //检查USER_AGENT
        elseif(preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])){
            return true;
        }
        else{
            return false;
        }
    }
}