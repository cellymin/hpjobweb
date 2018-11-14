<?php

/*
 * Describe   : 用户管理控制器
 */

class userControl extends myControl { 

    private $user;
    private $sa;
    private $app_key='ttouch#kuaile';
    private $client_id='YXA6tfPWgFtsEeWzNxtHA-A9OA';
    private $client_secret='YXA6hhR-xo8PGuE3BAqMfTQlfNAkYLs';
    private $url = "https://a1.easemob.com/ttouch/kuaile";

    function __construct() {
        parent::__construct();
        $this->user = K('index/user');
    }

    function userList(){
        $_GET = urldecode_array($_GET);
        $cond=array(
            'group'=>array(),
            'user'=>array()
        );

        if(isset($_GET['type'])){
            $cond['user']['type']=$_GET['type'];
        }

        if(isset($_GET['rid'])){
            $cond['group']='user_role.rid='.$_GET['rid'];
        }
        if(!empty($_GET['start_time']) && !empty($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond['user'][] = ' created > ' . $start_time . ' AND created < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond['user'][] = 'created >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond['user'][] = 'created <' . $end_time;
        }
        if(isset($_GET['username'])){
            $cond['user'][]='username like "%'.$_GET['username'].'%"';
        }
        if(isset($_GET['email'])){
            $cond['user']['email']=$_GET['email'];
        }
        if(isset($_GET['banned'])){
            $cond['user']['banned']=$_GET['banned'];
        }
        $role_list = $this->user->roleList(array(),'rid,title,pid,state');
        $users = $this->user->userList($cond);
        $count = M('user')->where($cond['user'])->count();
        if(!empty($users['num'])){
            $count = $users['num'];
        }
        

        foreach($users['user'] as $key=>$val){
            $verify= M('user_info')->where(array('uid'=>$val['uid']))->find();


            $users['user'][$key]['verify'] = $verify['verify'];

            if(empty($verify['id_number'])){

                $users['user'][$key]['gender'] = 3;//如果未做身份认证的人，性别改为暂无

            }else{

                $users['user'][$key]['gender'] = $verify['gender'];

            }
//            $users['user'][$key]['title'] = $val['hp_role'][$key]['title'];

        }
        //var_dump($users['user']);die;
        $this->assign('users', $users['user']);
        $this->assign('page', $users['page']);
        $this->assign('counts', $count);
        $this->assign('role_list', $role_list);
        $this->display();
    }




    function yewuList(){
        $role_list = $this->user->roleList(array(),'rid,title,pid,state');
        
        $users = $this->user->userList();
       
        $count = M('user')->where()->count();

        foreach($users['user'] as $key=>$val){
            $verify= M('user_info')->where(array('uid'=>$val['uid']))->find();


            $users['user'][$key]['verify'] = $verify['verify'];

            if(empty($verify['id_number'])){

                $users['user'][$key]['gender'] = 3;//如果未做身份认证的人，性别改为暂无

            }else{

                $users['user'][$key]['gender'] = $verify['gender'];

            }
//            $users['user'][$key]['title'] = $val['hp_role'][$key]['title'];

        }
        //var_dump($users['user']);die;
        $this->assign('users', $users['user']);
        $this->assign('page', $users['page']);
        $this->assign('counts', $count);
        $this->assign('role_list', $role_list);
        $this->display();
    }

    /**
     * @Title: userVerify
     * @Description: todo(用户审核列表)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    function userVerify(){
        $_GET = urldecode_array($_GET);
        $cond=array(
            'group'=>array(),
            'user'=>array()
        );
        if(isset($_GET['rid'])){
            $cond['group']='user_role.rid='.$_GET['rid'];
        }

        if(isset($_GET['verify'])){
            $userlists = M('user_info')->where(array('verify'=>$_GET['verify']))->findall();
            $uids = '';
            foreach($userlists as $key=>$value){
                $uids .=$value['uid'].',';
            }
            $uids = rtrim($uids,',');
            $uids = empty($uids) ? 0 :$uids;
            $cond['user'][] = 'uid in ('.$uids.') ';
        }

        if(!empty($_GET['start_time']) && !empty($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond['user'][] = ' created > ' . $start_time . ' AND created < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond['user'][] = 'created >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond['user'][] = 'created <' . $end_time;
        }
        if(isset($_GET['username'])){
            $cond['user'][]='username like "%'.$_GET['username'].'%"';
        }
        if(isset($_GET['email'])){
            $cond['user']['email']=$_GET['email'];
        }
        if(isset($_GET['banned'])){
            $cond['user']['banned']=$_GET['banned'];
        }
        $role_list = $this->user->roleList(array(),'rid,title,pid,state');
        $users = $this->user->userList($cond);
        $count = M('user')->where($cond['user'])->count();
        foreach($users['user'] as $key=>$val){
            $verify= M('user_info')->where(array('uid'=>$val['uid']))->find();


            $users['user'][$key]['verify'] = $verify['verify'];

            if(empty($verify['id_number'])){

                $users['user'][$key]['gender'] = 3;//如果未做身份认证的人，性别改为暂无

            }else{

                $users['user'][$key]['gender'] = $verify['gender'];

            }
//            $users['user'][$key]['title'] = $val['hp_role'][$key]['title'];

        }
//        var_dump($users['user']);die;
        $this->assign('users', $users['user']);
        $this->assign('page', $users['page']);
        $this->assign('counts', $count);
        $this->assign('role_list', $role_list);
        $this->display();
    }

    /**
     * @Title: delGroup
     * @Description: todo(解散群组)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delGroup(){
        $group_id = $_GET['group_id'];

        if(empty($group_id)){

            $del = M('group')->where('gid='.$_GET['gid'])->del();

            if($del){

                $this->success('解散成功');
            }else{

                $this->error('操作失败');
            }
        }

        $exist = $this->getGroupByHuanxin($group_id);

        if($exist['error']){

            $group = M('group')->where('group_id='.$group_id)->del();

            M('group_member')->where('group_id='.$group_id)->del();
            if($group){

                $this->success('解散成功');
            }else{

                $this->error('操作失败');
            }
        }else{

            $huanxin = $this->delHuanXin($group_id);

            if($huanxin['data']['success']){

                $group = M('group')->where('group_id='.$group_id)->del();

                M('group_member')->where('group_id='.$group_id)->del();

                if($group){

                    $this->success('解散成功');
                }else{

                    $this->error('操作失败');
                }
            }else{

                $this->error('环信群删除失败');
            }
        }
    }
//得到环信群信息
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
     * @Title: groupLists
     * @Description: todo(群组列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function groupLists(){
        $_GET = urldecode_array($_GET);

        $db = M('group');
        if(!empty($_GET['owner'])){
            $cond[] = 'owner like "%'.ltrim($_GET['owner']).'%"';
        }
        $count = $db->where($cond)->count();
        $page = new page($count,20);

        $groups = $db->where($cond)->order('type desc,created desc')->findall($page->limit());
        $this->assign('pages',$page->show());
        $this->assign('groups',$groups);
        $this->display();
    }

    /**
     * @Title: highSalary
     * @Description: todo(设为推荐群组)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function recommendGroup(){
        $gid = $_GET['gid'];
        $group = M('group')->where('gid=' . $gid)->find();
        if($group['type']==1){
            M('group')->where('gid=' . $gid)->update(array('type'=>0));//0不是推荐1是推荐群组
        }else{
            M('group')->where('gid=' . $gid)->update(array('type'=>1));;
        }
        $this->success('操作成功');
    }

    /**
     * @Title: contact
     * @Description: todo(一键推荐/取消)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function onekeyRecommend(){
        $db=M('group');
        $data=array();
        if($_POST['type']=='set-recommend'){
            $data['type']=1;
        }
        if($_POST['type']=='cancel-recommend'){
            $data['type']=0;
        }

        $db->in($_POST['gid'])->update($data);
        echo 1;
        exit;
    }

    /**
     * 添加用户 
     */
    function addUser() 
    {
        $role_list = $this->user->roleList('state=1');
        $branch_list = M('branch')->findall();
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            if ($this->user->userExist($_POST['username'])) {
                $this->error('用户名已经存在！');
            }
            if (!empty($_POST['email']) && $this->user->emailExist($_POST['email'])) {
                $this->error('Email已经存在！');
            }
            $auth_class=new auth();
            C('AUTH_EMAIL_ACTIVATE',FALSE);//关闭电子邮件激活
            if ($auth_class->register($_POST)) {
                 $this->success('添加成功');
            }
        }
        $this->assign('role_list', $role_list);
        $this->assign('branch_list', $branch_list);
        $this->display();
    }

    public function viewUserInfo()
    {
        if($_GET['type']=='cu'){//企业用户
            $userinfo=$this->user->companyInfo($_GET['id']);
        }
        if($_GET['type']=='pu'){//个人用户
            $userinfo=$this->user->userInfo($_GET['id']);
        }
        $this->assign('userinfo',$userinfo);
        $this->display('viewUserInfo_'.$_GET['type']);
    }
    /**
     * 修改用户信息表单
     */
    public function editUserInfoForm()  
    {
        $userinfo=$this->user->userInfo($_GET['id']);
        $branch_list = M('branch')->findall();
        
        $list = M('user')->where(array('uid'=>$_GET['id']))->find();

        $role = M('user_role')->where(array('uid'=>$_GET['id']))->find();
        //var_dump($role);die;
        $this->assign('role',$role);
        $this->assign('list',$list);
        $this->assign('userinfo',$userinfo);
        $this->assign('branch_list', $branch_list);
        $this->display('editUserInfo');
    }
    /*
     * 获取用户提现信息
     */
    public function getUserDra(){
        $db = M('commission_withdrawal');
        $userinfo = $db->where('cwid = '.$_GET['id'])->find();
        $id = $_GET['id'];
        if(empty($_POST['bank_account']) and empty($_POST['account_name']) and empty($_POST['bank'])){
            $this->assign('userinfo',$userinfo);
            $this->display('updateUserDra');
        }else {
            if($db->where('cwid = ' . $id)->update(array('bank_account' => $_POST['bank_account'], 'account_name' => $_POST['account_name'], 'bank' => $_POST['bank']))){
                $this->success('修改成功 O(∩_∩)O');
            }else{
                $this->error('操作失败 (′⌒`)');
            }
        }
    }
    /**
     * 修改用户信息
     */
     public function editUserInfo()
    {
            //var_dump($_POST);die;
            $cond='uid='.$_GET['id'];

            if(empty($_POST['password'])){
                unset($_POST['password']);
            }else{
                $user = M('user')->where($cond)->find();

                if(!empty($user['client_id'])){//是真实用户，不是系统用户

                    $huanxinResult = $this->modifyHuanXinPassword($user['username'],$_POST['password']);

                    if(!empty($huanxinResult['error'])){
                        $this->error('环信修改失败');
                    }

                }

                $_POST['password']=md5_d($_POST['password']);
                
                $auth_model = new authModel();

                $_POST['branchname'] = $_POST['branchname'];
                $auth_model->prune_all_keys($user['uid']);

            }

//            $_POST['username'] = ['username'=>$_POST['username']];
//            $_POST['user_point']=array('point'=>$_POST['point']);
//            $_POST['desc']=empty($_POST['desc']) ? '' : $_POST['desc'];
            if($this->user->updateUserinfo($cond,$_POST)){//更新用户信息,user、point表
                go('userList');
            }
    }
    //禁止用户
    public function banUser()
    {
        if($this->user->banUser(array('uid'=>$_POST['id']),$_POST['type'])){
            echo 1;
            exit;
        }
    }
    //删除用户
    public function delUser()
    {
        if($this->user->delUser(array('uid'=>$_POST['id']))){
            echo 1;
            exit;   
        }
    }

