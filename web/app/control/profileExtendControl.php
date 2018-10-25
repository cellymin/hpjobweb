<?php

include '../../backend/libs/phpqrcode.php';
include '../../backend/libs/excel_reader2.php';

class profileExtendControl extends Control {

    private $auth;
    private $user;
    private $message;

    private $app_key='ttouch#kuaile';
    private $client_id='YXA6tfPWgFtsEeWzNxtHA-A9OA';
    private $client_secret='YXA6hhR-xo8PGuE3BAqMfTQlfNAkYLs';
    private $url = "https://a1.easemob.com/ttouch/kuaile";

    function __construct() {
        parent::__construct();
        $this->auth = new auth;
        $this->user = K('user');
        $this->message = K('message');

    }

    /**
     * @Title: isRegister
     * @Description: todo(验证是否注册)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function isRegister(){

        $username = $_POST['phone'];

        $user = M('user')->where(['username'=>$username])->field('avatar,uid,username')->find();

        $data = ['is_register'=>0];//0未注册1已注册

        if(!empty($user)){

            $data = ['is_register'=>1,'user'=>$user];
        }

        Json_success('获取成功',$data);
    }

    /**
     * @Title: getUserInfo
     * @Description: todo(获取个人信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getUserInfo(){

        $db = M('user');

        $uid = $_SESSION['uid'];

        if(empty($uid)){

            Json_error('请先登录');
        }

        if(!empty($_POST['client_id'])){

            M('user')->where('uid = '.$uid)->update(['client_id'=>$_POST['client_id']]);
        }

        $point = M('user_point')->where('uid = '.$uid)->field('point')->find();

        $user = $db->where('uid = '.$uid)->find();

        if(empty($user)){

            Json_error('用户不存在');
        }

        unset($user['password']);

//        $user['phone'] = $_SESSION['username'];

        $user['point'] = $point['point'];

        if(!empty($user['birthday'])){

            $user['age'] = date('Y',time()) - date('Y',$user['birthday']);

        }else{

            $user['age'] = '';
        }

        $user_info = M('user_info')->where('uid = '.$uid)->find();

        if(!empty($user_info)){

            $user['verify'] = $user_info['verify'];//0未认证 1审核中 2审核未通过 3审核通过

            $user['name'] = $user_info['name'];

            $salarys = M('staff_salary')->where(['id_number'=>$user_info['id_number']])->findall();

            foreach ($salarys as $k=>$salary){

                $date[] = strtotime($salary['salary_month']);
            }

            if(empty($date)){

                $user['salary_date'] = '';
            }else{

                if(date('Y-m',max($date)) == '1970-01'){
                    $user['salary_date'] = '';
                }else{
                    $user['salary_date'] = date('Y-m',max($date));
                }
            }

        }else{

            $user['name'] = '';

            $user['verify'] = 0;

            $user['salary_date'] = '';
        }

        $user['sns_num'] = M('new_sns')->where(['uid'=>$uid,'del_state'=>0])->count();

        $role = M('user_role')->where(['uid'=>$uid])->find();

        if(!empty($role)){

            $user['rid'] = $role['rid'];
        }else{

            $user['rid'] = 0;
        }

        Json_success('获取成功',$user);

    }

    /**
     * @Title: uploadImg
     * @Description: todo(个人中心上传图片)
     * @author nipeiquan
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
     * @Title: validateSmsCode
     * @Description: todo(判断验证码)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function validateSmsCode(){

        $code_true = validateSmsCode($_POST['phone'],$_POST['code']);

        if(!$code_true){

            Json_error('验证码错误');
        }

        Json_success('验证通过');
    }

    /**
     * @Title: editUser
     * @Description: todo(编辑个人信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editUser(){
        $param = [];//gearman修改
        if ($this->auth->is_logged_in()) {

            $user = M('user');

            $param['uid'] = $_SESSION['uid'];//gearman修改的用户id

            $condition = 'uid = ' . $_SESSION['uid'];

            if(!empty($_POST['nickname'])) {

//                $sensitive = M('linkage')->where('lcgid=26')->findall();
//
//                foreach ($sensitive as $key=>$value){
//
////                    if(strpos($_POST['nickname'],$value['title']) !== false){     //使用绝对等于
//
//                        Json_error('昵称中含有敏感词，请重新输入');
////                    }
//                }

                $param['nickname'] = $_POST['nickname'];//gearman修改昵称

                $this->user->fillInfo($_SESSION['uid'],6);//加积分

                $member = M('user')->where(['uid'=>$_SESSION['uid']])->find();

                $this->editNicknameByHuanxin($member['username'],$_POST['nickname']);//修改环信昵称

            }

            if(!empty($_POST['avatar'])){//头像

                $param['avatar'] = $_POST['avatar'];//gearman修改小头像

            }

            if(!empty($_POST['hometown']) && !empty($_POST['hometown_id'])){//家乡

                $param['hometown'] = $_POST['hometown'];//gearman修改家乡
                $param['hometown_id'] = $_POST['hometown_id'];//gearman修改家乡id

                $this->user->fillInfo($_SESSION['uid'],2);//加积分
            }

            if(!empty($_POST['birthday'])){//生日

                $param['birthday'] = $_POST['birthday'];//gearman修改生日

                $this->user->fillInfo($_SESSION['uid'],3);//加积分
            }

            if(isset($_POST['gender'])){//性别0男1女

                $param['gender'] = $_POST['gender'];//gearman修改性别

                $this->user->fillInfo($_SESSION['uid'],4);//加积分

//                M('new_sns')->where(['uid'=>$_SESSION['uid']])->update(['gender'=>$_POST['gender']]);
//                M('new_sns_zan')->where(['uid'=>$_SESSION['uid']])->update(['usex'=>$_POST['gender']]);
//                M('user')->where(['uid'=>$_SESSION['uid']])->update(['gender'=>$_POST['gender']]);
            }

            if(!empty($_POST['address']) && !empty($_POST['now_address_id'])){//现居

                $user->where($condition)->update(array('address'=>$_POST['address'],'now_address_id'=>$_POST['now_address_id']));

                $this->user->fillInfo($_SESSION['uid'],5);//加积分
            }

            if(!empty($_POST['sign'])){//

                $user->where($condition)->update(array('sign'=>$_POST['sign']));//个签
            }

            if(!empty($_POST['emotional_state'])){//情感状态1保密2单身3热恋中4已婚

                $user->where($condition)->update(array('emotional_state'=>$_POST['emotional_state']));
            }

            if(!empty($_POST['background'])){//背景墙

                $user->where($condition)->update(array('background'=>$_POST['background']));
            }

            if(!empty($_POST['company_name'])){//在职公司

                $user->where($condition)->update(array('company_name'=>$_POST['company_name']));
            }

            $user = M('user')->where('uid = ' . $_SESSION['uid'])->find();

            $data=array(
                'avatar'=>empty($_POST['avatar'])?$user['avatar']:$_POST['avatar'],
                'nickname'=>empty($_POST['nickname'])?$user['nickname']:$_POST['nickname'],
                'gender'=>isset($_POST['gender'])?$_POST['gender']:$user['gender'],
                'hometown'=>empty($_POST['hometown'])?$user['hometown']:$_POST['hometown'],
                'birthday'=>empty($_POST['birthday'])?$user['birthday']:$_POST['birthday'],
                'address'=>empty($_POST['address'])?$user['address']:$_POST['address'],
                'sign'=>empty($_POST['sign'])?$user['sign']:$_POST['sign'],
                'emotional_state'=>empty($_POST['emotional_state'])?$user['emotional_state']:$_POST['emotional_state'],
                'background'=>empty($_POST['background'])?$user['background']:$_POST['background'],
            );

            if(!queryExistMissionLog($_SESSION['uid'],'15')){
                addMissionLog($_SESSION['uid'],15,$_SESSION['uid']);
            }

            $client = new GearmanClient();
            $client->addServer();
            $client->setCompleteCallback(function(GearmanTask $task){

            });
            $client->addTaskBackground('updateUser',json_encode($param));
            $client->runTasks();
            Json_success('修改成功',$data);

        }else{

            Json_error('你还没有登录');
        }
    }

    /**
     * @Title: getOtherUserInfo
     * @Description: todo(得到用户信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getOtherUserInfo(){

        $username = $_POST['username'];

        $uid = empty($_SESSION['uid'])?0:$_SESSION['uid'];

        if(!empty($_POST['uid'])){

            $user = M('user')->where(['uid'=>$_POST['uid']])->find();

            $user['sns_num'] = M('new_sns')->where(['uid'=>$_POST['uid'],'del_state'=>0])->count();

        }else{

            $user = M('user')->where('username ='.$username.' or phone = '.$username)->find();
        }

        if(empty($user)){

            Json_error('用户不存在');
        }

        $username = $user['username'];

        if(!empty($user['birthday'])){

            $user['age'] = date('Y',time()) - date('Y',$user['birthday']);

        }else{

            $user['age'] = '';
        }

        $attention = M('new_guanzhu')->where(['uid'=>$uid,'busernname'=>$username])->find();

        if($attention){

            $user['is_attention'] = 1;
        }else{

            $user['is_attention'] = 0;
        }
        $distance = getDistanceBetweenPoints($_POST['lat'],$_POST['lng'],$user['lat'],$user['lng']);

        $user['distance'] = $distance['kilometers'];

        Json_success('获取成功',$user);

    }

    /**
     * @Title: getRecruitQR
     * @Description: todo(个人中心二维码)
     * @author nipeiquan
     * @return  void  返回类型
     */
    
