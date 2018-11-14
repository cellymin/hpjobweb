<?php

class profileControl extends Control {
    private $auth;
    private $email_activate;
    private $user;
    private $resume;
    private $app_key='ttouch#kuaile';
    private $client_id='YXA6tfPWgFtsEeWzNxtHA-A9OA';
    private $client_secret='YXA6hhR-xo8PGuE3BAqMfTQlfNAkYLs';
    private $url = "https://a1.easemob.com/ttouch/kuaile";
    function __construct()
    {
        parent::__construct();
      $this->resume = K('resume');
        $this->email_activate=K('email_activate');
        $this->auth = new auth;
        /*if (!$this->auth->is_logged_in()) {
            if (ajax_request()) {
                echo json_encode(array('status' => 0, 'msg' => '请登录后操作'));
                exit;
            } else {
                $this->error(L('please_login'), 'index/auth/index');
            }
        }
        if (!$this->auth->check_uri_permissions()) {
            $this->error($this->auth->error);
        }
        $this->user=K('user');
        $this->resume=K('resume');*/
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
     * @Title: createNewRes
     * @Description: todo(创建简历)
     * @author liuzhipeng
     * @return  void  返回类型
     */
         function createNewRes() {
             $re = M('resume');
             $wk = M('work_exp');
             $ba = M('resume_basic');
             $uid = $_POST['uid'];
             $default = $re->where('uid = '.$uid)->find();

             if(isset($_POST['code'])){

                 $code = validateSmsCode($_POST['telephone'],$_POST['code']);

                 if(!$code){

                     Json_error('验证码错误');
                 }
             }

             $arr=array(//简历名称
                 'uid' => $uid,
                 'created' => time(),
                 'updated' => time($_POST['created']),
             );
             if(empty($default)){
                 $arr['default']=1;
             }
             if($resume=$re->insert($arr)) {

                 M('user')->where(['uid'=>$uid])->update(['resume_phone'=>$_POST['telephone']]);

                 if(!empty($_POST['photo'])){

                     $avatar = $_POST['photo'];
                 }else{

                     C('UPLOAD_IMG_DIR', '');
                     C('THUMB_ENDFIX', '');//只生成头像缩略图
                     $upload = new upload(PATH_ROOT . '/uploads/resume_avatars', array('jpg', 'jpeg', 'png', 'gif'));
                     $info = $upload->upload();
                     if ($info) {
                         $avatar = $info[0]['path'];
                     }
                 }

                 if ($re->where('resume_id=' . $resume)->update(array('avatar' =>__ROOT__ .'/'.$avatar))) {
                 } else {
                     Json_error('上传失败');
                 }
                 $bac = array(
                     'uid' => $uid,
                     'resume_id' => $resume,
                     'name' => $_POST['name'],
                     'gender' => $_POST['gender'],
                     'birthday' => $_POST['birthday'],
                     'link_email' => $_POST['link_email'],
                     'profile' => $_POST['profile'],
                     'telephone' => $_POST['telephone'],
                     'degree' => $_POST['degree'],
                     'major'=>$_POST['major'],
                     'work_exp' => $_POST['work_exp'],
                     'hope_provice' => $_POST['hope_provice'],
                     'hope_city' => $_POST['hope_city'],
                     'hope_town' => $_POST['hope_town'],
                     'hope_salary' => $_POST['hope_salary'],
                     'hope_career' => $_POST['hope_career'],
//                     'hope_career_t' => $_POST['hope_career_t'],
                     'deliver' => $_POST['deliver']
                 );
                 $work_exp = json_decode($_POST['work'], true);
                 foreach ($work_exp as $key => $value) {
                     $work = array(
                         'company_name' => $value['company_name'],
                         'department' => $value['department'],
                         'job_name' => $value['job_name'],
                         'job_start' => $value['job_start'],
                         'job_end' => $value['job_end'],
                         'job_desc' => $value['job_desc'],
                         'uid' => $_POST['uid'],
                         'resume_id' => $resume
                     );
                     $wk->insert($work);
                 }

                 if($_POST['deliver'] == 1 && !empty($_POST['hope_town']) && !empty($_POST['hope_salary']) &&!empty($_POST['hope_career'])){//三天内自动投递
                     $hope = M('resume_basic')->where(array('resume_id'=>$_POST['resume_id'],'deliver'=>1))->find();
                     $town = $hope['hope_town'];
//                     $salary = $hope['hope_salary'];
                     $career = $hope['hope_career'];
                     $town= mb_substr($town,0,-1,'utf-8');

                     if($_POST['hope_salary'] == '不限'){
                         $salary = '';
                     }elseif($_POST['hope_salary'] == '面议'){
                         $salary = 1;
                     }elseif($_POST['hope_salary'] == '2000元/月以下'){
                         $salary = 2000;
                     }elseif($_POST['hope_salary'] == '2001-3000元/月'){
                         $salary = 200103000;
                     }elseif($_POST['hope_salary'] == '3001-5000元/月'){
                         $salary = 300105000;
                     }elseif($_POST['hope_salary'] == '5001-8000元/月'){
                         $salary = 500108000;
                     }elseif($_POST['hope_salary'] == '8001-10000元/月'){
                         $salary = 800110000;
                     }elseif($_POST['hope_salary'] == '10001-15000元/月'){
                         $salary = 1000115000;
                     }elseif($_POST['hope_salary'] == '15001-25000元/月'){
                         $salary = 1500025000;
                     }elseif($_POST['hope_salary'] == '25001元/月以上'){
                         $salary = 2500000000;
                     }

                     $career_id = M('linkage')->where('title like "%'.$career.'%"')->find();

                     $city = M('city')->where('name like "%'.$town.'%"')->find();

                     $city_id = $city['id'];

                     $re = M('recruit')->where(array('town'=>$city_id,'salary'=>$salary,'class'=>$career_id['laid']))->limit(10)->findall();
                     if(!empty($re)){
                         foreach ($re as $key=>$val){
                             $data = array(
                                 'uid' => $_POST['uid'],
                                 'rel_name'=>$_POST['name'],
//                             'username'=>$_SESSION['username'],
                                 'resume_id' => $_POST['resume_id'],
                                 'recruit_id' => $val['recruit_id'],
                                 'company_name' => $val['company_name'],
                                 'recruit_name' => $val['recruit_name'],
                                 'resume_name' => $hope['hope_career'],
                                 'company_id' => $val['uid'],
                                 'sendtime' => time()
                             );
                             M('deliver')->insert($data);
                             $uid=$_SESSION['uid'];
                             $content='有相似职位,系统已为您自动投递';
                             $title='投递提醒';
                             addMessages($uid,$content,$title);

                         }

                     }
                 }

                 $data = array(
                     'name' => $arr,
                     'work' => $work_exp,
                     'basic' => $bac,
                     'avatar' => $avatar
                 );
                 if ($ba->insert($bac)) {
                     $count = M('opt_log')->field('time')->where(array('uid' => $uid, 'content' => '完善简历'))->count();
                     if ($count == 0){
                         $_SESSION['point'] = $this->_getUserPoint($uid);
                         $user = M('user')->where('uid = ' . $uid)->find();
                         $username = $user['username'];
                         $point = getPointRule('editResume');//获得应扣取积分

                         deductPoint($point,$uid);//增加积分

                         $con = '完善简历';

                         $result = array(
                             'uid' => $uid,
                             'content' => $con,
                             'point' => '+' . $point,
                             'created' => time(),
                             'ip' => ip_get_client(),//操作ip
                             'username' => $username,
                             'time' => time(),
                             'type' => 2
                         );

                         M('opt_log')->insert($result);

                     }

                     if(!queryExistMissionLog($uid,'11')){
                         addMissionLog($uid,11,$uid);
                     }
                     $db=M('user_info');
                     $sql = "select a.uid,a.username, a.normalmanid,b.rid,c.rname,c.title,d.verify from hp_user as a left join hp_user_role as b on a.normalmanid=b.uid left join hp_role as c on b.rid=c.rid left join 
                     hp_user_info as d on a.uid=d.uid where a.uid=" .$_POST['uid'] . " and c.state=1 and c.title='求职者'";
                     $info = $db->query($sql);
                     //查询该用户的邀请人的邀请佣金记录是否存在
                     $if_exist = M('commission_log')->where(array('uid'=>intval($info[0]['normalmanid']),'from_id'=>$_POST['uid']))->count();
                     //$if_exist大于0不再返现
                     $uname =  $db->query("SELECT username FROM  hp_user where uid=".intval($info[0]['normalmanid']));//邀请人用户名，手机号
                     $num = M('resume')->where(array('uid'=>$_POST['uid'],'verify'=>1))->count();//现存有效简历数量,简历数量大于0不再计数
                     if($if_exist==0 && $num==0 && (int)$info[0]['verify']) {//没有创建简历，没有返现邀请人过邀请佣金,已经认证通过
                         $mess = [];
                         $mess['uid'] = intval($info[0]['normalmanid']);//邀请人
                         $mess['content'] = '邀请返现';
                         $mess['username'] = $uname[0]['username'];//邀请人姓名，可用于手机号
                         $mess['create_time'] = time();
                         $mess['type'] = 3;
                         $mess['from_id'] = $_GET['uid'];//被邀请人
                         $mess['verify'] = 0;//未审核
                         $res2 = M('commission_log')->insert($mess);
                     }
                     Json_success('创建成功', $data);

                 } else {
                     Json_error('创建失败');
                 }
             }
    }
    /**
     * @Title: updateResume
     * @Description: todo(修改简历)
     * @author liuzhipeng
     * @return  void  返回类型
     */
     function updateResume(){
         $re = M('resume');
         $ba = M('resume_basic');
         $wk = M('work_exp');

         if(isset($_POST['code'])){

             $code = validateSmsCode($_POST['telephone'],$_POST['code']);

             if(!$code){

                 Json_error('验证码错误');
             }
         }
//         $condition = 'resume_id= '.$_POST['resume_id']  . ' AND uid = ' . $_POST['uid'];
         $condition = array('resume_id'=>$_POST['resume_id'],'uid'=>$_POST['uid']);
             $basic=array(//求职意向
                 'name' => $_POST['name'],
                 'gender' => $_POST['gender'],
                 'birthday' => $_POST['birthday'],
                 'major'=>$_POST['major'],
                 'link_email'=>$_POST['link_email'],
                 'profile'=>$_POST['profile'],
                 'telephone' => $_POST['telephone'],
                 'degree'=> $_POST['degree'],
                 'work_exp' =>  $_POST['work_exp'],
                 'hope_provice'=>$_POST['hope_provice'],
                 'hope_city' =>$_POST['hope_city'],
                 'hope_town' =>$_POST['hope_town'],
                 'hope_salary'=>$_POST['hope_salary'],
                 'hope_career'=>$_POST['hope_career'],
//                 'hope_career_t'=>$_POST['hope_career_t'],
                 'deliver'=>$_POST['deliver']
             );

         if(!empty($_POST['work'])){

             $wk->where(array('resume_id'=>$_POST['resume_id']))->del();

             $work =  json_decode($_POST['work'],true);
             foreach($work as $key=>$value){
                 $work_arr=array(
                     'uid'=>$_POST['uid'],
                     'resume_id'=>$_POST['resume_id'],
                     'company_name'=>$value['company_name'],
                     'department' =>$value['department'],
                     'job_name' =>$value['job_name'],
                     'job_start' =>$value['job_start'],
                     'job_end'  =>$value['job_end'],
                     'job_desc'=>$value['job_desc'],
                 );
                 $wk->where($condition)->insert($work_arr);
             }
         }

         if(!empty($_POST['photo'])){

             $avatar = $_POST['photo'];
         }else{

             C('UPLOAD_IMG_DIR','');
             C('THUMB_ENDFIX','');//只生成头像缩略图

             $upload=new upload(PATH_ROOT.'/uploads/resume_avatars',array('jpg','jpeg','png','gif'));

             $info=$upload->upload();

             if($info){
                 $avatar=$info[0]['path'];
             }
             if(empty($info)){

                 $resume = $re->where($condition)->find();
                 $avatar = empty($resume) ? '' : $resume['avatar'];
                 $avatar = substr($avatar,22);
             }
         }

         if($re->where($condition)->update(array('avatar'=>__ROOT__ .'/'.$avatar))){

         }else{
             Json_error('上传失败');
         }

         if(!queryExistMissionLog($_POST['uid'],'11')){
             addMissionLog($_POST['uid'],11,$_POST['uid']);
         }

        if($ba->where($condition)->update($basic)){
            Json_success('修改成功');
        }else{
            Json_error('修改失败');
        }
     }



    /**
     * @Title: createResumeJob
     * @Description: todo(添加工作经验)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function createResumeJob(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $db=M('work_exp');
            $data[] = array(
                'uid' =>$_POST['uid'],
                'resume_id' => $_POST['resume_id'],
                'company_name' => $_POST['company_name'],
                'job_start' => ($_POST['job_start']),
                'job_end' => ($_POST['job_end']),
                'department' => $_POST['department'],
                'job_name' =>$_POST['job_name'],
                'job_desc' => $_POST['job_desc']
            );
            if($db->insert($data)){
                Json_success('添加工作经验成功',$data);
            }
        }
    }

    /**
     * @Title: updateWorkExp
     * @Description: todo(修改工作经验)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function updateWorkExp(){
            $db = M('work_exp');
            $data =array(
                'company_name' => $_POST['company_name'],
                'job_start' => ($_POST['job_start']),
                'job_end' => ($_POST['job_end']),
                'department' => $_POST['department'],
                'job_name' =>$_POST['job_name'],
                'job_desc' => $_POST['job_desc']
            );
            if($db->where('work_exp_id='.$_POST['work_exp_id'])->update($data)){
                Json_success('修改成功',$data);
            }
    }

    /**
     * @Title: jobExpList
     * @Description: todo(工作经验列表)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function jobExpList(){

        $cond = 'resume_id='.$_POST['resume_id'].' AND uid=' . $_POST['uid'];
        $data = $this->resume->getResumeExp($cond);
        if($data){
        Json_success('获取成功',$data);
        }
    }

    /**
     * @Title: deleteResumeJob
     * @Description: todo(删除工作经验)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function deleteResumeJob(){
        $db = M('work_exp');

        $condition = 'work_exp_id=' . intval($_POST['work_exp_id']) . ' AND uid=' . $_POST['uid'];
        if ($db->where($condition)->del()) {
            Json_success('删除一条工作经验成功');
        }
    }

    /**
     * @Title: resume
     * @Description: todo(我的简历)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function resume(){
        $cond = 'uid='  . $_POST['uid'];
        $resume=M('resume')->order('updated desc')->where($cond)->field('resume_id,updated,default')->findall();
        $db = M('resume_basic');
        $data = array();
        foreach($resume as $key=>$value){
            $basic = $db->where(array('resume_id'=>$value['resume_id']))->field('hope_provice,hope_city,hope_town,hope_salary,hope_career,hope_career_t')->find();
            if(!empty($basic)){
                $basic['default']=$value['default'];
                $basic['updated'] = $value['updated'];
                $basic['resume_id'] = $value['resume_id'];
                $data[]= $basic;
            }

        }
        if(!empty($data)){
            Json_success('获取成功',$data);
        }else{
            Json_error('您还没有简历');
        }
    }

    /**
     * @Title: refreshResume
     * @Description: todo(刷新简历)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function refreshResume(){
        $cond=array('uid'=>$_POST['uid'],'resume_id'=>intval($_POST['resume_id']));
        $resume=$this->resume->getResume($cond,'updated');
        if($resume['updated']>=(date('Y-m-d'))){
            Json_error('对不起，你今天已经刷新过简历了，明天再来吧！');
        }
        if($this->resume->updateResume('resume',$cond,array('updated'=>time()))){
            Json_success('恭喜你，简历刷新成功！');
        }
    }

    /**
     * @Title: del
     * @Description: todo(删除简历)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function del() {
        $db = M('resume');
        $condition = 'resume_id=' .$_POST['resume_id']. ' AND uid='.$_POST['uid'];
        if ($db->table('resume')->where($condition)->del()) {
            $db->table('resume_basic')->where($condition)->del();
            $db->table('resume_edu')->where($condition)->del();
            $db->table('work_exp')->where($condition)->del();
            $num = M('resume')->where(['uid'=>$_POST['uid']])->count();

            if(!$num){

                M('user')->where(['uid'=>$_POST['uid']])->update(['resume_phone'=>'']);

            }
            Json_success('简历删除成功');
        }else{
            Json_error('简历删除失败');
        }
    }



    function viewResume() {
        $cond = 'resume_id=' . $_GET['id'] . ' AND uid=' . $_SESSION['uid'];
        $this->resume->incViews($cond);//增加查看次数
        $resume = array();
        $resume['resume'] = $this->resume->getResume($cond);
        if ($resume['resume']) {
            $data = new data('resume_basic');
            $resume['basic'] = $data->convert($this->resume->getResumeBasic($cond));

            $data = new data('resume_edu');
            $resume['edu'] = $data->convert($this->resume->getResumeEdu($cond));

            $data = new data('work_exp');
            $resume['exp'] = $data->convert($this->resume->getResumeExp($cond));

            $resume['append'] = $this->resume->getResumeAppend($cond);
            //$point = abs(getPointRule('download_resume'));
            $this->assign('resume', $resume);

            //$this->assign('point', $point);
            $this->display(PATH_ROOT . '/templates/resume_tpl/' . $resume['resume']['style'] . '/resume');
        } else {
            echo '未找到简历信息!';
        }
    }




    /**
     * @Title: lookThrough
     * @Description: todo(谁看过我)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function lookThrough(){
      /*  $db = M('resume_download');
        $uid = $_POST['uid'];
        $num = $db->where('uid ='.$uid)->count();
        $page = new page($num,15);
        $re=$db->where('uid ='.$uid)->findall();*/
//        if(empty($re)){
            Json_error('没有数据');
//        }
        /*$downloads = $db->field('resume_name,company_name,created,company_id,uid')->where('uid ='.$uid)->order('created desc')->findall($page->limit());
        $company = M('company_info');
        $info = $company->field('name,company_industry,company_property,company_scope,logo,star')->where('uid=' . $downloads['0']['company_id'])->findall($page->limit());
        $data = new data();
        foreach($info as $key =>$value){
            $info[$key] = $data->convert($value);
        }
        $info = $data->convert($info);
        if ($company->field('name,company_industry,company_property,company_scope,logo,star')->where('uid=' . $downloads['0']['company_id'])->findall($page->limit())) {
            Json_success('获取成功', $info);
        } else {
            Json_error('没有公司看过你');
        }*/
    }

