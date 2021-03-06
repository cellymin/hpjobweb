<?php

/*
 * Describe   : 扩展核心控制器
 */

class myControl extends Control {

    protected $auth;

    public function __construct()
    {
        $this->auth = new auth;
        if (!$this->auth->is_logged_in()) {
            $this->go('auth/index');
        }
        if (!$this->auth->check_uri_permissions()) {
            $this->error($this->auth->error,__APP__);
            die;
        }
        
    }

    function is_logged_in() {
        return $this->auth->is_logged_in();
    }

    /**
     * 成功,失败时做的工作
     * @param type $success $this->success的参数
     * @param type $error
     */
    function success_error($result, $success = array('成功', '', 3), $error = array('失败', '', 3)) {
        $success[1] = isset($success[1]) ? $success[1] : '';
        $success[2] = isset($success[2]) ? $success[2] : 3;
        $error[1] = isset($error[1]) ? $error[1] : '';
        $error[2] = isset($error[2]) ? $error[2] : 3;
        if ($result) {
            $this->success($success[0], $success[1], $success[2]);
        } else {
            $this->error($error[0], $error[1], $error[2]);
        }
    }


}