<?php


class userControl extends Control {

    private $auth;
    private $user;

    function __construct() {

        parent::__construct();

        $this->user = K('user');

        $this->auth = new auth;
    }

    /**
     * @Title: register
     * @Description: todo(注册)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function register(){

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {//判断$_POST的值不能为空

            $data = array(
                'username' => $_POST['phone'],
                'password' => $_POST['password'],
                'code'     => $_POST['code'],
                'is_bind' => 1,
                'nickname' => '开心工作'.rand(10000,99999),
            );

            $code_true = validateSmsCode($_POST['phone'],$_POST['code']);

            if(!$code_true){

                Json_error('验证码错误');
            }

            if (!$this->user->user_model->validate($data)) {

                Json_error($this->user->user_model->error);
            }

            if ($this->user->userExist($_POST['phone'])) {

                Json_error('用户名已存在！');
            }

            $huanxin = openRegister($data['username'],$data['password'],$data['nickname']);

            if($huanxin['error']){

                Json_error('环信注册失败');

            }

            if ($this->auth->register($data)) {//注册成功

                $data['created'] = time();

                $data['last_ip'] = ip_get_client();

                $user = M('user');
                $id = $user->where(array('username'=>$_POST['phone']))->field('uid,username')->find();
                $info=M('user_info');
                $info->insert(array('uid'=>$id['uid'],'create_time'=>time()));
                $_SESSION['point'] = 0;//注册得积分

                $point = getPointRule('newUser');//获得应扣取积分

                deductPoint($point,$id['uid']);//增加积分

                $con = '注册得积分';

                $data = array(
                    'uid' => $id['uid'],
                    'content' => $con,
                    'point' => '+' . $point,
                    'created' => time(),
                    'ip' => ip_get_client(),//操作ip
                    'username' => $id['username'],
                    'time' => time(),
                    'type' => 0
                );

                if(M('opt_log')->insert($data)){

                    Json_success('恭喜你注册成功。');
                }

            }else{

                Json_error('注册失败！请仔细检查你的注册资料。');
            }
        }
    }

    public function login(){

        $tab = M('user');
        $username = $_POST['username'];
        $password = $_POST['password'];
        $user_temp = M('user')->where("username = '$username'")->find();
        if(empty($user_temp)){
            Json_error('用户不存在');
        }
        if ($this->auth->auth_user_login($username,$password)) {
            if (!empty($_POST['client_id'])) {//存入设备id
                $tab->where('uid = ' . $_SESSION['uid'])->update(array('client_id' => $_POST['client_id']));
            }
            $user = $tab->where('uid=' . $_SESSION['uid'])->find();
            if(empty($user_temp['last_login']) && !empty($user['from_id'])){ //是否是被邀请人

                $from_user = M('user')->where('uid = ' . $user['from_id'])->find();
                $share_commission = getPointRule('shareCommission');
                $data = array(
                    'uid'=>$user['from_id'],
                    'content'=>'邀请返现',
                    'commission'=>'+'.$share_commission,
                    'username'=>$from_user['username'],
                    'create_time'=>time(),
                    'from_id'=>$_SESSION['uid'],
                    'type'=>'3'
                );
                M('commission_log')->insert($data);

                $point = getPointRule('bInvite');//获得应扣取积分

                deductPoint($point,$_SESSION['uid']);//增加积分

                $con = '被邀请得积分';

                $data = array(
                    'uid' => $_SESSION['uid'],
                    'content' => $con,
                    'point' => '+' . $point,
                    'created' => time(),
                    'ip' => ip_get_client(),//操作ip
                    'username' => $username,
                    'time' => time(),
                    'type' => 0
                );
                M('opt_log')->insert($data);
            }

            $user['sessionid'] = $_SESSION['sessionid'];
            $_SESSION['rid'] = $_SESSION['role']['rid'];
            $user['rid']=$_SESSION['rid'][0];
            M('user')->where('uid = '.$_SESSION['uid'])->update(array('is_online'=>1));//登陆更改在线状态
            Json_success('登录成功',$user);
        } else {
            Json_error('登录失败'.$this->auth->error);
        }
    }

    public function setPhone(){

        $default_resumes = M('resume')->where(['default'=>1])->findall();

        foreach ($default_resumes as $k=>$default_resume){

            $base_resume = M('resume_basic')->where(['resume_id'=>$default_resume['resume_id']])->find();

            M('user')->where(['uid'=>$default_resume['uid']])->update(['resume_phone'=>$base_resume['telephone']]);
        }
    }

}