    //个人资料
    /*function info() {
        $db = M('user_info');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $db->where('uid=' . $_SESSION['uid'])->update($_POST);
        }
        $field = $db->where('uid=' . $_SESSION['uid'])->find();
        Json_success($field);
    }*/
    //修改密码
    /*public function password(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(trim($_POST['old_pwd'])==''){
                Json_error('必须输入旧密码');
            }
            $length=strlen($_POST['password']);
            if($length>20 || $length<6){
                Json_error('密码长度必须在6到20位之间！');
            }
            if($_POST['password']!=$_POST['re_password']){
                Json_error('两次密码不一样。');
            }
            if($this->auth->change_password($_POST)){
                setcookie(C('AUTH_AUTOLOGIN_COOKIE_NAME'), '', time() - 100000, '/'); //删除cookie
                $this->session_destroy();
                Json_success('密码修改成功');
            }
            Json_error($this->auth->error);
        }
    }*/

    /**
     * @Title: avatars
     * @Description: todo(修改头像)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function avatars() {
        $db=M('user_info');
        $db_info=$db->field('avatar')->where('uid='.$_SESSION['uid'])->find();
        $avatar=$db_info['avatar'];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            C('UPLOAD_IMG_DIR','');//图片没有附文件夹
            C('THUMB_ENDFIX','');//只生成头像缩略图（即覆盖原文件）
            $upload=new upload(PATH_ROOT.'/uploads/avatars',array('jpg','jpeg','png','gif'),3145728,0,1,array(100,100,4));
            $info=$upload->upload();
            if($info){
                $db->where('uid='.$_SESSION['uid'])->update(array('avatar'=>$info[0]['thumb']));
                unlink(PATH_ROOT.'/'.$avatar);//删除之前的头像文件
                Json_success('头像修改成功');
            }else{
                Json_error($upload->error);
            }
        }
    }

    /*private function _buildAuthItem($post,&$data){
        $activation_key = token();
        $data = array(
            'uid' => $_SESSION['uid'],
            'activation_key' => $activation_key,
            'created' => time(),
            'email' => $post['email']
        );
    }*/