    public function getRecruitQR(){

        if ($this->auth->is_logged_in()) {

           // include_once('/usr/share/nginx/html/hpjobweb/web/backend/libs/phpqrcode.php');

            $uid = $_SESSION['uid'];

            $qrcode = @file_get_contents('/usr/share/nginx/html/hpjobweb/uploads/user_qrcode/'.$uid.'.png');

            if(empty($qrcode)){

                QRcode::png ("http://192.168.3.131/hpjobweb/app/auth/share/from/$uid?from=singlemessage&isappinstalled=1",'/usr/share/nginx/html/hpjobweb/uploads/user_qrcode/'.$uid.'.png');
            }

            $data = [
                'code'=>'http://192.168.3.131/hpjobweb/uploads/user_qrcode/'.$uid.'.png'
            ];

            Json_success('获取成功',$data);
        }else{

            Json_error('请先登录');
        }

    }
    
    public function getRecruitQR2(){

        if ($this->auth->is_logged_in()) {   

            require_once('phpqrcode.php');
            
            $object = new QRcode(); 

            $uid = $_SESSION['uid'];

            $user_role = M('user_role')->where(array('uid'=>$uid))->find();

            //普通用户的二维码
            if($user_role['rid'] == 8){
                //生成二维码
                $url='http://192.168.3.131/hpjobweb/index.php/app/auth/share/from/35920?from=singlemessage&isappinstalled=1&normalmanid='.$uid;
                $level=3;  
                $size=4;  
                $path = "./uploads/person_qrcode/";//创建路径
                $fileName = $path.$uid.'.png';  
                $errorCorrectionLevel =intval($level);//容错级别  
                $matrixPointSize = intval($size);//生成图片大小   
                $object->png($url, $fileName, $errorCorrectionLevel, $matrixPointSize, 2); 

                // $list =M('user')->order("uid desc")->limit(1)->find();
                $save['qrcode'] = $fileName;
                //var_dump($filename);die;
                $result = M('user')->where(array('uid'=>$uid))->update($save);

                $data = M('user')->where(array('uid'=>$uid))->field('qrcode')->find();
            }


            //业务员的二维码
            if($user_role['rid'] == 7){
                //生成二维码
                $url='http://192.168.3.131/hpjobweb/index.php/app/auth/share/from/35920?from=singlemessage&isappinstalled=1&salesmanid='.$uid;
                $level=3;  
                $size=4;  
                $path = "./uploads/person_qrcode/";//创建路径
                $fileName = $path.$uid.'.png';  
                $errorCorrectionLevel =intval($level);//容错级别  
                $matrixPointSize = intval($size);//生成图片大小  
                $object->png($url, $fileName, $errorCorrectionLevel, $matrixPointSize, 2); 

                // $list =M('user')->order("uid desc")->limit(1)->find();
                $save['qrcode'] = $fileName;
                //var_dump($filename);die;
                $result = M('user')->where(array('uid'=>$uid))->update($save);

                $data = M('user')->where(array('uid'=>$uid))->field('qrcode')->find();
            }
        
            Json_success('获取成功',$data);

            // $qrcode = @file_get_contents('/usr/share/nginx/html/hpjobweb/uploads/user_qrcode/'.$uid.'.png');

            // if(empty($qrcode)){

            //     QRcode::png ("http://192.168.3.131/hpjobweb/app/auth/share/from/$uid?from=singlemessage&isappinstalled=1",'/usr/share/nginx/html/hpjobweb/uploads/user_qrcode/'.$uid.'.png');
            // }

            // $data = [
            //     'code'=>'http://192.168.3.131/hpjobweb/uploads/user_qrcode/'.$uid.'.png'
            // ];

            // Json_success('获取成功',$data);
        }else{

            Json_error('请先登录');
        }

    }

