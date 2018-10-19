<?php

/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/3/20
 * Time: 10:21
 */
class messageModel extends Model
{
    public function __construct($table = null, $full = null, $driver = null)
    {

    }

    /**
     * @Title: createMission
     * @Description:todo(创建任务)
     * @Author: Kaiqi
     * @param $uid
     * @param $data
     * @return bool|mixed 返回类型
     */
    public function createMission($uid,$data,$flag,$message){
        $user = M("user")->where(['uid'=>$uid])->find();
        if(!empty($user)){
            $data['uid'] = $user['uid'];
            $data['unickname'] = $user['username'];
            $data['uavatar'] = $user['avatar'];
            $data['type'] = $data['data_type'] / 100;
            $data['create_at'] = time();
            $mid =  M("new_message")->insert($data);
            if($mid){
                $this->sendGearman($flag,$mid,$message,$data);
                return true;
            }
        }
        return false;
    }

    /**
     * @Title: sendGearman
     * @Description:todo(将任务发送到Gearman)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    private function sendGearman($type,$mid,$content,$data){
        $param = [
            'type' => $type,
            'mid' => $mid,
            'title' => $data['title'],
            'content'=>$content,
            'data_type'=>$data['data_type']
        ];
        $client = new GearmanClient();
        $client->addServer();
        $client->setCompleteCallback(function(GearmanTask $task){

        });
        $client->addTaskBackground('sendSysMessage',json_encode($param));
        $client->runTasks();
    }

    /**
     * @Title: sendEnrollMessage
     * @Description:todo(录取通知)
     * @Author: Kaiqi
     * @param $uid
     * @param $data
     * @param $mobiles
     * @return bool 返回类型
     */
    public function sendEnrollMessage($uid,$data,$mobiles){
        $user = M("user")->where(['uid'=>$uid])->find();
        if(empty($mobiles))return false;
        if(!empty($user)){
            $data['uid'] = $uid;
            $data['unickname'] = $user['username'];
            $data['uavatar'] = $user['avatar'];
        }

        $mid = M('new_message')->insert($data);
        $where = '';
        $value = '';
        foreach ($mobiles as $mobile){
            $value .= ','.$mobile;
        }
        $value = trim($value,',');
        $where[]='username in ('.$value.')';
        $receivers = M('user')->where($where)->field(['uid','client_id'])->findall();
        $client_id = [];
        $value = '';
        foreach ($receivers as $receiver){
            $value .= ',' . '(' . $mid . ',' . $receiver['uid'] . ',' . time() . ')';
            $client_id[] = $receiver['client_id'];
        }
        $value = trim($value,',');
        $sql = "INSERT INTO hp_new_message_user (mid,buid,create_at) VALUES $value ;";
        M('new_message_user')->exe($sql);
        $client_id = array_unique(array_filter($client_id));
        $hidden = array(
            'type' => 2,
            'data_type'=>201,
            'title' => $data['title'],
            'content' => $data['content']
        );
        return push($client_id, array('hidden' => $hidden, 'title' => '标题', 'content' => '内容'));
    }
}