<?php
/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/3/2
 * Time: 10:53
 */

class snsModel extends Model
{
    private $user_model;
    private $message_model;
    private $public_model;
    const PINGLUN = 301;
    const HUIFU = 303;
    const DIANZAN = 302;
    function __construct($table = null, $full = null, $driver = null){
        $this->user_model = K('user');
        $this->message_model = K('message');
        $this->public_model = K('public');
    }

    /**
     * @Title: readSns
     * @Description: todo(获取帖子)
     * @author nipeiquan
     * @param $uid
     * @param null $where
     * @param array $field
     * @param bool $flag
     * @param bool $ispage
     * @param string $order
     * @return  mixed  返回类型
     */
    public function readSns($uid,$where=null,$field=[],$flag=true,$ispage=true,$order='create_at desc'){//flag表示是否加了关注
        $db = M('new_sns');
        if(!empty($uid))$user = $this->user_model->getUserInfo($uid,['not_read_sids']);
        $sql['del_state'] = 0;
        if(!empty($user['not_read_sids'])){
            //$sql['sid'] = 'not in ('.$user['not_read_sids'].')';
           $sql[]='sid not in ('.$user['not_read_sids'].')';
        }
        if(!empty($where)){
            foreach ($where as $key => $value){
                $sql[$key] = $value;
            }
        }

        if($ispage) {
            $count = $db->where($sql)->count();
            $page = new page($count, 10);
            $fdata['count_page'] = $page->total_page;
            //$data = $db->where(['tag' => $tag,'del_state' => 0,'sid' =>'not in (58,59)'])->field($field)->order(['create_at'=>'desc'])->findall($page->limit());
            $data = $db->where($sql)->field($field)->order($order)->findall($page->limit());
            //$data = $db->field($field)->where($sql)->order(['create_at'=>'desc'])->findall($page->limit());
        }else{
            $data = $db->where($sql)->field($field)->order($order)->findall();
        }
        $model = K('public');
        foreach ($data as $key => $value){
            if($flag)$data[$key]['isconcern'] = $model->checkConcern($uid,$data[$key]['uid']);
            $data[$key]['zan_list'] = $this->getZanList($data[$key]['sid'],['uid','uvatar'],false);
            $data[$key]['is_zan'] = false;
            foreach($data[$key]['zan_list'] as $zan){
                if($zan['uid']==$uid){
                    $data[$key]['is_zan']=true;
                    break;
                }
            }
            if(empty($data[$key]['ubirthday'])){
                $data[$key]['ubirthday'] = '保密';
                $data[$key]['age'] = '';
            }else{
                $data[$key]['age'] = date('Y',time()) - date('Y',$data[$key]['ubirthday']);
            }
            if(!empty($uid)){
                $data[$key]['ismine'] = ($uid==$data[$key]['uid']);
            }else{
                $data[$key]['ismine'] = false;
            }
        }
        if($ispage){
            $fdata['sns']= $data;
             return $fdata;
        }else {
            return $data;
        }
    }

    /**
     * @Title: insertSns
     * @Description:todo(帖子写入数据库)
     * @Author: Kaiqi
     * @param $data(帖子数据)
     * @return bool|mixed|string 返回类型
     */
    public function insertSns($data){
        $uid = $data['uid'];
        $data['del_state'] = 0;
        $state = M('new_sns')->insert($data);
        if($state > 0){
            $this->user_model->changeModelNum($uid,3);
        }
        return $state;
    }