    /**
     * @Title: pointMissions
     * @Description: todo(任务列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function pointMissions(){

        if ($this->auth->is_logged_in()) {

            $uid = $_SESSION['uid'];

            $model = K('public');

            $missions = $model->getUserMissions($uid);

            Json_success('获取成功',$missions);

        }else{

            Json_error('你还没有登录');
        }
    }

    /**
     * @Title: receiveAward
     * @Description: todo(领取任务奖励)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function receiveAward(){

        if ($this->auth->is_logged_in()) {

            $mid = $_POST['mid'];

            if(empty($mid)){

                Json_error('缺少参数mid');
            }

            $mission = M('point_mission')->where(['mid'=>$mid])->find();

            $publicModel = K('public');

            $is_receive = $publicModel->isReceiveReward($_SESSION['uid'],$mid);

            if($is_receive){

                Json_error('今日已领取');
            }

            $finish_num = $publicModel->getFinishNum($_SESSION['uid'],$mission['mission_type']);

            if($finish_num < $mission['need_num']){

                Json_error('完成数量不足');
            }else{

                if($mission['mission_type'] ==1){//自拍签到

                    $type = 5;
                }elseif ($mission['mission_type']==2){

                    $type = 1;
                }else{

                    $type = $mission['mission_type'] + 5;
                }

                deductPoint($mission['point']);//增加积分

                addOptLog($_SESSION['uid'],$mission['mission_name'],$mission['point'],$_SESSION['username'],$type);

                M('point_mission_log')->add(['uid'=>$_SESSION['uid'],'mid'=>$mid,'mission_name'=>$mission['mission_name'],'point'=>$mission['point'],'created'=>time()]);

                Json_success('领取成功');
            }
        }else{

            Json_error('请先登录');
        }

    }

    /**
     * @Title: pointLog
     * @Description: todo(积分记录)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function pointLog(){

        if ($this->auth->is_logged_in()) {

            $db = M('opt_log');
            $cond = 'uid =' . $_SESSION['uid'].' AND created >= '.(time()-60*60*24*30);
            $record['content'] = $db->where($cond)->field('uid,username,content,point,time,type')->order('time desc')->findall();
            $_SESSION['point'] = $this->_getUserPoint($_SESSION['uid']);
            $record['user_point']=$_SESSION['point'];

            Json_success('获取成功',$record);
        }else{

            Json_error('未登录');
        }
    }

    /**
     * @Title: getSignInfo
     * @Description: todo(签到信息页)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getSignInfo(){

        if ($this->auth->is_logged_in()) {

            $ads = M('ads')->where('cate = 13')->field('ads_title,href,path')->order('sort desc')->findall();

            foreach ($ads as $k=>$v){

                $ads[$k]['path'] = 'http://192.168.3.131/hpjobweb/'.$v['path'];
            }

            $user = M('user')->where(['uid'=>$_SESSION['uid']])->find();

            $continue_days = $user['continue_sign_days'];

            $last_bonus = M('sign_bonus')->where('days_num >'.$continue_days)->order('days_num asc')->find();

            if(empty($last_bonus)){

                $data = [
                    'words'=>'',
                    'ads'=>$ads,
                ];

                Json_success('获取成功',$data);
            }else{

                $days = $last_bonus['days_num'] - $continue_days;

                if($last_bonus['type']==1){

                    $num = $last_bonus['bonus_num'];

                    if(empty($num)){

                        $words = '';
                    }else{

                        $words = '再连续签到'.$days.'天可获得'.$num.'积分';
                    }
                }else{

                    $words = '再连续签到'.$days.'天可获得'.$last_bonus['title'];
                }

                $data = [
                    'words'=>$words,
                    'ads'=>$ads
                ];
                Json_success('获取成功',$data);
            }
        }else{

            Json_error('请先登录');
        }
    }

    /**
     * @Title: sign
     * @Description: todo(签到)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function sign(){

        if ($this->auth->is_logged_in()) {

            $user = M('user')->where(['uid'=>$_SESSION['uid']])->find();

            $exist = $this->validateDaySign($_SESSION['uid']);//判断今日是否签到

            if(!$exist){

                Json_error('今日已经签到过了');
            }

            $last_sign = $user['last_sign'];

            if($_SESSION['role']['rid'][0]==7){   //业务员
                if(empty($_POST['address'])){
                    Json_error('签到失败,你还没有打开定位功能,请开启定位功能!');
                }
                $con=$_POST['address'];
                if(empty($_POST['lng']) || empty($_POST['lat'])){
                    Json_error('未拾取到您的坐标');
                }

                $add_log = addOptLog($_SESSION['uid'],$con,0,$_SESSION['username'],6,$_POST['lng'],$_POST['lat']);

                if($add_log){

                    addSignLog($_SESSION['uid'],$_SESSION['username'],0,0,'业务员签到','','');

                    Json_success('签到成功');
                }else{

                    Json_error('error');
                }

            }else{

                if($last_sign >= strtotime(date('Y-m-d',strtotime('-1 day')))){//判断是否连续签到

                    if(date('j')!=1){//判断当前是否是当月1号

                        M('user')->where(['uid'=>$_SESSION['uid']])->update(['continue_sign_days'=>$user['continue_sign_days']+1]);

                    }else{

                        M('user')->where(['uid'=>$_SESSION['uid']])->update(['continue_sign_days'=>1]);
                    }
                }else{

                    M('user')->where(['uid'=>$_SESSION['uid']])->update(['continue_sign_days'=>1]);
                }

                $user = M('user')->where(['uid'=>$_SESSION['uid']])->find();

                $bonus = M('sign_bonus')->where(['days_num'=>$user['continue_sign_days']])->find();

                if($bonus){

                    if($bonus['type'] == 1){//积分

                        $point = $bonus['bonus_num'];

                        deductPoint($point);//增加积分

                        $add_log = addOptLog($_SESSION['uid'],'签到',$point,$_SESSION['username'],1);

                        if($add_log){

                            addSignLog($_SESSION['uid'],$_SESSION['username'],1,$point,$bonus['title'],$bonus['icon'],$bonus['href']);

                            addMissionLog($_SESSION['uid'],2,$_SESSION['uid']);

                            Json_success('签到成功,获取'.$point.'积分');
                        }else{

                            Json_error('error');
                        }
                    }else{//实物,发送个消息

                        addSignLog($_SESSION['uid'],$_SESSION['username'],2,'',$bonus['title'],$bonus['icon'],$bonus['href']);

                        addMissionLog($_SESSION['uid'],2,$_SESSION['uid']);

                        Json_success('签到成功');
                    }
                }else{

                    $_SESSION['point'] = $this->_getUserPoint($_SESSION['uid']);

                    $point = getPointRule('firstLogin');//获得应扣取积分

                    deductPoint($point);//增加积分

                    $add_log = addOptLog($_SESSION['uid'],'签到',$point,$_SESSION['username'],1);

                    if($add_log){

                        addSignLog($_SESSION['uid'],$_SESSION['username'],1,$point,$bonus['title'],$bonus['icon'],$bonus['href']);

                        addMissionLog($_SESSION['uid'],2,$_SESSION['uid']);

                        Json_success('签到成功,获取'.$point.'积分');
                    }else{

                        Json_error('error');
                    }
                }

            }

        }else{

            Json_error('未登录');
        }
    }

    /**
     * @Title: getSignDate
     * @Description: todo(签到页面)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getSignDate(){

        if ($this->auth->is_logged_in()) {

            $user = M('user')->where(['uid'=>$_SESSION['uid']])->find();

            $continue_days = $user['continue_sign_days'];

            if (date('j') == 1) {

                M('user')->where(['uid' => $_SESSION['uid']])->update(['continue_sign_days' => 0]);

//                Json_success('获取成功', ['is_sign' => 0]);
            }

            $logs = M('sign_log')->where('uid = ' . $_SESSION['uid'] . ' AND created >' . (strtotime(date('Y-m'))-21600))->findall();//获取当前月凌晨
//            $logs = M('sign_log')->where('uid = ' . $_SESSION['uid'])->findall();

            $sign_date = [];
            $sign_data = [];

            foreach ($logs as $k => $log) {

                $date = intval(date('d', $log['created']));//把签到时间转化成日并去除第一个0
                $sign_date[] = $date;//当月已签日期

                $sign_data[$date] = [//以日为键
                    'date' => $date,
                    'href' => $log['href'],
                    'icon' => $log['icon']
                ];
            }

            $month = [];

            $sign_bonuses = M('sign_bonus')->where('days_num >='.$continue_days)->findall();//累计签到奖励

            for ($i = 1; $i < date('t') + 1; $i++) {

                if (in_array($i, $sign_date)) {

                    $month[$i]['date'] = $i;
                    $month[$i]['href'] = $sign_data[$i]['href'];
                    $month[$i]['icon'] = $sign_data[$i]['icon'];
                    $month[$i]['is_sign'] = 1;

                } else {

                    $month[$i]['date'] = $i;
                    $month[$i]['href'] = '';
                    $month[$i]['icon'] = 'http://192.168.3.131/hpjobweb/uploads/bonus_icon/img/65171489636509.png';
                    $month[$i]['is_sign'] = 0;

                    if ($i >= date('j')) {//得出今日之后的数据

                        $continue_days++;

                        foreach ($sign_bonuses as $k=>$sign_bonus){

                            if($sign_bonus['days_num'] == $continue_days){

                                $month[$i]['date'] = $i;
                                $month[$i]['href'] = $sign_bonus['href'];
                                $month[$i]['icon'] = $sign_bonus['icon'];
                                $month[$i]['is_sign'] = 0;
                            }
                        }
                    }

                }
            }

            Json_success('获取成功',array_values($month));
        }else{

            Json_error('请先登录');
        }
    }

    /**
     * @Title: getGroupLabs
     * @Description: todo(获取群组标签)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getGroupLabs(){

        $labs = M('new_lab')->where(['state'=>1])->findall();

        Json_success('获取成功',$labs);
    }

    /**
     * @Title: uploadGroupAvatar
     * @Description: todo(上传群组头像)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function uploadGroupAvatar(){

        C('UPLOAD_IMG_DIR','');

        C('THUMB_ENDFIX','');//只生成头像缩略图

        $upload=new upload(PATH_ROOT.'/uploads/group',array('jpg','jpeg','png','gif'));

        $info=$upload->upload();

        if($info){

            $avatar=$info[0]['path'];

            Json_success('上传成功',$avatar);
        }else{
            Json_error('上传失败');
        }
    }

    /**
     * @Title: createGroup
     * @Description: todo(创建群组)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function createGroup(){

        if ($this->auth->is_logged_in()) {

            $lng = $_POST['lng'];
            $lat = $_POST['lat'];
            $owner = $_POST['owner'];//群名
            $sensitive = M('linkage')->where('lcgid=26')->findall();
            foreach ($sensitive as $key=>$value){

                if(strpos($_POST['owner'],$value['title']) !== false){     //使用绝对等于
                    Json_error('群名中含有敏感词，请重新输入');
                }
            }

            $avatar = $_POST['avatar'];//头像
            $address = $_POST['address'];//地址
            $desc = $_POST['desc'];//描述
            $labs = $_POST['labs'];//标签名

            $user = M('user')->where(['username'=>$_SESSION['username']])->find();

            if(!empty($user['phone'])){

                $name = $user['phone'];
            }else{

                $name = $_SESSION['username'];
            }
            $create_huanxin = $this->createHuanXinGroup($owner,$desc,$name);//群名,描述,群主

            if(!$create_huanxin['error']){

                $group_id = $create_huanxin['data']['groupid'];//环信的群号
            }else{

                Json_error('环信创建失败',$create_huanxin['error_description']);
            }

            $result = array(
                'lng' => $lng,
                'lat' => $lat,
                'uid' => $_SESSION['uid'],
                'group_id' => $group_id,
                'owner' => $owner,
                'avatar' => $avatar,
                'group_permissions' => 1,//0私密群1公开群
                'user_permissions' => 1,//是否管理员同意0不需要1需要
                'desc' => $desc,
                'created' => time(),
                'grouper' => $_SESSION['username'],
                'lab_id' => 0,
                'lab_name' => $labs,
                'address' => $address
            );

            if (M('group')->insert($result)) {

                $group = M('group')->where(['group_id'=>$group_id])->find();

                $is_first_create = M('group')->where('uid=' . $_SESSION['uid'])->findall();

                $member_info = [
                    'gid'=>$group['gid'],
                    'group_id'=>$group_id,
                    'grouper'=>$_SESSION['username'],
                    'uid'=>$_SESSION['uid'],
                    'username'=>$_SESSION['username'],
                    'nickname'=>$user['nickname'],
                    'user_huanxin'=>empty($_SESSION['phone'])?$_SESSION['username']:$_SESSION['phone'],
                    'is_admin'=>1,
                    'avatar'=>$_SESSION['avatar'],
                    'created'=>time()
                ];

                M('group_member')->insert($member_info);

                if (empty($is_first_create)) {//建群+积分

                    $_SESSION['point'] = $this->_getUserPoint($_SESSION['uid']);

                    $point = getPointRule('createGroup');//获得应扣取积分

                    deductPoint($point);//增加积分

                    addOptLog($_SESSION['uid'],'创建群','+' . $point,$_SESSION['username'],0);
                }

                $result['gid'] = $group['gid'];

                Json_success('创建成功', $result);

            } else {

                Json_error('创建失败');
            }
        }else{

            Json_error('请先登录');
        }

    }

    /**
     * @Title: groupInfo
     * @Description: todo(群组信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function groupInfo(){

        if ($this->auth->is_logged_in()) {

            $lng = $_POST['lng'];

            $lat = $_POST['lat'];

            $gid = $_POST['gid'];

            $group = M('group')->where(['gid'=>$gid])->find();

            if(empty($lng)||empty($lat)){

                $group['distance'] = '定位失败';
            }else{

                $distance = getDistanceBetweenPoints($group['lat'], $group['lng'], $lat, $lng);

                $distance = $distance['kilometers'];

                $group['distance'] = $distance;
            }

            if(empty($group)){

                Json_error('群组不存在');
            }

            $group['total_member'] = M('group_member')->where(['gid'=>$gid])->count();

            $exist = M('group_member')->where(['gid'=>$gid,'uid'=>$_SESSION['uid']])->find();

            if($exist){

                $group['is_join'] = 1;
            }else{

                $group['is_join'] = 0;
            }

            if($_SESSION['uid'] == $group['uid']){

                $group['is_admin'] = 1;
            }else{

                $group['is_admin'] = 0;
            }

            $group['members'] = M('group_member')->where(['gid'=>$gid])->order('created asc')->field('uid,username,nickname,user_huanxin,is_admin,avatar')->limit(12)->findall();

            Json_success('获取成功',$group);
        }else{

            Json_error('请先登录');
        }

    }

    /**
     * @Title: getMuchGroupsInfo
     * @Description: todo(通过环信id得到多个群组信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getMuchGroupsInfo(){

        if ($this->auth->is_logged_in()) {

            $group_ids = $_POST['group_ids'];

            if(empty($group_ids)){

                Json_error('没有查到');
            }
            $groups = M('group')->where('group_id in ('.$group_ids.')')->findall();

            foreach ($groups as $k=>$group){

                $groups[$k]['total_member'] = M('group_member')->where(['gid'=>$group['gid']])->count();

                if($_SESSION['uid'] == $group['uid']){

                    $groups[$k]['is_admin'] = 1;
                }else{

                    $group[$k]['is_admin'] = 0;
                }

                $is_member = M('group_member')->where(['uid'=>$_SESSION['uid'],'gid'=>$group['gid']])->find();

                if($is_member){

                    $groups[$k]['is_member'] = 1;
                }else{

                    $groups[$k]['is_member'] = 0;
                }
            }

            Json_success('获取成功',$groups);

        }else{

            Json_error('请先登录');
        }
    }

    /**
     * @Title: getGroupAllMembers
     * @Description: todo(查看群组全部成员)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getGroupAllMembers(){

        $gid = $_POST['gid'];

        $group = M('group')->where(['gid'=>$gid])->find();

        if(empty($group)){

            Json_error('群组不存在');
        }

        $members = M('group_member')->where(['gid'=>$gid])->order('created asc')->field('uid,username,nickname,user_huanxin,is_admin,avatar')->findall();

        Json_success('获取成功',$members);
    }

    /**
     * @Title: editGroupInfo
     * @Description: todo(修改群组信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editGroupInfo(){

        if ($this->auth->is_logged_in()) {

            $group_id = $_POST['gid'];

            $group = M('group')->where(['gid'=>$group_id])->find();

            if($group['uid']!=$_SESSION['uid']){

                Json_error('没有权限');
            }

            $avatar = $_POST['avatar'];
            $background = $_POST['background'];//背景
            $owner = $_POST['owner'];
            $address = $_POST['address'];
            $lng = $_POST['lng'];
            $lat = $_POST['lat'];
            $labs = $_POST['labs'];//标签名
            $desc = $_POST['desc'];//描述

            $data = [
                'lng' => $lng,
                'lat' => $lat,
                'owner' => $owner,
                'avatar' => $avatar,
                'desc' => $desc,
                'lab_name' => $labs,
                'address' => $address
            ];

            if($_SESSION['uid'] == $group['uid']){

                $data['background'] = $background;
            }

            $update = M('group')->where(['gid'=>$group_id])->update($data);

            if($update){

                Json_success('修改成功',$data);
            }
        }else{

            Json_error('请先登录');
        }
    }

    /**
     * @Title: jobSubscribe
     * @Description: todo(职位订阅)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function jobSubscribe()
    {
        $db = M('job_want');

        $data_job = array(
            'uid' => $_SESSION['uid'],
            'keywords' => $_POST['keywords'],
            'class' => $_POST['class'],
            'hope_city' => $_POST['hope_city'],
            'hope_salary' => $_POST['hope_salary'],
            'hope_career' => $_POST['hope_career'],
            'star' => $_POST['star'],
            'exp' => $_POST['exp'],
            'origin' => $_POST['origin'],
            'warn' => $_POST['warn'],
            'create_time'=>time(),
            'update_time'=>time()
        );
        $up = array(
            'keywords' => $_POST['keywords'],//关键词
            'class' => $_POST['class'],//职位分类
            'hope_city' => $_POST['hope_city'],//区域
            'hope_salary' => $_POST['hope_salary'],//薪资
            'hope_career' => $_POST['hope_career'],//福利
            'star' => $_POST['star'],//信誉度
            'exp' => $_POST['exp'],//经验
            'origin' => $_POST['origin'],//0全部1开心直招2企业直招3代招
            'warn' => $_POST['warn'],//0不提醒1三天自动提醒
            'update_time'=>time()
        );

        $uid = $_SESSION['uid'];
        $content = '您关注的求职意向有消息了，点击立即查看！';
        $title = '投递提醒';

        $hidden = array(
            'type'=>2,
            'data_type'=>202,
            'title'=>$title,
            'content'=>$content,
            'keywords' => $_POST['keywords'],
            'class' => $_POST['class'],
            'hope_city' => $_POST['hope_city'],
            'hope_salary' => $_POST['hope_salary'],
            'hope_career' => $_POST['hope_career'],//福利
            'star' => $_POST['star'],
            'exp' => $_POST['exp'],
            'origin' => $_POST['origin'],
        );

        $data_content =[
            'keywords' => $_POST['keywords'],
            'class' => $_POST['class'],
            'hope_city' => $_POST['hope_city'],
            'hope_salary' => $_POST['hope_salary'],
            'hope_career' => $_POST['hope_career'],//福利
            'star' => $_POST['star'],
            'exp' => $_POST['exp'],
            'origin' => $_POST['origin'],
        ];

        $create_data = [
            'title'=>$title,
            'content'=>$content,
            'uid'=>$uid,
            'type'=>2,
            'data_type'=>202,
            'data_content'=>json_encode($data_content),
            'hidden'=>$hidden,
        ];

        $data_job = array_merge($data_job,['push_content'=>json_encode($create_data),'is_push'=>1]);

        $up = array_merge($up,['push_content'=>json_encode($create_data),'is_push'=>1]);

        $re = $db->where('uid =' . $_SESSION['uid'])->find();

        if (empty($re)) {

            if ($db->insert($data_job)) {

                Json_success('求职意向添加成功', $data_job);
            }
        } else {

            if ($db->where('uid = ' . $_SESSION['uid'])->update($up)) {

                Json_success('订阅成功',$up);
            } else {

                Json_error('订阅失败');
            }
        }

    }

    /**
     * @Title: addCompany
     * @Description: todo(添加用户自己企业)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function addCompany(){

        $exist = M('user_add_company')->where(['name'=>$_POST['name']])->find();

        if($exist){

            Json_success('添加成功');
        }

        $data = [
            'name'=>$_POST['name'],
            'address'=>'',
            'state'=>0,
            'created'=>time(),
        ];

        $add = M('user_add_company')->insert($data);

        if($add){

            Json_success('添加成功');
        }else{

            Json_error('添加失败');
        }
    }

    /**
     * @Title: searchCompany
     * @Description: todo(找同事搜索公司)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function searchCompany(){

        $key = 'ef1874d4093c90f7219b6296efb344bd';
        $keywords = $_POST['keywords'];
        $city = $_POST['city'];

        $url = "http://restapi.amap.com/v3/place/text?key=$key&keywords=$keywords&types=170000&city=$city";

        $data = json_decode(file_get_contents($url),true);

        if(!empty($data['pois'])){

            $companys = $data['pois'];
        }else{

            $companys = [];
        }

        $my_companys = M('user_add_company')->where('state = 1 AND name LIKE "%' . $keywords . '%"')->findall();

        $companys = array_merge($companys,$my_companys);

        if(empty($companys)){

            Json_error('未查到相关企业');
        }else{

            Json_success('获取成功',$companys);
        }
    }

    /**
     * @Title: validateDaySign
     * @Description: todo(验证是否签到)
     * @author nipeiquan
     * @param $uid
     * @return  bool  返回类型
     */
    private function validateDaySign($uid){

        $sign = M('sign_log')->where('uid ='.$uid.' AND created >='.strtotime(date('Y-m-d')))->find();

        if(!empty($sign)){

            return false;
        }else{

            return true;
        }
    }