    /**
     * @Title: export
     * @Description: todo(导出提现记录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function export(){
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename=提现记录导出.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo iconv("UTF-8", "GBK","提现金额")."\t";
        echo iconv("UTF-8", "GBK","银行账户")."\t";
        echo iconv("UTF-8", "GBK","开户名")."\t";
        echo iconv("UTF-8", "GBK","开户行")."\t";
        echo iconv("UTF-8", "GBK","申请时间")."\t";
        echo iconv("UTF-8", "GBK","联系电话")."\t";
        echo "\n";

    $infos = M('commission_withdrawal')->findall();
//        周超（组长） 2015/11/27 15:30:32
    foreach ($infos as $info){

        echo iconv("UTF-8", "GBK",$info['amount'])."\t";
        echo iconv("UTF-8", "GBK",'账户：'.$info['bank_account'])."\t";
        echo iconv("UTF-8", "GBK",$info['account_name'])."\t";
        echo iconv("UTF-8", "GBK",$info['bank'])."\t";
        echo iconv("UTF-8", "GBK",date('Y-m-d H:i:s',$info['create_time']))."\t";
        echo iconv("UTF-8", "GBK",$info['phone'])."\t";
        echo "\n";
    }

    }

    /**
     * @Title: export_salesmanShare
     * @Description: todo(导出邀请记录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function export_salesmanShare(){
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename=业务部邀请记录导出.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo iconv("UTF-8", "GBK","业务标识")."\t";
        echo iconv("UTF-8", "GBK","邀请时间")."\t";
        echo iconv("UTF-8", "GBK","被邀请人手机")."\t";
        echo iconv("UTF-8", "GBK","被邀请人姓名")."\t";
        echo iconv("UTF-8", "GBK","被邀请人身份证")."\t";
        echo "\n";

        $users = M('user')->where('`desc` > 0')->findall();
        $uids = '';
        foreach($users as $key=>$value){
            $uids .= $value['uid'].',';
        }

        $uids = substr($uids,0,strlen($uids)-1);
        if(empty($_POST['start_time']) || empty($_POST['end_time'])){//如果无时间筛选条件，导出所有
            $cond = 'uid in('.$uids.')';
        }else{//否则按照时间筛选导出
            $start_time = strtotime($_POST['start_time']);
            $end_time = strtotime($_POST['end_time']);
            $cond = 'uid in('.$uids.') and create_time > '.$start_time.' and create_time < '.$end_time;
        }
        $infos = M('commission_log')->where($cond)->findall();
        foreach ($infos as $info){
            $userinfo = M('user_info')->where(array('uid'=>$info['from_id']))->find();
            $user = M('user')->where(array('uid'=>$info['from_id']))->find();

            echo iconv("UTF-8", "GBK",$info['username'])."\t";
            echo iconv("UTF-8", "GBK",date('Y-m-d H:i:s',$user['created']))."\t";
            echo iconv("UTF-8", "GBK",$user['username'])."\t";
            echo iconv("UTF-8", "GBK",empty($userinfo['name']) ? '暂无' : $userinfo['name'])."\t";
            echo iconv("UTF-8", "GBK",empty($userinfo['id_number']) ? '暂无' : $userinfo['id_number'])."\t";
            echo "\n";
        }

    }

    /**
     * @Title: export_users
     * @Description: todo(导出用户列表)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function export_users(){

        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename=用户列表.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo iconv("UTF-8", "GBK","手机号码")."\t";
        echo iconv("UTF-8", "GBK","注册时间")."\t";
        echo iconv("UTF-8", "GBK","姓名")."\t";
        echo iconv("UTF-8", "GBK","身份证号码")."\t";
        echo iconv("UTF-8", "GBK","认证状态")."\t";
        echo iconv("UTF-8", "GBK","积分")."\t";
        echo iconv("UTF-8", "GBK","佣金")."\t";
        echo iconv("UTF-8", "GBK","注册途径")."\t";
        echo iconv("UTF-8", "GBK","来源")."\t";
        echo "\n";

        $start_time = strtotime($_POST['start_time']);

        $end_time = strtotime($_POST['end_time']);

        $cond = ' created > '.$start_time.' and created < '.$end_time;

        $users = M('user')->where($cond)->findall();

        $uids = '';

        foreach($users as $key=>$value){
            $uids .= $value['uid'].',';
        }
        $uids = substr($uids,0,strlen($uids)-1);
        $user_info = M('user_info')->where('uid in('.$uids.')')->findall();

        foreach ($users as $key=>$user){
            $user_point = M('user_point')->where('uid = '.$user['uid'])->find();
            $user_role = M('user_role')->where('uid = '.$user['uid'])->find();
            echo iconv("UTF-8", "GBK",empty($user['username'])) ? '' : $user['username']."\t";
            echo iconv("UTF-8", "GBK",date('Y-m-d H:i:s',$user['created']))."\t";
            echo iconv("UTF-8", "GBK",empty($user_info[$key]['name']) ? '暂无' :$user_info[$key]['name']) ."\t";
            echo iconv("UTF-8", "GBK",empty($user_info[$key]['id_number']) ? '暂无' : $user['id_number'])."\t";
            echo iconv("UTF-8", "GBK",$this->getUserVerifyCn($user_info[$key]['verify']))."\t";
            echo iconv("UTF-8", "GBK",$user_point['point'])."\t";
            echo iconv("UTF-8", "GBK",$user['commission'])."\t";
            echo iconv("UTF-8", "GBK",$user['type'])."\t";
            $a = array();
            if($user_role['rid'] == 8){
               if($user['type']){
                
                    if($user['branchname']){
                        $a[]=$user['branchname'];
                    }
                    if($user['salesmanname']){
                        $a[]=$user['salesmanname']."[ID:".$user['salesmanid']."]";
                    }
                    if($user['salesmanphoneno']){
                        $a[]=$user['salesmanphoneno'];
                    }
                    if($user['normalmanid']){
                         $normalman = M('user')->where(array('uid'=>$user['normalmanid']))->find();
                         $a[]=$normalman['username']."[ID:".$user['normalmanid']."]";
                    }
                }
            }
            $user['laiyuan'] = implode(",", $a);

            echo iconv("UTF-8", "GBK",$user['laiyuan'])."\t";
            echo "\n";
        }

    }

    /**
     * @Title: getUserVerifyCn
     * @Description: todo(得到用户认证状态(在导出用户列表方法被使用))
     * @author liuzhipeng
     * @param $verify
     * @return  string  返回类型
     */
    public function getUserVerifyCn($verify){
        if($verify==0){
            return '未认证';
        }elseif($verify==1){
            return '认证审核中';
        }elseif($verify==2){
            return '认证审核未通过';
        }elseif($verify==3){
            return '认证审核通过';
        }else{
            return '不详';
        }
    }

    public function getUserCommission(){
        $db = M('user');
        $user = $db->where('uid = '.$_GET['id'])->find();
        $this->assign('user',$user);
        $this->display();
    }



    //生成二维码
    public function getRecruitQR(){
        //include_once('/var/www/html/hpjobweb/web/backend/libs/phpqrcode.php');
        $object = new QRcode(); 
        $uid = $_GET['id'];
        $url='http://www.hap-job.com/index.php/app/auth/share/from/35920?from=singlemessage&isappinstalled=1&salesmanid='.$uid;
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
        if($result !== false){
             $this->success('生成成功');
        }
    }


