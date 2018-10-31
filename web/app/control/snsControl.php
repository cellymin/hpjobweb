<?php

/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/3/1
 * Time: 13:35
 */

class snsControl extends Control
{

    /**
     * @Title: getSns
     * @Description:todo(获取帖子)
     * @Author: Kaiqi
     * @必选 post: uid tag
     * @return void 返回类型
     */
    public function getSns(){
        if(C('square')==0){
            Json_error('广场功能暂时关闭，请等待管理员开启后重试,谢谢配合');
        }
        $model = K('sns');
        $tag = $_POST['tag'];

        $uid = $_SESSION['uid'];
        if(empty($tag)){
            Json_error("请添加tag参数");
        }
        $data = $model->readSns($uid,['tag'=>$tag],[],true,true,'is_top desc,create_at desc');
        if(!empty($data)) {
            Json_success("获取帖子成功", $data);
        }else{
            Json_error("未查到数据");
        }
    }

    /**
     * @Title: writeSns
     * @Description:todo(发帖)
     * @Author: Kaiqi
     * @可选 post:  content iamges
     * @必选 post:  uid tag fcity fname
     * @return void 返回类型
     */
    public function writeSns(){
        if(C('square')==0)Json_error('广场功能暂时关闭，请等待管理员开启后重试,谢谢配合');
        $model = K('sns');
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请先登录");
        $tag = $_POST['tag'];
        if(empty($tag))Json_error("缺少tag参数");
        if(3<$tag||$tag<1)Json_error("tag参数范围错误",$tag);
        $user_role = $_SESSION['role']['rid'][0];
        if($user_role!=7 && $user_role!=18 && $tag == 1){//业务员和运营维护
            Json_error("此板块不可发布");
        }
        $today_meipai = $model->violateTime($uid);
//        if($tag == 2 && $model->violateTime($uid)){
//            Json_error("一天只能发表一次美拍");
//        }
        $fcityname = $_POST['fcityname'];
        if(empty($fcityname))Json_error("缺少fcityname参数");
        $fcity = K('public')->getCityId($fcityname);
        $user = K('user')->getUserInfo($uid);
        $content = $_POST['content'];
        $images = formatImages($_POST['images']);
        $data = [
            'uid' => $uid,
            'tag' =>$tag,
            'unickname' => $user['nickname'],
            'uvatar' => $user['avatar'],
            'ubirthday' => $user['birthday'],
            //'ustate' => $_POST['ustate'],
            'gender' => $user['gender'],
            'city' => $user['address'],
            'content' => $content,
            'images' => $images,
            'fcity' => $fcity,
            'fcityname' => $fcityname,
            'create_at' => time(),
            'hometown_id' => $user['hometown_id']
        ];
        $state = $model->insertSns($data);

        if($state > 0){

            if($tag ==2&&!$today_meipai){

                $user = M('user')->where(['uid'=>$_SESSION['uid']])->find();

                $last_sign = $user['last_sign'];

                if($last_sign >= strtotime(date('Y-m-d',strtotime('-1 day')))){//判断是否连续签到

                    if(date('j')!=1){//判断当前是否是当月1号

                        M('user')->where(['uid'=>$_SESSION['uid']])->update(['continue_sign_days'=>$user['continue_sign_days']+1]);

                    }else{

                        M('user')->where(['uid'=>$_SESSION['uid']])->update(['continue_sign_days'=>1]);
                    }
                }else{

                    M('user')->where(['uid'=>$_SESSION['uid']])->update(['continue_sign_days'=>1]);
                }

                addMissionLog($uid,1,$uid);

                addSignLog($_SESSION['uid'],$_SESSION['username'],1,0,'自拍','','');
            }

            if(queryExistMissionLog($uid,'5,16')){
                addMissionLog($uid,5,$uid);
            }else{
                addMissionLog($uid,16,$uid);
            }

            Json_success("发布成功",$state);
        }else{

            Json_error("发布失败");
        }

    }

    private function _getUserPoint($uid) {
        $db = M('user_point');
        $point = $db->where('uid= ' . $uid)->find();
        return $point['point'];
    }

    /**
     * @Title: delSns
     * @Description:todo(删除帖子)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function delSns(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $sid = $_POST['sid'];
        if(empty($sid))Json_error("缺少sid参数");
        $model = K('sns');
        $state = $model->delSns($uid,$sid);
        if($state > 0)Json_success("删除成功",$state);
        else Json_error("删除失败",0);
    }
    /**
     * @Title: uploadSnsImags
     * @Description:todo(上传图片)
     * @Author: Kaiqi
     * @必选 post: uid
     * @return 图片上传的路径，多个图片路径之间用#分割
     */
    public function uploadSnsImags(){

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
        Json_success("上传成功",$imgs);
    }

