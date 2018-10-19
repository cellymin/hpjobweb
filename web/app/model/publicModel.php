<?php


class publicModel extends Model {

    private $user_model;
    function __construct($table = null, $full = null, $driver = null){
        $this->user_model = K('user');
    }

    /**
     * @Title: checkConcern
     * @Description:todo(检查关注)
     * @Author: Kaiqi
     * @param $uid 关注者id
     * @param $buid 被关注者id
     * @return bool 返回类型
     */

    public function checkConcern($uid,$buid){
        $db = M('new_guanzhu');
        $count = $db->where(['uid' => $uid,'buid' => $buid])->count();
        return $count!=0;
    }

    /**
     * @Title: setConcern
     * @Description:todo(添加用户关注)
     * @Author: Kaiqi
     * @* @param $param
     * @return bool|mixed 返回类型
     */
    public function setConcern($param){
        $uid = $param['uid'];
        $buid = $param['buid'];
        $user = $this->user_model->getUserInfo($uid,['nickname','avatar','username']);
        $buser =$this->user_model->getUserInfo($buid,['nickname','avatar','username']);

        if(!isset($user)||!isset($buser))Json_error("用户不存在");
        $data = [
            'uid' => $uid,
            'buid' => $buid,
            'unickname' => $user['nickname'],
            'bunickname' => $buser['nickname'],
            'uvatar' => $user['avatar'],
            'buvatar' => $buser['avatar'],
            'create_at' => time(),
            'username' => $user['username'],
            'busernname' => $buser['username']
        ];
        $state =  M('new_guanzhu')->insert($data);
        if($state > 0){
            $this->user_model->changeModelNum($uid,1);
            $this->user_model->changeModelNum($buid,2);
        }
        return $state;
    }

    /**
     * @Title: delConcern
     * @Description:todo(取消关注)
     * @Author: Kaiqi
     * @param $param
    @return mixed 返回类型
     */
    public function delConcern($param){
        $uid = $param['uid'];
        $buid = $param['buid'];
        $state =  M('new_guanzhu')->del($param);
        if($state > 0){
            $this->user_model->changeModelNum($uid,1,-1);
            $this->user_model->changeModelNum($buid,2,-1);
        }
        return $state;
    }

    /**
     * @Title: getUserMissions
     * @Description: todo(任务列表)
     * @author nipeiquan
     * @param $uid
     * @return  mixed  返回类型
     */
    public function getUserMissions($uid){

        $missions = M('point_mission')->where(['state'=>1])->order('daily_mission asc')->findall();

        $data = [
            'finish'=>[],
            'daily'=>[],
            'new_member'=>[],
        ];

        foreach ($missions as $k=>$mission){

            $finish_num = $this->getFinishNum($uid,$mission['mission_type']);

            $missions[$k]['finish_num'] = $finish_num;

            $is_receive = $this->isReceiveReward($uid,$mission['mid']);

            $missions[$k]['is_receive'] = $is_receive;//是否领取过奖励0未1已

            if($finish_num >= $mission['need_num']){

                $missions[$k]['is_finish'] = 1;

                $data['finish'][] = $missions[$k];
            }else{

                $missions[$k]['is_finish'] = 0;

                if($mission['daily_mission'] == 1){

                    $data['daily'][] = $missions[$k];
                }else{

                    $data['new_member'][] = $missions[$k];
                }

            }

        }

        return $data;
    }

    /**
     * @Title: isReceiveReward
     * @Description: todo(判断今日是否领取过奖励)
     * @author nipeiquan
     * @param $uid
     * @param $mid
     * @return  int  返回类型
     */
    public function isReceiveReward($uid,$mid){

        $mission = M('point_mission')->where(['mid'=>$mid])->find();

        if($mission['daily_mission']==2){//新手任务只要有1次就算领取过,不可每天领取

            $log = M('point_mission_log')->where('uid ='.$uid.' AND mid ='.$mid)->find();
        }else{

            $log = M('point_mission_log')->where('uid ='.$uid.' AND mid ='.$mid.' AND created >'.strtotime(date('Y-m-d')))->find();
        }

        if($log){

            return 1;
        }else{

            return 0;
        }
    }

    /**
     * @Title: getFinishNum
     * @Description: todo(根据任务类型得到今日完成数)
     * @author nipeiquan
     * @param $uid
     * @param $type
     * @return  mixed  返回类型
     */
    public function getFinishNum($uid,$type){

        $today_time = strtotime(date('Y-m-d'));

        switch ($type)
        {
            case 1://每日-自拍签到
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =1 AND create_at >'.$today_time)->count();
                break;
            case 2://每日-普通签到
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =2 AND create_at >'.$today_time)->count();
                break;
            case 3://每日-限时抢兑
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =3 AND create_at >'.$today_time)->count();
                break;
            case 4://每日-回复帖子
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =4 AND create_at >'.$today_time)->count();
                break;
            case 5://每日-发布帖子
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =5 AND create_at >'.$today_time)->count();
                break;
            case 6://每日-分享
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =6 AND create_at >'.$today_time)->count();
                break;
            case 7://每日-点赞帖子
                $finish_num = count(M('mission_log')->where('uid ='.$uid.' AND type =7 AND create_at >'.$today_time)->group('data_id')->findall());
                break;
            case 8://每日-关注好友
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =8 AND create_at >'.$today_time)->count();
                break;
            case 9://每日-抽奖
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =9 AND create_at >'.$today_time)->count();
                break;
            case 10://新手-抽奖
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =10')->count();
                break;
            case 11://新手-完善简历
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =11')->count();
                break;
            case 12://新手-参与抢兑
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =12')->count();
                break;
            case 13://新手-申请工作
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =13')->count();
                break;
            case 14://新手-注册成功
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =14')->count();
                break;
            case 15://新手-完善个人信息
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =15')->count();
                break;
            case 16://新手-首次发帖
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =16')->count();
                break;
            case 17://新手-首次回复
                $finish_num = M('mission_log')->where('uid ='.$uid.' AND type =17')->count();
                break;
            default:
                $finish_num = 0;
        }

        return $finish_num;
    }

    /**
     * @Title: getCityId
     * @Description:todo(根据城市名获取id)
     * @Author: Kaiqi
     * @param $name
     * @return mixed 返回类型
     *
     */
    public function getCityId($name){
        $db = M('city');
        $result = $db->where('name LIKE "%' . $name . '%"')->find();
        return $result['id'];
    }

    /**
     * @Title: getCommentUser
     * @Description:todo(获取评论信息)
     * @Author: Kaiqi
     * @param $cid
     * @param array $field
     * @return mixed 返回类型
     */
    public function getComment($cid,$field=[]){
        $db = M('new_comment');
        $result = $db->where(['cid'=>$cid])->field($field)->find();
        return $result;
    }

    /**
     * @Title: getGroupInfo
     * @Description:todo(获取群信息)
     * @Author: Kaiqi
     * @param $gid
     * @param array $field
     * @return mixed 返回类型
     */
    public function getGroupInfo($gid,$field=[]){
        $db = M('group');
        $result = $db->where(['gid'=>$gid])->field($field)->find();
        return $result;
    }

    /**
     * @Title: writeQueryLog
     * @Description:todo(查询薪资写入日志)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function writeQueryLog($uid,$company_name){
        $user = K('user')->getUserInfo($uid);
        M('salary_query_log')->insert([
                'uid' => $user['uid'],
                'username'=>$user['username'],
                'company' => $company_name,
                'created_at'=>time()
        ]);
    }
}