    /**
     * @Title: delSns
     * @Description:todo(删除帖子)
     * @Author: Kaiqi
     * @param $uid
     * @param $sid
     * @return bool|mixed 返回类型
     */
    public function delSns($uid,$sid){
        $author = M('new_sns')->where(['sid'=>$sid])->find();
        if($author['uid'] != $uid)Json_error("不能删除他人帖子");
        $update = M('new_sns')->where(['sid' => $sid])->update(['del_state' => 1]);
        if($update > 0){
            $this->user_model->changeModelNum($uid,3,-1);
        }
        $state = K("message")->delSnsMessage($sid);
        return $update&&$state;
    }
    /**
     * @Title: changeModelNum
     * @Description:todo(修改模型数据)
     * @Author: Kaiqi
     * @param $sid (帖子id)
     * @param $type (操作类型)
     * @param int $num (数据改变数)
     * @return bool|mixed 返回类型
     */
    public function changeModelNum($sid,$type,$num = 1){//type 1表示点赞数 2评论数
        $db = M('new_sns');
        $sns = $db->where(['sid' => $sid])->find();
        if($type == 1){
            $update = $db->where(['sid' => $sid])->update(['zan_num' => $sns['zan_num']+$num]);
            return $update;
        }else if($type == 2){
            $update = $db->where(['sid' => $sid])->update(['comment_num' => $sns['comment_num']+$num]);
            return $update;
        }else{
            return false;
        }
    }

    /**
     * @Title: addComment
     * @Description:todo(插入评论)
     * @Author: Kaiqi
     * @param $uid
     * @param null $buid
     * @param $sid
     * @param $content
     * @return bool|mixed 返回类型
     */

    public function addComment($uid,$cid=null,$sid,$content){//type 1表示评论 2表示回复
        $user = $this->user_model->getUserInfo($uid,['uid','nickname','avatar']);
        $data = [
            'sid' => $sid,
            'uid' => $uid,
            'unickname' => $user['nickname'],
            'uvatar' => $user['avatar'],
//            'buid' => $buser['uid'],
//            'bunickname' => $buser['nickname'],
            'content' => $content,
            'data_type'=> 1,
            'del_state' => 0,
            'create_at' => time()
        ];
        $this->changeModelNum($sid,2,1);
        if(empty($cid)){
            $data['type'] =1;
        }else{
            $comment = $this->public_model->getComment($cid);
            $buser = $this->user_model->getUserInfo($comment['uid']);
            $data['buid'] = $buser['uid'];
            $data['bunickname'] = $buser['nickname'];
            $data['type'] = 2;
        }
        $state = M('new_comment')->insert($data);
        if($state > 0){
            if(empty($cid)){
                $sns = $this->getSnsInfo($sid,2);

                if($sns['uid'] == $uid)return true;

                $state = $this->commentMessage($uid,$sns['uid'],self::PINGLUN,$content,$sns['content'],$sid);
            }else{
                $state = $this->commentMessage($uid,$data['buid'],self::HUIFU,$content,$comment['content'],$sid);
            }
        }
        return $state;
    }

    /**
     * @Title: getComment
     * @Description:todo(获取评论)
     * @Author: Kaiqi
     * @param $sid
     * @param array $field
     * @return mixed 返回类型
     */
    public function getComment($sid,$field=[]){
        $data = M('new_comment')->where(['sid' => $sid,'del_state' => 0])->field($field)->order(['create_at'=>'asc'])->findall();
        return $data;
    }

    /**
     * @Title: delComment
     * @Description:todo(删除评论)
     * @Author: Kaiqi
     * @param $cid
     * @return bool|mixed 返回类型
     */
    public function delComment($cid,$uid){
        $comment = M('new_comment')->where(['cid' => $cid])->find();
//        if($comment['uid']!=$uid)Json_error("不能删除他人评论");

        $sns = M('new_sns')->where(['sid'=>$comment['sid']])->find();
        if($sns['uid']!=$uid && $comment['uid']!=$uid)Json_error("不能删除该评论");

        $state = M('new_comment')->where(['cid' => $cid])->update(['del_state' => 1]);
        if($state > 0){
            $now = $this->changeModelNum($comment['sid'],2,-1);
            if($now > 0)return $state;
            return false;
        }
        return false;
    }
    /**
     * @Title: checkZan
     * @Description:todo(检查是否已经点赞)
     * @Author: Kaiqi
     * @param $uid (点赞用户id)
     * @param $sid  (帖子id)
     * @return bool 返回类型
     */
    public function checkZan($uid,$sid){
        $state = M('new_sns_zan')->where(['uid'=>$uid,'sid'=>$sid])->find();
        return !empty($state);
    }