    /**
     * @Title: commentSns
     * @Description:todo(添加评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function commentSns(){//如果有被评论人则为回复，否则为评论
        $uid = $_SESSION['uid'];
        if(empty($uid)){
            Json_error("请登录");
        }

        $sid = $_POST['sid'];
        if(empty($sid))Json_error("缺少sid参数");
        $content = $_POST['content'];
        if(empty($content))Json_error("缺少content参数");
        $cid = $_POST['cid'];
        $model = K('sns');
        $state = $model->addComment($uid,$cid,$sid,$content);
        if($state > 0){

            if(queryExistMissionLog($uid,'4,17')){
                addMissionLog($uid,4,$sid);
            }else{
                addMissionLog($uid,17,$sid);
            }

            Json_success('评论成功',$state);
        }else{

            Json_error("评论失败");
        }
    }

    /**
     * @Title: getComment
     * @Description:todo(获取评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getComment(){
        $sid = $_POST['sid'];
        if(empty($sid))Json_error("缺少sid参数");
        $model = K('sns');
        $data = $model->getComment($sid,['cid','uid','unickname','uvatar','buid','bunickname','type','content','create_at']);
        Json_success("查询成功",$data);
    }

    /**
     * @Title: delComment
     * @Description:todo(删除评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function delComment(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请先登录");
        $cid = $_POST['cid'];
        if(empty($cid))Json_error("缺少cid参数");
        $model = K('sns');
        $state = $model->delComment($cid,$uid);
        if($state > 0)Json_success("删除成功",$state);
        else Json_error("删除失败",$state);
    }
    /**
     * @Title: zanSns
     * @Description:todo(点赞或者取赞)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function zanSns(){
        $uid = $_SESSION['uid'];
        $sid = $_POST['sid'];
        $buid = $_POST['buid'];
        if(empty($uid)){
            Json_error('请先登录');
        }
        if(empty($sid)){
            Json_error("缺少sid参数");
        }
        if(empty($buid)){
            Json_error("缺少buid参数");
        }
        $model = K('sns');
        $state = $model->checkZan($uid,$sid);
        if($state > 0){
            $now = $model->delZan($uid,$sid);
            if($now > 0)Json_success("取消点赞",$now);//提示语不能改
        }else{
            $now = $model->addZan($uid,$buid,$sid);
            if($now > 0){

                addMissionLog($uid,7,$sid);

                Json_success("点赞成功",$now);
            }
        }
    }

    /**
     * @Title: concernUser
     * @Description:todo(关注用户或取消关注)
     * @Author: Kaiqi
     * @必选 post: uid buid
     * @return void 返回类型
     *
     */
    public function concernUser(){
        $uid = $_SESSION['uid'];
        $buid = $_POST['buid'];
        if(empty($uid)){
            Json_error('请先登录');
        }
        if(empty($buid)){
            $username = $_POST['username'];
            $buser = M('user')->where(['username'=>$username])->find();
            $buid = $buser['uid'];
            if(empty($buid))Json_error("缺少buid参数");
        }
        $model = K('public');

        if(!$model->checkConcern($uid,$buid)){//没有关注

            $state =  $model->setConcern([
                'uid' => $uid,
                'buid' => $buid
            ]);

            $state1 = $model->setConcern([
                    'uid' => $buid,
                    'buid' => $uid
            ]);

            if($state > 0){
                addMissionLog($uid,8,$uid);
                if(!empty($state1)) Json_success('关注成功');
                else Json_error("相互关注失败");
            }else {
                Json_error("关注失败");
            }

        }else{
            $state = $model->delConcern([
                'uid' => $uid,
                'buid' => $buid
            ]);

            $state1 = $model->delConcern([
                    'uid' => $buid,
                    'buid' => $uid
            ]);
            if($state > 0)Json_success("取关成功");
            else Json_error('取关失败');
        }
    }

    /**
     * @Title: reportSns
     * @Description: todo(举报动态)
     * @author Kaiqi
     * @return  void  返回类型
     */
    public function reportSns(){
        if(C('square')==0){
            Json_error('广场功能暂时关闭，请等待管理员开启后重试,谢谢配合');
        }
        $uid = $_SESSION['uid'];
        if(empty($uid)){
            Json_error('请登录');
        }
        $sid = $_POST['sid'];
        if(empty($sid)){
            Json_error('该动态不存在');
        }
        $reason = $_POST['reason'];
        $model = K('sns');
        $state = $model->reportSns($uid,$sid,$reason);
        if($state>0){
            Json_success('举报成功，我们会尽快为您处理，感谢您的支持');
        }else{
            Json_error('系统错误');
        }
    }

