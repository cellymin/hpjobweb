<?php

class indexControl extends Control {

    function index() {
        $this->display();
    }



    //城市导航
    function citymap()
    {
        $db=M('city');
        $citys=$db->cache(86400*10)->where('is_open=1')->order('ucfirst')->findall();
        $citys_fmt=array();
        foreach ($citys as $value) {
            $citys_fmt[$value['ucfirst']][]=$value;
        }
        Json_success($citys);
        /*$this->assign('citys_format',$citys_fmt);
        $this->display('citymap');*/
    }

    public function getLocation(){

        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        $address = getCity($lat,$lng,2);

        Json_success('success',$address['formatted_address']);
    }

    //城市专栏招聘
    function cityColumn()
    {
        $db=M('city');
        $cond=array(
            'pinyin'=>$_GET['name'],
            'is_open'=>1//如果开启了主站
        );
        $city=$db->field('id,name')->where($cond)->find();
        if(!$city){
            $this->error('对不起，未找到城市信息。','index');
        }
        $this->assign('city',$city);
        $this->display('city');
    }
    //企业主页
    function company() {
        if (!isset($_GET['id']) or $_GET['id']==1) {
            $this->error('没有该企业的信息');
        }
        $uid = intval($_GET['id']);//企业ID
        $db = M('company_info');
        $company = $db->where('uid=' . $uid)->find();
        if (!$company) {
            $this->error('没有该企业的信息');
        }
        $cond = array('uid' =>$uid ,'expiration_time>'.time(),'verify=1','state=1');
        $recruits = $db->table('recruit')->where($cond)->field('recruit_id,start_time,recruit_name')->findall();
        $data=new data('recruit');
        $company=$data->convert($company);
        $this->assign('company', $company);
        $this->assign('recruits',$recruits);
        if(empty($company['tpl_style'])){
            $company['tpl_style']='skyblue';//默认风格
        }
        if(!empty($_GET['style']) && $uid==$_SESSION['uid']){//企业预览模板
            $company['tpl_style'] = $_GET['style'];
        }
        $this->display(PATH_ROOT . '/templates/company_tpl/' . $company['tpl_style'] . '/index');
    }