    /**
     * @Title: editUserCommission
     * @Description: todo(佣金转移)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function editUserCommission(){     $db = M('user');
        $user = $db->where('uid = '.$_GET['id'])->find();
        if($_POST['username'] != $_POST['re_name']) {
            $this->error('两次输入不一致,请重新输入');
        }else{
            $commission = M('user')->where('username = '.$_POST['username'])->find();
            if(empty($commission)){
                $this->error('用户不存在 (ㄒoㄒ) ');
            }
            $add_com = $user['commission'] + $commission['commission'];
            if($user_com = $db->where('uid = '.$_GET['id'])->update(array('commission'=>$add_com))){
                $db->where('username = '.$_POST['username'])->update(array('commission'=>0));
                $this->success('转移成功 O(∩_∩)O ');
            }else{
                $this->error('转移失败 (ㄒoㄒ) ');
            }
        }

    }

    /**
     * @Title: verifyRecruit
     * @Description: todo(身份审核)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function verifyRole()
    {
        $db=M('user_info');
        $data=array();
        $user = M('user')->where('uid='.$_GET['uid'])->find();

        if($_GET['name']=='2'){
            addNewMessages($_GET['uid'],$content='您的身份认证未通过审核，请手持身份证使用前置摄像头拍摄，确保五官、身份证上所有信息清晰可见，安卓用户请关闭自拍镜像，请再次进入我的佣金/工资查询进行身份认证,如有疑问，请联系客服 4006920099。',$title='身份审核',4,401);
            $hidden = array(
                'type'=>4,
                'data_type'=>401,
                'title'=>'身份审核',
                'content'=>'您的身份认证未通过审核，请手持身份证使用前置摄像头拍摄，确保五官、身份证上所有信息清晰可见，安卓用户请关闭自拍镜像，请再次进入我的佣金/工资查询进行身份认证,如有疑问，请联系客服 4006920099。'
            );
            if(!empty($user['client_id'])){

                push(array($user['client_id']),array('hidden'=>$hidden,'title'=>'身份审核','content'=>$hidden['content']));
            }
            $data['verify']=2;
            $data['id_number']='';
            $data['name'] = '';
        }
        if($_GET['name']=='3'){
            addNewMessages($_GET['uid'],$content='您的身份认证已经通过审核，我的佣金/工资查询已经可以查询，开开心心找工作，享受贴心服务！',$title='身份审核',4,401);
            $hidden = array(
                'type'=>4,
                'data_type'=>401,
                'title'=>'身份审核',
                'content'=>'您的身份认证已经通过审核，我的佣金/工资查询已经可以查询，开开心心找工作，享受贴心服务！'
            );
            if(!empty($user['client_id'])){

                push(array($user['client_id']),array('hidden'=>$hidden,'title'=>'身份审核','content'=>$hidden['content']));
            }

            $data['verify']=3;
        }
        if($db->in(array('uid'=>$_GET['uid']))->update($data)){
            $this->yaointoComlog($_GET['uid'],3);
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
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
     * 添加角色 
     */
    function addRole() 
    {
        if (isset($_POST['addRole'])) {
            $this->user->addRole($_POST);
            $this->success('角色添加成功,别忘了配置角色权限噢。','roleList');
        }
        $roles=$this->user->roleList();
        $this->assign('roles',$roles);
        $this->display();
    }
    /**
     * 配置用户组权限
     */
    public function configPermission()
    {
        $db = M('node');
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $action='insert';
            if($db->table('access')->count($_GET['rid'])){
                $action='update';
            }
            $_POST['permissions']=json_encode($_POST['permissions']);
            $db->table('access')->$action();
            $this->success('权限修改成功。','roleList');
        }
        $authModel=new authModel;
        $permissions=$authModel->get_permissions(array($_GET['rid']));
        $nodes=$db->order('sort,nid')->findall();
        $nodes=formatLevelData2($nodes,array('nid','pid'));
        $this->assign('nodes',$nodes);
        $this->assign('permissions',$permissions);
        $this->display();
    }
    /**
     * 配置用户角色
     */
    public function configUserRole()
    {
        $db=M('user_role');
        $db->where('uid='.$_POST['uid'])->del();
        foreach ($_POST['rid'] as $value) {
            $db->insert(array('uid'=>$_POST['uid'],'rid'=>$value));
        }
        go('userList');
    }
    /**
     * 节点列表
     */
    public function nodeList()
    {
        $db=M('node');
        $nodes=$db->order('sort,nid')->findall();
        $nodes=formatLevelData2($nodes,array('nid','pid'));
        $this->assign('nodes',$nodes);
        $this->display();
    }
    /**
     * 角色列表
     */
    public function roleList()
    {
        if($_SERVER['REQUEST_METHOD']=='POST'){
            foreach ($_POST['sort'] as $key => $value) {
                //角色排序
                $this->user->updateRole(array('rid'=>$key),array('sort'=>$value));
            }
            go('roleList');
        }
        $roles=$this->user->roleList();
        $this->assign('roles',$roles);
        $this->display();
    }
    /**
     * 删除角色
     */
    public function delRole()
    {
        if($this->user->delRole($_POST,'is_sys=0')){
            echo 1;
            exit;
        }
        echo 0;
        exit;
    }


    /**
     * 禁止/开启用户角色
     */
    public function banRole()
    {
        $this->user->updateRole($_POST,array('state'=>$_GET['state']));
        echo 1;
        exit;
    }
    public function addNode()
    {
        $db=M('node');
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $_POST['name']='/'.trim($_POST['name'],'/').'/';
            if($db->insert()){
                $this->success('添加节点成功。','nodeList');
            }
        }
        $nodes=$db->order('sort,nid')->findall();
        $nodes=formatLevelData2($nodes,array('nid','pid'));
        $this->assign('nodes',$nodes);
        $this->display();
    }
    /**
     * 删除节点
     */
    public function delNode()
    {
        $db=M('node');
        if($db->in(node_son_id($_POST['id']))->del()){
            echo 1;
            exit;
        }
    }


    /**
     * 修改节点
     */
    public function editNode()
    {
        $db=M('node');
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $db->where('nid='.$_GET['nid'])->update();
            $this->success('修改节点信息成功','nodeList');
        }
        $node=$db->where('nid='.$_GET['nid'])->find();
        $nodes=$db->field('nid,title,pid')->order('sort,nid')->findall();
        $nodes=formatLevelData2($nodes,array('nid','pid'));
        $this->assign('nodes',$nodes);
        $this->assign('node',$node);
        $this->display();
    }

    /**
     * @Title: salesmanShare
     * @Description: todo(业务部邀请列表)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function salesmanShare(){
        $_GET = urldecode_array($_GET);
        $cond = array();
        if(isset($_GET['username'])){
            $_GET['username'] = urldecode($_GET['username']);

            $cond[]='username like "%'.$_GET['username'].'%"';
        }

        if(isset($_GET['from_id'])){

            $_GET['from_id'] = urldecode($_GET['from_id']);
            $user = M('user')->where('username like "%'.$_GET['from_id'].'%"')->findall();
//            $uid = empty($user) ? 0 :$user['uid'];
            $uid='';
            foreach($user as $k=>$v){
                $uid = $v['uid'].',';
            }
            $uid = substr($uid,0,strlen($uid)-1);

            $cond[]=' from_id in ('.$uid.')';
        }

        if(!empty($_GET['start_time']) && isset($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = ' create_time > ' . $start_time . ' AND create_time < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'create_time >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'create_time <' . $end_time;
        }
        $users = M('user')->where('`desc` > 0')->findall();
        $uids = '';
        foreach($users as $key=>$value){
            $uids .= $value['uid'].',';
        }

        $uids = substr($uids,0,strlen($uids)-1);
        $cond[] = 'uid in('.$uids.')';
        $count = M('commission_log')->where($cond)->count();
        $page = new page($count,10);
        $commissions = M('commission_log')->order('id desc')->where($cond)->findall($page->limit());

        foreach($commissions as $key=>$val){
            $commissions[$key]['counts'] = $count;
            $uid = $val['uid'];//邀请人uid
            $resume = M('user_info')->where('uid = '.$uid)->find();
            $username = M('user')->where('uid = '.$uid)->find();
            $f_uid = $val['from_id'];//被邀请人uid
            // 被邀请人存在
            $f_name = M('user_info')->where('uid = '.$f_uid)->find();//被邀请人身份证信息
            $mobile = M('user')->where('uid = '.$f_uid)->find();//被邀请人手机号

            $commissions[$key]['f_name'] = $f_name['name'];
            $commissions[$key]['f_gender'] = $f_name['gender'];
            $commissions[$key]['f_id_number'] = $f_name['id_number'];
            $commissions[$key]['f_mobile'] = $mobile['username'];
            $commissions[$key]['created_at'] = $mobile['created'];
            if($f_uid==0){
                $commissions[$key]['f_mobile'] = '暂无';
            }

            $commissions[$key]['name'] = $resume['name'];
            $commissions[$key]['gender'] = $resume['gender'];
            $commissions[$key]['id_number'] = $resume['id_number'];
            $commissions[$key]['phone'] = $username['username'];

            if(empty($resume['name'])){
                $commissions[$key]['name'] = '暂无';
            }
            if(empty($resume['id_number'])){
                $commissions[$key]['id_number'] = '暂无';
            }
            if(empty($f_name['name'])){
                $invite[$key]['f_name'] = '暂无';
            }
            if(empty($f_name['id_number'])){
                $invite[$key]['f_id_number'] = '暂无';
            }
//            if(empty($mobile['username'])){
//                $invite[$key]['f_mobile'] = '暂无';
//            }
        }
        $this->assign('commission',$commissions);
        $this->assign('page',$page->show());
        $this->assign('counts',$count);
        $this->display();
    }

    /**
     * @Title: commission
     * @Description: todo(佣金列表)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function commission(){
        $_GET = urldecode_array($_GET);
        $users = M('user')->where('`desc` > 0')->findall();
        $uids = '';
        foreach($users as $key=>$value){
            $uids .= $value['uid'].',';
        }

        $uids = substr($uids,0,strlen($uids)-1);
        $cond[] = 'uid not in('.$uids.')';
        if(isset($_GET['username'])){
            $_GET['username'] = urldecode($_GET['username']);

            $cond[] ='username like "%'.$_GET['username'].'%"';
        }

        if(isset($_GET['commission'])){
            $_GET['commission'] = urldecode($_GET['commission']);

            $cond[] ='commission like "%'.$_GET['commission'].'%"';
        }
        if(!empty($_GET['start_time']) && isset($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = ' create_time > ' . $start_time . ' AND create_time < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = ' create_time >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'create_time <' . $end_time;
        }
        if(isset($_GET['type'])){
            $cond[] =' type = '.$_GET['type'];
        }

        $count = M('commission_log')->where($cond)->count();
        $page = new page($count,10);
        $commissions = M('commission_log')->order('id desc')->where($cond)->findall($page->limit());
//        echo '<pre/>';
//        var_dump($cond);
//        var_dump($commissions);
        //die();
        foreach($commissions as $key=>$val){
            $uid = $val['uid'];//邀请人uid
            $resume = M('user_info')->where('uid = '.$uid)->find();
            $username = M('user')->where('uid = '.$uid)->find();
            $f_uid = $val['from_id'];//被邀请人uid
          // 被邀请人存在
                $f_name = M('user_info')->where('uid = '.$f_uid)->find();//被邀请人身份证信息
                $mobile = M('user')->where('uid = '.$f_uid)->find();//被邀请人手机号

                $commissions[$key]['f_name'] = $f_name['name'];
                $commissions[$key]['f_gender'] = $f_name['gender'];
                $commissions[$key]['f_id_number'] = $f_name['id_number'];
                $commissions[$key]['f_mobile'] = $mobile['username'];
            if($f_uid==0){
                $commissions[$key]['f_mobile'] = '暂无';
            }

            $commissions[$key]['name'] = $resume['name'];
            $commissions[$key]['gender'] = $resume['gender'];
            $commissions[$key]['id_number'] = $resume['id_number'];
            $commissions[$key]['phone'] = $username['username'];

            if(empty($resume['name'])){
                $commissions[$key]['name'] = '暂无';
            }
            if(empty($resume['id_number'])){
                $commissions[$key]['id_number'] = '暂无';
            }
            if(empty($f_name['name'])){
                $invite[$key]['f_name'] = '暂无';
            }
            if(empty($f_name['id_number'])){
                $invite[$key]['f_id_number'] = '暂无';
            }
//            if(empty($mobile['username'])){
//                $invite[$key]['f_mobile'] = '暂无';
//            }
        }
        $this->assign('commission',$commissions);
        $this->assign('page',$page->show());
        $this->display();
    }

    /**
     * @Title: verify
     * @Description: todo(佣金审核)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function verify(){
        $uid = $_GET['uid'];
        $commissions = M('user_info')->where('uid=' . $uid)->find();
        $verify = $commissions['verify'];
        if($verify==2){
            M('user_info')->where('uid=' . $uid)->update(array('verify'=>3));
        }elseif($verify==3){
            M('user_info')->where('uid=' . $uid)->update(array('verify'=>2));
        }elseif($verify==1){
            M('user_info')->where('uid=' . $uid)->update(array('verify'=>3));
        }
        $this->success('修改成功');
    }


    /**
     * @Title: insertCommission
     * @Description: todo(导入入职返现)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function insertCommission(){
        $upload = new upload(PATH_ROOT . '/uploads/excel', array('xls', 'xlsx'));
        $info = $upload->upload();
        if ($info) {
            $file = $info[0]['path'];
        }
        $data = parseExcel($file);
        unset($data[1]);
        $db=M('commission_into');
        $ta=M('user_info');
        $td=M('user');
//        $errors = array();
        foreach ($data as $k=>$v) {
            $uid = $ta->where (array ('id_number' => $v[5]))->find ();
            $commission = $td->where (array ('uid' => $uid['uid']))->field ('commission')->find ();

            $arr = array (
//                'uid' => $uid['uid'],
                'content' => $v[1],
                'commission' => '+' . $v[2],
                'company_name' => $v[3],
                'job_time' => $v[4],
                'id_number' => $v[5],
                'create_time' => time (),
                'type' => '1',
                'root'=>$_SESSION['username']
            );
            $cut = $commission['commission'] + $v[2];
            $td->where ('uid =' . $uid['uid'])->update (array ('commission' => $cut));
            $db->insert ($arr);
        }

        $this->success('操作成功');

    }


    /**
     * @Title: commission_into
     * @Description: todo()
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function commission_into(){
        $_GET = urldecode_array($_GET);
        if($_GET['action']=='ruzhifanxian'){
            //获取所有62天之内入职的人员，
            //符合入职标准1，达到相应时间2，符合性别设定 gender=0 男 gender=1 女
            $db = M('deliver');
            //有离职时间;
            //和离职时间相比，没有离职和当前时间相比，71天中所有入职，且未生成返现佣金的人员，最多返现时长60天
            $res = $db->query("SELECT COUNT(a.uid) as unum,a.id,a.uid,a.username,a.rel_name,a.recruit_id,a.company_name,a.company_id,a.entry_time,a.gender,b.recruit_name,b.welfare,b.return_money,u.id_number,a.resign_time
                                FROM hp_deliver AS a INNER JOIN hp_recruit AS b ON a.recruit_id=b.recruit_id INNER JOIN hp_user_info as u ON a.uid=u.uid
                        WHERE ( (unix_timestamp(DATE_SUB(NOW() ,INTERVAL 71 DAY)) <= a.entry_time AND a.resign_time=0 AND (unix_timestamp(DATE_SUB(NOW() ,INTERVAL 27 DAY)) > a.entry_time) AND a.entry_status=1)
                         OR 
                        ((unix_timestamp(DATE_SUB(FROM_UNIXTIME(a.resign_time, '%Y-%m-%d %H:%i:%S') ,INTERVAL 71 DAY)) <= a.entry_time AND a.resign_time>0) AND unix_timestamp(DATE_SUB(FROM_UNIXTIME(a.resign_time, '%Y-%m-%d %H:%i:%S') ,INTERVAL 27 DAY)) > a.entry_time AND a.entry_status=0))
                        AND  b.welfare LIKE '%948%' AND b.return_money IS NOT NULL AND a.send_status=0 AND  a.entry_time<unix_timestamp(NOW()) GROUP BY a.uid ORDER BY a.entry_time DESC");
            //send_statuts 0代表未生成佣金 1已生成佣金
//            echo '<pre/>';
//            var_dump($res);
//            die();
            if(empty($res)){
                $this->success('没有数据要生成');
            }
            foreach ($res as $k=>$v){
                if($v['unum']==1){ //时间段内只入职了一次，查到相应职位看条件是否符合
                    if(strpos($v['return_money'],',') !== false) { //英文逗号
                        $reway = explode(',',$v['return_money']);
                    }else if(strpos($v['return_money'],'，') !== false){ //中文逗号
                        $reway = explode('，',$v['return_money']);
                    }else{
                        $reway = explode(',',$v['return_money']);
                    }
                    if(!empty($reway[0])){ //第一个数组元素不为空可能是男士可能是女士
                        $rewayinfo[0] = explode(' ',$reway[0]);
                        if(strpos($reway[0],'男') !== false){ //男士有返现
                            if($v['gender']==0){ //投递人是男士
                                if($rewayinfo[0][1]=='月返'){
                                    //入职日向后推一个月
                                    $welday = strtotime("+1 months", $v[entry_time]);
                                    $pretime =intval($v['resign_time'])>0?intval($v['resign_time']):time();//截止到离职日期或者当前日期
                                    if((int)$welday<=$pretime){//返现日期是（离职）今日或者（离职）今日之前添加佣金记录
                                        $deliverid[]=$v['id'];
                                        $comloginfo[] = array (
                                            'content' => '入职返现',
                                            'rel_name' => $v['rel_name'],
                                            'uid' => $v['uid'],
                                            'commission' => '+' .$rewayinfo[0][2],//入职返现
                                            'company_name' => $v['company_name'],//公司名称
                                            'job_time' => date('Y-m-d',$v['entry_time']),//入职时间
                                            'resign_time' => $v['resign_time']>0?date('Y-m-d',$v['resign_time']):'',//离职时间
                                            'id_number' => $v['id_number'],//身份证号
                                            'create_time' => time (),//
                                            'type' => '3',//属于系统生成的数据
                                            'root'=>$_SESSION['username'],
                                            'status'=>0 //未审核
                                        );
                                       // var_dump($arr);
                                    }
                                }else{
                                    $str=$rewayinfo[0][1];
                                    if(preg_match('/\d+/',$str,$arr)) {
                                        $welday = strtotime('+'.$arr[0].' days',$v['entry_time']);
                                        $pretime =$v['resign_time']>0?$v['resign_time']:time();
                                        if((int)$welday<=$pretime){ //返现日期是今日或者今日之前添加佣金记录
                                            $deliverid[]=$v['id'];
                                            $comloginfo[] = array (
                                                'content' => '入职返现',
                                                'rel_name' => $v['rel_name'],
                                                'uid' => $v['uid'],
                                                'commission' => '+' .$rewayinfo[0][2],//入职返现
                                                'company_name' => $v['company_name'],//公司名称
                                                'job_time' => date('Y-m-d',$v['entry_time']),//入职时间
                                                'resign_time' => $v['resign_time']>0?date('Y-m-d',$v['resign_time']):'',//离职时间
                                                'id_number' => $v['id_number'],//身份证号
                                                'create_time' => time (),//
                                                'type' => '3',//属于系统生成的数据
                                                'root'=>$_SESSION['username'],
                                                'status'=>0 //未审核

                                            );
                                        }
                                    }
                                }
                            }
                        }else if(strpos($reway[0],'女') !== false){ //女士有返现
                            if($v['gender']==1){ //投递人是女士
                                if($rewayinfo[0][1]=='月返'){
                                    //入职日向后推一个月
                                    $welday = strtotime("+1 months", $v[entry_time]);
                                    $pretime =intval($v['resign_time'])>0?intval($v['resign_time']):time();//截止到离职日期或者当前日期
                                    if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                        $deliverid[]=$v['id'];
                                        $comloginfo[] = array (
                                            'content' => '入职返现',
                                            'rel_name' => $v['rel_name'],
                                            'uid' => $v['uid'],
                                            'commission' => '+' .$rewayinfo[0][2],//入职返现
                                            'company_name' => $v['company_name'],//公司名称
                                            'job_time' => date('Y-m-d',$v['entry_time']),//入职时间
                                            'resign_time' => $v['resign_time']>0?date('Y-m-d',$v['resign_time']):'',//离职时间
                                            'id_number' => $v['id_number'],//身份证号
                                            'create_time' => time (),//
                                            'type' => '3',//属于系统生成的数据
                                            'root'=>$_SESSION['username'],
                                            'status'=>0 //未审核
                                        );
                                    }
                                }else{
                                    $str=$rewayinfo[0][1];
                                    if(preg_match('/\d+/',$str,$arr)) {
                                        $welday = strtotime('+'.$arr[0].' days',$v['entry_time']);
                                        $pretime =intval($v['resign_time'])>0?intval($v['resign_time']):time();//截止到离职日期或者当前日期
                                        if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                            $deliverid[]=$v['id'];
                                            $comloginfo[] = array (
                                                'content' => '入职返现',
                                                'rel_name' => $v['rel_name'],
                                                'uid' => $v['uid'],
                                                'commission' => '+' .$rewayinfo[0][2],//入职返现
                                                'company_name' => $v['company_name'],//公司名称
                                                'job_time' => date('Y-m-d',$v['entry_time']),//入职时间
                                                'resign_time' => $v['resign_time']>0?date('Y-m-d',$v['resign_time']):'',//离职时间
                                                'id_number' => $v['id_number'],//身份证号
                                                'create_time' => time (),//
                                                'type' => '3',//属于系统生成的数据
                                                'root'=>$_SESSION['username'],
                                                'status'=>0 //未审核
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        unset($arr);
                        unset($welday);
                        unset($rewayinfo);
                        unset($welday);
                        unset($pretime);
                    }
                    if(!empty($reway[1])){ //女士有返现
                        $rewayinfo[1] = explode(' ',$reway[1]);
                        if($v['gender']==1){ //投递人是女士
                            if($rewayinfo[1][1]=='月返'){
                                //入职日向后推一个月
                                $welday = strtotime("+1 months", $v[entry_time]);
                                $pretime =intval($v['resign_time'])>0?intval($v['resign_time']):time();//截止到离职日期或者当前日期
                                if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                    //$rewayinfo[1][2] 返现佣金 可能不是数字 类似市场价一类的
                                    $deliverid[]=$v['id'];
                                    $comloginfo[] = array (
                                        'content' => '入职返现',
                                        'rel_name' => $v['rel_name'],
                                        'uid' => $v['uid'],
                                        'commission' => '+' .$rewayinfo[1][2],//入职返现
                                        'company_name' => $v['company_name'],//公司名称
                                        'job_time' => date('Y-m-d',$v['entry_time']),//入职时间
                                        'resign_time' => $v['resign_time']>0?date('Y-m-d',$v['resign_time']):'',//离职时间
                                        'id_number' => $v['id_number'],//身份证号
                                        'create_time' => time (),//
                                        'type' => '3',//属于系统生成的数据
                                        'root'=>$_SESSION['username'],
                                        'status'=>0 //未审核
                                    );
                                }
                            }else{
                                $str=$rewayinfo[0][1];
                                if(preg_match('/\d+/',$str,$arr)) {
                                    $welday = strtotime('+'.$arr[0].' days',$v['entry_time']);
                                    $pretime =intval($v['resign_time'])>0?intval($v['resign_time']):time();//截止到离职日期或者当前日期
                                    if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                        $deliverid[]=$v['id'];
                                        $comloginfo[] = array (
                                            'content' => '入职返现',
                                            'rel_name' => $v['rel_name'],
                                            'uid' => $v['uid'],
                                            'commission' => '+' .$rewayinfo[1][2],//入职返现
                                            'company_name' => $v['company_name'],//公司名称
                                            'job_time' => date('Y-m-d',$v['entry_time']),//入职时间
                                            'resign_time' => $v['resign_time']>0?date('Y-m-d',$v['resign_time']):'',//离职时间
                                            'id_number' => $v['id_number'],//身份证号
                                            'create_time' => time (),//
                                            'type' => '3',//属于系统生成的数据
                                            'root'=>$_SESSION['username'],
                                            'status'=>0 //未审核
                                        );
                                    }
                                }
                            }
                        }
                        unset($arr);
                        unset($welday);
                        unset($rewayinfo);
                        unset($welday);
                        unset($pretime);
                    }

                }else{//时间段内入职多次
                    $res2 = $db->query("SELECT a.id,a.uid,a.username,a.rel_name,a.recruit_id,a.company_name,a.company_id,a.entry_time,a.gender,b.recruit_name,b.welfare,b.return_money,u.id_number,a.resign_time
                                FROM hp_deliver AS a INNER JOIN hp_recruit AS b ON a.recruit_id=b.recruit_id INNER JOIN hp_user_info as u ON a.uid=u.uid
                        WHERE ( (unix_timestamp(DATE_SUB(NOW() ,INTERVAL 71 DAY)) <= a.entry_time AND a.resign_time=0 AND (unix_timestamp(DATE_SUB(NOW() ,INTERVAL 27 DAY)) > a.entry_time) AND a.entry_status=1)
                         OR 
                        ((unix_timestamp(DATE_SUB(FROM_UNIXTIME(a.resign_time, '%Y-%m-%d %H:%i:%S') ,INTERVAL 71 DAY)) <= a.entry_time AND a.resign_time>0) AND unix_timestamp(DATE_SUB(FROM_UNIXTIME(a.resign_time, '%Y-%m-%d %H:%i:%S') ,INTERVAL 27 DAY)) > a.entry_time AND a.entry_status=0))
                        AND  b.welfare LIKE '%948%' AND b.return_money IS NOT NULL AND a.send_status=0 AND a.uid=".$v['uid']." ORDER BY a.entry_time DESC");
                    foreach ($res2 as $kk=>$vv){
                        //一个用户的多条入职投递,一般不存在，用户不在职会有一个离职标识,离职时间与在职时间之间的差值是否满足返现条件
                        //特殊情况 一个用户多次入职但是都没有离职标志默认和现在时刻相比
                        if(strpos($vv['return_money'],',') !== false) { //英文逗号
                            $reway = explode(',',$vv['return_money']);
                        }else if(strpos($vv['return_money'],'，') !== false){ //中文逗号
                            $reway = explode('，',$vv['return_money']);
                        }else{
                            $reway = explode(',',$vv['return_money']);
                        }
                        $rewayinfo[0] = explode(' ',$reway[0]);
                        //第一个数组元素不为空可能是男士可能是女士
                        if(!empty($reway[0])){ //第一个数组不为空可能是男士可能是女士
                            $rewayinfo[0] = explode(' ',$reway[0]);
                            if(strpos($reway[0],'男') !== false){ //男士有返现

                                //var_dump($rewayinfo);
                                if($vv['gender']==0){ //投递人是男士
                                    if($rewayinfo[0][1]=='月返'){
                                        //入职日向后推一个月
                                        $welday = strtotime("+1 months", $vv[entry_time]);
                                        $pretime =intval($vv['resign_time'])>0?intval($vv['resign_time']):time();//截止到离职日期或者当前日期
                                        if((int)$welday<=$pretime){//返现日期是（离职）今日或者（离职）今日之前添加佣金记录
                                            $deliverid[]=$vv['id'];
                                            $comloginfo[] = array (
                                                'content' => '入职返现',
                                                'rel_name' => $vv['rel_name'],
                                                'uid' => $v['uid'],
                                                'commission' => '+' .$rewayinfo[0][2],//入职返现
                                                'company_name' => $vv['company_name'],//公司名称
                                                'job_time' => date('Y-m-d',$vv['entry_time']),//入职时间
                                                'resign_time' => $vv['resign_time']>0?date('Y-m-d',$vv['resign_time']):'',//离职时间
                                                'id_number' => $vv['id_number'],//身份证号
                                                'create_time' => time (),//
                                                'type' => '3',//属于系统生成的数据
                                                'root'=>$_SESSION['username'],
                                                'status'=>0 //未审核

                                            );
                                            // var_dump($arr);
                                        }
                                    }else{
                                        $str=$rewayinfo[0][1];
                                        if(preg_match('/\d+/',$str,$arr)) {
                                            $welday = strtotime('+'.$arr[0].' days',$vv['entry_time']);
                                            $pretime =$vv['resign_time']>0?$vv['resign_time']:time();
                                            if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                                $deliverid[]=$vv['id'];
                                                $comloginfo[] = array (
                                                    'content' => '入职返现',
                                                    'rel_name' => $vv['rel_name'],
                                                    'uid' => $v['uid'],
                                                    'commission' => '+' .$rewayinfo[0][2],//入职返现
                                                    'company_name' => $vv['company_name'],//公司名称
                                                    'job_time' => date('Y-m-d',$vv['entry_time']),//入职时间
                                                    'resign_time' => $vv['resign_time']>0?date('Y-m-d',$vv['resign_time']):'',//离职时间
                                                    'id_number' => $vv['id_number'],//身份证号
                                                    'create_time' => time (),//
                                                    'type' => '3',//属于系统生成的数据
                                                    'root'=>$_SESSION['username'],
                                                    'status'=>0 //未审核

                                                );
                                            }
                                        }
                                    }
                                }
                            }else if(strpos($reway[0],'女') !== false){ //女士有返现
                                if($vv['gender']==1){ //投递人是女士
                                    if($rewayinfo[0][1]=='月返'){
                                        //入职日向后推一个月
                                        $welday = strtotime("+1 months", $vv[entry_time]);
                                        $pretime =intval($vv['resign_time'])>0?intval($vv['resign_time']):time();//截止到离职日期或者当前日期
                                        if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                            $deliverid[]=$vv['id'];
                                            $comloginfo[] = array (
                                                'content' => '入职返现',
                                                'rel_name' => $vv['rel_name'],
                                                'uid' => $v['uid'],
                                                'commission' => '+' .$rewayinfo[0][2],//入职返现
                                                'company_name' => $vv['company_name'],//公司名称
                                                'job_time' => date('Y-m-d',$vv['entry_time']),//入职时间
                                                'resign_time' => $vv['resign_time']>0?date('Y-m-d',$vv['resign_time']):'',//离职时间
                                                'id_number' => $vv['id_number'],//身份证号
                                                'create_time' => time (),//
                                                'type' => '3',//属于系统生成的数据
                                                'root'=>$_SESSION['username'],
                                                'status'=>0 //未审核
                                            );
                                        }
                                    }else{
                                        $str=$rewayinfo[0][1];
                                        if(preg_match('/\d+/',$str,$arr)) {
                                            $welday = strtotime('+'.$arr[0].' days',$vv['entry_time']);
                                            $pretime =intval($vv['resign_time'])>0?intval($vv['resign_time']):time();//截止到离职日期或者当前日期
                                            if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                                $deliverid[]=$vv['id'];
                                                $comloginfo[] = array (
                                                    'content' => '入职返现',
                                                    'rel_name' => $vv['rel_name'],
                                                    'uid' => $v['uid'],
                                                    'commission' => '+' .$rewayinfo[0][2],//入职返现
                                                    'company_name' => $vv['company_name'],//公司名称
                                                    'job_time' => date('Y-m-d',$vv['entry_time']),//入职时间
                                                    'resign_time' => $vv['resign_time']>0?date('Y-m-d',$vv['resign_time']):'',//离职时间
                                                    'id_number' => $vv['id_number'],//身份证号
                                                    'create_time' => time (),//
                                                    'type' => '3',//属于系统生成的数据
                                                    'root'=>$_SESSION['username'],
                                                    'status'=>0 //未审核
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                            unset($arr);
                            unset($welday);
                            unset($rewayinfo);
                            unset($welday);
                            unset($pretime);
                        }

                        if(!empty($reway[1])){ //女士有返现
                            $rewayinfo[1] = explode(' ',$reway[1]);
                            if($vv['gender']==1){ //投递人是女士
                                if($rewayinfo[1][1]=='月返'){
                                    //入职日向后推一个月
                                    $welday = strtotime("+1 months", $vv[entry_time]);
                                    $pretime =intval($vv['resign_time'])>0?intval($vv['resign_time']):time();//截止到离职日期或者当前日期
                                    if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                        //$rewayinfo[1][2] 返现佣金 可能不是数字 类似市场价一类的
                                        $deliverid[]=$vv['id'];
                                        $comloginfo[] = array (
                                            'content' => '入职返现',
                                            'rel_name' => $vv['rel_name'],
                                            'uid' => $v['uid'],
                                            'commission' => '+' .$rewayinfo[1][2],//入职返现
                                            'company_name' => $vv['company_name'],//公司名称
                                            'job_time' => date('Y-m-d',$vv['entry_time']),//入职时间
                                            'resign_time' => $vv['resign_time']>0?date('Y-m-d',$vv['resign_time']):'',//离职时间
                                            'id_number' => $vv['id_number'],//身份证号
                                            'create_time' => time (),//
                                            'type' => '3',//属于系统生成的数据
                                            'root'=>$_SESSION['username'],
                                            'status'=>0 //未审核
                                        );
                                    }
                                }else{
                                    $str=$rewayinfo[0][1];
                                    if(preg_match('/\d+/',$str,$arr)) {
                                        $welday = strtotime('+'.$arr[0].' days',$vv['entry_time']);
                                        $pretime =intval($vv['resign_time'])>0?intval($vv['resign_time']):time();//截止到离职日期或者当前日期
                                        if((int)$welday<=$pretime){//返现日期是今日或者今日之前添加佣金记录
                                            $deliverid[]=$vv['id'];
                                            $comloginfo[] = array (
                                                'content' => '入职返现',
                                                'rel_name' => $vv['rel_name'],
                                                'uid' => $v['uid'],
                                                'commission' => '+' .$rewayinfo[1][2],//入职返现
                                                'company_name' => $vv['company_name'],//公司名称
                                                'job_time' => date('Y-m-d',$vv['entry_time']),//入职时间
                                                'resign_time' => $vv['resign_time']>0?date('Y-m-d',$vv['resign_time']):'',//离职时间
                                                'id_number' => $vv['id_number'],//身份证号
                                                'create_time' => time (),//
                                                'type' => '3',//属于系统生成的数据
                                                'root'=>$_SESSION['username'],
                                                'status'=>0 //未审核
                                            );
                                        }
                                    }
                                }
                            }
                            unset($arr);
                            unset($welday);
                            unset($rewayinfo);
                            unset($welday);
                            unset($pretime);
                        }

                    }
                }
            }
//            echo '<pre/>';
//            var_dump($comloginfo);
//            die();
            $result1 = M('commission_into')->insert($comloginfo);
            if(count($deliverid)>0){//更改投递表中佣金生成的状态为已生成
                $deidstr = implode(',',$deliverid);
                $result = M('deliver')->exe("update hp_deliver set send_status=1 where id in(".$deidstr.")");
            }
            if($result1 && $result){
                $this->success('操作成功');
            }
        }
        $cond = array();
        if(isset($_GET['id_number'])){
            $_GET['username'] = urldecode($_GET['id_number']);

            $cond[]='id_number like "%'.$_GET['id_number'].'%"';
        }

        if(isset($_GET['commission'])){
            $_GET['commission'] = urldecode($_GET['commission']);

            $cond[]='commission like "%'.$_GET['commission'].'%"';
        }
        if(isset($_GET['company_name'])){
            $_GET['company_name'] = urldecode($_GET['company_name']);

            $cond[]='company_name like "%'.$_GET['company_name'].'%"';
        }
        if(!empty($_GET['start_time']) && isset($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = ' create_time > ' . $start_time . ' AND create_time < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'create_time >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'create_time <' . $end_time;
        }
        $count = M('commission_into')->where($cond)->count();
        $page = new page($count,20);
        $commissions = M('commission_into')->order('id desc')->where($cond)->findall($page->limit());
        $this->assign('commissions',$commissions);
        $this->assign('page',$page->show());
        $this->display();
    }

    public function delInto()
    {
        $db = M ('commission_into');
        if($db->in($_POST['id'])->del()){
            Json_success('删除成功');
        }else{
            Json_error('删除失败');
        }
//        foreach ($_POST['id'] as $key => $val) {
//            $db->in (array('id' => $val))->del();
//        }
//        $this->display();
    }


    /**
     * @Title: insertSalary
     * @Description: todo(导入工资表)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function insertSalary(){
        $upload = new upload(PATH_ROOT . '/uploads/excel', array('xls', 'xlsx'));
        $info = $upload->upload();
        if ($info) {
            $file = $info[0]['path'];
        }
        $data = parseExcel($file);
        unset($data[1]);
        $db=M('staff_salary');
        $errors = array();
        foreach ($data as $k=>$v){

            $arr=array(
                'time'=>time(),
                'name'=>$v[1],
                'id_number'=>$v[2],
                'job_number'=>$v[3],
                'company_name'=>$v[4],
                'salary_month'=>$v[5],
                'basic_salary'=>$v[6],
                'should_salary'=>$v[7],
                'withhold_count'=>$v[8],
                'really_salary'=>$v[9],
                'seniority'=>$v[10],
                'attendance_bonus'=>$v[11],
                'subsidy_count'=>$v[12],
                'subsidy_transportation'=>$v[13],
                'subsidy_house'=>$v[14],
                'overtime_count'=>$v[15],
                'overtime_cost'=>$v[16],
                'prize'=>$v[17],
                'qr_prize'=>$v[18],
                'end_prize'=>$v[19],
                'last_month'=>$v[20],
                'file_cost1'=>$v[21],
                'absence_deductions'=>$v[22],
                'other_deductions'=>$v[23],
                'outsource_cost'=>$v[24],
                'jinpo'=>$v[25],
                'cpf'=>$v[26],
                'house_cost'=>$v[27],
                'tax'=>$v[28],
                'checkoff'=>$v[29],
                'file_cost2'=>$v[30],
                'open_card'=>$v[31],
            );
            $re=$db->insert($arr);

        }
        if($re){
                $this->success('操作成功');
        }else{
            $errors[] = array(
                'err_col'=>$k,
                'err_recruit'=>$v[1],
            );
            var_dump($errors);
        }
    }

    /**
     * @Title: insertSalary_20170220
     * @Description: todo(薪资导入-最新)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function insertSalary_20170220(){

        $upload = new upload(PATH_ROOT . '/uploads/excel', array('xls', 'xlsx'));

        $info = $upload->upload();

        if ($info) {

            $file = $info[0]['path'];
        }

        $data = parseExcel($file);

        $db=M('staff_salary');

        $errors = array();

        $id_numbers = [];

        foreach ($data as $k=>$v){

            if($k!=1){

                $extra_data = [];

                foreach ($v as $j=>$item){

                    if($j > 8){

                        $extra_data[] = [
                            'key'=>$data[1][$j],
                            'value'=>$item
                        ];
                    }
                }

                $id_numbers[] = $v[2];

                $arr=array(
                    'time'=>time(),
                    'name'=>$v[1],
                    'id_number'=>$v[2],
                    'job_number'=>$v[3],
                    'company_name'=>$v[4],
                    'salary_month'=>$v[5],
                    'should_salary'=>$v[6],
                    'withhold_count'=>$v[7],
                    'really_salary'=>$v[8],
                    'extra_data'=>json_encode($extra_data)
                );

                $re=$db->insert($arr);

            }

        }

        $IDS = ['id_numbers'=>array_filter($id_numbers)];

        if($re){

            $client = new GearmanClient();
            $client->addServer();
            $client->setCompleteCallback(function(GearmanTask $task){

            });
            $client->addTaskBackground('sendMessage',json_encode($IDS));
            $client->runTasks();

            $this->success('操作成功');

        }else{

            $errors[] = array(
                'err_col'=>$k,
                'err_recruit'=>$v[1],
            );

            var_dump($errors);
        }
    }


    /**
     * @Title: salary
     * @Description: todo(工资)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function salary(){
        $_GET = urldecode_array($_GET);
        $cond = array();
        if(isset($_GET['name'])){
            $_GET['name'] = urldecode($_GET['name']);

            $cond[]='name like "%'.$_GET['name'].'%"';
        }

        if(isset($_GET['company_name'])){
            $_GET['company_name'] = urldecode($_GET['company_name']);

            $cond[]='company_name like "%'.$_GET['company_name'].'%"';
        }

        if(isset($_GET['id_number'])){

            $cond[]='id_number like "%'.$_GET['id_number'].'%"';
        }
        if(isset($_GET['job_number'])){

            $cond[]='job_number like "%'.$_GET['job_number'].'%"';
        }

        if(isset($_GET['salary_month'])){
            $cond[]='salary_month like "%'.$_GET['salary_month'].'%"';
        }
        if(!empty($_GET['start_time']) && isset($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'time > ' . $start_time . ' AND time < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'time >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'time <' . $end_time;
        }
        $count=M('staff_salary')->where($cond)->count();

        $page=new page($count,20);

        $salary = M('staff_salary')->order('id desc')->where($cond)->findall($page->limit());

        $this->assign('salary',$salary);

        $this->assign('page',$page->show());

        $this->display();
    }

    /**
     * @Title: more_salary
     * @Description: todo(薪资查看更多)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function more_salary(){

        $id = $_GET['id'];

        $salary = M('staff_salary')->where(['id'=>$id])->find();

        $salary['extra_data'] = json_decode($salary['extra_data'],true);

        $this->assign('salary',$salary);

        $this->display();
    }

    /**
     * @Title: wait_verify
     * @Description: todo(待审核的用户)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function wait_verify(){
        $_GET = urldecode_array($_GET);
        $cond = array();

        if(isset($_GET['id_number'])){

            $cond[]='id_number like "%'.$_GET['id_number'].'%"';
        }
        if(!empty($_GET['start_time']) && isset($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'create_time > ' . $start_time . ' AND create_time < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'create_time > ' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'create_time < ' . $end_time;
        }
        $cond['verify']=1;
        $count=M('user_info')->where($cond)->count();

        $page=new page($count,20);

        $user = M('user_info')->where($cond)->findall($page->limit());
        foreach($user as $key=>$val){
            $username= M('user')->where(array('uid'=>$val['uid']))->find();


            $user[$key]['username'] = $username['username'];
//            $users['user'][$key]['title'] = $val['hp_role'][$key]['title'];

        }

        $this->assign('user',$user);

        $this->assign('pages',$page->show());

        $this->display();
    }

    /**
     * @Title: delete_salary
     * @Description: todo(删除工资记录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function delete_salary()
    {
        $db = M('staff_salary');
        if($db->in($_POST)->del()){
            echo 1;
            exit;
        }
    }


    /**
     * @Title: salesman
     * @Description: todo(业务员签到列表)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function salesman(){
        $_GET = urldecode_array($_GET);
        $cond=array();
        if(isset($_GET['username'])){
            $_GET['username'] = urldecode($_GET['username']);

            $cond[]='username like "%'.$_GET['username'].'%"';
        }

        if(isset($_GET['name'])){
            $_GET['name'] = urldecode($_GET['name']);

            $user = M('user_info')->where('name like "%'.$_GET['name'].'%"')->find();

            $uid = $user['uid'];

            $cond[]='uid = '.$uid;
        }

        if(isset($_GET['content'])){
            $_GET['content'] = urldecode($_GET['content']);

            $cond[]='content like "%'.$_GET['content'].'%"';
        }
        if(!empty($_GET['start_time']) && isset($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = ' time > ' . $start_time . ' AND time < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'time >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'time <' . $end_time;
        }
//        $cond['point'] = 0;
        $cond['type'] = 6;
        $count = M('opt_log')->where($cond)->count();
        $page = new page($count,20);
        $salas = M('opt_log')->order('time desc')->where($cond)->findall($page->limit());
        foreach($salas as $key=>$val){
            $name = M('user_info')->where(array('uid'=>$val['uid']))->find();
            $salas[$key]['name'] = $name['name'];
            if(empty($name['name'])){
                $salas[$key]['name'] = '无';
            }
        }
        $this->assign('salas',$salas);
        $this->assign('pages',$page->show());
        $this->display();
    }




    /**
     * @Title: search
     * @Description: todo(工资查询搜索)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function search(){

        $result=M('staff_salary')->where('id_number = '.$_POST['id_number'])->findall();
        $this->assign('result',$result);
        $this->display();
    }

    /**
     * @Title: searchCommission
     * @Description: todo(佣金查询)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function searchCommission(){
        $where = ' content like "%'.$_GET['name'].'%"';

        $re = M('commission_log')->where($where)->findall();

        $this->assign('re',$re);

        $this->display('re');
    }

    /**
     * @Title: commission_withdra
     * @Description: todo(提现记录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function commission_withdra(){
        $_GET = urldecode_array($_GET);
        $db = M('commission_withdrawal');
        $cond = array();
        if(isset($_GET['phone'])){
            $_GET['phone'] = urldecode($_GET['phone']);

            $cond[] = 'phone like "%'.$_GET['phone'].'%"';
        }

        if(isset($_GET['account_name'])){
            $_GET['account_name'] = urldecode($_GET['account_name']);

            $cond[] = 'account_name like "%'.$_GET['account_name'].'%"';
        }
        if(!empty($_GET['start_time']) && isset($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = ' create_time > ' . $start_time . ' AND create_time < ' . $end_time;
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'create_time >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'create_time <' . $end_time;
        }
        if(isset($_GET['status'])){
            $cond['status']=$_GET['status'];
        }
        $count = $db->where($cond)->count();

        $page = new page($count,10);

        $result = $db->order('create_time desc')->where($cond)->findall($page->limit());
        foreach($result as $key=>$val){
            $name = M('user_info')->where(array('uid'=>$val['uid']))->find();
            $result[$key]['name'] = $name['name'];
            $result[$key]['gender'] = $name['gender'];
            $result[$key]['id_number'] = $name['id_number'];
            if(empty($name['name'])){
                $result[$key]['name'] = '无';
            }
            if(empty($name['id_number'])){
                $result[$key]['id_number'] = '无';
            }
        }

        $this->assign('result',$result);

        $this->assign('pages',$page->show());

        $this->display();

    }


    public function shareVerify(){
        $arr=array();
        $tab = M('user_message');
        $commission = M('user')->where(array('uid'=>$_GET['uid']))->find();
        $client_id = $commission['client_id'];
        $info = M('commission_log')->where(array('id'=>$_GET['id']))->find();
        if($_GET['name']=='3'){
            $arr['verify'] = 1;
            $arr['root_time'] = time();
            $arr['root'] = $_SESSION['username'];
            M('commission_log')->where(array('id'=>$_GET['id']))->update($arr);
            if($info['type'] == 3){
                $share_commission = getPointRule('shareCommission');
                M('user')->inc('commission','uid = '.$_GET['uid'],$share_commission);
                $tab->insert(array('uid'=>$_GET['uid'],'content'=>'您于'.date('Y-m-d H:i:s',$info['create_time']).'参与的分享邀请佣金已经通过审核，5元佣金已经到账，如有疑问请联系客服4006920099！','title'=>'系统消息','created'=>time()));
                $hidden = array(
                    'type'=>1,
                    'data_type'=>101,
                    'title'=>'系统消息',
                    'content'=>'您于'.date('Y-m-d H:i:s',$info['create_time']).'参与的分享邀请佣金已经通过审核，5元佣金已经到账，如有疑问请联系客服4006920099！'
                );
                push(array($client_id),array('hidden'=>$hidden,'title'=>'title','content'=>'content'));
            }
            $this->success('操作成功');
        }
        if($_GET['name']== 2 ){
            $arr['verify']=2;
            $arr['root_time'] = time();
            $arr['root'] = $_SESSION['username'];
            M('commission_log')->where(array('id'=>$_GET['id']))->update($arr);
            $tab->insert(array('uid'=>$_GET['uid'],'content'=>'您于'.date('Y-m-d H:i:s',$info['create_time']).'参与的分享邀请佣金未通过审核，如有疑问请联系客服4006920099！','title'=>'系统消息','created'=>time()));
            $hidden = array(
                'type'=>1,
                'data_type'=>101,
                'title'=>'系统消息',
                'content'=>'您于'.date('Y-m-d H:i:s',$info['create_time']).'参与的分享邀请佣金未通过审核，如有疑问请联系客服4006920099！'
            );
            push(array($client_id),array('hidden'=>$hidden,'title'=>'title','content'=>'content'));
            $this->success('操作成功');
        }



    }

    /**
     * 生成的数据入职返现审核
     *
     */
    public function entryVerify(){
        $arr=array();
        $tab = M('user_message');
        $commission = M('user')->where(array('uid'=>$_GET['uid']))->find();
        $client_id = $commission['client_id'];
        $info = M('commission_into')->where(array('id'=>$_GET['id']))->find();
        $from_user = M('user')->where('uid = ' . $commission['from_id'])->find();//邀请人信息

        if($_GET['name']=='3' && $info['type'] == 3 && (int)$_GET['mon']>0 && $commission['from_id']>0){ //审核通过 入职返现状态改变 用户表佣金数值变化 如果是第一次得到入职返现邀请人得到10%入职返现
            //查询是否给经纪人返过入职返现

            $ifcunzai =  M('user')->query("select * from hp_commission_log as a inner join hp_user as b on a.from_id=b.uid where a.uid=b.from_id and a.type=2 and b.uid=".$_GET['uid']);

            if(empty($ifcunzai)){ //如果不存在给经纪人入职返现的记录
                $data = array(
                    'uid'=>$commission['from_id'],
                    'content'=>'入职返现',
                    'commission'=>'+'.intval($_GET['mon'])*0.1,
                    'username'=>$from_user['username'],
                    'create_time'=>time(),
                    'from_id'=>$_GET['uid'],
                    'type'=>'2'
                );
                M('commission_log')->insert($data);
            }
            //用户佣金金额变化
            $usercom['commission']=$commission['commission']+intval($_GET['mon']);
            M('user')->where(array('uid'=>$_GET['uid']))->update($usercom);

            //更改佣金审核状态
            $arr['status'] = 1;
            $arr['reviewtime'] = time();
            $arr['reviewer'] = $_SESSION['username'];
            M('commission_into')->where(array('id'=>$_GET['id']))->update($arr);

            $this->success('操作成功');
        }
        if($_GET['name']== 2 ){//审核不通过
            $arr['status'] = 2;
            $arr['reviewtime'] = time();
            $arr['reviewer'] = $_SESSION['username'];
            M('commission_into')->where(array('id'=>$_GET['id']))->update($arr);
            $this->success('操作成功');
        }
    }

    /**
     * @Title: updateWithdrawal
     * @Description: todo(提现审核)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function updateWithdrawal(){
        $db = M('commission_withdrawal');
        $arr=array();
        if($_GET['name']=='3'){
            $arr['status']=3;
            $arr['root_time'] = time();
            $arr['root'] = $_SESSION['username'];
        }
        if($_GET['name']=='2'){
            $arr['status']=2;
            $arr['root_time'] = time();
            $arr['root'] = $_SESSION['username'];
        }
        if($db->in(array('cwid'=>$_GET['cwid']))->update($arr)){
            $uid = $db->where(array('cwid'=>$_GET['cwid']))->find();
                if($_GET['name']=='3'){
                    $commission = M('user')->where(array('uid'=>$uid['uid']))->find();
                    $client_id = $commission['client_id'];

                    addNewMessages($uid['uid'],'您于'.date('Y-m-d',$uid['create_time']).'提现的'.$uid['amount'].'佣金已通过审核，请您近期关注您提现银行卡的入账情况，如有疑问请联系客服4006920099！','提现消息',4,401);

                    $hidden = array(
                        'type'=>4,
                        'data_type'=>401,
                        'title'=>'提现消息',
                        'content'=>'您于'.date('Y-m-d',$uid['create_time']).'提现的'.$uid['amount'].'佣金已到帐。'
                    );

                    push(array($client_id),array('hidden'=>$hidden,'title'=>'title','content'=>'content'));
                }
            if($_GET['name']=='2'){
                $commission = M('user')->where(array('uid'=>$uid['uid']))->find();
                $add = $commission['commission'] + $uid['amount'];
                M('user')->where(array('uid'=>$uid['uid']))->update(array('commission' => $add));
                $data = array(
                    'uid'=>$uid['uid'],
                    'content'=>'提现未通过',
                    'commission'=>'+'.$uid['amount'],
                    'username'=>$commission['username'],
                    'create_time'=>time(),
                    'type'=>'1'
                );
                M('commission_log')->insert($data);
                addNewMessages($uid['uid'],'您于'.date('Y-m-d',$uid['create_time']).'提现的'.$uid['amount'].'佣金审核未通过,佣金已回到你的账户,如有疑问请联系客服4006920099！','提现消息',4,401);
                $commission = M('user')->where(array('uid'=>$uid['uid']))->find();
                $client_id = $commission['client_id'];
                $hidden = array(
                    'type'=>4,
                    'data_type'=>401,
                    'title'=>'提现消息',
                    'content'=>'您于'.date('Y-m-d',$uid['create_time']).'提现的'.$uid['amount'].'佣金审核未通过,佣金已回到你的账户,如有疑问请联系客服4006920099。'
                );

                push(array($client_id),array('hidden'=>$hidden,'title'=>'提现消息','content'=>$hidden['content']));
            }

            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * @Title: updateWithdrawalCheck
     * @Description: todo(提现记录（全选）)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function updateWithdrawalCheck(){
        $db = M('commission_withdrawal');
        $message = M('user_message');
        $arr=array();
        if($_POST['type']=='3'){
            $arr['status']=3;
            $arr['root_time'] = time();
            $arr['root'] = $_SESSION['username'];
        }
        if($_POST['type']=='2'){
            $arr['status']=2;
            $arr['root_time'] = time();
            $arr['root'] = $_SESSION['username'];
        }
            $user = $db->in(array('cwid'=>$_POST['id']))->findall();
            $update = $db->in(array('cwid'=>$_POST['id']))->update($arr);
            $value = '';
                foreach($user as $k=>$v){//审核通过
                    if($_POST['type']=='3'){
                        $content = '你于'.date('Y-m-d',$v['create_time']).'提现的'.$v['amount'].'佣金已通过审核，请您近期关注您提现银行卡的入账情况，如有疑问请联系客服4006920099';
                        $title = '账号消息';
                }elseif($_POST['type']=='2'){//审核未通过
                        $commission = M('user')->where(array('uid'=>$v['uid']))->find();
                            M('user')->inc('commission','uid = '.$v['uid'],$v['amount']);
                            $data = array(
                                'uid'=>$v['uid'],
                                'content'=>'提现未通过',
                                'commission'=>'+'.$v['amount'],
                                'username'=>$commission['username'],
                                'create_time'=>time(),
                                'type'=>'1'
                            );
                            M('commission_log')->insert($data);
                        $content ='你于'.date('Y-m-d',$v['create_time']).'提现的'.$v['amount'].'佣金审核未通过,佣金已回到你的账户';
                        $title = '账号消息';
                    }
                    $value .= ',' . '(' . $v['uid'] . ',"' . $content . '",' . time () . ',"' . $title . '" )';
            }
        $values = ltrim($value,',');
        $sql = "INSERT INTO hp_user_message (uid,content,created,title) VALUES $values;";
        if(M('user_message')->exe($sql)){
            foreach ($user as $key=>$value){
                $uid = $value['uid'];
                $users = M('user')->in(array('uid'=>$uid))->findall();
                foreach($users as $k=>$val){
                    $client_id[] = $val['client_id'];
                }
            }
            $hidden = array(
                'type'=>4,
                'data_type'=>401,
                'title'=>'账号消息',
                'content'=>$content
            );
            push($client_id,array('hidden'=>$hidden,'title'=>'账号消息','content'=>$content));
        }




        if($update){
            Json_success('操作成功');
        }else{
            Json_error('操作失败');
        }
    }

    /**
     * @Title: shareVerifyCheck
     * @Description: todo(邀请返现批量审核)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function shareVerifyCheck(){
        $db = M('commission_log');

        $title = '佣金消息';
        $arr=array();
        if($_POST['type']==1){  //审核通过
            $arr['verify']=1;
            $arr['root_time'] = time();
            $arr['root'] = $_SESSION['username'];
        }
        if($_POST['type']== 2){  //审核不通过
            $arr['verify']=2;
            $arr['root_time'] = time();
            $arr['root'] = $_SESSION['username'];
        }

        $user = $db->where('verify = 0 AND id IN('.implode(',',$_POST['id']).')')->findall();

        if(empty($user)){
            Json_error('操作失败');
        }

        if($db->where('verify = 0 AND id IN('.implode(',',$_POST['id']).')')->update($arr)){
            foreach($user as $k=>$v){

                if($v['type'] == 3 && $_POST['type'] == 1) {//判断是否是邀请好友
                    $share_commission = getPointRule('shareCommission');
                    M ('user')->inc ('commission', 'uid = ' . $v['uid'], $share_commission);
                }
                if($_POST['type']==2){
                    $content = '您于'.date('Y-m-d',$v['create_time']).'参与的分享邀请佣金未通过审核，如有疑问请联系客服4006920099！';
                }else{
                    $content = '您于'.date('Y-m-d',$v['create_time']).'参与的分享邀请佣金已经通过审核，5元佣金已经到账，如有疑问请联系客服4006920099！';
                }
                addNewMessages($v['uid'],$content,$title,4,401);
            }

            foreach ($user as $key=>$value){
                $uid = $value['uid'];
                $users = M('user')->in(array('uid'=>$uid))->findall();
                foreach($users as $k=>$val){
                    $client_id[] = $val['client_id'];
                }
            }
            $hidden = array(
                'type'=>4,
                'data_type'=>401,
                'title'=>'佣金消息',
                'content'=>$content
            );
            push($client_id,array('hidden'=>$hidden,'title'=>'账号消息','content'=>$content));
            Json_success('操作成功');
        }

    }

/*
 * @param year 年度,quarter 季度
 * 第一季度 1-3 第二季度4-6 第三季度7-9 第四季度 10-12
 */
    public function quarterCreate(){
        if(intval($_POST['year'])>0 && intval($_POST['quarter'])){
            $year = intval($_POST['year']);//年度
            $quarter = intval($_POST['quarter']);//季度

            if($quarter==1){
                $start =  strtotime(date($year.'-m-01',mktime(0,0,0,($quarter - 1) *3 +1,1,date($year))));//季度开始时间
                $end =   strtotime(date($year.'-m-t 23:59:59',mktime(0,0,0,$quarter * 3,1,date($year))));//季度结束时间
            }
          //查询季度时间内所有已经通过审核的
            //查询所有
           die();
        }else{
            Json_success('数据格式不对');
        }
        Json_success('操作成功',$_POST);
    }
    /**
     * @Title: updateAuditCheck
     * @Description: todo(待审核（全选）)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function updateAuditCheck(){
        $db = M('user_info');
        $data=array();
        $title = '身份审核';
        if($_POST['type']=='3'){
            foreach($_POST['id'] as $key=>$val){
                addNewMessages($val,$content='您的身份认证已经通过审核，我的佣金/工资查询已经可以查询，开开心心找工作，享受贴心服务！',$title='系统消息',4,401);
                $user = M('user')->where('uid='.$val)->find();
                $client_id[] = $user['client_id'];
            }
            $hidden = array(
                'type'=>4,
                'data_type'=>401,
                'title'=>$title,
                'content'=>'您的身份认证已经通过审核，我的佣金/工资查询已经可以查询，开开心心找工作，享受贴心服务！'
            );
            push($client_id,array('hidden'=>$hidden,'title'=>$title,'content'=>$hidden['content']));
            $data['verify']=3;
        }
        if($_POST['type']=='2'){
            foreach($_POST['id'] as $key=>$val){
                addNewMessages($val,$content='您的身份认证未通过审核，请手持身份证使用前置摄像头拍摄，确保五官、身份证上所有信息清晰可见，安卓用户请关闭自拍镜像，请再次进入我的佣金/工资查询进行身份认证,如有疑问，请联系客服 4006920099。',$title='系统消息',4,401);
                $user = M('user')->where('uid='.$val)->find();
                $client_id[] = $user['client_id'];
            }
            $data['verify']=2;
            $data['id_number'] = '';
            $data['name'] = '';
            $hidden = array(
                'type'=>4,
                'data_type'=>401,
                'title'=>$title,
                'content'=>'您的身份认证未通过审核，请手持身份证使用前置摄像头拍摄，确保五官、身份证上所有信息清晰可见，安卓用户请关闭自拍镜像，请再次进入我的佣金/工资查询进行身份认证,如有疑问，请联系客服 4006920099。'
            );
            push($client_id,array('hidden'=>$hidden,'title'=>$title,'content'=>$hidden['content']));
        }
        foreach($_POST['id'] as $key=>$val){
            $update = $db->in(array('uid'=>$val))->update($data);
            $this->yaointoComlog($val,3);
        }

        if($update){
            Json_success('操作成功');
        }else{
            Json_error('操作失败');
        }
    }
    /*
    * @param $uid $type
    *
    */
    public function yaointoComlog($uid,$type){
        $db = M('user_info');
        //获取用户邀请人id,角色id,title
        $sql = "select a.uid,a.username, a.normalmanid,b.rid,c.rname,c.title from hp_user as a left join hp_user_role as b on a.normalmanid=b.uid left join hp_role as c on b.rid=c.rid
                where a.uid=" . $uid . " and c.state=1 and c.title='求职者'";
        $db5 = M('commission_log');
        $info = $db->query($sql);
        $uname =  $db->query("SELECT username FROM  hp_user where uid=".intval($info[0]['normalmanid']));
        if (!empty($info)) {//邀请人角色是有效求职者
            $res = M('resume')->where(array('uid' => $uid,'Verify '=>1))->find();
            $invite_commission = getPointRule('inviteCommission');
            if (!empty($res) && $type == '3') {//认证通过且已经创建简历已通过
                //返邀请人佣金后台设置
                $mess = [];
                $mess['uid'] = intval($info[0]['normalmanid']);//邀请人
                $mess['content'] = '邀请返现';
                $mess['commission'] = '+'.$invite_commission;
                $mess['username'] =$uname[0]['username'];//邀请人姓名，可用于手机号
                $mess['create_time'] = time();
                $mess['type'] = 3;
                $mess['from_id'] =  $uid;//被邀请人
                $mess['verify'] = 0;//未审核
                $res2 = $db5->insert($mess);
                if($res2){
                    return $res2;
                }else{
                    return 0;
                }
            }
        }
    }

    /**
     * @Title: reportLists
     * @Description: todo(举报列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function reportLists(){
        $db = M('sns_report');
        $count = $db->count();
        $page = new page($count,20);
        $reports = $db->order('status asc,time desc')->findall($page->limit());
        foreach ($reports as $key=>$value){
            $sns = M('sns')->where('sid=' . $value['sid'])->find();
            $reports[$key]['imgs'] = $sns['imgs'];
        }
        $this->assign('reports',$reports);
        $this->assign('pages',$page->show());
        $this->display();
    }

    /**
     * @Title: snsLists
     * @Description: todo(动态列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function snsLists(){
        $_GET = urldecode_array($_GET);
        $cond[] = 'del_state = 0';
        if(!empty($_GET['content'])){
            $cond[] = 'content like "%'.ltrim($_GET['content']).'%"';
        }
        $db = M('sns');
        $count = $db->where($cond)->count();
        $page = new page($count,20);
        $sns = $db->where($cond)->order('is_top desc,update_time desc')->findall($page->limit());
        $this->assign('sns',$sns);
        $this->assign('pages',$page->show());
        $this->display();
    }

    /**
     * @Title: delSns
     * @Description: todo(删除被举报)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delSns(){
        $id = $_GET['id'];
        $db = M('sns_report');
        $sid = $db->where('rid=' . $id)->field('sid')->find();
        if(empty($sid)){
            $this->error('该举报下的动态不存在了');
        }
        $deliver = $db->where(array('rid'=>$id))->find();
        if($deliver['status']==0){
            if(M('new_sns')->where(array('sid'=>$sid))->update(['del_state'=>1])){
                $db->where(array('rid'=>$id))->update(array('status'=>1));
                $this->success('操作成功');
            }else{
                $this->error('系统错误');
            }
        }else{
            $this->error('已被删除');
        }
    }

    /**
     * @Title: delListSns
     * @Description: todo(删除动态)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delListSns(){
        $id = $_GET['id'];
        $db = M('sns');
        if($db->where(array('sid'=>$id))->update(['del_state'=>1])){
            $this->success('删除成功');
        }else{
            $this->error('系统错误');
        }
    }

    /**
     * @Title: topListSns
     * @Description: todo(置顶动态)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function topListSns(){
        $id = $_GET['id'];
        $db = M('sns');
        if($db->where(array('sid'=>$id))->update(array('is_top'=>1,'update_time'=>time()))){
            $this->success('置顶成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * @Title: chanelTopListSns
     * @Description: todo(取消置顶动态)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function chanelTopListSns(){
        $id = $_GET['id'];
        $db = M('sns');
        if($db->where(array('sid'=>$id))->update(array('is_top'=>0,'update_time'=>time()))){
            $this->success('取消成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * @Title: commentLists
     * @Description: todo(动态评论列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function commentLists(){

        $sns_id = $_GET['sns_id'];

        $db = M('sns_comment');

        $where = 'sns_id = '.$sns_id;

        $count = $db->where($where)->count();

        if($count>0){

            $page = new page($count,20);
            $comment = $db->where($where)->order('created desc')->findall($page->limit());
            $this->assign('comment',$comment);
            $this->assign('pages',$page->show());
            $this->display();
        }else{

            $this->success('该帖子暂时没有评论');
        }
    }

    /**
     * @Title: delComment
     * @Description: todo(删除评论)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delComment(){
        $cid = $_GET['cid'];
        $db = M('sns_comment');
        if($db->where(array('cid'=>$cid))->del()){
            $this->success('删除成功');
        }else{
            $this->error('系统错误');
        }
    }

    /**
     * @Title: sendMessage
     * @Description: todo(管理员发送消息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function sendMessage(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            ini_set("memory_limit","-1");
            $content = $_POST['content'];
            $type = $_POST['type'];
            $title = '系统消息';

            if(empty($_POST['path'][1][0])){
                $picture = '';
            }else{
                $picture = __ROOT__.'/'.$_POST['path'][1][0];
            }

            $where = '';

            if($type==1){
                $roles = M('user_role')->where('rid=8')->findall();

                foreach($roles as $key=>$role){
                    $uids_arr[] = $role['uid'];
                }
                $uids = implode(',',$uids_arr);

                $where = 'uid in('.$uids.')';
            }

            if($type==2){
                $roles = M('user_role')->where('rid=7')->findall();

                foreach($roles as $key=>$role){
                    $uids_arr[] = $role['uid'];
                }
                $uids = implode(',',$uids_arr);

                $where = 'uid in('.$uids.')';
            }
            $user = M('user')->where($where)->findall();

            $user_group = array_chunk($user,1000);

            for ($i=0; $i<=count($user_group)-1; $i++) {

                $values = '';
                foreach ($user_group[$i] as $k=>$val){

                    $values .= ',' . '('.$val['uid']. ',"' .$content. '",' .time(). ',"' .$title. '",' .$type.  ',"' .$picture.')';

                }

                $values = ltrim($values,',');

                $sql = "INSERT INTO hp_user_message (uid,content,created,title,type,image) VALUES $values;";

                M('user_message')->exe($sql);
            }

            foreach ($user as $key=>$value){

                $client_id[] = $value['client_id'];
            }

            $client_id = array_unique(array_filter($client_id));

            $hidden = array(
                'type'=>1,
                'data_type'=>101,
                'title'=>$title,
                'content'=>$content
            );

            $client_id_group = array_chunk($client_id,100);

            for ($i=0; $i<=count($client_id_group)-1; $i++) {

                push($client_id_group[$i], array('hidden' => $hidden, 'title' => $title, 'content' => $content));
            }

            $this->success('发送成功');

        }
        $this->display();
    }

    public function send_message(){
        $uids = $_POST['uids'];
        $content = $_POST['content'];
        $type = 1;
        $title = '系统消息';
        $user = M('user')->where('uid in ('.$uids.')')->findall();
        if(empty($user)){
            $this->error('发送失败');
        }

        foreach ($user as $key=>$value){

            $data = [
                'title'=>$title,
                'uid'=>$value['uid'],
                'unickname'=>$value['nickanme'],
                'uavatar'=>$value['avatar'],
                'type'=>$type,
                'data_type'=>105,
                'content'=>$content,
                'create_at'=>time(),
            ];

            $insert = M('new_message')->insert($data);

            if($insert){
                M('new_message_user')->insert(['mid'=>$insert,'buid'=>$value['uid'],'create_at'=>time()]);
            }

            $hidden = array(
                'type'=>1,
                'title'=>$title,
                'data_type'=>105,
                'content'=>$content
            );

            push([$value['client_id']],array('hidden'=>$hidden,'title'=>$title,'content'=>$content));

        }

        $this->success('发送成功了');

    }

    /**
     * @Title: sendSysMessage
     * @Description:todo(发送系统消息)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function sendSysMessage(){
        if($_SERVER['REQUEST_METHOD']=='GET') {
            $this->display();
        }else if($_SERVER['REQUEST_METHOD']=='POST'){
            $title = $_POST['title'];
            $content = $_POST['content'];
            $url = $_POST['link_url'];
            if(empty($_POST['path'][1][0])){
                $data_avatar = '';
            }else{
                $data_avatar = __ROOT__.'/'.$_POST['path'][1][0];
            }
            $html = $_POST['editorValue'];
            $uid = $_SESSION['uid'];
            $flag = $_POST['type'];
            $data_type = $_POST['massage_type'];
            if($uid == 1){
                $state = K('message')->createMission($uid,[
                    'title'=> $title,
                    'content'=>$content,
                    'data_avatar'=>$data_avatar,
                    'html'=>$html,
                    'link_url'=> $url,
                    'data_type'=>$data_type
                ],$flag,"你有一条新的系统消息!!");
                if($state)$this->success('发送成功');
                else{
                    $this->error('发送失败');
                }
            }
        }
    }

    /**
     * @Title: uploadImg
     * @Description:todo(富文本后台)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function ueditorBack(){
        //header("Content-Type: text/html; charset=utf-8");
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(__DIR__.DIRECTORY_SEPARATOR."config.json")), true);
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = $this->action_upload($CONFIG);
                break;

            /* 列出图片 */
            case 'listimage':
                $result = $this->action_list($CONFIG);
                break;
            /* 列出文件 */
            case 'listfile':
                $result = $this->action_list($CONFIG);
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = $this->action_crawler($CONFIG);
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

    /**
     * @Title: sendMessageByExcel
     * @Description: todo(excel发消息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function sendMessageByExcel(){

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $upload = new upload(PATH_ROOT . '/uploads/excel', array('xls', 'xlsx'));
            $info = $upload->upload();
            if ($info) {
                $file = $info[0]['path'];
            }
            $data = parseExcel($file);
            $content = $_POST['content'];
            $title = $_POST['name'];
            if(empty($_POST['path'][1][0])){
                $data_avatar = '';
            }else{
                $data_avatar = __ROOT__.'/'.$_POST['path'][1][0];
            }
            $mobiles = array_filter(array_column($data,1));
            unset($mobiles[0]);
            $uid = $_SESSION['uid'];
            $state = K('message')->sendEnrollMessage($uid,[
                'data_avatar' =>  $data_avatar,
                'title' => $title,
                'content'=>$content,
                'data_type'=>201,
                'type' => 2,
                'create_at' =>time()
            ],$mobiles);
            //var_dump($mobiles);
            if($state)$this->success();
            else{
                $this->error();
            }
        }else {
            $this->display();
        }
    }

    /**
     * @Title: action_upload
     * @Description:todo(富文本上传图片方法)
     * @Author: Kaiqi
     * @param $CONFIG
     * @return string 返回类型
     */
    private function action_upload($CONFIG){
        $base64 = "/uploads/h5";
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize" => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles']
                );
                $fieldName = $CONFIG['imageFieldName'];
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $CONFIG['scrawlPathFormat'],
                    "maxSize" => $CONFIG['scrawlMaxSize'],
                    "allowFiles" => $CONFIG['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $CONFIG['scrawlFieldName'];
                $base64 = "base64";
                break;
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $CONFIG['videoPathFormat'],
                    "maxSize" => $CONFIG['videoMaxSize'],
                    "allowFiles" => $CONFIG['videoAllowFiles']
                );
                $fieldName = $CONFIG['videoFieldName'];
                break;
            case 'uploadfile':
            default:
                $config = array(
                    "pathFormat" => $CONFIG['filePathFormat'],
                    "maxSize" => $CONFIG['fileMaxSize'],
                    "allowFiles" => $CONFIG['fileAllowFiles']
                );
                $fieldName = $CONFIG['fileFieldName'];
                break;
        }
        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($fieldName, $config, $base64);

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */

        /* 返回数据 */
        return json_encode($up->getFileInfo());
    }

    private function action_list($CONFIG){
        /* 判断类型 */
        switch ($_GET['action']) {
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $CONFIG['fileManagerAllowFiles'];
                $listSize = $CONFIG['fileManagerListSize'];
                $path = $CONFIG['fileManagerListPath'];
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $CONFIG['imageManagerAllowFiles'];
                $listSize = $CONFIG['imageManagerListSize'];
                $path = $CONFIG['imageManagerListPath'];
        }
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }
        //倒序
        //for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
        //    $list[] = $files[$i];
        //}

        /* 返回数据 */
        $result = json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));
        return $result;
    }
    private function action_crawler($CONFIG){
        set_time_limit(0);
        $config = array(
            "pathFormat" => $CONFIG['catcherPathFormat'],
            "maxSize" => $CONFIG['catcherMaxSize'],
            "allowFiles" => $CONFIG['catcherAllowFiles'],
            "oriName" => "remote.png"
        );
        $fieldName = $CONFIG['catcherFieldName'];

        /* 抓取远程图片 */
        $list = array();
        if (isset($_POST[$fieldName])) {
            $source = $_POST[$fieldName];
        } else {
            $source = $_GET[$fieldName];
        }
        foreach ($source as $imgUrl) {
            $item = new Uploader($imgUrl, $config, "remote");
            $info = $item->getFileInfo();
            array_push($list, array(
                "state" => $info["state"],
                "url" => $info["url"],
                "size" => $info["size"],
                "title" => htmlspecialchars($info["title"]),
                "original" => htmlspecialchars($info["original"]),
                "source" => htmlspecialchars($imgUrl)
            ));
        }

        /* 返回抓取数据 */
        return json_encode(array(
            'state'=> count($list) ? 'SUCCESS':'ERROR',
            'list'=> $list
        ));
    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    private function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {
                        $files[] = array(
                            'url' => substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime' => filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }
}