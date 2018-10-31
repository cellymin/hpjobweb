<?php

/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/3/10
 * Time: 15:00
 */
class messageControl extends Control
{
    private $message;
    function __construct()
    {
        parent::__construct();
        $this->message = K('message');
    }

    /**
     * @Title: getMessageList
     * @Description:todo(获取消息列表)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getMessageList(){
        $uid = $_SESSION['uid'];

        if(empty($uid))Json_error("请先登录");
        $ban_mid = $_POST['ban_mid'];
        $data = $this->message->getMessage($uid,$ban_mid);
        if($data == -1)Json_success("没有新消息",$data);
        else Json_success("获取成功",$data);
    }

    /**
     * @Title: writeMessage
     * @Description:todo(写消息)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function writeMessage(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $type = $_POST['type'];
        if(empty($type))Json_error("缺少type参数");
        $data_type = $_POST['data_type'];
        if(empty($data_type))Json_error("缺少data_type参数");
        $title = $_POST['title'];
//        if(empty($title))Json_error("请填写标题");
        $content = $_POST['content'];
        $link_url = $_POST['link_url'];
        $data = [
          'uid' => $uid,
          'type' => $type,
          'data_type' => $data_type,
          'title' => $title,
          'content' => $content,
          'link_url' => $link_url,
          'data_content' => '',
          'create_at' =>time(),
          'update_at' => time()
        ];
        $state = $this->message->createMessage($data);
        if($state > 0) {
            Json_success("消息创建成功", $state);
        }else{
            Json_error("消息创建失败");
        }
    }

    /**
     * @Title: delMessage
     * @Description:todo(删除消息)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function delMessage(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $mid = $_POST['mid'];
        if(empty($mid))Json_error('缺少mid参数');
        $state = $this->message->delMessage($mid,$uid);
        if($state > 0)Json_success("删除成功");
        else Json_error("删除失败");
    }

    /**
     * @Title: updateMessage
     * @Description:todo(更新消息内容)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function updateMessage(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error('请登录');
        $mid = $_POST['mid'];
        if(empty($mid))Json_error("缺少mid参数");
        $tag = $_POST['tag'];
        $subtag = $_POST['subtag'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $link_url = $_POST['link_url'];
        $data = [
            'uid' => $uid,
            'tag' => $tag,
            'subtag' => $subtag,
            'title' => $title,
            'content' => $content,
            'link_url' => $link_url,
            'update_at' => time()
        ];
        $state = $this->message->updateMessage($mid,$data);
        if($state > 0)Json_success('更新成功',$state);
        else Json_error("更新失败");
    }

    /**
     * @Title: getAvatar
     * @Description:todo(获取小头像)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getAvatar(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $usernames = $_POST['usernames'];
        $param = explode(',',$usernames);
        $result = $this->message->getAvatar($param);

        Json_success("获取小头像",$result);
    }

    /**
     * @Title: createMission
     * @Description:todo(创建消息任务)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function createMission(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $data = [
            'uid'=>$uid,
            'type'=>$_POST['type'],
            'data_type'=>$_POST['data_type'],
            'title'=>$_POST['title'],
            'content'=>$_POST['content'],
            'link_url'=>$_POST['link_url'],
            'images'=>$_POST['images']
        ];
        $buid = [];
        array_push($buid,$_POST['buids']);
        if(empty($buid))Json_error("缺少buids参数");
        $state = $this->message->createMission($buid,0,$data);
        if($state) Json_success("创建完成",$state);
        else Json_error("创建失败");
    }

    /**
     * @Title: getSpecialMessage
     * @Description:todo(获取特定信息)
     * @Author: Kaiqi
     * @return void 返回类型
     *
     */
    public function getSpecialMessage(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $mid = $_POST['ban_mid'];
        $data_type = $_POST['data_type'];
        $message = $this->message->getSpecialMessage($uid,$mid,$data_type);
        Json_success("获取成功",$message);
    }
}