    /**
     * @Title: _getUserPoint
     * @Description: todo(得到用户积分)
     * @author nipeiquan
     * @param $uid
     * @return  mixed  返回类型
     */
    private function _getUserPoint($uid) {
        $db = M('user_point');
        $point = $db->where('uid= ' . $uid)->find();
        return $point['point'];
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
     * @Title: createHuanXinGroup
     * @Description: todo(创建环信群)
     * @author nipeiquan
     * @param $groupname
     * @param $desc
     * @param $owner
     * @return  mixed  返回类型
     */
    private function createHuanXinGroup($groupname,$desc,$owner){

        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $token = $rs['access_token'];
        $url=$this->url."/chatgroups/";
        $arr=array(
            'groupname'=> $groupname,//群组名称，此属性为必须的
            'desc'=> $desc,//群组描述，此属性为必须的
            'public'=> true,//是否是公开群，此属性为必须的
            'maxusers'=> 100,//群组成员最大数（包括群主），值为数值类型，默认值200，最大值2000，此属性为可选的
            'approval'=> true,//加入公开群是否需要批准，默认值是false（加入公开群不需要群主批准），此属性为必选的，私有群必须为true
            'owner'=> $owner,//群组的管理员，此属性为必须的
            'allowinvites'=>true,//是否允许群成员邀请别人加入此群。 true：允许群成员邀请人加入此群，false：只有群主才可以往群里加人
            'invite_need_confirm'=>false
        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, $arr, $header, "POST"),true);
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

    public function test(){

        $url = 'http://api.msg.vip/http/sms/send?authToken=279b206b58a9463588189cbd22d323ce';

        $key = 'e43dc0f0ba894176be13cff47a1c4135';

        $data = [
            'appid'=>'ff8080815d4b129b015d63460452003c',
            'data'=>[
                [
                    'mobile'=>'18321272927',
                    'content'=>'验证码2323',
                    'sign'=>'【先知】',
                ]

            ]
        ];

        $str = json_encode($data);

        $request = md5($str.$key);

        $header = array(
            'Accept: application/json',
            'Encrypt-Type:md5',
            'Content-Type: application/json;charset=utf-8',
            "Authorization: $request"
        );

        var_dump($this->curl($url, $data, $header, "POST"));
    }

    public function test1(){

        $members = M('group_member')->where('nickname = ""')->findall();

        foreach ($members as $k=>$member){

            $user = M('user')->where(['uid'=>$member['uid']])->find();

            M('group_member')->where(['uid'=>$member['uid']])->update(['nickname'=>$user['nickname']]);
        }
    }

}