    /**
     * @Title: addZan
     * @Description:todo(点赞)
     * @Author: Kaiqi
     * @param $uid (点赞用户id)
     * @param $buid (被点赞用户id)
     * @param $sid (帖子id)
     * @return bool|mixed 返回类型
     */
    public function addZan($uid,$buid,$sid){
        $user= $this->user_model->getUserInfo($uid,['nickname','gender','birthday','avatar']);
        $buser = $this->user_model->getUserInfo($buid,['nickname','gender','birthday','avatar']);
        $data = [
            'sid' => $sid,
            'uid' => $uid,
            'buid' => $buid,
            'unickname' => $user['nickname'],
            'usex' => $user['gender'],
            'ubirthday' => $user['birthday'],
            'bunickname' => $buser['nickname'],
            'uvatar' => $user['avatar'],
            'buvatar' => $buser['avatar'],
            'create_at' => time()
        ];
        $state = M('new_sns_zan')->insert($data);
        if($state > 0){
            $this->changeModelNum($sid,1);
            if($uid == $buid)return true;
            $sns = $this->getSnsInfo($sid,1);
            $state = $this->zanMessage($uid,$sid,$buid,$sns['content']);
            return $state;
        }else{
            return false;
        }
    }

    /**
     * @Title: delZan
     * @Description:todo(取消点赞)
     * @Author: Kaiqi
     * @param $uid
     * @param $sid
    @return bool|mixed 返回类型
     */
    public function delZan($uid,$sid){
        $state = M('new_sns_zan')->where(['uid' => $uid,'sid'=>$sid])->delete();
        if($state > 0){
            $state = ($this->changeModelNum($sid,1,-1)!=false);
        }
        return $state;
    }

    /**
     * @Title: getZanList
     * @Description:todo(获取点赞列表)
     * @Author: Kaiqi
     * @param $sid
     * @param array $field
     * @return mixed 返回类型
     */
    public function getZanList($sid,$field = [],$ispage=true){
        //Json_success("d",$sid);
        $db = M('new_sns_zan');
        if($ispage) {
            $count = $db->where(['sid' => $sid])->count();
            $page = new page($count, 7);
            $list = $db->where(['sid' => $sid])->field($field)->findall($page->limit());
        }else{
            $list = $db->where(['sid' => $sid])->field($field)->findall();
        }
        return $list;
    }

    /**
     * @Title: reportSns
     * @Description:todo(举报帖子)
     * @Author: Kaiqi
     * @param $uid
     * @param $sid
     * @param $reason
     * @return bool|mixed 返回类型
     */
    public function reportSns($uid,$sid,$reason){
        $sns = M('new_sns')->where(['sid' => $sid])->find();
        $user = M('user')->where(['uid' => $uid])->field('uid,username')->find();
        $buid = $this->user_model->getUserInfo($sns['uid'],['username']);
        $data = array(
            'sid'=>$sid,
            'uid'=>$uid,
            'username'=>$user['username'],
            'buid'=>$sns['uid'],
            'busername'=>$buid['username'],
            'content'=>$sns['content'],
            'reason'=>$reason,
            'time'=>time()
        );
        return M('sns_report')->insert($data);
    }

    /**
     * @Title: getSameTownSns
     * @Description:todo(获取同乡帖子)
     * @Author: Kaiqi
     * @param $uid
     * @return mixed 返回类型
     *
     */
    public function getSameTownSns($uid){
        $user = $this->user_model->getUserInfo($uid,['hometown_id']);
        //Json_error("dd",$user['hometown_id']);
        if(empty($user['hometown_id'])){Json_error("未设置自己故乡");}
//        $count = M('new_sns')->where(['tag' => 3,'hometown_id' => $user['hometown_id'],'del_state' => 0])->count();
//        $page = new page($count,10);
//        $data = M('new_sns')->where(['tag' => 3,'hometown_id' => $user['hometown_id'],'del_state' => 0])->findall($page->limit());
        $data = $this->readSns($uid,['hometown_id' => $user['hometown_id'],'tag'=>3]);
        return $data;
    }

    /**
     * @Title: zanMessage
     * @Description:todo(生成点赞消息任务)
     * @Author: Kaiqi
     * @param $uid
     * @param $buid
     * @return mixed 返回类型
     */
    private function zanMessage($uid,$sid,$buid,$content){
        return $this->message_model->createMission([$buid],0,[
            'uid' => $uid,
            'type' => 3,
            'data_type' => 302,
            'title' =>'赞了这个帖子',
            'content' => $content,
            'data_id' => $sid
        ]);
    }

