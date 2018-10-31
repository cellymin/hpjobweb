<?php
/*
 * Describe   : 后台登录
 */
class authControl extends Control {
    private $auth;
    private $user;

    function __construct() {
        parent::__construct();
        $this->auth = O('auth');
    }
    function index() {

        $this->login();
    }

    private function _captcha_check($code) {
        return $this->auth->captcha_check($code);
    }

    function login() {
        if (!$this->auth->is_logged_in()) {
            //验证字段
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if(C('IP_MANAGE_OPEN')==1){
                    $ip_array = explode(',',C('IP_MANAGE'));
                    $isin = in_array($_SERVER["REMOTE_ADDR"],$ip_array);
                    if($isin==false) {
                        $this->error('对不起，您的ip不在允许范围内，请联系管理员');
                    }
                }

                // 如果尝试登录次数大于配置次数设置验证码规则
                if (C('AUTH_CAPTCHA_LOGIN') || $this->auth->is_max_login_attempts_exceeded()) {
                    if (!$this->_captcha_check($_POST['code'])) {
                        $this->error(L('validata_error'), 'index');
                    }
                }

                $username = isset($_POST['name']) ? $_POST['name'] : FALSE;
                $password = isset($_POST['pwd']) ? $_POST['pwd'] : FALSE;
                $remember = isset($_POST['remember']) ? $_POST['remember'] : FALSE;
                if ($this->auth->auth_user_login($username, $password, $remember)) {
                    $this->success('登录成功', __APP__);
                } else {
                    //检查用户无法登录是否因为用户是被禁止的或没有用户
                    if ($this->auth->is_banned()) {
                        $this->error('对不起，你已被禁止登录，禁止原因：'.$this->auth->get_ban_reason(),__WEB__);
                    } else {
                        $this->error($this->auth->error);
                    }
                }
            } else {
                //默认不显示验证码，直到登录错误到了最大值
                $show_captcha = FALSE;
                // 如果登录错误大于配置文件中的最大登录错误次数或默认开启显示验证码则显示验证码
                if ($this->auth->is_max_login_attempts_exceeded() || C('AUTH_CAPTCHA_LOGIN')) {
                    $show_captcha = TRUE;
                }
                //加载登录页面
                $this->assign('show_captcha', $show_captcha);
                $this->display();
            }
        } else {
            $this->success('你已经登录了',__APP__);
        }
    }

    /**
     * 注销登录
     */
    public function logout() {
        setcookie(C('AUTH_AUTOLOGIN_COOKIE_NAME'), '', time() - 100000, '/'); //删除cookie
        $this->session_destroy();
        $this->success('注销成功！', __CONTROL__);
    }

}
