<?php

/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/3/10
 * Time: 15:34
 */
class messageModel extends Model
{
    private $user_model;
    private $messageDb;
    private $last_time;
    private $map_type;
    private $alas;
    function __construct()
    {
        $this->user_model = K('app/user');
        $this->messageDb = M('new_message');
        $this->last_time = strtotime("-7 days");
        $this->map_type = [//消息子分类编号
          "抽奖活动" => 101,
          "帖子推荐"=> 102,
          "限时抢兑"=> 103,
          "职位推荐"=>104,
          "系统消息"=>105,
          "录用通知" => 201,
          "投递提醒" => 202,
          "面试历史" => 203,
          "评论" => 301,
          "点赞" => 302,
          "回复" => 303,
          "账号消息" => 401,
        ];
        $this->alas =[//返回消息的所在的数组
          101=>"system_notice",
          102=>"system_notice",
          103=>"system_notice",
          104=>"system_notice",
          105=>"system_notice",
          201=>"work_notice",
          202=>"work_notice",
          203=>"work_notice",
          301=>"comment_notice",
          302=>"zan_notice",
          303=>"comment_notice",
          401=>"account_notice"
        ];
    }

    /**
     * @Title: getMessage
     * @Description:todo(获取消息)
     * @Author: Kaiqi
     * @param $tag
     * @param array $field
     * @param array $where
     * @return mixed 返回类型
     *
     */
    public function getMessage($uid,$ban_mid=''){
        $db = M('new_message_user');
        if(!empty($ban_mid)) $con[]='mid not in ('.$ban_mid .')';
        $con['buid'] = $uid;
        $con[] = 'create_at > '.$this->last_time;
        $mids = $db->where($con)->field(['mid'])->findall();
        if(empty($mids))return $this->dealData([]);
        $str = "";
        foreach ($mids as $mid){
            $str= $str.','.$mid['mid'];
        }
        $str = trim($str,',');
        $sql[]='mid in ('.$str.')';
        //$count = $this->messageDb->where($sql)->count();
        //$page = new page($count,10);
        $data = $this->messageDb->where($sql)->order(['create_at'=>'desc'])->findall();

        $data = $this->dealData($data);

        foreach ($data['work_notice'] as $k=>$message){

            if(!empty($message['data_content'])){

                $data['work_notice'][$k]['data_content'] = json_decode($message['data_content'],true);
            }

        }
        return $data;
    }

    /**
     * @Title: dealData
     * @Description:todo(将获取信息按照类型分类便于前台调用)
     * @Author: Kaiqi
     * @param $datas
     * @return array 返回类型
     */
    private function dealData($datas){
        $result = [];
        foreach ($this->map_type as $type){
            $result[$this->alas[$type]] = [];
        }
        foreach ($datas as $data){
            foreach ($this->map_type as $type){
                if($data['data_type']==$type){
                    array_push($result[$this->alas[$type]],$data);
                }
            }
        }
        return $result;
    }
    /**
     * @Title: writeMessage
     * @Description:todo(创建一条消息)
     * @Author: Kaiqi
     * @param $data uid(消息发起人id)/type/(可选)data_type/(可选)link_url/content
     * @flag:true表示推送false不推送
     * @return bool|mixed 返回类型
     */
    public function createMessage($data){//type 3评论/回复/点赞 2工作通知 4账号信息 1系统信息
        if(empty($data['uid']||empty($data['type']||empty($data['data_type']) ) ) )return false;
        $user = $this->user_model->getUserInfo($data['uid'],['nickname','avatar']);
        if(empty($user))return false;
        $data['unickname'] = $user['nickname'];
        $data['uavatar'] = $user['avatar'];
        $data['create_at'] = time();
        $state = $this->messageDb->insert($data);
        return $state;
    }

    /**
     * @Title: pushMessage
     * @Description:todo(推送消息)
     * @Author: Kaiqi
     * @param $data
     * @return bool 返回类型
     */
    private function pushMessage($client_id,$type,$data_type,$hidden = null){
        $title = "新消息:";
        if($type == 1){
            if($data_type == 101)
                $content = "你有一条未读的系统消息";
            else
                return false;
        }else if($type == 2){
            if($data_type == 201)
                $content = "你有一条未读的录用通知";
            else if($data_type == 202)
                $content = "你有一条新的投递提醒";
            else if($data_type == 203)
                $content = "你有一条新的面试历史";
            else
                return false;
        }else if($type == 3){
            if($data_type == 301){
                $content = "你有一条未读的评论";
            }else if($data_type == 302){
                $content = "你有一条新的点赞";
            }else if($data_type== 303){
                $content = '你有一条新回复';
            }else{
                return false;
            }
        }else if($type==4){
            if($data_type == 401)
                $content = "你有一条账号信息";
            else
                return false;
        }
       if(empty($hidden)) {
           $hidden = [
               'type' => $type,
               'data_type' => $data_type,
               'title' => $title,
               'content' => $content
           ];
       }
        $send = push($client_id,['hidden'=>$hidden,'title'=>$title.$content,'content'=>$content]);

        if(empty($send))return false;
        return true;
    }