    /**
     * @Title: commentMessage
     * @Description:todo(添加评论回复消息)
     * @Author: Kaiqi
     * @param $uid
     * @param $buid
     * @param $data_type
     * @param $title
     * @param $content
     * @return mixed 返回类型
     */
    private function commentMessage($uid,$buid,$data_type,$title,$content,$sid){
        return $this->message_model->createMission([$buid],0,[
            'uid' => $uid,
            'type' => 3,
            'data_type' => $data_type,
            'title' => $title,
            'content' => $content,
            'data_id' => $sid
        ]);
    }

    /**
     * @Title: getSnsAuthor
     * @Description:todo(获取帖子作者)
     * @Author: Kaiqi
     * @param $sid
     * @return mixed 返回类型
     */
    private function getSnsAuthor($sid){
        $sns = M('new_sns')->where(['sid' => $sid])->find();
        return $this->user_model->getUserInfo($sns['uid']);
    }

    /**
     * @Title: violateTime
     * @Description:todo(判断用户当天是否发过美拍)
     * @Author: Kaiqi
     * @param $uid
     * @return bool 返回类型
     */
    public function violateTime($uid){
        $sns = M('new_sns')->where(['tag'=>2,'uid' =>$uid])->order(['create_at'=>'desc'])->find();
        if(empty($sns))return false;
        if(date('Y-m-d',time()) == date('Y-m-d',$sns['create_at'])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @Title: getSnsInfo
     * @Description:todo(获取简单的帖子信息(没有点赞评论))
     * @Author: Kaiqi
     * @param $sid
     * @return  返回类型
     */
    private function getSnsInfo($sid,$type){//点赞1评论2
        $sns = M('new_sns')->where(['sid' => $sid])->field()->find();
        if(!empty($sns['images'])){
            $arr = explode('#',$sns['images']);
            for($x=0;$x<count($arr);$x++) {
                $sns['content'] .= "[图片]";
            }
        }
        return $sns;
    }

    /**
     * @Title: getNearPeople
     * @Description:todo(获取附近的人)
     * @Author: Kaiqi
     * @param bool $ispage
     * @param int $limit
    @return array 返回类型
     */
    public function getNearPeople($ispage = true,$limit = 4){
        $fdata = [];
        $lng = $_POST['lng'];$lat = $_POST['lat'];
        $days_7 = time()-7*86400;
//        $lng = 121.486766;$lat = 31.211933;
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请先登录");
        if(empty($lng)||empty($lat))Json_error("位置信息获取失败");
        $gender = $_POST['gender'];
        $age_low = $_POST['age_low'];
        $age_high = $_POST['age_high'];
        $days_15 = time()-15*86400;
        $active_time = empty($_POST['active_time'])?$days_15:$_POST['active_time'];
        if(!empty($age_low)&&!empty($age_high)){
            $age_low = time() - $age_low*3600*24*365;
            $age_high = time() - $age_high*3600*24*365;
            $con[] = 'birthday > '.$age_high.' and birthday <'.$age_low;
        }
        if(!empty($active_time)){
            $active_time = time() - $active_time;
            $con[] = 'last_login > '.$active_time;
        }
        $squares = returnSquarePoint($lng, $lat);
        if(isset($gender)) {
            if ($gender != 2) {
                $con[] = 'gender=' . $gender;
            }
        }
        $con[] = "lat<>0 and lat>" . $squares['minLat'] . " and lat<" . $squares['maxLat'] . " and lng>" . $squares['minLng'] . " and lng<" . $squares['maxLng'];
        $con[] = 'uid!=' . $uid;
        $con[] = 'is_online =1 and last_login >'.$days_7;
        if($ispage){
            $count = M('user')->where($con)->count();
            $page = new page($count,15);
            $fdata['count_page'] = $page->total_page;
            $result = M('user')->field('uid,avatar,sign,birthday,gender,username,nickname,lng,lat,last_login')->where($con)->findall($page->limit());
        }else {
            $result = M('user')->field('uid,avatar,sign,birthday,gender,username,nickname,lng,lat,last_login')->where($con)->limit($limit)->findall();
        }
        foreach ($result as $key => $value) {
            $attention = M('new_guanzhu')->where(['uid'=>$_SESSION['uid'],'busernname'=>$value['username']])->find();

            if($attention){

                $result[$key]['is_attention'] = 1;
            }else{

                $result[$key]['is_attention'] = 0;
            }
            $result[$key]['distance'] = getDistanceBetweenPoints($value['lat'], $value['lng'], $lat, $lng);
            $result[$key]['distance'] = $result[$key]['distance']['kilometers'];
            if(!empty($result[$key]['birthday'])) {
                $result[$key]['age'] = date('Y',time()) - date('Y',$result[$key]['birthday']);
            }else{
                $result[$key]['age'] = "";
            }
        }
        foreach ($result as $key=>$val){
            $distance[$key] = $val['distance'];
        }
        array_multisort($distance,$result);
        foreach ($result as $key=>$val){
            if($val['distance']<0.5){
                $result[$key]['distance'] = '500m以内';
            }elseif ($val['distance']>0.5&&$val['distance']<1){
                $result[$key]['distance'] = '1km以内';
            }elseif ($val['distance']>1&&$val['distance']<5){
                $result[$key]['distance'] = '5km以内';
            }else{
                $result[$key]['distance'] = $val['distance'].'km';
            }
        }
        if($ispage) {
            $fdata['people'] = $result;
            return $fdata;
        }else {
            return $result;
        }
    }

    /**
     * @Title: getNearGroup
     * @Description: todo(获取附近群)
     * @author nipeiquan
     * @param string $limit
     * @return  array|mixed  返回类型
     */
    public function getNearGroup($limit=''){
        $lng = $_POST['lng'];
        $lat = $_POST['lat'];

        $tags = $_POST['tag'];

        $uid = empty($_SESSION['uid'])?0:$_SESSION['uid'];

        if(empty($lng)||empty($lat))Json_error("位置信息获取失败");
        $squares = returnSquarePoint($lng, $lat);
        $con ="lat<>0 and lat>" . $squares['minLat'] . " and lat<" . $squares['maxLat'] . " and lng>" . $squares['minLng'] . " and lng<" . $squares['maxLng'];

        if(!empty($tags)){

            $tags = '"'.implode('","',explode(',',$tags)).'"';
        }

        if(!empty($tags)){

            $con .= " AND lab_name in($tags)";
        }

        if(!empty($limit)){

            $data = M('group')->where($con)->field('gid,owner,desc,grouper,address,group_id,avatar,lng,lat,lab_name')->limit($limit)->findall();

        }else{

            $result = M('group')->where($con)->field('gid,owner,desc,grouper,address,group_id,avatar,lng,lat,lab_name')->findall();

            $groups = [];

            foreach ($result as $key => $value) {

                $groups[$value['address']][] = $value;//按地址分群

            }

            $data = [];

            foreach ($groups as $k=>$group){

                $distance = getDistanceBetweenPoints($group[0]['lat'], $group[0]['lng'], $lat, $lng);

                $distance = $distance['kilometers'];

                $data[] = [

                    'address'=>$k,
                    'distance'=>$distance,
                    'groups'=>$group
                ];

            }

            foreach ($data as $key=>$items){

                $distances[$key] = $items['distance'];

                foreach ($items['groups'] as $k=>$group){

                    $data[$key]['groups'][$k]['people_num'] = M('group_member')->where(['gid'=>$group['gid']])->count();

                    $exist = M('group_member')->where(['gid'=>$group['gid'],'uid'=>$uid])->find();

                    if($exist){

                        $data[$key]['groups'][$k]['is_join'] = 1;
                    }else{

                        $data[$key]['groups'][$k]['is_join'] = 0;
                    }

                    if($uid == $group['uid']){

                        $data[$key]['groups'][$k]['is_admin'] = 1;
                    }else{

                        $data[$key]['groups'][$k]['is_admin'] = 0;
                    }
                }

            }

            array_multisort($distances,$data);
        }

        return $data;
    }
}