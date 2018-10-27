<?php


class authControl extends Control {

    private $auth;
    private $user;
    private $app_key='ttouch#kuaile';
    private $client_id='YXA6tfPWgFtsEeWzNxtHA-A9OA';
    private $client_secret='YXA6hhR-xo8PGuE3BAqMfTQlfNAkYLs';
    private $url = "https://a1.easemob.com/ttouch/kuaile";
    function __construct() {
        parent::__construct();
        $this->auth = new auth;
        $this->user = K('user');

    }
    private function curl($url, $data, $header = false, $method = "POST"){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $ret = curl_exec($ch);
        return $ret;
    }


    function index() {
        $this->login();
    }

    private function _captcha_check($code) {
        return $this->auth->captcha_check($code);
    }

    /**
     * @Title: login
     * @Description: todo(登录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function login(){
            //验证字段
            $tab = M('user');
            $username = $_POST['username'];
            $password = $_POST['password'];
            $user_temp = M('user')->where("username = '$username'")->find();
            if(empty($user_temp)){
                Json_error('用户不存在');
            }
            if ($this->auth->auth_user_login($username,$password)) {
                if (!empty($_POST['client_id'])) {//存入设备id
                    $tab->where('uid = ' . $_SESSION['uid'])->update(['client_id' => $_POST['client_id'],'lng'=>$_POST['lng'],'lat'=>$_POST['lat']]);
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
                M('user')->where('uid = '.$_SESSION['uid'])->update(array('is_online'=>1,'last_login'=>time()));//登陆更改在线状态
                    Json_success('登录成功',$user);
            } else {
                Json_error('登录失败'.$this->auth->error);
            }
    }

    /**
     * @Title: get_address
     * @Description: todo(添加经纬度)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function get_address(){
        if ($this->auth->is_logged_in()) {
            $db=M('user');
            $uid=$_SESSION['uid'];
            $data=array(
                'lng'=>$_POST['lng'],
                'lat'=>$_POST['lat'],
                'login_time'=>time()
            );
            if($db->where('uid = '.$uid)->update($data)){
                Json_success('添加成功',$data);
            }else{
                Json_error('添加失败');
            }
        }else{
            Json_error('未登录');
        }
    }


    /**
     * @Title: register
     * @Description: todo(注册)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {//判断$_POST的值不能为空
            $data = array(
                'username' => $_POST['phone'],
                'password' => $_POST['password'],
                'code'     => $_POST['code'],
                'type'     => empty($_POST['type'])?'app':$_POST['type'],
                'is_bind' => 1,
                'is_yewu' => 1,
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

            $huanxin = $this->openRegister($data['username'],$data['password'],$data['nickname']);

            if($huanxin['error']){

                Json_error('环信注册失败');

            }

            if ($this->auth->register($data)) {//注册成功
                $data['created'] = time();
                $data['last_ip'] = ip_get_client();

                $a=M('user');
                $id=$a->where(array('username'=>$_POST['phone']))->field('uid,username')->find();
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

    /**
     * @Title: addUser
     * @Description: todo(环信注册)
     * @author liuzhipeng
     * @return  mixed  返回类型
     */
    private function openRegister($username,$password,$nickname) {
        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $token = $rs['access_token'];
        $url=$this->url.'/users';
        $username_str = substr($username,0,7);
        $arr=array(
            'username'=>$username,
            'password'=>$password,
            'nickname'=> empty($nickname) ? '开心工作'.rand(10000,99999) : $nickname,
        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, $arr, $header, "POST"),true);
    }

    public function getUser(){
        var_dump($this->getHuanXinUser($_POST['username']));
    }

    private function getHuanXinUser($username){
        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $token = $rs['access_token'];
        $url=$this->url.'/users/'.$username;

        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, '', $header, "GET"),true);
    }



    /**
     * Ajax查找用户是否存在
     * @param type $username 
     */
    function userExist() {
        $username = $_POST['name'];
        if ($this->user->userExist($username)) {
            echo 1;
            exit;
        }
        echo 0;
        exit;
    }


    /*
     * 发送验证码
     */
    function sms()
    {
        $mobile = $_POST['phone'];
        $type = $_POST['type'];
        $user = M('user')->where(['username'=>$mobile])->find();

        $con = '';

        switch ($type)
        {
            case 1:
                if ($this->user->userExist($_POST['phone'])) {
                    Json_error('用户名已存在！');
                }
                $con = '注册';
                break;
            case 2:
                if(!empty($user['phone'])){

                    Json_error('第三方登录无法修改密码哦~');
                }
                if($this->user->userExist($_POST['phone'])){
                    $con='忘记密码';
                }else{
                    Json_error('用户名不存在！');
                }
                break;
            case 3:
                if($user['is_bind']==2){
                    Json_error('请绑定手机号码');
                }
                $db = M('user_info');
                $ta = M('staff_salary');
                $re = $db->where(array('uid'=>$user['uid']))->find();
                if(!empty($re['id_number'])){
                    $id_number=$re['id_number'];
                    $sa = $ta->where(array('id_number'=>$id_number))->find();
                    if($mobile!='13601511443'){

                        if ($sa['time'] > strtotime(date('Y-m-d')) && $sa['query_number'] > 2) {//在今天之内

                            Json_error('今天查询次数超过三次了');
                        }
                    }

                    $con='查询工资';
                }else{
                    Json_error('请绑定身份证');
                }
                break;
            case 4:
                if($user['is_bind']==2){
                    Json_error('请绑定手机号码');
                }
                $con='提现';
                break;
            case 5:
                if ($this->user->userExist($_POST['phone'])) {
                    Json_error('该手机号码已绑定！');
                }
        }

        sendSmsMsg($mobile,$con,'',$type);

        Json_success('发送成功',$_SESSION['smscode']);
    }

    /**
     *  验证码
     *
     */
    function validateCode() {
        $code = new code();
        $code->show();
    }

    /**
     * @Title: logout
     * @Description: todo(注销登录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function logout(){
        if(empty($_SESSION['uid'])){
            M('user')->where('uid = '.$_POST['uid'])->update(array('is_online'=>0,'client_id'=>'','lng'=>'','lat'=>''));//注销登陆更改在线状态
        }else{
            M('user')->where('uid = '.$_SESSION['uid'])->update(array('is_online'=>0,'client_id'=>'','lng'=>'','lat'=>''));//注销登陆更改在线状态

        }
        setcookie(C('AUTH_AUTOLOGIN_COOKIE_NAME'), '', time() - 100000, '/'); //删除cookie
        $this->session_destroy();
            Json_success('注销成功');

    }

    /**
     * @Title: uploadImg
     * @Description: todo(公共上传图片)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function uploadImg(){
        C('UPLOAD_IMG_DIR','');
        C('THUMB_ENDFIX','');//只生成头像缩略图
        $upload=new upload(PATH_ROOT.'/uploads/user_info',array('jpg','jpeg','png','gif'));
        $info=$upload->upload();
        if($info){
            $avatar=$info[0]['path'];
            Json_success('上传成功',$avatar);
        }else{
            Json_error('上传失败');
        }

    }
    /**
     * 查找用户积分
     * @param type $uid
     */
    private function _getUserPoint($uid) {
        $db = M('user_point');
        $point = $db->where('uid= ' . $uid)->find();
        return $point['point'];
    }

    /**
     * @Title: getPoint
     * @Description: todo(获取用户积分)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function getPoint(){
        $_SESSION['point'] = $this->_getUserPoint($_SESSION['uid']);
        Json_success('获取成功',$_SESSION['point']);
    }


    /**
     * @Title: find_password
     * @Description: todo(忘记密码)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function find_password()
    {
        $username = $_POST['phone'];

        $code = $_POST['code'];

        $code_true = validateSmsCode($username,$code);

        if(!$code_true){

            Json_error('验证码错误');
        }

        $user = M('user')->where("username = '$username'")->find();

        if(empty($user)){

            Json_error('用户不存在');
        }else{
            if(!empty($user['phone'])){

                Json_error('第三方登录无法做忘记密码操作哦~');
            }
            if(M('user')->where('username=' . $username)->update(array('password'=>md5_d($_POST['password'])))){
                session_destroy();
                Json_success('密码修改成功，请重新登录。');
            }
        }
    }

    /**
     * @Title: editPassword
     * @Description: todo(新版修改密码)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editPassword(){

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $username=$_POST['username'];

            $code = $_POST['code'];

            $code_true = validateSmsCode($username,$code);

            if(!$code_true){

                Json_error('验证码错误');
            }

            $password = $_POST['password'];

            $db = M('user');

            $userinfo = $db->where(array('username'=>$username))->find();

            if(empty($userinfo)){

                Json_error('用户不存在');
            }

            if(empty($userinfo['phone'])){

                $huanxinResult = $this->modifyHuanXinPassword($username,$password);

                if(!empty($huanxinResult['error'])){

                    Json_error('修改失败');
                }

            }else{//修改环信

                if(($db->where('uid='.$userinfo['uid'])->update(array('password'=>md5_d($password)))>=0)){

                    session_destroy();
                    Json_success('密码修改成功，请重新登录。');
                }else{

                    Json_success('修改失败le');
                }
            }

            if(($db->where('uid='.$userinfo['uid'])->update(array('password'=>md5_d($password)))>=0)){

                session_destroy();
                Json_success('密码修改成功，请重新登录。');
            }else{

                Json_error('修改失败了');
            }
        }
    }

    /**
     * @Title: password
     * @Description: todo(修改密码)
     * @author liuzhipeng
     * @return  void  返回类型
     */
        public function password(){

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                $username=$_POST['username'];

                $data['newpassword'] = $_POST['pwd'];

                $db=M('user');

                $userinfo=$db->where(array('username'=>$username))->find();

                if(empty($userinfo)){
                    Json_error('用户不存在');
                }
                if($userinfo['password']!=md5_d($_POST['old_pwd'])){
                    Json_error('原始密码错误，修改用户密码失败。');
                }

                if(empty($userinfo['phone'])){

                    $huanxinResult = $this->modifyHuanXinPassword($username,$_POST['pwd']);

                    if(!empty($huanxinResult['error'])){
                        Json_error('修改失败');
                    }
                }else{//修改环信
                    if(($db->where('uid='.$userinfo['uid'])->update(array('password'=>md5_d($_POST['pwd'])))>=0)){
                        session_destroy();
                        Json_success('密码修改成功，请重新登录。');
                    }else{
                        Json_success('修改失败le');
                    }
                }

                if(($db->where('uid='.$userinfo['uid'])->update(array('password'=>md5_d($_POST['pwd'])))>=0)){
                    session_destroy();
                    Json_success('密码修改成功，请重新登录。');
                }else{
                    Json_success('修改失败le');
                }
            }
        }

        private function modifyHuanXinPassword($username,$newpassword){

            $url = $this->url . "/token";
            $data = array(
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            );
            $rs = json_decode($this->curl($url, $data), true);
            $token = $rs['access_token'];
            $url = $this->url . "/users/".$username."/password";
            $header = array(
                'Authorization: Bearer ' . $token
            );
            $arr=array(
                'newpassword'=>$newpassword,
            );
            return json_decode($this->curl($url, $arr, $header,"PUT"),true);
        }

    /**
     * @Title: have_login
     * @Description: todo(是否登陆过)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function have_login(){
        $authid_qq = $_POST['authid_qq'];
        if(empty($authid_qq)){
            Json_error('请传参数');
        }
        $user = M('user')->where(array('authid_qq'=>$authid_qq))->find();

        if(empty($user)){
            Json_success('没登陆过',array('have_login'=>2));
        }else{
            Json_success('登陆过',array('have_login'=>1));
        }
    }




    /**
     * @Title: authLogin
     * @Description: todo(第三方登录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function authLogin()
    {
        $db = M('user');

        $data = array(
            'username'=>$_POST['username'],
            'authid_qq' => $_POST['authid'],
            'nickname' => $_POST['nickname'],
            'authtype' => $_POST['authtype'],//1qq2微信3微博
            'password' => '123456',
            'avatar' => $_POST['avatar'],
            'client_id' => empty($_POST['client_id'])?'':$_POST['client_id'],
            'unionid' => empty($_POST['unionid'])?'':$_POST['unionid']
        );

        if(!empty($data['unionid'])){

            $auth = $db->where(['unionid'=>$data['unionid']])->find();
        }else{

            $auth = $db->where(['authid_qq'=>$data['authid_qq']])->find();
        }

        if (empty($auth)) {//未登陆过，执行注册程序
            $data['created'] = time();
            $data['last_ip'] = ip_get_client();
            $data['last_login'] = time();

            $data['is_newuser'] = 1;

//                $username = $data['username'];

            if ($this->user->userExist($data['username']) &&!empty($_POST['username'])) {
                Json_error('手机号已被绑定');
            }
            if(empty($data['username'])){
                $data['username'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

                $data['phone'] = $data['username'];
                $data['is_bind'] = 2;//如果没传就不绑定
            }else{
                $data['phone'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $data['is_bind'] = 1;//如果传了手机号码就绑定
            }
            $huanxin = $this->openRegister($data['phone'], '123456',$data['nickname']);

            if ($huanxin['error']) {

                Json_error('环信注册失败');

            }

            $data['type'] = empty($_POST['type'])?'app':$_POST['type'];

            if($this->auth->register($data)) {

                $authid = $data['authid_qq'];
                $password = '123456';
                if ($this->auth->auth_user($authid, $password)) {
                    $user = $db->where('uid=' . $_SESSION['uid'])->find();
                    $user['sessionid'] = session_id();
                    $_SESSION['rid'] = $_SESSION['role']['rid'];
                    $user['rid'] = $_SESSION['rid'][0];
                    $_SESSION['point'] = 0;//注册得积分

                    $point = getPointRule('newUser');//获得应扣取积分

                    deductPoint($point, $user['uid']);//增加积分

                    $con = '注册得积分';

                    $data = array(
                        'uid' => $user['uid'],
                        'content' => $con,
                        'point' => '+' . $point,
                        'created' => time(),
                        'ip' => ip_get_client(),//操作ip
                        'username' => $user['username'],
                        'time' => time(),
                        'type' => 0
                    );
                    M('opt_log')->insert($data);
                    $info=M('user_info');
                    $info->insert(array('uid'=>$user['uid'],'create_time'=>time()));
                    M('user')->where(array('uid'=>$user['uid']))->update(array('is_online'=>1));//登陆更改在线状态
                    Json_success('登录成功', $user);
                } else {
                    Json_error('登陆失败');
                }
            }else{
                Json_error('注册失败');
            }
        } else {//已经登陆过，执行登陆程序

            if(!empty($data['unionid'])){

                $authid = $data['unionid'];
            }else{

                $authid = $data['authid_qq'];
            }

            $password = 123456;
            if ($this->auth->auth_user($authid, $password)) {//使用第三方指定登陆接口
                $user = $db->where('uid = ' . $_SESSION['uid'])->find();
                $user['sessionid'] = session_id();
                $_SESSION['rid'] = $_SESSION['role']['rid'];
                $user['rid']=$_SESSION['rid'][0];
                M('user')->where('uid = '.$_SESSION['uid'])->update(array('is_online'=>1,'client_id'=>$data['client_id'],'lng'=>$_POST['lng'],'lat'=>$_POST['lat'],'last_login'=>time()));//登陆更改在线状态
                Json_success('登录成功', $user);
            }else{
                Json_error('登录失败');
            }
        }


    }

    /**
     * @Title: binding
     * @Description: todo(绑定手机号码)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function binding(){
        $uid = empty($_POST['uid']) ? 0 : $_POST['uid'];

        $username = $_POST['username'];

        $code_true = validateSmsCode($username,$_POST['code']);

        if(!$code_true){

            Json_error('验证码错误');
        }

        if(empty($uid) || empty($username)){
            Json_error('绑定失败');
        }

        /*if ($this->user->userExist($username)) {
            Json_error('手机号已被绑定');
        }*/

        if(M('user')->where(array('uid'=>$uid))->update(array('username'=>$username,'is_bind'=>'1'))){
            $this->updateUser($uid,$username);
            Json_success('绑定成功');
        }else{

            Json_error('绑定失败');
        }


    }

    /**
     * @Title: unbind
     * @Description: todo(解绑)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function unbind(){

        $uid = empty($_POST['uid']) ? 0 : $_POST['uid'];

        $db = M('user');

        $user = $db->where(array('uid'=>$uid))->find();

        if(empty($user['phone'])){

            Json_error('您的账号不需要解绑');
        }

        $code_true = validateSmsCode($user['phone'],$_POST['code']);

        if(!$code_true){

            Json_error('验证码错误');
        }

        if(empty($uid)){
            Json_error('解 绑 失 败');
        }

        $username = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        if($db->where(array('uid'=>$uid))->update(array('username'=>$user['phone'],'is_bind'=>'2'))){

            $this->updateUser($uid,$username);
            Json_success('解绑成功',$user['phone']);
        }else{

            Json_error('解绑失败');
        }


    }

    /**
     * @Title: updateUser
     * @Description: todo(绑定(解绑)时修改其他表的用户名字段)
     * @author liuzhipeng
     * @param $uid
     * @param $username
     * @return  void  返回类型
     */
    private function updateUser($uid,$username){

        M('commission_log')->where(['uid'=>$uid])->update(array('username'=>$username));//修改佣金记录

        M('deliver')->where(['uid'=>$uid])->update(array('username'=>$username));//修改投递记录

        M('group')->where(['uid'=>$uid])->update(array('grouper'=>$username));//修改群主

        M('opt_log')->where(['uid'=>$uid])->update(array('username'=>$username));//修改积分记录

        M('sns')->where(['uid'=>$uid])->update(array('username'=>$username));//修改帖子用户名

        M('sns_comment')->where(['uid'=>$uid])->update(array('username'=>$username));//修改评论用户名

        M('sns_comment')->where(['buid'=>$uid])->update(array('busername'=>$username));//修改评论用户名

        M('sns_report')->where(['uid'=>$uid])->update(array('username'=>$username));//修改回复用户名

        M('sns_report')->where(['buid'=>$uid])->update(array('busername'=>$username));//修改回复用户名
    }

    /**
     * @Title: editPerson
     * @Description: todo(修改个人信息)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function editPerson(){
        if ($this->auth->is_logged_in()) {
            $db=M('user');
            $condition = 'uid = ' . $_SESSION['uid'];
            if(!empty($_POST['nickname'])) {

                $user = $db->where(['uid'=>$_SESSION['uid']])->find();

//                $sensitive = M('linkage')->where('lcgid=26')->findall();
//                foreach ($sensitive as $key=>$value){
////                    if(strpos($_POST['nickname'],$value['title']) !== false){     //使用绝对等于
//                        Json_error('昵称中含有敏感词，请重新输入');
////                    }
//                }
                $db->where($condition)->update(array('nickname' => $_POST['nickname']));
                M('sns')->where($condition)->update(array('uname'=>$_POST['nickname']));
                $this->user->fillInfo($_SESSION['uid'],6);//加积分

                $this->editNicknameByHuanxin($user['username'],$_POST['nickname']);//修改环信昵称
            }

            if(!empty($_POST['hometown'])){

                $db->where($condition)->update(array('hometown'=>$_POST['hometown']));
                $this->user->fillInfo($_SESSION['uid'],2);//加积分
            }
            if(!empty($_POST['birthday'])){

                $db->where($condition)->update(array('birthday'=>$_POST['birthday']));
                $this->user->fillInfo($_SESSION['uid'],3);//加积分
            }
            if(isset($_POST['gender'])){

                $db->where($condition)->update(array('gender'=>$_POST['gender']));
                $this->user->fillInfo($_SESSION['uid'],4);//加积分
            }
            if(!empty($_POST['address'])){

                $db->where($condition)->update(array('address'=>$_POST['address']));
                $this->user->fillInfo($_SESSION['uid'],5);//加积分
            }
            if(!empty($_POST['desc'])){

                $db->where($condition)->update(array('desc'=>$_POST['desc']));
            }

            C('UPLOAD_IMG_DIR','');
            C('THUMB_ENDFIX','');//只生成头像缩略图
            $upload=new upload(PATH_ROOT.'/uploads/user_info',array('jpg','jpeg','png','gif'));
            $info=$upload->upload();
            if($info){
                $avatar=$info[0]['path'];
            }
            if(!empty($info)){
                if ($db->where($condition)->update(array('avatar'=>__ROOT__ .'/'.$avatar))) {
                    $this->user->fillInfo($_SESSION['uid'],1);//加积分
                } else {
                    Json_error('上传失败');
                }

            }
//            return array(C('DEBUG')=>1);

            $data=array(
                'name'=>$_POST['nickname'],
                'gender'=>$_POST['gender'],
                'hometown'=>$_POST['hometown'],
                'birthday'=>$_POST['birthday'],
                'address'=>$_POST['address'],
                'avatar'=>$avatar
            );
            Json_success('修改成功',$data);
        }else{
            Json_error('你还没有登录');
        }
    }

    /**
     * @Title: getPerson
     * @Description: todo(获取个人信息)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function getPerson(){
        $db=M('user');
        if(isset($_POST['username'])){


            $re=$db->where('username = '.$_POST['username'].' or phone = '.$_POST['username'])->field('uid,username,nickname,gender,avatar,hometown,birthday,address,is_bind')->find();
            if(!empty($re)){

                Json_success('获取成功',$re);
            }else{
                Json_error('参数错误');
            }
        }

            $uid=$_SESSION['uid'];
            $point = M('user_point')->where('uid = '.$uid)->field('point')->find();
            $re=$db->where('uid = '.$uid)->field('uid,gender,username,nickname,avatar,hometown,birthday,address,is_bind')->find();
            $re['phone']=$_SESSION['username'];
            $re['point']=$point['point'];
            if(!empty($re)){
                Json_success('获取成功',$re);
            }else{
                Json_error('空');
            }

    }

    /**
     * @Title: sign
     * @Description: todo(签到)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function sign(){
        if ($this->auth->is_logged_in()) {
            if($_SESSION['role']['rid'][0]==7){   //业务员
                if(empty($_POST['address'])){
                    Json_error('签到失败,你还没有打开定位功能,请开启定位功能!');
                }
                $con=$_POST['address'];
                if(empty($_POST['lng']) || empty($_POST['lat'])){
                    Json_error('未拾取到您的坐标');
                }
                $point=0;

                deductPoint($point);

                $data=array(
                    'uid'=>$_SESSION['uid'],
                    'content'=>$con,
                    'created'=>time(),
                    'ip'=>ip_get_client(),//操作ip
                    'username'=>$_SESSION['username'],
                    'lng'=>$_POST['lng'],
                    'lat'=>$_POST['lat'],
                    'time'=>time(),
                    'type'=>1
                );

                $db=M('opt_log');

                if($db->insert($data)) {
                    Json_success('签到成功');
                }

            }else{ //不是业务员
                $log = M('opt_log')->field('time')->where(array('uid'=>$_SESSION['uid'],'type'=>1))->order('time desc')->find();

                if($log['time']<strtotime(date('Y-m-d'))){//每天登录+积分

                    $_SESSION['point'] = $this->_getUserPoint($_SESSION['uid']);

                    $point=getPointRule('firstLogin');//获得应扣取积分

                    deductPoint($point);//增加积分

                    $con='签到';

                    $data=array(
                        'uid'=>$_SESSION['uid'],
                        'content'=>$con,
                        'point'=> '+'.$point,
                        'created'=>time(),
                        'ip'=>ip_get_client(),//操作ip
                        'username'=>$_SESSION['username'],
                        'time'=>time(),
                        'type'=>1
                    );

                    $db=M('opt_log');

                    if($db->insert($data)) {
                        Json_success('签到成功');
                    }

                }else{
                    Json_error('今日已签到！');
                }
            }

        }else{
            Json_error('未登录');
        }
    }


    /**
     * @Title: is_sign
     * @Description: todo(判断是否签到)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function is_sign(){

        $log = M('opt_log')->field('time')->where(array('uid'=>$_SESSION['uid'],'type'=>1))->order('time desc')->find();

        if($_SESSION['role']['rid'][0]==7) {
            Json_success('业务员无签到限制');
        }else{
            if ($log['time'] < strtotime(date('Y-m-d'))) {
                Json_success('今日未签到');
            } else {
                Json_error('今日已签到！');
            }
        }
    }


    /**
     * @Title: sign_record
     * @Description: todo(签到记录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function sign_record(){
        if ($this->auth->is_logged_in()) {
            if($_SESSION['role']['rid']==7) {
                $db = M('opt_log');
                $cond = 'uid =' . $_SESSION['uid'].' AND type = 1 AND created >= '.(time()-60*60*24*60);

                $record['content'] = $db->where($cond)->field('content')->order('time desc')->findall();

                if (!empty($record['content'])) {
                    Json_success('获取成功(业务员)', $record);
                } else {
                    Json_error('你还没有签到过，去签到吧！');
                }

            }else{
                $db = M('opt_log');

                $cond = 'uid = '. $_SESSION['uid'].' AND type = 1 AND created >= '.(time()-60*60*24*60);

                $record['content'] = $db->where($cond)->field('uid,username,content,point,time')->order('time desc')->findall();

                if (!empty($record['content'])) {
                    Json_success('获取成功(求职者)', $record);
                } else {
                    Json_error('你还没有签到过，去签到吧！');
                }
            }

        }else{
            Json_error('未登录');
        }
    }



    /**
     * @Title: point_record
     * @Description: todo(积分记录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function point_record(){
        if ($this->auth->is_logged_in()) {
            $db = M('opt_log');
            $cond = 'uid =' . $_SESSION['uid'].' AND created >= '.(time()-60*60*24*30);
            $record['content'] = $db->where($cond)->field('uid,username,content,point,time,type')->order('time desc')->findall();
            $_SESSION['point'] = $this->_getUserPoint($_SESSION['uid']);
            $record['user_point']=$_SESSION['point'];
            if (!empty($record['content'])) {
                Json_success('获取成功', $record);
            }else{
                Json_error('暂无数据');
            }
        }else{
            Json_error('未登录');
        }
    }

    /**
     * @Title: startImg
     * @Description: todo(启动图)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function startImg(){
        $db=M('ads');
        $now = time();

        $img=$db->where("cate = 14 and starttime <$now and endtime >$now")->field('ads_title,href,path')->find();

        if(empty($img)){

            Json_success('获取成功',['ads_title'=>'','href'=>'','path'=>'']);
        }

        Json_success('获取成功',$img);
    }

    /**
     * @Title: ads
     * @Description: todo(APP首页轮播广告)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function ads(){
        $db=M('ads');
        $now = time();
        if($img=$db->where("cate = 1 and starttime <$now and endtime >$now")->field('ads_title,href,path')->order('sort desc')->findall()){
            Json_success('获取成功',$img);
        }else{
            Json_error('获取失败');
        }
    }





    /**
     * @Title: user_commission
     * @Description: todo(判断是否认证)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function identification(){
        if ($this->auth->is_logged_in()) {
            $db=M('user_info');

            $point = M('user_point')->where('uid = '.$_POST['uid'])->field('point')->find();

            $re=M('user_info')->where('uid = '.$_POST['uid'])->find();

            $re['point'] = $point['point'];

            if($re['verify']==0){
               Json_success('未认证',$re);
            }elseif($re['verify']==1){
                Json_success('已认证,请等待审核',$re);
            }elseif($re['verify']==2) {
                Json_success('审核未通过',$re);
            }elseif($re['verify']==3){

                Json_success('已审核',$re);
            }
        }else{
            Json_error('未登录');
        }
    }

    /**
     * @Title: uploadForApprove
     * @Description: todo(app认证传图片)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function uploadForApprove(){

        $type = empty($_POST['type'])?$_GET['type']:$_POST['type'];

        C('UPLOAD_IMG_DIR','');

        C('THUMB_ENDFIX','');//只生成头像缩略图

        $upload=new upload(PATH_ROOT.'/uploads/user_info',array('jpg','jpeg','png','gif'));

        $info=$upload->upload();

        if($info){

            $avatar=$info[0]['path'];

            $prefix = 'http://120.55.165.117/';

            switch ($type)
            {
                case 1://身份审核第一步人脸
                    M('user_info')->where(['uid'=>$_SESSION['uid']])->update(['photo'=>$prefix.$avatar]);
                    break;
                case 2://身份证脸面
                    M('user_info')->where(['uid'=>$_SESSION['uid']])->update(['face_base'=>$prefix.$avatar]);
                    break;
                case 3://身份证国徽面
                    M('user_info')->where(['uid'=>$_SESSION['uid']])->update(['back_base'=>$prefix.$avatar]);
                    break;
            }

            Json_success('上传成功',$avatar);
        }else{

            Json_error('上传失败');
        }

    }


    /**
     * @Title: uploadBase64
     * @Description: todo(base64上传图片)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function uploadBase64(){

        $type = $_POST['type'];

        $base64 = $_POST['base64'];

        $format = $_POST['format'];

        if(!empty($base64) && $base64 != 'null'){

            $img = base64_decode($base64);

            $name = random(32);

            if(!empty($base64)&&!empty($format)){

                $upload = file_put_contents('/usr/share/nginx/html/hpjobweb/uploads/user_info/'.$name.'.'.$format, $img);

            }

            if($upload){

                $prefix = 'http://120.55.165.117/';

                $file = $name.'.'.$format;

                switch ($type)
                {
                    case 1://身份审核第一步人脸
                        M('user_info')->where(['uid'=>$_SESSION['uid']])->update(['photo'=>$prefix.'uploads/user_info/'.$file]);
                        break;
                    case 2://身份证脸面
                        M('user_info')->where(['uid'=>$_SESSION['uid']])->update(['face_base'=>$prefix.'uploads/user_info/'.$file]);
                        break;
                    case 3://身份证国徽面
                        M('user_info')->where(['uid'=>$_SESSION['uid']])->update(['back_base'=>$prefix.'uploads/user_info/'.$file]);
                        break;
                }

                Json_success('success','uploads/user_info/'.$name.'.'.$format);
            }

        }else{

            Json_success('success','');
        }
    }

    /**
     * @Title: approve
     * @Description: todo(认证)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function approve(){

        set_time_limit(180);
        if ($this->auth->is_logged_in()) {

            ini_set('memory_limit', '-1');

            $avatar = '';

            $db=M('user_info');
            if(!empty($_POST['id_number'])){

                $id=$db->where(array('id_number'=>$_POST['id_number']))->field('uid')->findall();
                foreach($id as $key=>$val){
                    if($val['uid'] != $_SESSION['uid']){
                        Json_error('身份证已和其他账号关联');
                    }
                }
            }

                C('UPLOAD_IMG_DIR','');
                C('THUMB_ENDFIX','');//只生成头像缩略图
                $upload=new upload(PATH_ROOT.'/uploads/user_info',array('jpg','jpeg','png','gif'));

                $info=$upload->upload();

                if($info){
                    $avatar=$info[0]['path'];
                }

                if(empty($info)){

                    /**
                     * 新版本 新增身份证正反面识别  2017-02-14
                     */
                    if(!empty($_POST['face_base'])&&!empty($_POST['back_base'])){

                        $href = '/usr/share/nginx/html/hpjobweb';

                        $data = validateIdCardImg(base64_encode(file_get_contents($href.'/'.$_POST['face_base'])),base64_encode(file_get_contents($href.'/'.$_POST['back_base'])));

//                        $data = validateIdCardImg($_POST['face_base'],$_POST['back_base']);
                        if(empty($data[0]['num'])){

                            Json_error('未识别身份证,请重新上传');
                        }else{

                            $id=$db->where(array('id_number'=>$data[0]['num']))->field('uid')->findall();

                            foreach($id as $key=>$val){

                                if($val['uid'] != $_SESSION['uid']){

                                    Json_error('身份证已和其他账号关联');
                                }
                            }

                            if($data[0]['sex'] == '男'){

                                $sex = 0;
                            }else{

                                $sex = 1;
                            }

                            $db->where(['uid'=>$_SESSION['uid']])->update(['gender'=>$sex,'name'=>$data[0]['name'],'verify'=>1,'id_number'=>$data[0]['num'],'card_address'=>$data[0]['address'],'card_start_time'=>$data[1]['start_date'],'card_end_time'=>$data[1]['end_date'],'apply_time'=>time()]);

                            Json_success('身份证识别已通过,请等待管理员审核');
                        }
                    }else{

                        Json_error('请完整上传正反面');
                    }

                }else {

                    $data=array(
                        'name'=>$_POST['name'],
                        'gender'=>$_POST['gender'],
                        'id_number'=>$_POST['id_number'],
                        'photo'=>__ROOT__ .'/'.$avatar,
                        'verify'=>1,
                        'apply_time'=>time()
                    );
                }

            if($db->where(array('uid'=>$_SESSION['uid']))->update($data)){
                $log = M('opt_log')->where('uid = '.$_SESSION['uid'])->find();
                if(empty($log)){
                    $_SESSION['point'] = $this->_getUserPoint($_SESSION['uid']);//填写身份证获积分

                    $point = getPointRule('idCard');//获得应扣取积分

                    deductPoint($point);//增加积分

                    $con = '身份证认证';

                    $data = array(
                        'uid' => $_SESSION['uid'],
                        'content' => $con,
                        'point' => '+' . $point,
                        'created' => time(),
                        'ip' => ip_get_client(),//操作ip
                        'username' => $_SESSION['username'],
                        'time' => time(),
                        'type' => 0
                    );

                    M('opt_log')->insert($data);
                }
                Json_success('已认证，正在审核中...');
            }

        }else{
            Json_error('未登录');
        }

    }

    /**
     * @Title: user_commission
     * @Description: todo(用户佣金余额)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function user_commission(){
        if ($this->auth->is_logged_in()) {
            $db=M('user');
            $id_number = M('user_info')->where('uid = '.$_SESSION['uid'])->find();
            $log = M('commission_into')->where(array('id_number'=>$id_number['id_number'],'type'=>1))->findall();
            if(!empty($log)){
                foreach($log as $key=>$val){
                    $array = array(
                        'uid'=>$_SESSION['uid'],
                        'content'=>$val['content'],
                        'commission'=>'+'.$val['commission'],
                        'create_time'=>strtotime($val['job_time']),
                        'company'=>$val['company_name'],
                        'id_number'=>$val['id_number'],
                        'username'=>$_SESSION['username'],
//                        'create_time'=>time(),
                        'ip'=>ip_get_client(),
                        'type'=>2,
                    );
                    M('commission_log')->insert($array);
                    $commission = M('user')->where(array('uid' => $_SESSION['uid']))->find();
                    $cut = $commission['commission'] + $val['commission'];
                    M('commission_into')->where(array('id_number'=>$id_number['id_number']))->update(array('type'=>2));
                    M('user')->where (array('uid'=>$_SESSION['uid']))->update (array ('commission' => $cut));

                }
            }

            if($re=$db->where('uid = '.$_SESSION['uid'])->field('commission')->find()){
                Json_success('获取成功',$re);
            }

        }else{
            Json_error('未登录');
        }
    }

    /**
     * @Title: bank_info
     * @Description: todo(获取银行信息)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function bank_info(){
        if ($this->auth->is_logged_in()) {
            $db=M('commission_withdrawal');
            $bank_info=$db->where('uid = '.$_SESSION['uid'])->field('bank_account,bank')->find();
            $name = M('user_info')->where('uid = '.$_SESSION['uid'])->find();
            $bank_info['account_name'] = $name['name'];
            Json_success('获取成功',$bank_info);
        }else{
            Json_error('未登录');
        }
    }

    /**
     * @Title: withdraw
     * @Description: todo(提现)
     * @author liuzhipeng
     * @return  void  返回类型
     */
        public function withdraw()
        {
        if ($this->auth->is_logged_in()) {

            $code_true = validateSmsCode($_POST['phone'],$_POST['code']);

            if(!$code_true){

                Json_error('验证码错误');
            }

            $db = M('commission_withdrawal');
            $re = $db->where('uid = ' . $_SESSION['uid'])->field('bank_account,account_name,bank')->find();
            $commission = M('user')->where('uid = ' .$_SESSION['uid'])->field('commission')->find();

            if (empty($re)) {
                $data = array(
                    'uid' => $_SESSION['uid'],
                    'amount' => $_POST['amount'],
                    'type' => $_POST['type'],
                    'bank_account' => $_POST['bank_account'],
                    'account_name' => $_POST['account_name'],
                    'bank' => $_POST['bank'],
                    'phone' => $_POST['phone'],
                    'code' => $_POST['code'],
                    'create_time' => time(),
                    'update_time' => time($_POST['create_time'])
                );
                if($_POST['amount']<50){
                    Json_error('满50才能提现哦!亲!');
                }
                if(empty($_POST['bank_account']) || empty($_POST['account_name']) ||empty($_POST['bank'])){
                    Json_error('银行相关信息都没有，怎么给你提现');
                }
                    if ($_POST['amount'] > $commission['commission']) {
                        Json_error('佣金不足');
                    } else {

                        if($db->insert($data)) {
                            $cut = $commission['commission'] - $_POST['amount'];
                            M('user')->where(array('uid'=>$_SESSION['uid']))->update(array('commission' => $cut));
                            $arr = array(
                                'uid'=>$_SESSION['uid'],
                                'content'=>'提现',
                                'commission'=>'-'.$_POST['amount'],
                                'username'=>$commission['username'],
                                'create_time'=>time(),
                                'type'=>'1'
                            );
                            M('commission_log')->insert($arr);
                            Json_success('提现成功，请等待审核');
                        }else{
                            Json_error('提现失败');
                        }
                    }

                } else {
                    $data = array(
                        'uid' => $_SESSION['uid'],
                        'amount' => $_POST['amount'],
                        'bank_account' => $re['bank_account'],
                        'account_name' => $re['account_name'],
                        'bank' => $re['bank'],
                        'phone' => $_POST['phone'],
                        'code' => $_POST['code'],
                        'create_time' => time(),
                        'update_time' => time($_POST['create_time'])
                    );
                if($_POST['amount']<50){
                    Json_error('满50才能提现哦!亲!');
                }

                    if ($_POST['amount'] > $commission['commission']) {
                        Json_error('佣金不足');
                    } else {
                        if($db->insert($data)) {
                            $cut = $commission['commission'] - $_POST['amount'];
                            M('user')->where(array('uid'=>$_SESSION['uid']))->update(array('commission' => $cut));
                            $arr = array(
                                'uid'=>$_SESSION['uid'],
                                'content'=>'提现',
                                'commission'=>'-'.$_POST['amount'],
                                'username'=>$commission['username'],
                                'create_time'=>time(),
                                'type'=>'1'
                            );
                            M('commission_log')->insert($arr);
                            Json_success('提现成功');
                        }else{
                            Json_error('提现失败');
                        }
                    }
                }
        }else {
            Json_error('未登录');
        }
    }

    /**
     * @Title: commission_log
     * @Description: todo(佣金明细)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function commission_log(){
        if ($this->auth->is_logged_in()) {
            $db=M('commission_log');
            //如果没有入职返现记录
                if($re=$db->where('uid = '.$_SESSION['uid'])->field('create_time,uid,job_time,company,content,commission')->order('create_time desc')->findall()){
                    Json_success('获取成功',$re);
                }else{
                    Json_error('没有数据');
                }
        }else{
            Json_error('未登录');
        }
    }

    /**
     * @Title: getGroupByHuanxin
     * @Description: todo(查找环信群组)
     * @author nipeiquan
     * @param $groupId
     * @return  mixed  返回类型
     */
    private function getGroupByHuanxin($groupId) {
        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $token = $rs['access_token'];
        $url=$this->url."/chatgroups/".$groupId;
        $arr=array();
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, $arr, $header, "GET"),true);
    }

    /**
     * @Title: editNicknameByHuanxin
     * @Description: todo(修改环信昵称)
     * @author nipeiquan
     * @param $userId
     * @param $nickname
     * @return  mixed  返回类型
     */
    private function editNicknameByHuanxin($userId,$nickname){

        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $token = $rs['access_token'];
        $url=$this->url."/users/".$userId;
        $arr=array(
            'nickname'=> $nickname,
        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, $arr, $header, "PUT"),true);
    }

    /**
     * @Title: createGroup
     * @Description: todo(查看环信群组)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function getGroup(){

        $groupId = $_POST['group_id'];

        $result = $this->getGroupByHuanxin($groupId);

        Json_success('获取成功',$result['data']);
    }


    public function testcode(){
        var_dump($_SESSION);
    }

    /**
     * @Title: salary_query
     * @Description: todo(查询工资)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public  function salary_query(){
        if ($this->auth->is_logged_in()) {
            $db = M('user_info');
            $ta = M('staff_salary');
            $re = $db->where(array('uid'=>$_SESSION['uid']))->find();
            $user = M('user')->where(['uid'=>$_SESSION['uid']])->find();
            $id_number=$re['id_number'];
            $sa = $ta->where(array('id_number'=>$id_number))->find();

            if ($sa['time'] > strtotime(date('Y-m-d'))) {//在今天之内

                if ($sa['query_number'] > 2) { //是否超过三次
                    Json_error('今天查询次数超过三次了');
                }
                $number = $sa['query_number'] + 1;

                if(!validateSmsCode($user['mobile'],$_POST['code'])){
                    Json_error('验证码错误');
                }
//                    if ($_POST['code'] != $_SESSION['smscode']) {
//                        Json_error('验证码错误');
//                    }
                if (empty($sa)) {
                    Json_error('没有工资记录');
                }
                        $ta->where(array('id_number'=>$id_number))->update(array('query_number' => $number,'time'=>time()));
                        $list = $ta->order('time desc')->where(array('id_number'=>$id_number))->limit('2')->findall();

                        Json_success('获取成功', $list);

            }else{//不在今天范围内
//                Json_error('测试');
                $ta->where(array('id_number'=>$id_number))->update(array('query_number'=>0,'time'=>time()));

                $number = 1;

                if(!validateSmsCode($user['mobile'],$_POST['code'])){
                    Json_error('验证码错误');
                }
//                    if ($_POST['code'] != $_SESSION['smscode']) {
//                        Json_error('验证码错误');
//                    }
                if (empty($sa)) {
                    Json_error('没有工资记录');
                }
                        $ta->where(array('id_number'=>$id_number))->update(array('query_number' => $number,'time'=>time()));
                        $list = $ta->where(array('id_number'=>$id_number))->findall();

                        Json_success('获取成功', $list);
            }
        }
    }

    /**
     * @Title: salary_list1
     * @Description: todo(薪资查询-最新)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public  function salary_list1(){
        if ($this->auth->is_logged_in()) {

            $db = M('user_info');
            $ta = M('staff_salary');
            $re = $db->where(array('uid'=>$_SESSION['uid']))->find();
            $user = M('user')->where(['uid'=>$_SESSION['uid']])->find();
            $id_number=$re['id_number'];
            $sa = $ta->where(array('id_number'=>$id_number))->find();

            if ($sa['time'] > strtotime(date('Y-m-d'))) {//在今天之内

                if($_SESSION['uid']!=9637){

                    if ($sa['query_number'] > 2) { //是否超过三次
                        Json_error('今天查询次数超过三次了');
                    }
                }

                $number = $sa['query_number'] + 1;

                if(!validateSmsCode($user['username'],$_POST['code'])){
                    Json_error('验证码错误');
                }
//                if ($_POST['code'] != $_SESSION['smscode']) {
//                    Json_error('验证码错误');
//                }
                if (empty($sa)) {
                    Json_error('没有工资记录');
                }
                $ta->where(array('id_number'=>$id_number))->update(array('query_number' => $number,'time'=>time()));
                $list = $ta->where(array('id_number'=>$id_number))->field('id,company_name,salary_month,job_number,name,id_number,should_salary,withhold_count,really_salary,extra_data,time,query_number')->order('salary_month desc')->findall();

            }else{//不在今天范围内

                $ta->where(array('id_number'=>$id_number))->update(array('query_number'=>0,'time'=>time()));

                $number = 1;

                if(!validateSmsCode($user['username'],$_POST['code'])){
                    Json_error('验证码错误');
                }
//                if ($_POST['code'] != $_SESSION['smscode']) {
//                    Json_error('验证码错误');
//                }
                if (empty($sa)) {
                    Json_error('没有工资记录');
                }
                $ta->where(array('id_number'=>$id_number))->update(array('query_number' => $number,'time'=>time()));
                $list = $ta->where(array('id_number'=>$id_number))->field('id,company_name,salary_month,job_number,name,id_number,should_salary,withhold_count,really_salary,extra_data,time,query_number')->order('salary_month desc')->findall();

            }

            $arr = [];

            foreach ($list as $k=>$val){

                $val['extra_data'] = json_decode($val['extra_data']);

                $val['extra_data'] = empty($val['extra_data']) ? [] : $val['extra_data'];

                $year = explode('-',$val['salary_month']);

                if(!empty($year)){

                    $year = $year[0];

                    $arr[$year][$val['company_name']][] = $val;
                }

            }

            $data = [];

            foreach ($arr as $key => $value) {

                $data_data = [];


                foreach ($value as $item => $t){

                    $ids = '';

                    $total_income = 0;

                    $date = [0];

                    foreach ($t as $a=>$b){

                        $ids .= $b['id'].',';

                        $total_income += $b['really_salary'];

                        $date[] = strtotime($b['salary_month']);
                    }

                    $max_date = date('Y-m',max($date));

                    $data_data[] = [
                        'company'=>$item,
                        'month_count'=>count($t),
                        'total_income'=>$total_income,
                        'ids'=>rtrim($ids,','),
                        'last_month'=>$max_date
//                        'val'=>$t
                    ];
                }

                $data[] = [
                    'date'=>$key,
                    'items'=>$data_data
                ];

            }

            $user_info = [

                'name'=>$re['name'],
                'id_number'=>$re['id_number'],
                'avatar'=>$_SESSION['avatar']
            ];

            $return = [

                'user'=>$user_info,
                'items'=>$data
            ];

            Json_success('获取成功', $return);
        }else{

            Json_error('请重新登录后再试');
        }
    }

    /**
     * @Title: salary_list2
     * @Description: todo(工资查询第二步)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function salary_list2(){

        $infos = M('staff_salary')->where('id in('.$_POST['ids'].')')->field('id,salary_month')->findall();

        foreach ($infos as $k=>$info){

            if(!empty($info['extra_data'])){

                $info['extra_data'] = json_decode($info['extra_data'],true);
            }
        }

        Json_success('获取成功',$infos);
    }

    /**
     * @Title: salary_list3
     * @Description: todo(工资查询第三步)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function salary_list3(){
        $info = M('staff_salary')->where(['id'=>$_POST['id']])->field('company_name,salary_month,job_number,name,id_number,should_salary,withhold_count,really_salary,extra_data,time,query_number')->find();
        $uid = $_SESSION['uid'];
        if(empty($uid))$uid = 0;
        K('public')->writeQueryLog($uid,$info['company_name']);
        if(!empty($info['extra_data'])){

            $info['extra_data'] = json_decode($info['extra_data'],true);
        }

        Json_success('获取成功',$info);
    }

    /**
     * @Title: share
     * @Description: todo(邀请)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function share(){   

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {  


            $phone = $_POST['phone'];

            $code = $_POST['code'];


            $branch_id = $_POST['branch_id'];

            $salesmanid = $_POST['salesmanid'];

            $normalmanid = $_POST['normalmanid'];

            $code_true = validateSmsCode($phone,$code);

            if(!$code_true){

                Json_error('验证码错误');
            }

            $from = $_POST['from'];

            $password = mt_rand(100000,999999);

            $username_str = substr($phone,0,7).'****';

            $huanxin = $this->openRegister($phone,$password,$username_str);

            $data = array(
                'username' => $phone,
                'password' => "$password",
                'nickname' => substr($phone,0,7).'****',
                'code'     => $code,
                'created'=>time(),
                'last_ip'=>ip_get_client(),
                'from_id'=>$from,
                'is_yewu'=>1,
                'is_bind'=>1,
                'branch_id'=>$branch_id,
                'salesmanid'=>$salesmanid,
                'normalmanid'=>$normalmanid
            );

            if($huanxin['error']){

                Json_success('注册失败');

            }else{
                 //var_dump($data);die;
                if ($this->auth->register($data)) {//注册成功 


                    $a=M('user');
                    $id=$a->where(array('username'=>$phone))->field('uid,username')->find();
                    $info=M('user_info');
                    $info->insert(array('uid'=>$id['uid'],'create_time'=>time()));
                    $msg = '恭喜你注册成功,初始密码为:'.$password.',请及时登录重置密码！';
                    $con = '';
                    sendSmsMsg($phone,$con,$msg);

                    if(!empty($from)){

                        $point = getPointRule('newUser');//获得应扣取积分

                        deductPoint($point,$id['uid']);//增加积分

                        $con = '注册得积分';

                        $arr = array(
                            'uid' => $id['uid'],
                            'content' => $con,
                            'point' => '+' . $point,
                            'created' => time(),
                            'username' => $id['username'],
                            'time' => time(),
                            'type' => 0
                        );
                        M('opt_log')->insert($arr);
                    }

//                    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
//                        $loadUrl = 'https://itunes.apple.com/us/app/kai-xin-gong-zuo/id1068849473?l=zh&ls=1&mt=8';
//                    }else{
//                        $loadUrl = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.tongyu.luck.happywork';
//                    }

                    addMissionLog($id['uid'],14,$id['uid']);

                    Json_success('注册成功',"http://a.app.qq.com/o/simple.jsp?pkgname=com.tongyu.luck.happywork");

                }else{
                    Json_error('注册失败');
                }
            }

        }else{

            $this->display('app/share');

        }


    }


    public function sendSmsForShare(){

        $phone = $_POST['phone'];

        if(empty($phone)||strlen($phone)!=11){
            Json_error('手机号码错误');
        }

        $username = M('user')->field('username')->where(array('username'=>$phone))->find();

        if(!empty($username)){
            Json_error('手机号码已注册');
        }

        sendSmsMsg($phone,'');

        Json_success('发送成功');
    }

    /**
     * @Title: viewResume
     * @Description: todo(简历预览)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function viewResume()
    {
        $resume_id = $_GET['resume_id'];

        $cond = 'resume_id = ' . $resume_id;

        $resume_mode = K('resume');

        $resume['resume'] = $resume_mode->getResume($cond);

        if ($resume['resume']) {


            $resume['basic'] = $resume_mode->getResumeBasic($cond);

            $resume['edu'] = $resume_mode->getResumeEdu($cond);



            $resume['exp'] = $resume_mode->getResumeExp($cond);

            $this->assign('resume', $resume);

        }

        $this->display('app/resume');

    }

    /**
     * @Title: salaryList
     * @Description: todo(工资列表)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function salaryList(){

        $uid = empty($_GET['uid'])?0:intval($_GET['uid']);

        $id_number = M('user_info')->where('uid = '.$uid)->find();
        $sql = 'SELECT UNIX_TIMESTAMP(concat(salary_month,"-01")) as salary_month FROM hp_staff_salary WHERE id_number = "'.$id_number['id_number'].'" ORDER BY salary_month desc LIMIT 1';

        $salary_month = M('staff_salary')->query($sql);

        $con = 'UNIX_TIMESTAMP(concat(salary_month,"-01")) <= '.$salary_month[0]['salary_month'].'AND UNIX_TIMESTAMP(concat(salary_month,"-01")) >= '.($salary_month[0]['salary_month'] - 3235767).' AND id_number = "'.$id_number['id_number'].'"';

        $salary = M('staff_salary')->field('id,name,id_number,salary_month,company_name,time')->order('id ASC')->where($con)->findall();

        $this->assign('salarys',$salary);

        $this->display('app/salaryList');

    }

    /**
     * @Title: salaryInfo
     * @Description: todo(工资信息)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function salaryInfo(){

        $id = $_GET['id'];

        $salary = M('staff_salary')->where(array('id'=>$id))->find();

        $this->assign('salary',$salary);

        $this->display('app/salaryInfo');

    }

    public function weixinLogin(){

        $code = $_POST['code'];

        $weixin = new weixin();

        $weixin->code = $code;

        $auth = $weixin->getOpenid();

        $db = M('user');

        $_POST['authtype'] = 1;

        if(!isset($auth['openid'])){

            Json_error('微信登陆失败',$auth);

        }

        $userInfo = $weixin->getUserInfo($auth['access_token'],$auth['openid']);

        $data = array(
            'username'=>'',
            'authid_qq' => $auth['openid'],
            'nickname' => $userInfo['nickname'],
            'authtype' => $_POST['authtype'],
            'password' => '123456',
            'avatar' => $userInfo['headimgurl'],
            'unionid' => $userInfo['unionid'],
        );

        if(!empty($data['unionid'])){

            $auth = $db->where(['unionid'=>$data['unionid']])->find();

        }else{

            $auth = $db->where(['authid_qq'=>$data['authid_qq']])->find();

        }

        if (empty($auth)) {//未登陆过，执行注册程序
            $data['created'] = time();
            $data['last_ip'] = ip_get_client();
            $data['is_newuser'] = 1;

//                $username = $data['username'];

            if ($this->user->userExist($data['username'])) {
                Json_error('手机号已被绑定');
            }
            if(empty($data['username'])){
                $data['username'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

                $data['phone'] = $data['username'];
                $data['is_bind'] = 2;//如果没传就不绑定
            }else{
                $data['phone'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $data['is_bind'] = 1;//如果传了手机号码就绑定
            }
            $huanxin = $this->openRegister($data['phone'], '123456',$data['nickname']);

            if ($huanxin['error']) {

                Json_error('环信注册失败');

            }

            if($this->auth->register($data)) {

                $authid = $data['authid_qq'];
                $password = '123456';
                if ($this->auth->auth_user($authid, $password)) {
                    $user = $db->where('uid=' . $_SESSION['uid'])->find();
                    $user['sessionid'] = session_id();
                    $_SESSION['rid'] = $_SESSION['role']['rid'];
                    $user['rid'] = $_SESSION['rid'][0];
                    $_SESSION['point'] = 0;//注册得积分

                    $point = getPointRule('newUser');//获得应扣取积分

                    deductPoint($point, $user['uid']);//增加积分

                    $con = '注册得积分';

                    $data = array(
                        'uid' => $user['uid'],
                        'content' => $con,
                        'point' => '+' . $point,
                        'created' => time(),
                        'ip' => ip_get_client(),//操作ip
                        'username' => $user['username'],
                        'time' => time(),
                        'type' => 0
                    );
                    M('opt_log')->insert($data);
                    $info=M('user_info');
                    $info->insert(array('uid'=>$user['uid'],'create_time'=>time()));
                    M('user')->where(array('uid'=>$user['uid']))->update(array('is_online'=>1));//登陆更改在线状态
                    Json_success('登录成功', $user);
                } else {
                    Json_error('登陆失败');
                }
            }else{
                Json_error('注册失败');
            }
        } else {//已经注册过，执行登陆程序

            if(!empty($data['unionid'])){

                $unionid = $userInfo['unionid'];

            }else{

                $unionid = $data['authid_qq'];
            }

            $password = 123456;

            if ($this->auth->auth_user($unionid, $password)) {//使用第三方指定登陆接口

                if(!empty($data['unionid'])){

                    $user = M('user')->where(['unionid'=>$data['unionid']])->find();

                    M('user')->where(['unionid'=>$data['unionid']])->update(array('is_online'=>1,'client_id'=>$data['client_id']));//登陆更改在线状态

                }else{

                    $user = M('user')->where(['authid_qq'=>$data['authid_qq']])->find();

                    M('user')->where(['authid_qq'=>$data['authid_qq']])->update(array('is_online'=>1,'client_id'=>$data['client_id']));//登陆更改在线状态

                }

                $user['sessionid'] = session_id();
                $_SESSION['rid'] = $_SESSION['role']['rid'];
                $user['rid']=$_SESSION['rid'][0];
                Json_success('登录成功', $user);
            }else{

                Json_error('登录失败');
            }
        }
    }

    public function test(){

        $group_members = M('group_member')->where('nickname != ""')->findall();

        foreach ($group_members as $k=>$group_member){

            $user = M('user')->where(['uid'=>$group_member['uid']])->find();

            M('group_member')->where(['uid'=>$group_member['uid']])->update(['nickname'=>$user['nickname']]);
        }


    }

}