    /**
     * @Title: createMission
     * @Description:todo(创建消息任务)
     * @Author: Kaiqi
     * @param $buid
     * @param int $mid 如果是现有消息传入消息id新建的消息填0但data不可为可空
     * @param array $data 格式如下
     * {
     *  $buids = ['19549','19548']; 消息接收人id
        $data  =[
            'uid' => '19547',  //发送用户id(必填)
            'type' => '2',     //大分类1系统消息2工作通知3个人4账号消息(必填)
            'data_type' =>'201',//(必填)小分类101系统信息  201工作通知 301 评论 302 点赞 303回复 401 账号消息
            'title' => '三星电子机械公司',//标题
     *      'data_id' => 帖子的id
            'content' => '你好,你被开除了',//内容
            'link_url' => '',
            'images' =>''
        ];
     * }
     * @return bool|mixed 返回类型
     */
    public function createMission($buids=[],$mid=0,$data=[]){

        if(empty($mid)&&empty($data))return false;
        $db = M('new_message_user');
        if(empty($mid)){
            $mid = $this->createMessage($data);
        }
        $temp = "";
        $state = true;
        foreach ($buids as $buid) {
            $state = $db->insert([
                'buid' => $buid,
                'mid' => $mid,
                'create_at'=>time()
            ]);
            if(!$state)break;
            $temp .=  "," .$buid;
        }
        $temp = trim($temp,',');
        if(!empty($temp)) {
            $sql[] = 'uid in (' . $temp . ')';
            $users = M('user')->where($sql)->field('client_id')->findall();
        }else{
            $users = M('user')->field('client_id')->findall();
        }
        if(empty($users)){
            Json_error("未发现用户");
        }
        $reciever = [];
        foreach ($users as $user){
            array_push($reciever,$user['client_id']);
        }
        if(empty($reciever))return false;
        if($state){
            $state = $this->pushMessage($reciever,$data['type'],$data['data_type'],empty($data['hidden'])?'':$data['hidden']);
            return $state;
        }else{
            return $state;
        }
    }

    /**
     * @Title: delMessage
     * @Description:todo(删除消息任务)
     * @Author: Kaiqi
     * @param $mid
     * @param $uid
     * @return mixed 返回类型
     */
    public function delMessage($mid,$uid){
        //$state = $this->messageDb->where(['mid'=>$mid])->update(['del_state' => 0]);
        $state = M("new_message_user")->where(['mid'=>$mid,'buid'=>$uid])->del();
        return $state;
    }

    public function updateMessage($mid,$data){
        $state = $this->messageDb->where(['mid'=>$mid])->update($data);
        return $state;
    }

    /**
     * @Title: getAvatar
     * @Description:todo(获取用户小头像)
     * @Author: Kaiqi
     * @param $params
     * @return mixed 返回类型
     */
    public function getAvatar($params){
        $sql = "";
        foreach ($params as $param){
            $sql.='"'.$param.'",';
        }
        $sql = trim($sql,',');
        if(!empty($sql)){
            $con[]='username in ('.$sql.') or phone in ('.$sql.')';
        }else{
            Json_success("没有用户");
        }
        $result = M('user')->where($con)->field(['phone','username','avatar','nickname'])->findall();
        return $result;
    }

    /**
     * @Title: getSpecialMessage
     * @Description:todo(获取特定种类的信息)
     * @Author: Kaiqi
     * @param $uid
     * @param string $mid
     * @param string $data_type
     * @param bool $ispage
     * @return mixed 返回类型
     */
    public function getSpecialMessage($uid,$mid="",$data_type="",$ispage=false){
        $con['buid'] = $uid;
        if(!empty($mid))$con[]="mid not in (".$mid.")";
        $mids = M("new_message_user")->where($con)->field(['mid'])->findall();
        $sql = "";
        foreach ($mids as $mid){
            $sql.=$mid['mid'].',';
        }

        $sql = trim($sql,',');
        unset($con);
        if(!empty($sql)){
            $con[] = "mid in (".$sql.")";
        }
        if(!empty($data_type))$con[]="data_type in (".$data_type.")";
        //Json_error("dd",$con);
        if($ispage){
            $count = M("new_message")->where($con)->count();
            $page = new page($count,10);
            $message = M("new_message")->where($con)->findall($page->limit());
        }else {
            $message = M("new_message")->where($con)->findall();
        }
        return $message;
    }

    /**
     * @Title: delSnsMessage
     * @Description:todo(删除帖子)
     * @Author: Kaiqi
     * @param $sid
     * @return bool 返回类型
     */
    public function delSnsMessage($sid){
        $mids = M('new_message')->where(['data_id'=>$sid,'type'=>3])->field(['mid'])->findall();
        $state = M("new_message")->where(['data_id'=>$sid,'type'=>3])->del();
        if(empty($mids))return true;
        $sql= "";
        foreach ($mids as $mid){
            $sql.=$mid['mid'].',';
        }
        $sql = trim($sql,',');
        $con[]="mid in (".$sql.")";
        $state1 = M("new_message_user")->where($con)->del();
        return $state&&$state1;
    }
}