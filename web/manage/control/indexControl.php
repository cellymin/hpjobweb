<?php

/*
 * Describe   : 后台首页
 */

class indexControl extends myControl {

    private $backend_menu;

    function __construct() {
        parent::__construct();
        $this->backend_menu = K('backendMenu');
    }

    function index() {
        $menu_list = $this->backend_menu->oneLevelMenu();
        $this->assign('menu_list', $menu_list);
        $this->display();
    }
    function home(){
        $this->display();
    }

    function pinyin(){
        echo string::pinyin($_POST['pinyin']);
        exit;
    }
    
    function update_cache() {
        if (dir::del(PATH_TEMP . '/Applications')) {
            $this->success("缓存目录已经全部删除成功");
        }
    }

    /**
     * @Title: editPassword
     * @Description: todo(修改密码)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editPass(){

        if ($_SERVER['REQUEST_METHOD']=='POST') {

            $password = md5_d($_POST['password']);
            $data = array(
                'password'=>$password,
            );
            if(M('user')->where('uid='.$_SESSION['uid'])->update($data)){
                setcookie(C('AUTH_AUTOLOGIN_COOKIE_NAME'), '', time() - 100000, '/'); //删除cookie
                $this->session_destroy();
                $this->success('修改成功','http://www.hap-job.com/index.php/backend/auth/index');
            }

        }
        $this->display();
    }

}