    //安全认证


    /**
     * @Title: delFav
     * @Description: todo(取消职位收藏)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function delFav() {
        $recruit_id = $_POST['recruit_id'];
        $db = M('favorite');
        if ($db->where('uid=' . $_SESSION['uid'] . ' AND recruit_id=' . $recruit_id)->del()) {
            Json_success('取消成功');
        }else{
            Json_error('取消失败');
        }
    }

    /**
     * @Title: collectRecruit
     * @Description: todo(收藏职位)
     * @author nipeiquan
     * @param $data
     * @return  json  返回类型
     */
    function favorite() {

        $recruit_id = $_POST['recruit_id'];

        $uid = $_SESSION['uid'];

        $recruit = M('recruit')->where('recruit_id=' . $recruit_id)->find();

        $company = M('company_info')->where('uid=' . $recruit['uid'])->find();

        $favorite = M('favorite')->where('uid=' . $uid . ' and recruit_id=' . $recruit_id)->find();

        if($favorite['type']==1){
            Json_success('您已收藏该职位');
        }
        $data = array(
            'recruit_id' => $recruit_id,
            'recruit_name' => $recruit['recruit_name'],
            'address' =>$recruit['address'],
            'uid' => $uid,
            'company_name' => $recruit['company_name'],
            'company_id' =>$recruit['uid'],
            'recruit_num' =>$recruit['recruit_num'],
            'work_exp' =>$recruit['work_exp'],
            'salary' =>$recruit['salary'],
            'degree' =>$recruit['degree'],
            'created' => $recruit['start_time'],
            'star' =>$company['star'],
            'type' =>1
        );
        $convert = new data();
            $dat = $convert->convert($data);

        if(M('favorite')->insert($data)){
            Json_success('收藏成功',$dat);
        }else{
            Json_error('收藏失败');
        }

    }

