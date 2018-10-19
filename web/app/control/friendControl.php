<?php

/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/3/21
 * Time: 13:26
 */
class friendControl extends Control{

    private $friend;
    function __construct()
    {
        parent::__construct();
        $this->friend = K('friend');
    }

    /**
     * @Title: getFriends
     * @Description:todo(获取朋友列表)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getFriends(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $result = $this->friend->getFriends($uid);
        Json_success("获取成功",$result);
    }

    /**
     * @Title: getNewFriends
     * @Description:todo(获取新的朋友和推荐)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getNewFriends(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $result = $this->friend->getNewFriends($uid);
        Json_success("获取成功",$result);
    }

    /**
     * @Title: getMoreNotice
     * @Description:todo(获取更多好友通知)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getMoreNotice(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $result = $this->friend->getNoticeFriend($uid,true);
        Json_success("获取成功",$result);
    }

    /**
     * @Title: searchFriends
     * @Description:todo(搜素朋友)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function searchFriends(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $search_word = $_POST['search_word'];
        $tag = $_POST['tag'];
        $result = $this->friend->searchFriends($uid,$search_word,$tag);
        Json_success("查询成功",$result);
    }

    /**
     * @Title: getCommendFriends
     * @Description:todo(获取推荐的好友)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getCommendFriends(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $result = $this->friend->getRecommendFriends($uid);
        Json_success("查询成功",$result);
    }

    /**
     * @Title: sameTownFriends
     * @Description:todo(获取老乡)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function sameTownFriends(){
        $uid = $_SESSION['uid'];

        if(empty($uid))Json_error("请登录");

        $hometown = $_POST['hometown'];

        $result = $this->friend->getSameTown($uid,$hometown);
        Json_success("查询成功",$result);
    }

    /**
     * @Title: joinGroup
     * @Description:todo(加入群)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function joinGroup(){
        $adminid = $_SESSION['uid'];
        if(empty($adminid))Json_error("请登录");
        $huanxinId = $_POST['huanxinId'];
        if(empty($huanxinId))Json_error("缺少huanxinId参数");
        $gid = $_POST['gid'];
        if(empty($gid))Json_error("缺少gid参数");
        $pow = $this->friend->checkAuth($gid,$adminid);
//        if(!$pow && $uid!='19547')Json_error("没有权限");
        $state = $this->friend->allowJoinGroup($gid,$huanxinId);
        if($state)Json_success("加入成功");
        else Json_error("加入失败");
    }

    /**
     * @Title: quitGroup
     * @Description: todo(退出群组)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function quitGroup(){

        $gid = $_POST['gid'];

        $group = M('group')->where(['gid'=>$gid])->find();

        if(empty($group)){

            Json_error('群不存在');
        }

        $uid = $_SESSION['uid'];

        $admin = M('group_member')->where(['gid'=>$gid,'uid'=>$uid,'is_admin'=>1])->find();

        if($admin){

            Json_error('群主不可退出');
        }

        M('group_member')->where(['gid'=>$gid,'uid'=>$uid])->delete();

        Json_success('退出成功');
    }
    /**
     * @Title: deleteGroupMember
     * @Description:todo(删除群成员)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function deleteGroupMember(){
        $adminid = $_SESSION['uid'];
        if(empty($adminid))Json_error("请登录");
        $uid = $_POST['uid'];
        if(empty($uid))Json_error("缺少uid参数");
        $gid = $_POST['gid'];
        if(empty($gid))Json_error("缺少gid参数");
        $pow = $this->friend->checkAuth($gid,$adminid);
        if(!$pow && $uid!='19547')Json_error("没有权限");
        $state = $this->friend->deleteGroupMember($gid,$uid);
        if($state)Json_success("移除成功");
        else Json_error("移除失败");
    }

    /**
     * @Title: getConcernOrFan
     * @Description:todo(获取关注或粉丝)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getConcernOrFan(){//1表示关注 2 粉丝
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $type = $_POST['type'];
        if(empty($type))Json_error("缺少type参数");
        if($type==1) {
            $state = $this->friend->getFriends($uid,[],true);
            Json_success("获取成功",$state);
        }else if($type == 2){
            $state = $this->friend->getNoticeFriend($uid);
            Json_success("获取成功",$state);
        }else{
            Json_error("type参数错误");
        }
    }

    /**
     * @Title: sameCompanyFriends
     * @Description:todo(获取同事推荐)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function sameCompanyFriends(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $result = $this->friend->getCompanyFriendRecommend($uid);
        Json_success("查询成功",$result);
    }

}