    /**
     * @Title: getZanList
     * @Description:todo(获取点赞列表(详情))
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getZanList(){
        $model = K('sns');
        $buid = $_SESSION['uid'];
        if(empty($buid))Json_error("请登录");
        $sid = $_POST['sid'];
        if(empty($sid))Json_error("缺少sid参数");
        ///Json_success("dd",$sid);
        $list = $model->getZanList($sid,['uid','unickname','usex','ubirthday','uvatar']);
        foreach ($list as $key => $value) {
            $list[$key]['isconcern'] = K('public')->checkConcern($buid,$list[$key]['uid']);
            $list[$key]['age'] = date('Y',time()) - date('Y',$list[$key]['ubirthday']);
        }
        Json_success("获取成功",$list);
    }

    /**
     * @Title: shieldSns
     * @Description:todo(屏蔽帖子)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function shieldSns(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $sid = $_POST['sid'];
        if(empty($sid))Json_error("缺少sid参数");
        $model = K('user');
        $state = $model->shieldSns($sid,$uid);
        if($state)Json_success("屏蔽成功");
        else{
            Json_error("屏蔽失败");
        }
    }

    /**
     * @Title: getSameTownSns
     * @Description:todo(获取老乡帖子)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getSameTownSns(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $model = K('sns');
        //$user = K('user')->getUserInfo($uid);
        //$data = $model->readSns(3,$uid,['hometown_id'=>$user['hometown_id']]);
        $data = $model->getSameTownSns($uid);
        Json_success("获取成功",$data);
    }

    /**
     * @Title: getUserSns
     * @Description:todo(获取用户的发帖)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getUserSns(){
        //$uid = $_SESSION['uid'];
        $myUid = empty($_SESSION['uid'])?0:$_SESSION['uid'];
//        if(empty($myUid))Json_error("请登录");
        $uid = $_POST['uid'];
        if(empty($uid)){
            Json_error("请指定用户");
        }
        $model = K('sns');
        $data = $model->readSns($myUid,['uid' => $uid],[],false);
        Json_success("获取成功",$data);
    }

    /**
     * @Title: getNearInfo
     * @Description:todo(获取附近人和群-4个)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getNearInfo(){
        $model = K('sns');
        $nearPeople = $model->getNearPeople(false);
        $nearGroup = $model->getNearGroup(4);
//        $recommendGroups = M('group')->where('type=1')->findall();

        $result =[
            'nearPeople' => $nearPeople,
            'nearGroup' => $nearGroup,
//            'recommendGroups' => $recommendGroups
        ];
        //array_push($result,$nearPeople,$nearGroup);
        Json_success("获取成功",$result);
    }

    /**
     * @Title: getNearPeople
     * @Description:todo(获取附近的人)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getNearPeople(){
        $model = K('sns');
        $result = $model->getNearPeople();
        if(!empty($result))Json_success("获取成功",$result);
        else Json_error("没有附近的人");
    }

    /**
     * @Title: getNearGroup
     * @Description:todo(获取附近的群-全部)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getNearGroup(){
        $model = K('sns');
        $result = $model->getNearGroup();
        Json_success("获取成功",$result);
    }
    /**
     * @Title: getDetailSns
     * @Description:todo(获取帖子详情)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getDetailSns(){
        $uid = empty($_SESSION['uid'])?0:$_SESSION['uid'];
        $sid = $_POST['sid'];
        if(empty($sid))Json_error("请输入帖子id");
        $model = K('sns');
        $data = $model->readSns($uid,['sid'=>$sid],[],true,false);
        if(empty($data)){
            Json_error("帖子不存在或已删除");
        }else{
            foreach ($data as $key => $value) {
                $data[$key]['comment'] = $model->getComment($sid);
            }
            Json_success('获取成功',$data);
        }
    }

    /**
     * @Title: getRecommendGroups
     * @Description: todo(得到推荐群组)
     * @author Kaiqi
     * @return  void  返回类型
     */
    public
    function getRecommendGroups()
    {
        $db = M('group');

        $groups = $db->where('type=1')->findall();

        foreach ($groups as $key => $value){

            $groups[$key]['groups']['people_num'] = M('group_member')->where(['gid'=>$value['gid']])->count();

            $exist = M('group_member')->where(['gid'=>$value['gid'],'uid'=>$_SESSION['uid']])->find();

            if($exist){

                $groups[$key]['is_join'] = 1;
            }else{

                $groups[$key]['is_join'] = 0;
            }

            if($_SESSION['uid'] == $value['uid']){

                $groups[$key]['is_admin'] = 1;
            }else{

                $groups[$key]['is_admin'] = 0;
            }
        }

        Json_success('获取成功', $groups);
    }

    /**
     * @Title: snsShare
     * @Description: todo(帖子分享页面)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function snsShare(){

//        $sid = $_POST['sid'];
        $sid = $_GET['sid'];

        $sns = M('new_sns')->where(['sid'=>$sid])->find();

        if(empty($sns)){

            Json_error('帖子不存在');
        }


        $sns['images']=explode('#',$sns['images']);
        $more = M('new_sns')->where('images !=""')->order('create_at desc')->limit(4)->findall();

        foreach ($more as $key=>$value){

            $more[$key]['images']=explode('#',$value['images']);
        }
        $data = [
            'info'=>$sns,
            'more'=>$more
        ];
        $this->assign('data', $data);

        $this->display('app/sns');

//        Json_success('获取成功',$data);
    }

    public function messageView(){

        $mid = $_GET['mid'];

        $message = M('new_message')->where(['mid'=>$mid])->find();

        if(empty($message)){

            echo '未找到文件';die;
        }

        $this->assign('message',$message);
        $this->display('app/message');
    }

}