    /**
     * @Title: viewFav
     * @Description: todo(获得收藏职位)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function viewFav() {
        $uid = $_SESSION['uid'];
        $db = M('favorite');
        $nums = $db->where('uid=' . $uid .' AND '.' type=1')->count();
        $page=new page($nums,10);
        $favorites = $db->where('uid=' . $uid .' AND '.' type=1')->findall($page->limit());
        $convert = new data();
        foreach ($favorites as $key => $value) {
            $favorites[$key] = $convert->convert($value);
            $favorites[$key]['created'] = date('Y-m-d',$value['created']);
            $favorites[$key]['start_time'] = date('Y-m-d',$value['created']);
        }
        $data = array(
            'favorites'=>$favorites,
            'page' => array(
                'count'=>$page->total_page
            ),
        );

        if($favorites){
            Json_success('获取成功',$data);
        }else{
            Json_error('您还没有收藏任何职位');
        }
    }


    /**
     * @Title: auto_delivery
     * @Description: todo(三天内自动投递)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function auto_delivery(){
            $hope = M('resume_basic')->where('resume_id = ' . $_POST['resume_id'] . ' AND deliver = 1')->find();

            $town = $hope['hope_town'];
            $salary = $hope['hope_salary'];
            $career = $hope['hope_career'];
            $town= mb_substr($town,0,-1,'utf-8');

            $salary_id = M('linkage')->where('title like "%'.$salary.'%"')->find();

            $career_id = M('linkage')->where('title like "%'.$career.'%"')->find();

            $city = M('city')->where('name like "%'.$town.'%"')->find();

            $city_id = $city['id'];

            if($re = M('recruit')->where(array('town'=>$city_id,'class'=>$career_id['laid']))->limit(10)->findall()){

                foreach ($re as $key=>$val){
                    $data = array(
                        'uid' => $_SESSION['uid'],
                        'rel_name'=>$hope['name'],
                        'username'=>$_SESSION['username'],
                        'resume_id' => $_POST['resume_id'],
                        'recruit_id' => $val['recruit_id'],
                        'company_name' => $val['company_name'],
                        'recruit_name' => $val['recruit_name'],
                        'resume_name' => $hope['hope_career'],
                        'company_id' => $val['uid'],
                        'sendtime' => time()
                    );
                    M('deliver')->insert($data);
                    $uid=$_SESSION['uid'];
                    $content='有相似职位,系统已为您自动投递';
                    $title='投递提醒';
                    addMessages($uid,$content,$title);

                    Json_success('投递成功');
                }

            }
//            else{
//                Json_success('还没有符合的职位,请等待...');
//            }
    }

    /**
     * @Title: deliver
     * @Description: todo(投递简历)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function deliver()
    {

        $db = M('deliver');

        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $resume = M('resume')->where(array('uid' => $uid, 'default' => 1))->find();

        $resume_id = $resume['resume_id'];

        if (empty($resume_id)) {

            Json_error('请先设置默认简历',[],301);
        }

        $recruit_id = explode(",", $_POST['recruit_id']);
        $nums = count($recruit_id);

        if ($nums < 1) {
            Json_error('请先选择职位');
        } else {
            foreach ($recruit_id as $key => $value) {
                $deliver = $db->where('recruit_id=' . $value . ' and uid = ' . $uid)->find();
                $sendtime = $deliver['sendtime'];
                $count = $db->where('recruit_id=' . $value . ' and uid = ' . $uid)->count();
                if ($count <=0 || time() > ($sendtime+7*24*3600)) {
                    $resume_basic = M('resume_basic')->where('resume_id=' . $resume_id)->find();

                    $recruit = M('recruit')->where('recruit_id=' . $value)->find();

                    $data = array(

                        'resume_id' => $resume_id,
                        'uid' => $uid,
                        'username'=>$_SESSION['username'],
                        'rel_name'=>$resume_basic['name'],
                        'gender'=>$resume_basic['gender'],
                        'sendtime' => time(),
                        'recruit_id' => $recruit['recruit_id'],
                        'company_name' => $recruit['company_name'],
                        'recruit_name' => $recruit['recruit_name'],
                        'resume_name' => $resume_basic['hope_career'],
                        'company_id' => $recruit['uid']
                    );

                    $db->insert($data);

                } else {
                    Json_error('七天内不可重复投递');
                }
            }
        }
        $count = M('deliver')->where('uid=' . $uid)->findall();
        if (empty($count)) {
            $_SESSION['point'] = $this->_getUserPoint($uid);

            $point = getPointRule('deliver');//获得应扣取积分

            deductPoint($point);//增加积分

            $con = '首次申请工作';

            $data = array(
                'uid' => $uid,
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

        if(!queryExistMissionLog($uid,'13')){
            addMissionLog($uid,13,$uid);
        }

        Json_success('投递成功');
    }

        /**
         * @Title: postLog
         * @Description: todo(投递记录)
         * @author nipeiquan
         * @return  void  返回类型
         */
        function postLog()
        {
            $db = M('deliver');
            $uid = $_SESSION['uid'];
            $nums = $db->where('uid=' . $uid)->count();
            $page = new page($nums, 10);
            $delivers = $db->where('uid=' . $uid)->order('sendtime DESC')->findall($page->limit());
            if (!empty($delivers)) {
                foreach ($delivers as $key => $value) {
                    $recruit_id = $value['recruit_id'];
                    $recruit = M('recruit')->where('recruit_id=' . $recruit_id)->field('address,city,town,uid,recruit_num,work_exp,degree,start_time,salary,star')->find();
                    $data[] = array(
                        'deliver_id'=> $value['id'],
                        'recruit_id' => $value['recruit_id'],
                        'recruit_name' => $value['recruit_name'],
                        'address' => $recruit['address'],
                        'city' => $recruit['city'],
                        'town' => $recruit['town'],
                        'uid' => $recruit['uid'],
                        'company_name' => $value['company_name'],
                        'recruit_num' => $recruit['recruit_num'],
                        'work_exp' => $recruit['work_exp'],
                        'degree' => $recruit['degree'],
                        'start_time' => $recruit['start_time'],
                        'salary' => $recruit['salary'],
                        'star' => $recruit['star'],
                        'sendtime' => $value['sendtime'],
                    );
                }
                $convret = new data();
                foreach ($data as $key => $value) {
                    $data[$key] = $convret->convert($value);
                }
                $data = array(
                    'result' => $data,
                    'count_page' => $page->total_page
                );
                Json_success('获取成功', $data);
            } else {
                Json_error('您还没有没有投递记录');
            }
        }

    /**
     * @Title: delPostLog
     * @Description: todo(删除投递记录)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delDeliverLogs(){

        $uid = $_SESSION['uid'];

        $ids = $_POST['deliver_id'];

        $del = M('deliver')->where('id in('.$ids.') AND uid='.$uid )->del();

        if($del){

            Json_success('删除成功');
        }else{

            Json_error('删除失败');
        }
    }

        /**
         * @Title: jobInvension
         * @Description: todo(求职意向)
         * @author liuzhipeng
         * @return  void  返回类型
         */
        function jobInvension()
        {
            $db = M('job_want');
            $is_hot = $_POST['is_hot'];//1热门0全部
            $data = array(
                'uid' => $_SESSION['uid'],
                'hope_city' => $_POST['hope_city'],
                'hope_salary' => $_POST['hope_salary'],
                'hope_career' => $_POST['hope_career'],
                'warn' => $_POST['warn'],
                'is_hot'=>$is_hot,
                'create_time'=>time(),
                'update_time'=>time()
            );
            $up = array(
                'hope_city' => $_POST['hope_city'],
                'hope_salary' => $_POST['hope_salary'],
                'hope_career' => $_POST['hope_career'],
                'warn' => $_POST['warn'],
                'is_hot'=>$is_hot,
                'update_time'=>time()
            );
            $town = $_POST['hope_city'];
            $salary = $_POST['hope_salary'];
            $career = $_POST['hope_career'];
            if($town != '新区'){
                $town= rtrim($town,"区");
            }

            $career_id = M('linkage')->where('title = "'.$career.'"')->find();

            $city = M('city')->where('name = "'.$town.'"')->find();

            if($_POST['hope_salary'] == '不限'){
                $salary = 0;
            }elseif($_POST['hope_salary'] == '面议'){
                $salary = 1;
            }elseif($_POST['hope_salary'] == '2000元/月以下'){
                $salary = 2000;
            }elseif($_POST['hope_salary'] == '2001-3000元/月'){
                $salary = 200103000;
            }elseif($_POST['hope_salary'] == '3001-5000元/月'){
                $salary = 300105000;
            }elseif($_POST['hope_salary'] == '5001-8000元/月'){
                $salary = 500108000;
            }elseif($_POST['hope_salary'] == '8001-10000元/月'){
                $salary = 800110000;
            }elseif($_POST['hope_salary'] == '10001-15000元/月'){
                $salary = 1000115000;
            }elseif($_POST['hope_salary'] == '15001-25000元/月'){
                $salary = 1500025000;
            }elseif($_POST['hope_salary'] == '25001元/月以上'){
                $salary = 2500000000;
            }

            $city_id = $city['id'];

            if($_POST['warn'] == 1) {
                if($salary == 0 ){

                    $recruit = M('recruit')->where('( town = '. $city_id. ' or city = '.$city_id.') and (class = '.$career_id['laid'].' or class_two ='.$career_id['laid'].')')->limit (10)->findall();

                }else{
                    $recruit = M('recruit')->where('( town = '. $city_id. ' or city = '.$city_id.') and (class = '.$career_id['laid'].' or class_two ='.$career_id['laid'].') and salary = '.$salary)->limit (10)->findall();

                }
                if(!empty($recruit)){
                    $uid = $_SESSION['uid'];
                    $content = '您关注的求职意向有消息了，点击立即查看！';
                    $title = '投递提醒';
                    $user = M('user')->where('uid=' . $_SESSION['uid'])->find();
                    $client_id = $user['client_id'];

                    if($is_hot==1){//热门
                        $hidden = array(
                            'type'=>2,
                            'data_type'=>202,
                            'title'=>'投递提醒',
                            'content'=>'您关注的求职意向有消息了，点击立即查看！！',
                            'address'=>$_POST['hope_city'],
                            'class'=>$career_id['laid'],//按二级搜
                            'salary'=>$salary,
                            'is_hot'=>1
                        );
                        addMessages ($uid, $content, $title,0,0,$career_id['laid'],$_POST['hope_city'],$salary,1);
                    }elseif($is_hot==0){//全部

                        $hidden = array(
                            'type'=>2,
                            'data_type'=>202,
                            'title'=>'投递提醒',
                            'content'=>'您关注的求职意向有消息了，点击立即查看！！',
                            'address'=>$_POST['hope_city'],
                            'class'=>$career_id['laid'],//按三级搜
                            'salary'=>$salary,
                            'is_hot'=>0
                        );
                        addMessages ($uid, $content, $title,0,0,$career_id['laid'],$_POST['hope_city'],$salary,0);
                    }

                    push(array($client_id),array('hidden'=>$hidden,'title'=>'title','content'=>'content'));
                }
            }
            $re = $db->where('uid =' . $_SESSION['uid'])->find();
            if (empty($re)) {
                if ($db->insert($data)) {
                    Json_success('求职意向添加成功', $data);
                }
            } else {
                if ($db->where('uid = ' . $_SESSION['uid'])->update($up)) {
                    Json_success('修改成功',$up);
                } else {
                    Json_error('修改失败');
                }
            }

        }