    //意见反馈
    function feedback(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $_POST['content']=htmlspecialchars($_POST['content']);
            $_POST['title']=strip_tags($_POST['title']);
            $_POST['name']=strip_tags($_POST['name']);
            $_POST['email']=strip_tags($_POST['email']);
            $_POST['type']=intval($_POST['type']);
            $_POST['created']=time();
            $db=M('feedback');
            if($db->insert()){
                $this->success('反馈成功！','index');
            }
        }
        $type=array(
            1=>'建议',
            2=>'咨询',
            3=>'举报',
            4=>'求助'
        );
        if(!isset($_GET['type'])){
            $_GET['type']=1;
        }
        $this->assign('type',$type);
        $this->display();
    }



    /**
     * @Title: sns
     * @Description: todo(广场首页)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function sns(){

//        $uid = empty($_GET['uid'])?0:intval($_GET['uid']);
//
//        $_POST['page'] = $_GET['page'] = 1;
//
//        $count = M('sns')->count();
//
//        $page = new page($count,10);
//
//        $sns = M('sns')->order('time desc')->findall($page->limit());
//
//        foreach($sns as $key=>$value){
//
//            if($value['uid']==$uid){
//                $sns[$key]['is_del'] = true;
//            }else{
//                $sns[$key]['is_del'] = false;
//            }
//
//            if(!empty($value['imgs'])){
//
//
//                $imgs = explode('#',$value['imgs']);
//
////                $thumb = str_replace('.png','-120-120.png',$imgs);
//
//                $sns[$key]['imgs'] = $imgs;
//            }
//
//            $sns[$key]['from_time'] = from_time($value['time']);
//
//
//            $comments = M('sns_comment')->order('created desc')->where(array('sns_id'=>$value['sid']))->findall();
//
//            $zan = M('sns_zan')->where(array('sns_id'=>$value['sid'],'uid'=>$uid))->find();
//
//            $sns[$key]['is_zan'] = empty($zan)?0:1;
//
//            $sns[$key]['comments'] = $comments;
//
//        }
//
//        $this->assign('sns',$sns);


        $this->display('app/index');
    }

    public function getItem(){

        $uid = empty($_GET['uid'])?0:intval($_GET['uid']);

        $_POST['page'] = $_GET['page'];

        $count = M('sns')->where(['del_state'=>0])->count();

        $page = new page($count,10);

        $sns = M('sns')->where(['del_state'=>0])->order('is_top desc,time desc')->findall($page->limit());

        foreach($sns as $key=>$value){

            if($value['uid']==$uid){
                $sns[$key]['is_del'] = true;
            }else{
                $sns[$key]['is_del'] = false;
            }

            if(!empty($value['imgs'])){
                $imgs = explode('#',$value['imgs']);
                $sns[$key]['imgs'] = $imgs;
            }

            $sns[$key]['from_time'] = from_time($value['time']);


            $comments = M('sns_comment')->order('created asc')->where(array('sns_id'=>$value['sid']))->findall();

            $zan = M('sns_zan')->where(array('sns_id'=>$value['sid'],'uid'=>$uid))->find();

            $sns[$key]['is_zan'] = empty($zan)?0:1;

            $sns[$key]['comments'] = $comments;

        }

        $this->assign('page',$page->nostylenext());

        $this->assign('sns',$sns);

        $this->display('app/item');
    }

    public function addhit(){
        $sid = $_POST['sid'];

        M('sns')->inc('hits','sid = '.$sid,1);
    }

    /**
     * @Title: award
     * @Description: todo(滚动记录)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function award(){


        $logs = M('prize_log')->where("name != '下次没准就能中哦！' and name != '下次没准就能中哦!' and name not like '%积分%'")->order('time desc')->findall();

        $this->assign('logs',$logs);

        $this->display('award/index');
    }

    /**
     * @Title: doAward
     * @Description: todo(抽奖)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function doAward(){

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($user_agent, 'MicroMessenger') == true) {
            Json_error('请先下载app哦');
        }
        $role = M('user_role')->where(['uid'=>$_POST['uid']])->find();

        if($role['rid']==7){

            Json_error('该功能不对业务员开放哦~');
        }

//        $log = M('prize_log')->where('uid ='.$_POST['uid'].' AND time >'.strtotime(date('Y-m-d')))->findall();
//
//        $count = count($log);
//
//        if($count >=5){
//
//            Json_error('每天最多抽奖5次哦');
//        }
//        if($_POST['uid']!=19454 && $_POST['uid']!=38){
//
//            Json_error('抽奖模块正在更新中...');
//        }
        $prize_arr = M('prize')->limit(8)->findall();

        foreach ($prize_arr as $key => $val) {
            $arr[$key] = $val['probability'];
        }

        $rid = $this->get_rand($arr);


        if(empty($_POST['uid'])){

            Json_error('系统错误',$rid);

        }else {

            $uid = intval($_POST['uid']);

            $user = M('user')->where(array('uid'=>$uid))->find();

            if(!empty($user['authid_qq']) && $user['is_bind']==2){//未绑定手机号码
                $info = array(
                    'status'=>20,
                    'msg'=>'请先绑定手机号码',
                    'data'=>array()
                );
                echo json_encode($info);
                die;
            }

            $client_id = $user['client_id'];

            if(empty($user)){
                Json_error('用户不存在');
            }

            $user_point = M('user_point')->where(array('uid' => $uid))->find();

            $point = $user_point['point'];

//            deductPoint($point,$id['uid']);//增加积分

            if ($point < 100) {

                Json_error('积分不足');

            } else {

                $point = $point - 100;

                M('user_point')->where(array('uid'=>$uid))->update(array('point'=>$point));

                $data = array(
                    'uid' => $uid,
                    'content' => '抽奖扣积分',
                    'point' => '-' . 100,
                    'created' => time(),
                    'ip' => ip_get_client(),//操作ip
                    'username' => $user['username'],
                    'time' => time(),
                    'type' => 0
                );

                M('opt_log')->insert($data);

                $prize =  $prize_arr[$rid];

                $data = array(
                    'pid'=>$prize['prize_id'],
                    'name'=>$prize['name'],
                    'uid'=>$uid,
                    'username'=>$user['username'],
                    'time'=>time()
                );

                $log_id = M('prize_log')->add($data);

                if(queryExistMissionLog($uid,'10')){
                    addMissionLog($uid,9,$prize['prize_id']);
                }else{
                    addMissionLog($uid,10,$prize['prize_id']);
                }

                switch($prize['type']){
                    case 1://实物
                        $content='恭喜你抽中'.$prize['name'].'，请您在两周内及时到开心工作线下体验店领取，领取奖品时请出示本条系统消息及有效证件，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！';
                        $title='抽奖提醒';
                        addNewMessages($uid,$content,$title,1,101,$prize['prize_id'],0,$user['username']);

                        Json_success('恭喜你抽中'.$prize['name'].'，请您在两周内及时到开心工作线下体验店领取，领取奖品时请出示本条系统消息及有效证件，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！',['rid'=>$rid,'log_id'=>$log_id]);
                        break;
                    case 2://积分
                        $content='恭喜你抽中'.$prize['name'].'，请您关注中奖积分账户的入账情况，如有疑问请联系客服4006920099！';
                        $title='抽奖提醒';
                        addNewMessages($uid,$content,$title,1,101,0,0,$user['username']);
                        $point = $point+$prize['point'];

                        M('user_point')->where(array('uid'=>$uid))->update(array('point'=>$point));

                        $data = array(
                            'uid' => $uid,
                            'content' => '抽奖中积分',
                            'point' => '+' . $prize['point'],
                            'created' => time(),
                            'ip' => ip_get_client(),//操作ip
                            'username' => $user['username'],
                            'time' => time(),
                            'type' => 0
                        );

                        M('opt_log')->insert($data);

//                        addMissionLog($uid,9,$prize['prize_id']);

                        Json_success('恭喜你抽中'.$prize['name'].',请您关注中奖积分账户的入账情况，如有疑问请联系客服4006920099',['rid'=>$rid,'log_id'=>$log_id]);

                        break;
                    case 3://未中奖
                        Json_success($prize['name'],['rid'=>$rid,'log_id'=>0]);
                        break;
                    case 4://话费
                        $content='恭喜你抽中'.$prize['name'].'，请您于3个工作日后关注手机话费的入账情况，如有疑问请联系客服4006920099！';
                        $title='抽奖提醒';
                        addNewMessages($uid,$content,$title,1,101,0,0,$user['username']);

//                        addMissionLog($uid,9,$prize['prize_id']);

                        Json_success('恭喜你抽中'.$prize['name'].'，请您于3个工作日后关注手机话费的入账情况，如有疑问请联系客服4006920099！',['rid'=>$rid,'log_id'=>$log_id]);
                        break;
                }

            }
        }
    }

    public function awardPush(){

        $prize_id = $_POST['log_id'];

        $prize = M('prize_log')->where('lid ='.$prize_id)->find();

        $uid = intval($prize['uid']);

        $user = M('user')->where(array('uid'=>$uid))->find();

        $client_id = $user['client_id'];

        if(empty($user)){
            Json_error('用户不存在');
        }

        $content = '';

        switch($prize['pid'])
        {
            case 1:
                Json_success('success');
                break;
            case 2:
                $content = '恭喜你抽中'.$prize['name'].'，请您在两周内及时到开心工作线下体验店领取，领取奖品时请出示本条系统消息及有效证件，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！';
                break;
            case 3:
                $content = '恭喜你抽中'.$prize['name'].'，请您于3个工作日后关注手机话费的入账情况，如有疑问请联系客服4006920099！';
                break;
            case 4:
                $content = '恭喜你抽中'.$prize['name'].'，请您在两周内及时到开心工作线下体验店领取，领取奖品时请出示本条系统消息及有效证件，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！';
                break;
            case 5:
                Json_success('success');
                break;
            case 6:
                $content = '恭喜你抽中'.$prize['name'].',请您关注中奖积分账户的入账情况，如有疑问请联系客服4006920099！';
                break;
            case 7:
                $content = '恭喜你抽中'.$prize['name'].'，请您于3个工作日后关注手机话费的入账情况，如有疑问请联系客服4006920099！';
                break;
            case 8:
                $content = '恭喜你抽中'.$prize['name'].'，请您在两周内及时到开心工作线下体验店领取，领取奖品时请出示本条系统消息及有效证件，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！';
                break;
        }

        $hidden = array(
            'type'=>1,
            'data_type'=>101,
            'title'=>'抽奖提醒',
            'content'=>$content
        );

        if(push(array($client_id),array('hidden'=>$hidden,'title'=>'抽奖提醒','content'=>$content))){

            Json_success('推送成功');
        }else{

            Json_error('推送失败');
        }

    }

    function get_rand($proArr) {


        $result = '';

        //概率数组的总概率精度
        $proSum = array_sum($proArr);

        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);

        return $result;

    }

//    public function teamAvatar(){
//
//        $id = $_GET['group'];
//
//        $avatar = M('group')->field('avatar')->where(array('group_id'=>$id))->find();
//
//        header('Content-Type:image/png');
//
//        echo file_get_contents($avatar['avatar']);
//
//    }

//    public function userAvatar(){
//
//        $username = $_GET['username'];
//
//        $avatar = M('user')->where('username = '.$username.' or phone = '.$username)->find();
//
//        header('Content-Type:image/png');
//
//        echo file_get_contents($avatar['avatar']);
//
//    }

//    public function userAvatarId(){
//
//        $uid = $_GET['uid'];
//
//        $avatar = M('user')->field('avatar')->where(array('uid'=>$uid))->find();
//
//        header('Content-Type:image/png');
//
//        echo file_get_contents($avatar['avatar']);
//
//    }



    public function upload(){

        C('UPLOAD_IMG_DIR', '');
        C('THUMB_ENDFIX', '');//只生成头像缩略图
        $upload = new upload(PATH_ROOT . '/uploads/dynamic', array('jpg', 'jpeg', 'png', 'gif'));
        $infos = $upload->upload();

        $data = array(
            'upload'=>$upload->error,
            'info'=>$infos
        );

        Json_success('success',$data);

    }

    /**
     * @Title: delSns
     * @Description: todo(删除动态)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delSns(){
        $sid = $_POST['sid'];
        if(empty($sid)){
            Json_error('动态不存在');
        }
        if(M('sns')->where('sid=' . $sid)->update(['del_state'=>1])){
            Json_success('删除成功');
        }else{
            Json_error('系统错误');
        }
    }

    /**
     * @Title: checkDifficult
     * @Description: todo(软件更新)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function checkDifficult(){

        $title = $_POST['title'];

        $versions = M('version_update')->where('vid > 0')->order('title desc')->find();

        if(empty($versions) || empty($title)){

            Json_success('已是最新',$versions);
        }elseif($versions['title'] <= $title){

            Json_success('已是最新',$versions);
        }
        if($versions['title'] > $title){

            Json_success('有新的更新',$versions);
        }
    }

    /**
     * @Title: haveUser
     * @Description: todo(判断是否有这个人)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function haveUser(){
        $username = $_POST['username'];
        if(empty($username)){
            Json_error('请输入一个帐号或昵称');
        }
        $user = M('user')->where('username= "' . $username .'" or nickname = "' .$username .'" or phone ="'.$username.'"')->find();
        if(!empty($user['phone']) && !empty($user['authid_qq'])){//如果是第三方登录，username替换成phone
            $user['username'] = $user['phone'];
        }
        if(empty($user)){
            Json_error('没查到用户');
        }else{
            Json_success('获取成功',$user);
        }
        Json_success('成功获取',$user);
    }

    public function testPush(){

        var_dump(push(array('66165e9e1deb2ff51d613883aee84382'),array('hidden'=>'hideen','title'=>'title','content'=>'content')));

    }

    /**
     * @Title: advice
     * @Description: todo(意见反馈)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function advice(){
        $phone = empty($_POST['phone']) ? '' : $_POST['phone'];

        $content = $_POST['content'];

        if(M('advice')->add(array('phone'=>$phone,'content'=>$content,'creat_time'=>time()))){
            Json_success('反馈成功');
        }else{
            Json_error('反馈失败');
        }
    }

    /**
     * @Title: getRecruitsByIds
     * @Description: todo(通过ids得到职位列表)
     * @author nipeiquan
     * @param $recruit_ids
     * @return  void  返回类型
     */
    public function getRecruitsByIds(){

        $recruit_ids = $_POST['recruit_ids'];

        $recruits = M('recruit')->where('recruit_id in ('.$recruit_ids.')')->findall();

        $convert = new data;
        if ($recruits) {
            foreach ($recruits as $key => $value) {
                $recruits[$key] = $convert->convert($value);
                $recruits[$key]['start_time'] = date('Y-m-d',$value['start_time']);
            }
        }
        Json_success('获取成功',$recruits);
    }

    /**
     * @Title: getUserByPhone
     * @Description: todo(通过环信username获取本地用户信息)(需要优化)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function getUserByPhone(){

//        Json_success('',array());
        $username = $_POST['username'];

        if(empty($username)){
            Json_error('获取失败');
        }

        $username = explode(',',$username);

        $uids = '';

        foreach($username as $key=>$value){
            $user = M('user')->where('username = '.$value.' or phone = '.$value)->find();

            $uids[] = $user['uid'];

        }

        $uids = array_filter($uids);

        foreach($uids as $key=>$value){
            if(empty($uids[$key])){
                unset($uids[$key]);
            }
        }

        $uids = implode(',',$uids);

        $uids = rtrim($uids,',');
//        $t1 = microtime(true);
//        $m1 = memory_get_usage(true);
//        echo $this->fixByte($m1). '<br />';
        $user = M('user')->in($uids)->findall();
//
//        $t2 = microtime(true);
//        $m2 = memory_get_usage(true);
//        echo '<br />' . $this->fixByte($m2). '<br />';
//        echo '<hr >';
//        echo 'time ' . round(($t2 - $t1), 4) .'<br />';
//        echo 'mem ' . $this->fixByte($m2 - $m1) .  '<br />';

//        $array  = array(
//            array(
//            "uid"=> "9090",
//            "phone"=> "",
//            "username"=> "17705528779",
//            "password"=> "3ff19fba94281dc4d94e8657f6c746aa",
//            "client_id"=> "",
//            "email"=> "",
//            "banned"=> "0",
//            "ban_reason"=> "0",
//            "newpass"=> "",
//            "newpass_key"=> "",
//            "newpass_time"=> null,
//            "created"=> "1468461698",
//            "last_ip"=> "183.207.217.164",
//            "last_login"=> "1468462952",
//            "email_verify"=> "0",
//            "lng"=> "",
//            "lat"=> "",
//            "gender"=> "0",
//            "nickname"=> "1770552****",
//            "authid_qq"=> "",
//            "authid_wx"=> "",
//            "authid_wb"=> "",
//            "authtype"=> "0",
//            "avatar"=> "",
//            "commission"=> "0",
//            "hometown"=> "",
//            "birthday"=> null,
//            "address"=> "",
//            "desc"=> "",
//            "from_id"=> "149",
//            "is_yewu"=> "0",
//            "is_online"=> "0",
//            "is_bind"=> "1",
//            "is_newuser"=> "1"
//            )
//        );

//
        Json_success('success',$user);
    }

    function fixByte($byte, $string = true, $dot_num = 9) {
        $ret = array(
            'data'=>$byte,
            'danwei'=>'Byte',
        );
        if ($byte < 1024) {
        } else if ($byte < 1024*1024) {
            $ret['data'] = round($byte / 1024, $dot_num);
            $ret['danwei']='K';
        } else if ($byte < 1024*1024*1024) {
            $ret['data'] = round($byte / (1024*1024), $dot_num);
            $ret['danwei']='M';
        } else if ($byte < 1024*1024*1024*1024) {
            $ret['data'] = round($byte / (1024*1024*1024), $dot_num);
            $ret['danwei']='GB';
        } else if ($byte < 1024*1024*1024*1024*1024) {
            $ret['data'] = round($byte / (1024*1024*1024*1024), $dot_num);
            $ret['danwei']='TB';
        }
        if ($string) {
            $ret = $ret['data'] . ' ' . $ret['danwei'];
        }
        return $ret;
    }


    /**
     * @Title: getWeiXinSignPackage
     * @Description: todo()
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function weiXinSignPackage(){

        $jssdk = new JSSDK();

        $signPackage = $jssdk->getSignPackage($_POST['url']);

        Json_success('success',$signPackage);

    }




    /**
     * @Title: actionDirectWeixinLogin
     * @Description: todo(微信登录跳转)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function directWeixinLogin(){

        $uri = __CONTROL__.'/directCallBack';

        $weixin = new weixin();

        $url = $weixin->createOauthUrlForCode($uri, 'snsapi_userinfo');

        Header("Location: $url");
    }

    /**
     * @Title: directCallBack
     * @Description: todo(网页授权回调)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function directCallBack(){

        $url = 'http://m.192.168.3.131/hpjobweb/login.html?code='.$_GET['code'];

        Header("Location: $url");

    }

    /**
     * @Title: run
     * @Description: todo(进程修改信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function run(){
//改这里后一定需重启 supervisorctl reload

        $worker= new GearmanWorker();

        $worker->addServer();

        $worker->addFunction('updateUser', function(GearmanJob $job){

            ob_start();
            echo 'say data:'.$job->workload().PHP_EOL;//显示传输过来的数据

            $data = json_decode($job->workload(),true);
//            file_put_contents(PATH_TEMP.'/json.log',$data,FILE_APPEND);
            $uid = $data['uid'];

            M('new_sns')->where(['uid'=>$uid])->update(['gender'=>$data['gender']]);
            M('new_sns_zan')->where(['uid'=>$uid])->update(['usex'=>$data['gender']]);
            M('user')->where(['uid'=>$uid])->update(['gender'=>$data['gender']]);

            if(!empty($data['avatar'])){

                M('friend')->where(['uid'=>$uid])->update(['avatar'=>$data['avatar']]);
                M('friend')->where(['f_uid'=>$uid])->update(['f_avatar'=>$data['avatar']]);
                M('group_member')->where(['uid'=>$uid])->update(['avatar'=>$data['avatar']]);
                M('new_comment')->where(['uid'=>$uid])->update(['uvatar'=>$data['avatar']]);
                M('new_guanzhu')->where(['uid'=>$uid])->update(['uvatar'=>$data['avatar']]);
                M('new_guanzhu')->where(['buid'=>$uid])->update(['buvatar'=>$data['avatar']]);
                M('new_message')->where(['uid'=>$uid])->update(['uavatar'=>$data['avatar']]);
                M('new_sns')->where(['uid'=>$uid])->update(['uvatar'=>$data['avatar']]);
                M('new_sns_zan')->where(['uid'=>$uid])->update(['uvatar'=>$data['avatar']]);
                M('new_sns_zan')->where(['buid'=>$uid])->update(['buvatar'=>$data['avatar']]);
                M('user')->where(['uid'=>$uid])->update(['avatar'=>$data['avatar']]);
            }

            if(!empty($data['nickname'])){

                M('new_comment')->where(['uid'=>$uid])->update(['unickname'=>$data['nickname']]);
                M('new_comment')->where(['buid'=>$uid])->update(['bunickname'=>$data['nickname']]);
                M('new_guanzhu')->where(['uid'=>$uid])->update(['unickname'=>$data['nickname']]);
                M('new_guanzhu')->where(['buid'=>$uid])->update(['bunickname'=>$data['nickname']]);
                M('new_message')->where(['uid'=>$uid])->update(['unickname'=>$data['nickname']]);
                M('new_sns')->where(['uid'=>$uid])->update(['unickname'=>$data['nickname']]);
                M('new_sns_zan')->where(['uid'=>$uid])->update(['unickname'=>$data['nickname']]);
                M('new_sns_zan')->where(['buid'=>$uid])->update(['bunickname'=>$data['nickname']]);
                M('user')->where(['uid'=>$uid])->update(['nickname'=>$data['nickname']]);
                M('group_member')->where(['uid'=>$uid])->update(['nickname'=>$data['nickname']]);
            }

            if(!empty($data['hometown_id'])){//hometown

                M('new_sns')->where(['uid'=>$uid])->update(['hometown_id'=>$data['hometown_id']]);
                M('user')->where(['uid'=>$uid])->update(['hometown_id'=>$data['hometown_id'],'hometown'=>$data['hometown']]);
            }

            if(!empty($data['birthday'])){

                M('new_sns')->where(['uid'=>$uid])->update(['ubirthday'=>$data['birthday']]);
                M('user')->where(['uid'=>$uid])->update(['birthday'=>$data['birthday']]);
            }

            if(isset($data['gender'])){

                M('new_sns')->where(['uid'=>$uid])->update(['gender'=>$data['gender']]);
                M('new_sns_zan')->where(['uid'=>$uid])->update(['usex'=>$data['gender']]);
                M('user')->where(['uid'=>$uid])->update(['gender'=>$data['gender']]);
            }

            return "";

        });

        $worker->addFunction('sendMessage', function(GearmanJob $job){

            ob_start();
            echo 'say data:'.$job->workload().PHP_EOL;//显示传输过来的数据

            $data = json_decode($job->workload(),true);
//            file_put_contents(PATH_TEMP.'/json.log',$job->workload(),FILE_APPEND);

            if(!empty($data)){

                $IDS = $data['id_numbers'];

                if(!empty($IDS)){

                    $IDS = '"'.implode('","',$IDS).'"';

                    $user_infos = M('user_info')->where('id_number in('.$IDS.')')->findall();

                    $uids = implode(',',array_filter(array_column($user_infos,'uid')));

                    $time = time() - 120*3600*24;

                    if(!empty($uids)){

                        $users = M('user')->where('uid in('.$uids.') AND last_login < '.$time)->findall();

                        $mobiles = implode(',',array_filter(array_column($users,'username')));

                        sendSmsMsg($mobiles,'','您有一笔工资已经到账，点击下载查询工资明细→http://dwz.cn/5US6kQ，求职服务热线：4006920099！');
                    }

                }
            }

            return "";

        });

        $worker->addFunction('sendSysMessage',function(GearmanJob $job){
            ob_start();
            $data = json_decode($job->workload(),true);
            $type = $data['type'];
            $mid = $data['mid'];
            $data_type = $data['data_type'];
            $title = $data['title'];
            $content = $data['content'];

//            $uid = 38;
//            $user = M('user')->where(['uid'=>$uid])->find();
//            M('new_message_user')->insert([
//               'mid' => $mid,
//               'buid' => 27585,
//                'create_at' => time()
//            ]);
//            M('new_message_user')->insert([//重庆Android
//                'mid' => $mid,
//                'buid' => 19454,//19454
//                'create_at' => time()
//            ]);
//            M('new_message_user')->insert([//重庆Android
//                'mid' => $mid,
//                'buid' => 19547,//19454
//                'create_at' => time()
//            ]);
//            M('new_message_user')->insert([//重庆Android
//                'mid' => $mid,
//                'buid' => 19547,//19454
//                'create_at' => time()
//            ]);
//            M('new_message_user')->insert([//重庆Android
//                'mid' => $mid,
//                'buid' => 38,//19454
//                'create_at' => time()
//            ]);
//            M('new_message_user')->insert([//重庆Android
//                'mid' => $mid,
//                'buid' => 27648,//19454
//                'create_at' => time()
//            ]);
//            M('new_message_user')->insert([//重庆Android
//                'mid' => $mid,
//                'buid' => 26889,//19454
//                'create_at' => time()
//            ]);
//            M('new_message_user')->insert([//重庆Android
//                'mid' => $mid,
//                'buid' => 27921,//19454
//                'create_at' => time()
//            ]);
//            M('new_message_user')->insert([//重庆Android
//                'mid' => $mid,
//                'buid' => 2,//19454
//                'create_at' => time()
//            ]);
//            $hidden = array(
//                        'type' => 1,
//                        'title' => $title,
//                        'data_type' => $data_type,
//                        'content' => $content
//                    );
//            push(['f8ebc37b024e85bd31f2f5abcc678d1d','3c2ac95da11e854fcc0c20870009f6d2','f8ebc37b024e85bd31f2f5abcc678d1d','e14cb44cf8aade8fd5232d7a4c9fa235','8483851d46dc330110d9777774786ed9','bd35fc3ffb0c5c163e61d1dcc5f7a932','8ffc27b6a3e688cf371089354c453b84','3b074e2fb8798cd3597a58ff37ced8a4'], array('hidden' => $hidden, 'title' => $title, 'content' => $content));



            $where = '';
            if($type == 1){
                $where['rid'] = 8;
            }
            if($type == 2){
                $where['rid'] = 7;
            }
            if(empty($type)){
                $count = M('user')->count();
                $page = new page($count,100);
                for($i = 0;$i < $page->total_page;$i++){
                    $users = M('user')->findall(["limit" => max(0, $i * $page->arc_row) . "," . $page->arc_row]);
                    $value = '';
                    $client_id = [];
                    foreach ($users as $k => $v) {
                        $value .= ',' . '(' . $mid . ',' . $v['uid'] . ',' . time() . ')';
                        $client_id[] = $v['client_id'];
                    }
                    $value = ltrim($value, ',');
                    $sql = "INSERT INTO hp_new_message_user (mid,buid,create_at) VALUES $value ;";
                    M('new_message_user')->exe($sql);
                    $client_id = array_unique(array_filter($client_id));
                    $hidden = array(
                        'type' => 1,
                        'title' => $title,
                        'data_type' => $data_type,
                        'content' => $content
                    );
                    push($client_id, array('hidden' => $hidden, 'title' => $title, 'content' => $content));
                }
            }else{
                $count = M('user_role')->where($where)->count();
                $page = new page($count, 100);
                for ($i = 0; $i < $page->total_page; $i++) {
                    $uids_arr = [];
                    $client_id = [];
                    $roles = M("user_role")->where($where)->findall(["limit" => max(0, $i * $page->arc_row) . "," . $page->arc_row]);
                    foreach ($roles as $role) {
                        $uids_arr[] = $role['uid'];
                    }
                    $uids = implode(',', $uids_arr);
                    $sql = '';
                    if(!empty($uids)) $sql = 'uid in (' . $uids . ')';
                    $users = M('user')->where($sql)->field(['uid', 'client_id'])->findall();
                    $value = '';
                    foreach ($users as $k => $v) {
                        $value .= ',' . '(' . $mid . ',' . $v['uid'] . ',' . time() . ')';
                        $client_id[] = $v['client_id'];
                    }
                    $value = ltrim($value, ',');
                    $sql = "INSERT INTO hp_new_message_user (mid,buid,create_at) VALUES $value ;";
                    M('new_message_user')->exe($sql);
                    $client_id = array_unique(array_filter($client_id));
                    $hidden = array(
                        'type' => 1,
                        'title' => $title,
                        'data_type' => $data_type,
                        'content' => $content
                    );
                    push($client_id, array('hidden' => $hidden, 'title' => $title, 'content' => $content));
                }
            }
            return "";
        });
        while ($worker->work());
    }

    /**
     * @Title: timer
     * @Description: todo(三天内自动根据订阅推送职位)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function timer(){

        $time = time() - 24*3600;//一天前的给他推送
        $need_push = M('job_want')->where('is_push = 1 AND push_content != "" AND update_time <'.$time)->findall();

        foreach ($need_push as $k=>$value){

            $hidden = json_decode($value['push_content'],true);

            $where = [];

            if(!empty($hidden['keywords'])){

                $where[] = "recruit_name like '%".$hidden['keywords']."%'";
            }

            if(!empty($hidden['class'])){

                $where[] = 'class_two= '.$hidden['class'] .' or class = '.$hidden['class'];
            }

            if(!empty($hidden['hope_city'])){

                $where[] = 'town = '. $hidden['hope_city']. ' or city = '.$hidden['hope_city'];
            }

            if(!empty($hidden['hope_salary'])){

                $where[] = 'salary = '.$hidden['hope_salary'];
            }

            if(!empty($hidden['hope_career'])){

                $welfare = $hidden['hope_career'];
                $where[] = 'welfare like '%".$welfare."%'';
            }

            if(!empty($hidden['star'])){

                $where[] = 'star ='.$hidden['star'];
            }

            if(!empty($hidden['exp'])){

                $where[] = 'work_exp ='.$hidden['exp'];
            }

            if(!empty($hidden['origin'])){

                $where[] = 'origin ='.$hidden['origin'];
            }

            $recruit = M('recruit')->where($where)->limit (10)->findall();

            if(!empty($recruit)){

                K('message')->createMission([$value['uid']],0,$hidden);

                M('job_want')->where(['id'=>$value['id']])->update(['is_push'=>2]);
            }
        }
    }

}