        /**
         * @Title: user_intension
         * @Description: todo(获取用户求职意向)
         * @author liuzhipeng
         * @return  void  返回类型
         */
        function user_intension()
        {
            if ($this->auth->is_logged_in()) {
                $db = M('job_want');
                $uid = $_SESSION['uid'];
                if ($re = $db->where('uid = ' . $uid)->field('uid,hope_city,hope_salary,hope_career,warn,is_hot')->find()) {
                    Json_success('获取成功', $re);
                } else {
                    Json_error('没有求职意向');
                }
            } else {
                Json_error('未登录');
            }
        }

        /**
         * @Title: resumeView
         * @Description: todo(简历详情)
         * @author liuzhipeng
         * @return  void  返回类型
         */
        function resumeView()
        {
            $cond = 'resume_id=' . $_POST['resume_id'];
            $re = M('resume');
            $ba = M('resume_basic');
            $wk = M('work_exp');
            $resume = $re->where($cond)->field('resume_id,avatar,default')->find();
            $basic = $ba->where($cond)->field('name,link_email,gender,profile,birthday,telephone,degree,major,work_exp,hope_city,hope_town,hope_salary,hope_career_t,hope_career,deliver')->find();
            $work = $wk->where($cond)->field('company_name,department,job_desc,job_name,job_start,job_end')->findall();
            $data = array(
                'resume' => $resume,
                'basic' => $basic,
                'work' => $work,
            );
            Json_success('获取成功', $data);
        }

        /**
         * @Title: preview
         * @Description: todo(简历预览)
         * @author liuzhipeng
         * @return  void  返回类型
         */
        function preview()
        {
            $cond = 'resume_id=' . $_POST['resume_id'];
            $re = M('resume');
            $ba = M('resume_basic');
            $wk = M('work_exp');
            $resume = $re->where($cond)->field('resume_id,avatar,default')->find();
            $basic = $ba->where($cond)->field('name,link_email,gender,profile,birthday,telephone,degree,major,work_exp,hope_city,hope_town,hope_career,hope_salary,deliver')->find();
            $work = $wk->where($cond)->field('resume_id,uid,company_name,department,job_desc,job_name,job_start,job_end')->findall();
            $data = array(
                'resume' => $resume,
                'basic' => $basic,
                'work' => $work,
            );
            Json_success('获取成功', $data);
        }

        /**
         * @Title: defaultRes
         * @Description: todo(设置为默认简历)
         * @author liuzhipeng
         * @return  void  返回类型
         */
        function defaultRes()
        {
            $db = M('resume');
            $data = array('default' => 0);
            $uid = $_SESSION['uid'];
            if(empty($uid))Json_error("请登录");
            $condition = 'resume_id=' . $_POST['resume_id'] . ' AND uid=' .$uid;
            $re = $db->where($condition)->field('default')->find();
            $de = $re['default'];
            if ($de == 1) {
                Json_error('已经是默认简历');
            }
            $db->where('uid =' . $_SESSION['uid'])->update($data);
            if ($db->where($condition)->update(array('default' => 1))) {
                $basic = M('resume_basic')->where(['resume_id' => $_POST['resume_id']])->find();
                if(!empty($basic)){

                    M('user')->where(['uid'=>$_SESSION['uid']])->update(['resume_phone'=>$basic['telephone']]);

                }
                Json_success('设置成功');
            } else {
                Json_error('请选择简历');
            }
        }

        /**
         * @Title: nearPeople
         * @Description: todo(附近的人)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function nearPeople()
        {
            $lng = $_POST['lng'];
            $lat = $_POST['lat'];
            $uid = $_SESSION['uid'];
            $gender = $_POST['gender'];
            $squares = returnSquarePoint($lng, $lat);
            if($gender!=2){
                $con[] = 'gender='.$gender;
            }
            $con[] = "lat<>0 and lat>" . $squares['minLat'] . " and lat<" . $squares['maxLat'] . " and lng>" . $squares['minLng'] . " and lng<" . $squares['maxLng'];
            $con[] = 'uid!=' . $uid;
            $con[] = 'is_online =1';
            $result = M('user')->field('uid,avatar,username,nickname,lng,lat,last_login,desc')->where($con)->findall();
            foreach ($result as $key => $value) {
                $result[$key]['distance'] = getDistanceBetweenPoints($value['lat'], $value['lng'], $lat, $lng);
                $result[$key]['distance'] = $result[$key]['distance']['kilometers'];
            }
            foreach ($result as $key=>$val){

                $distance[$key] = $val['distance'];
            }
            array_multisort($distance,$result);
//
            if (($result)) {
                Json_success('获取成功', $result);
            } else {
                Json_error('附近暂时没有人');
            }

        }

        /**
         * @Title: nearGroups
         * @Description: todo(附近的群)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function nearGroups()
        {

            $lng = $_POST['lng'];
            $lat = $_POST['lat'];
            $squares = returnSquarePoint($lng, $lat);
            $con ="lat<>0 and lat>" . $squares['minLat'] . " and lat<" . $squares['maxLat'] . " and lng>" . $squares['minLng'] . " and lng<" . $squares['maxLng'];
            $result = M('group')->where($con)->field('gid,owner,desc,grouper,address,group_id,lng,lat,avatar')->findall();
            foreach ($result as $key => $value) {
                $result[$key]['distance'] = getDistanceBetweenPoints($value['lat'], $value['lng'], $lat, $lng);
                $result[$key]['distance'] = $result[$key]['distance']['kilometers'];
            }
            foreach ($result as $key=>$val){

                $distance[$key] = $val['distance'];
            }
            array_multisort($distance,$result);
            if (!empty($result)) {
                Json_success('获取成功', $result);
            } else {
                Json_error('附近暂时没有群');
            }
        }

    /**
     * @Title: getSensitive
     * @Description: todo(判断敏感词)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getSensitive(){
        $owner = $_POST['owner'];
        if(empty($owner)){
            Json_error('请填写群名');
        }
        $sensitive = M('linkage')->where('lcgid=26')->findall();
        foreach ($sensitive as $key=>$value){
//            $lcs = new lcs();
//            $percent = $lcs->getSimilar($owner,$value['title']);
//            if($percent > 0.7){
//                Json_error('群名中含有敏感词，请重新输入');
//            }
            if(strpos($owner,$value['title']) !== false){     //使用绝对等于
                Json_error('群名中含有敏感词，请重新输入');
            }
        }
        Json_success('可以创建');
    }

        /**
         * @Title: getMessages
         * @Description: todo(得到消息列表)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function getMessages()
        {
            $uid = $_SESSION['uid'];
            if(empty($uid)){
                Json_error('请先登录');
            }
            $user = M('user')->where('uid=' . $uid)->find();
            $created = $user['created'];
            $db = M('user_message');
            $user_role = M('user_role')->where('uid=' . $uid)->find();
            if($user_role['rid']==7){
                $type = 2;//业务员
            }elseif($user_role['rid']==8){
                $type = 1;//求职者
            }
            $count = $db->where('(uid =' . $uid.') AND (type = 3 or type = ' . $type.' or type = 0) AND created >=' . $created)->count();
            $page = new page($count, 20);
            $messages = $db->where('(uid =' . $uid.') AND (type = 3 or type = ' . $type.' or type = 0) AND created >=' . $created)->order('created desc')->findall($page->limit());
            $result = array(
                'messages' => $messages,
                'count_page' => $page->total_page
            );
            if (empty($messages)) {
                Json_success('还没有消息哦',$result);
            } else {
                Json_success('获取成功', $result);
            }

        }

    /**
     * @Title: delMessage
     * @Description: todo(删除消息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delMessage(){

        $uid = $_SESSION['uid'];
        if(empty($uid)){
            Json_error('请先登录');
        }
        $message_id = $_POST['message_id'];
        if(!empty($message_id)){
            if(M('user_message')->where('message_id in ('.$message_id.')')->del()){
                Json_success('删除成功');
            }else{
                Json_error('系统错误');
            }
        }else{
            Json_error('该消息已不存在');
        }
    }

        /**
         * @Title: createGroup
         * @Description: todo(创建群)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function createGroup()
        {

            $lng = $_POST['lng'];
            $lat = $_POST['lat'];
            $uid = $_POST['uid'];
            $group_id = $_POST['group_id'];
            $owner = $_POST['owner'];
            $sensitive = M('linkage')->where('lcgid=26')->findall();
            foreach ($sensitive as $key=>$value){
//                $lcs = new lcs();
//                $percent = $lcs->getSimilar($_POST['owner'],$value['title']);
//                if($percent > 0.7){
//                    Json_error('群名中含有敏感词，请重新输入');
//                }
                if(strpos($_POST['owner'],$value['title']) !== false){     //使用绝对等于
                    Json_error('群名中含有敏感词，请重新输入');
                }
            }
            $address = $_POST['address'];
            $grouper = $_POST['grouper'];
            $desc = $_POST['desc'];
            $group_permissions = $_POST['group_permissions'];
            $user_permissions = $_POST['user_permissions'];
            C('UPLOAD_IMG_DIR', '');
            C('THUMB_ENDFIX', '');//只生成头像缩略图
            $upload = new upload(PATH_ROOT . '/uploads/group', array('jpg', 'jpeg', 'png', 'gif'));
            $info = $upload->upload();
            if (!empty($info)) {
                if ($info) {
                    $avatar = $info[0]['path'];
                } else {
                    Json_error('头像上传失败');
                }
            }
            if (!empty($avatar)) {
                $avatar = __ROOT__ . '/' . $avatar;
            } else {
                $avatar = '';
            }
            $result = array(
                'lng' => $lng,
                'lat' => $lat,
                'uid' => $uid,
                'group_id' => $group_id,
                'owner' => $owner,
                'avatar' => $avatar,
                'group_permissions' => $group_permissions,
                'user_permissions' => $user_permissions,
                'desc' => $desc,
                'created' => time(),
                'grouper' => $grouper,
                'address' => $address
            );
            if (M('group')->insert($result)) {
                $group = M('group')->where('uid=' . $uid)->findall();
                if (empty($group)) {//建群+积分

                    $_SESSION['point'] = $this->_getUserPoint($uid);

                    $point = getPointRule('createGroup');//获得应扣取积分

                    deductPoint($point);//增加积分

                    $con = '创建群';

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
                Json_success('创建成功', $result);
            } else {
                Json_error('创建失败');
            }
        }

    /**
     * @Title: editGroup
     * @Description: todo(修改群资料)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editGroup(){
        $uid = $_SESSION['uid'];
        $gid = $_POST['group_id'];
        $group = M('group')->where('group_id='.$gid)->find();
        if($uid!=$group['uid']){
            Json_error('只有群主才能修改信息哦');
        }

        C('UPLOAD_IMG_DIR', '');
        C('THUMB_ENDFIX', '');//只生成头像缩略图
        $upload = new upload(PATH_ROOT . '/uploads/group', array('jpg', 'jpeg', 'png', 'gif'));
        $info = $upload->upload();
        if (!empty($info)) {
            if ($info) {
                $avatar = $info[0]['path'];
            } else {
                Json_error('头像上传失败');
            }
        }
        if (!empty($avatar)) {
            $avatar = __ROOT__ . '/' . $avatar;
        } else {
            $avatar = $group['avatar'];
        }
        $owner = $_POST['owner'];
        $desc = $_POST['desc'];
        $result = array(
            'owner' => $owner,
            'avatar' => $avatar,
            'group_permissions' => $_POST['group_permissions'],
            'user_permissions' => $_POST['user_permissions'],
            'desc' => $desc,
            'address' => $_POST['address']
        );
        $huanxin = $this->editGroupByHuanxin($gid,$owner);

        if($huanxin['data']['groupname']){

            if (M('group')->where(array('group_id'=>$gid,'uid'=>$uid))->update($result)) {
                Json_success('修改成功');
            }else{
                Json_error('系统错误');
            }

        }else{
            Json_error('环信修改未成功!');
        }

    }

    /**
     * @Title: editGroupByHuanxin
     * @Description: todo(修改环信群信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    private function editGroupByHuanxin($gid,$owner){
        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $token = $rs['access_token'];
        $url = $this->url . "/chatgroups/" . $gid;
        $arr = array(
            'groupname'=>$owner,
        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, $arr, $header, "PUT"), true);
    }
    private function getUserHuanXin($name){
        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $token = $rs['access_token'];
        $url = $this->url . "/users/" . $name."/contacts/users";
//        $arr = array(
//            'users'=>$owner,
//        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, '',$header, "GET"), true);
    }

    public function getMyFriends(){
        $name = $_POST['name'];

        $huanxin = $this->getUserHuanXin($name);


        if(!empty($huanxin['data'])){

            $data = $huanxin['data'];


            $data = implode(',',$data);


            $users = M('user')->where('username in('.$data.')')->findall();

            Json_success('success',$users);

        }else{
            Json_error('暂无好友!');
        }
    }

    /**
     * @Title: delGroup
     * @Description: todo(删除群)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delGroup(){

        $group_id = $_POST['group_id'];

        $group = M('group')->where(['group_id'=>$group_id])->find();

        if(empty($group)){

            Json_error('群组不存在');
        }

        if($_SESSION['uid']!=$group['uid']){

            Json_error('没有权限');
        }

        $huanxin = $this->delHuanXin($group_id);

        $del = M('group')->where('group_id='.$group_id)->del();

        M('group_member')->where('group_id ='.$group_id)->del();
        if($del){

            Json_success('解散成功');
        }else{

            Json_error('操作失败');
        }

    }

    public function delHuanXin($gid){
        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);

        $token = $rs['access_token'];
        $url = $this->url . "/chatgroups/" . $gid;
        $arr = array();
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, $arr, $header, "DELETE"), true);
    }

    /**
     * @Title: joinGroup
     * @Description: todo(加群)安卓专用
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function joinGroup(){
        $group_id = $_POST['group_id'];

        $uid = $_POST['uid'];

        $user = M('user')->where('uid=' . $uid)->field('uid,username,avatar,nickname')->find();
        if(empty($user['uid'])){
            Json_error('用户不存在');
        }
        $group = M('group')->where('group_id=' . $group_id)->field('gid,grouper,avatar')->find();
        if(empty($group['gid'])){
            Json_error('群id获取失败');
        }
        $group_member = M('group_member')->where('uid=' . $uid .' AND group_id=' . $group_id)->find();
        if(!empty($group_member['jid'])){
            Json_error('你已经是该群一员了');
        }
        $data = array(
            'group_id'=>$group_id,
            'uid'=>$uid,
            'username'=>$user['username'],
            'nickname'=>$user['nickname'],
            'uavatar'=>$user['avatar'],
            'grouper'=>$group['grouper'],
            'gavatar'=>$group['avatar'],
            'join_time'=>time()
        );
        if(M('group_member')->insert($data)){
            Json_success('加入成功');
        }else{
            Json_error('系统错误');
        }
    }

    /**
     * @Title: getUserGroup
     * @Description: todo(得到用户所在的所有群)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getUserGroup(){
        $uid = $_POST['uid'];
        if(empty($uid)){
            Json_error('用户不存在');
        }
        $groups = M('group_member')->where('uid=' . $uid)->field('group_id,grouper,gavatar')->findall();
        if(empty($groups)){
            Json_error('获取失败');
        }else{
            Json_success('获取成功',$groups);
        }
    }

        /**
         * @Title: getGroupInfo
         * @Description: todo(得到群信息)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function getGroupInfo()
        {

            $group_id = $_POST['group_id'];

            $group = M('group')->where(array('group_id' => $group_id))->find();

            if(empty($group)){

                Json_error('该群已经不存在');
            }
            $group_huanxin = $this->getGroupByHuanxin($group_id);

            $huanxin = $group_huanxin['data'][0];

            $group['qunzhu'] = $huanxin['affiliations'][0]['qunzhu'];
            $group['huanxin'] =  empty($huanxin)? (object)null:$group_huanxin['data'][0];

            foreach ($huanxin['affiliations'] as $key=>$value){
                if(!isset($value['member'])){
                    $where = 'username=' . $value['owner'].' or phone = '.$value['owner'];
                }else{
                    $where = 'username=' . $value['member'].' or phone = '.$value['member'];
                }
                $nickname[] = M('user')->where($where)->field('username,nickname')->find();
            }
            $group['member'] = $nickname;
            if ($group) {
                Json_success('获取成功', $group);
            } else {
                Json_error('获取群信息失败，请重新获取');
            }
        }

        /**
         * @Title: getRecommendGroups
         * @Description: todo(得到推荐群组)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function getRecommendGroups()
        {
            $db = M('group');
            $groups = $db->where('type=1')->findall();
            if (empty($groups)) {
                Json_error('暂时没有推荐群组');
            } else {
                Json_success('获取成功', $groups);
            }
        }

        /**
         * @Title: releaseDynamic
         * @Description: todo(发布动态)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function releaseDynamic()
        {
            if(C('square')==0){

                Json_error('广场功能暂时关闭，请等待管理员开启后重试,谢谢配合');
            }
            $uid = $_SESSION['uid'];
            $content = $_POST['content'];
            $user = M('user')->where('uid=' . $uid)->find();
            C('UPLOAD_IMG_DIR', '');
            C('THUMB_ENDFIX', '');//只生成头像缩略图
            $upload = new upload(PATH_ROOT . '/uploads/dynamic', array('jpg', 'jpeg', 'png', 'gif'));
            $infos = $upload->upload();

            $imgs = '';
            if (!empty($infos)) {
                foreach ($infos as $info) {
                    $imgs .= __ROOT__ . '/' . $info['path'] . '#';
                }
                $imgs = rtrim($imgs, '#');
            }
            $result = array(
                'uid' => $uid,
                'content' => $content,
                'uname' => $user['nickname'],
                'username'=>$user['username'],
                'uavatar' => $user['avatar'],
                'gender' => $user['gender'],
                'imgs' => $imgs,
                'time' => time(),
                'update_time' => time()
            );
            $log = M('opt_log')->where('uid=' . $uid . ' AND content="首次发布动态"')->count();
            if (M('sns')->add($result)) {
                if ($log=='0') {//每天发布+积分

                    $_SESSION['point'] = $this->_getUserPoint($uid);

                    $point = getPointRule('firstDynamic');//获得应扣取积分

                    deductPoint($point);//增加积分

                    $con = '首次发布动态';

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

                } else {
                    $count = M('sns')->where('uid=' . $uid . ' AND time > ' . strtotime(date('Y-m-d')))->count();
                    if ($count < 6) {
                        $_SESSION['point'] = $this->_getUserPoint($uid);

                        $point = getPointRule('releaseDynamic');//获得应扣取积分

                        deductPoint($point);//增加积分

                        $con = '发布动态';

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
                }
                Json_success('发布成功', $result);
            } else {
                Json_error('发布失败');
            }
        }

        /**
         * @Title: commentSns
         * @Description: todo(评论/回复动态)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function commentSns()
        {
            if(C('square')==0){

                Json_error('广场功能暂时关闭，请等待管理员开启后重试,谢谢配合');
            }
            $sns_id = $_POST['sns_id'];
            $pid = $_POST['buid'];
            $content = $_POST['content'];
            $uid = $_SESSION['uid'];
            $user = M('user')->where('uid=' . $uid)->find();
            if (!isset($content)) {
                Json_error('请输入评论内容');
            }
            if(empty($pid)){
                $pid = 0;
                $sns = M('sns')->where('sid=' . $sns_id)->find();
                $result = array(
                    'sns_id' => $sns_id,
                    'pid' => $pid,
                    'uid' => $uid,
                    'buid' => $sns['uid'],
                    'busername'=>$sns['username'],
                    'content' => $content,
                    'nickname' => $user['nickname'],
                    'username'=>$user['username'],
                    'bnickname' => $sns['uname'],
                    'created' => time()
                );
            }else{
                $buser = M('user')->where('uid=' . $pid)->find();
                $result = array(
                    'sns_id' => $sns_id,
                    'pid' => $pid,
                    'uid' => $uid,
                    'buid' => $pid,
                    'busername'=>$buser['username'],
                    'content' => $content,
                    'nickname' => $user['nickname'],
                    'username'=>$user['username'],
                    'bnickname' => $buser['nickname'],
                    'created' => time()
                );
            }

            if (M('sns_comment')->add($result)) {
                Json_success('评论成功', $result);
            } else {
                Json_error('评论失败');
            }

        }

        /**
         * @Title: zanSns
         * @Description: todo(点赞动态)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function zanSns()
        {
            $sns_id = $_POST['sns_id'];
            $uid = $_SESSION['uid'];
            if (empty($uid)) {
                Json_error('请先登录');
            }
            $sns = M('sns')->where('sid=' . $sns_id)->find();
            $user = M('user')->where('uid=' . $uid)->find();
            $result = array(
                'sns_id' => $sns_id,
                'uid' => $uid,
                'buid' => $sns['uid'],
                'nickname' => $user['nickname'],
                'bnickname' => $sns['uname'],
                'time' => time()
            );
            if (M('sns_zan')->where(array('uid' => $uid, 'sns_id' => $sns_id))->find()) {
                if (M('sns_zan')->where(array('uid' => $uid, 'sns_id' => $sns_id))->del()) {
                    M('sns')->dec('hits', 'sid=' . $sns_id, 1);
                    $sns = M('sns')->where('sid=' . $sns_id)->find();
                    $hits = $sns['hits'];
                    Json_success('取消成功', $hits);
                }
            } else {
                if (M('sns_zan')->add($result)) {
                    M('sns')->inc('hits', 'sid=' . $sns_id, 1);
                    $sns = M('sns')->where('sid=' . $sns_id)->find();
                    $hits = $sns['hits'];
                    Json_success('点赞成功', $hits);
                }
            }
        }

        /**
         * @Title: getDynamicLists
         * @Description: todo(得到全部动态列表)
         * @author nipeiquan
         * @return  void  返回类型
         */
        public
        function getDynamicLists()
        {
            $db = M('sns');
            $count = $db->where(['del_state'=>0])->count();
            $page = new page($count, 15);
            $lists = $db->where(['del_state'=>0])->order('time desc')->findall($page->limit());
            $result = array(
                'lists' => $lists,
                'count_page' => $page->total_page
            );
            if ($lists) {
                Json_success('获取成功', $result);
            } else {
                Json_error('获取动态失败');
            }
        }

        private
        function curl($url, $data, $header = false, $method = "POST")
        {
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

        /**
         * @Title: getGroupByHuanxin
         * @Description: todo(得到环信群信息)
         * @author liuzhipeng
         * @return  mixed  返回类型
         */
        private
        function getGroupByHuanxin($groupId)
        {
            $url = $this->url . "/token";
            $data = array(
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            );
            $rs = json_decode($this->curl($url, $data), true);
            $token = $rs['access_token'];
            $url = $this->url . "/chatgroups/" . $groupId;
            $arr = array();
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            );
            return json_decode($this->curl($url, $arr, $header, "GET"), true);
        }

    /**
     * @Title: shareIntegral
     * @Description: todo(分享得积分)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function shareIntegral()
    {
        $uid = $_POST['uid'];
            $log = M('opt_log')->field('time')->where('uid=' . $uid . ' AND type=4')->order('time desc')->find();
            if($log['time'] > strtotime(date('Y-m-d'))) {
                $count = M('opt_log')->where('uid=' . $uid . ' AND type=4 AND time >' . strtotime(date('Y-m-d')))->count();
                if ($count < 2) {
                    $_SESSION['point'] = $this->_getUserPoint($uid);
                    $user = M('user')->where('uid=' . $uid)->find();
                    $username = $user['username'];
                    $point = getPointRule('share');//获得应扣取积分
                    deductPoint($point, $uid);//增加积分
                    $con = '分享得积分';
                    $result = array(
                        'uid' => $uid,
                        'content' => $con,
                        'point' => '+' . $point,
                        'created' => time(),
                        'ip' => ip_get_client(),//操作ip
                        'username' => $username,
                        'time' => time(),
                        'type' => 4
                    );
                    if (M('opt_log')->insert($result)) {
                        Json_success('恭喜您获得50积分');
                    }
                } else {
                    Json_error('您今天通过分享获得的积分已达上限');
                }

            }else{
                $_SESSION['point'] = $this->_getUserPoint($uid);
                $user = M('user')->where('uid=' . $uid)->find();
                $username = $user['username'];
                $point = getPointRule('share');//获得应扣取积分
                deductPoint($point, $uid);//增加积分
                $con = '分享得积分';
                $result = array(
                    'uid' => $uid,
                    'content' => $con,
                    'point' => '+' . $point,
                    'created' => time(),
                    'ip' => ip_get_client(),//操作ip
                    'username' => $username,
                    'time' => time(),
                    'type' => 4
                );
                if (M('opt_log')->insert($result)) {
                    Json_success('恭喜您获得50积分');
                }
            }

    }

    /**
     * @Title: addFriendsIntegral
     * @Description: todo(加好友得积分)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function addFriendsIntegral()
    {
        $uid = $_POST['uid'];
        $user = M('user')->where('uid=' . $uid)->find();
        $username = $user['username'];
        $log = M('opt_log')->field('time')->where('uid=' . $uid . ' AND content="首次加好友"')->order('time desc')->count();
        if($log=='0'){
            $_SESSION['point'] = $this->_getUserPoint($uid);//加首个好友得50
            $point = getPointRule('addFirstFriend');//获得应扣取积分
            deductPoint($point, $uid);//增加积分
            $con = '首次加好友';
            $result = array(
                'uid' => $uid,
                'content' => $con,
                'point' => '+' . $point,
                'created' => time(),
                'ip' => ip_get_client(),//操作ip
                'username' => $username,
                'time' => time(),
                'type' => 0
            );
            if (M('opt_log')->insert($result)) {
                Json_success('恭喜您获得50积分');
            }
        }else{
            $count = M('opt_log')->where('uid=' . $uid . ' AND type=3 AND time >' . strtotime(date('Y-m-d')))->count();
            if($count!=='0') {
                if ($count < 10) {
                    $_SESSION['point'] = $this->_getUserPoint($uid);
                    $point = getPointRule('addFirend');//获得应扣取积分
                    deductPoint($point, $uid);//增加积分
                    $con = '加好友得积分';
                    $result = array(
                        'uid' => $uid,
                        'content' => $con,
                        'point' => '+' . $point,
                        'created' => time(),
                        'ip' => ip_get_client(),//操作ip
                        'username' => $username,
                        'time' => time(),
                        'type' => 3
                    );
                    if (M('opt_log')->insert($result)) {
                        Json_success('恭喜您获得10积分');
                    }
                } else {
                    Json_error('您今天通过加好友获得的积分已达上限');
                }

            }elseif($count=='0'){
                $_SESSION['point'] = $this->_getUserPoint($uid);
                $point = getPointRule('addFirend');//获得应扣取积分
                deductPoint($point, $uid);//增加积分
                $con = '加好友得积分';
                $result = array(
                    'uid' => $uid,
                    'content' => $con,
                    'point' => '+' . $point,
                    'created' => time(),
                    'ip' => ip_get_client(),//操作ip
                    'username' => $username,
                    'time' => time(),
                    'type' => 3
                );
                if (M('opt_log')->insert($result)) {
                    Json_success('恭喜您获得10积分');
                }
            }
        }

    }

    /**
     * @Title: reportSns
     * @Description: todo(举报动态)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function reportSns(){
        if(C('square')==0){

            Json_error('广场功能暂时关闭，请等待管理员开启后重试,谢谢配合');
        }
        $sid = $_POST['sid'];
        if(empty($sid)){
            Json_error('该动态不存在');
        }
        $sns = M('sns')->where('sid=' . $sid)->find();
        $uid = $_SESSION['uid'];
        if(empty($uid)){
            Json_error('用户不存在');
        }
        $user = M('user')->where('uid=' . $uid)->field('uid,username')->find();
        $reason = $_POST['reason'];
        $data = array(
            'sid'=>$sid,
            'uid'=>$uid,
            'username'=>$user['username'],
            'buid'=>$sns['uid'],
            'busername'=>$sns['username'],
            'content'=>$sns['content'],
            'reason'=>$reason,
            'time'=>time()
        );
        if(M('sns_report')->insert($data)){
            Json_success('举报成功，我们会尽快为您处理，感谢您的支持');
        }else{
            Json_error('系统错误');
        }
    }

    /**
     * @Title: getAddressBook
     * @Description: todo(得到通讯录成员注册状态)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getAddressBook(){

        $mobiles = $_POST['mobiles'];

        if(!empty($mobiles)){

            $mobiles = explode(',',$mobiles);

            $data = [];

            foreach ($mobiles as $k=>$mobile){

                $user = M('user')->where(['username'=>$mobile])->find();

                if($user){

                    $data[$k]['uid'] = $user['uid'];
                    $data[$k]['mobile'] = $mobile;
                    $data[$k]['avatar'] = $user['avatar'];
                }
            }

            $data = array_values($data);

            $model = K('public');

            foreach ($data as $key=>$value){

                $is_attention = $model->checkConcern($_SESSION['uid'],$value['uid']);

                if($is_attention){

                    $data[$key]['is_attention'] = 1;
                }else{

                    $data[$key]['is_attention'] = 0;
                }
            }

            Json_success('获取成功',array_values($data));
        }else{

            Json_error('未获取到通讯录成员');
        }
    }

    /**
     * @Title: addGroupMember
     * @Description: todo(拉环信下的群成员,自己测试用)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function addGroupMember(){

        $groups = M('group')->where('gid >=292 and gid<=417')->findall();

        $groupids = array_column($groups,'group_id');

        foreach ($groupids as $k=>$groupid){

            $group_huanxin = $this->getGroupMembers($groupid);

            if(!empty($group_huanxin['data'])){

                foreach ($group_huanxin['data'] as $key=>$value){

                    if(isset($value['owner'])){

                        $owner = $value['owner'];

                        $data = [
                            'group_id'=>$groupid,
                            'grouper'=>$owner,
                            'username'=>$owner,
                            'user_huanxin'=>$owner,
                            'is_admin'=>1,
                            'created'=>time(),
                        ];

                    }else{

                        $username = $value['member'];

                        $data = [
                            'group_id'=>$groupid,
                            'grouper'=>$username,
                            'username'=>$username,
                            'user_huanxin'=>$username,
                            'is_admin'=>0,
                            'created'=>time(),
                        ];
                    }

                    var_dump(M('group_member')->add($data));

                }
            }else{

                var_dump(111);
            }
        }
    }

    public function completeGroupMemberInfo(){

        $infos = M('group_member')->where(['uid'=>0])->findall();

        foreach ($infos as $k=>$info){

            $group = M('group')->where(['group_id'=>$info['group_id']])->find();

            $user = M('user')->where(['username'=>$info['username']])->find();

            $gid = $group['gid'];

            $uid = $user['uid'];

            $avatar = $user['avatar'];

            var_dump(M('group_member')->where(['lid'=>$info['lid']])->update(['gid'=>$gid,'uid'=>$uid,'avatar'=>$avatar]));

        }
    }

    private
    function getGroupMembers($groupId)
    {
        $url = $this->url . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $token = $rs['access_token'];
        $url = $this->url . "/chatgroups/" . $groupId.'/users';
        $arr = array();
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode($this->curl($url, $arr, $header, "GET"), true);
    }
}


