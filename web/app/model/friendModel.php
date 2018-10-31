<?php

/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/3/21
 * Time: 13:45
 */
class friendModel extends Model{
    private $guanzhu;
    private $user;
    private $myfriends;
    private $myfield;
    function __construct(){
        $this->guanzhu = M('new_guanzhu');
        $this->user = M('user');
        $this->myfield = ['uid','username','phone','nickname','birthday','gender','avatar','sign'];
    }

    /**
     * @Title: getFriends
     * @Description:todo(获取自己的朋友)
     * @Author: Kaiqi
     * @param $uid
     * @param array $field
     * @return mixed 返回类型
     */
    public function getFriends($uid,$field =[],$ispage=false){
        $fdata = [];
        $fids = $this->guanzhu->where(['uid' => $uid])->field(['buid'])->findall();
        if(empty($fids))return false;
        $sql = "";
        foreach ($fids as $fid){
            $sql.=','.$fid['buid'];
        }
        $sql = trim($sql,',');
        $con[] = 'uid in ('.$sql.")";
        if($ispage){
            $count = M('user')->where($con)->count();
            $page = new page($count,10);
            $fdata['count_page']=$page->total_page;
            $friends = M('user')->where($con)->field($this->myfield )->findall($page->limit());
        }else {
            $friends = M('user')->where($con)->field($this->myfield )->findall();
        }
        $notices = $this->guanzhu->where(['buid'=>$uid])->findall();
        for($i=0;$i<count($friends);$i++){
            for($j=0;$j<count($notices);$j++){
                if($friends[$i]['uid']==$notices[$j]['uid']){
                    $friends[$i]['isconcern'] = true;
                }
            }
            if(empty($friends[$i]['isconcern'])){
                $friends[$i]['isconcern'] = false;
            }
            if(!empty($friends[$i]['birthday'])) {
                $friends[$i]['age'] = date('Y', time()) - date('Y', $friends[$i]['birthday']);
            }else{
                $friends[$i]['age'] = "";
            }
        }
        if($ispage){
            $fdata['friend'] = $friends;
            return $fdata;
        }else {
            return $friends;
        }
    }

    /**
     * @Title: getRecommendFriends
     * @Description:todo(获取推荐的朋友)
     * @Author: Kaiqi
     * @param $uid
     * @param int $limit
     * @return array 返回类型
     */
    public function getRecommendFriends($uid,$limit = 3){
       $user = K('user')->getUserInfo($uid,['gender','hometown_id','now_address_id']);
       if(empty($user))Json_error("用户不存在");
       if($user['gender'] == 1)$user['gender'] = 0;
       else if($user['gender'] == 0)$user['gender'] =1;
       if(empty($this->myfriends)){
           $state = $this->getFriends($uid,['uid']);
           if($state!=false)
           $this->myfriends = $state;
       }
       $sql = "";
       if(!empty($this->myfriends)) {
           foreach ($this->myfriends as $friend) {
               $sql .= $friend['uid'] . ',';
           }
       }
       $sql = trim($sql,',');

//        $time = time()-864000;//10天内登陆的
//        $con[] = 'last_login >'.$time;
       if(!empty($sql)){
           $con[] = "uid not in (" . $sql . ")";
       }
       $con[]="gender = ".$user['gender'];

       if(!empty($user['company_name'])){
           $con["company_name"] = $user['company_name'];
       }

       $field = ['uid','username','phone','nickname','birthday','gender','avatar','sign'];
       $result = M('user')->where($con)->order(['created'=>"desc"])->field($field)->limit(500)->findall();//->findall();

       if(count($result)<3){
           unset($con["company_name"]);
           if(!empty($user['now_address_id'])) {
               $con['now_address_id'] = $user["now_address_id"];
           }
           $result = M('user')->where($con)->order(['created'=>"desc"])->field($field)->limit(500)->findall();//->findall();
           if(count($result)<3){
               unset($con['now_address_id']);
               $result = M('user')->where($con)->order(['created'=>"desc"])->field($field)->limit(500)->findall();//->findall();
           }
       }

       $temp = [];
       $data = [];
       for($i=0;$i<$limit;$i++){
           while(true) {
               $rand = rand(0, 499);
               if(empty($temp[$rand])){
                  $temp[$rand] = 1;
                  break;
               }
           }
           if(!empty($result[$rand]['hometown_id'])&&!empty($user['hometown_id'])){
               if($result[$rand]['hometown_id']==$user['hometown_id']){
                   $result[$rand]['sametown'] = true;
               }else{
                   $result[$rand]['sametown'] = false;
               }
           }else{
               $result[$rand]['sametown'] = false;
           }
           if(!empty($result[$rand]['birthday'])){
               $result[$rand]['age'] = date('Y',time())-date('Y',$result[$rand]['birthday']);
           }else{
               $result[$rand]['birthday'] = 0;
               $result[$rand]['age'] = "";
           }
           array_push($data,$result[$rand]);
        }
       return $data;
    }

    /**
     * @Title: getNoticeFriend
     * @Description:todo(获取关注自己的人)
     * @Author: Kaiqi
     * @param $uid
     * @param bool $ispage
     * @return null 返回类型
     */
    public function getNoticeFriend($uid,$ispage = true){
        if(empty($this->myfriends)){
            $this->myfriends = $this->getFriends($uid);
        }
        $con['buid'] = $uid;
        $notices = $this->guanzhu->where($con)->order(['create_at'=>'desc'])->field(['uid'])->findall();
        $sql = "";
        foreach ($notices as $notice){
            $sql.=$notice['uid'].',';
        }
        $sql = trim($sql,',');
        unset($con);
        $con[] = 'uid in ('.$sql.')';
        $field = ['uid','username','nickname','birthday','gender','avatar','sign'];
        if(!empty($sql)) {
            if ($ispage) {
                $count = count($notices);
                $page = new page($count, 10);
                $fdata['count_page'] = $page->total_page;
                $nfriends = $this->user->where($con)->field($field)->findall($page->limit());
            } else {
                $nfriends = $this->user->where($con)->field($field)->limit(3)->findall();
            }
        }else{
            $nfriends = [];
        }
        $this->checkConcernAndAge($nfriends);
//        for($i=0;$i<count($nfriends);$i++){
//           for($j=0;$j<count($this->myfriends);$j++){
//                if($this->myfriends[$j]['buid']==$nfriends[$i]['uid']){
//                    $nfriends[$i]['isconcern'] = true;
//                }
//           }
//           if(empty($nfriends[$i]['isconcern'])){
//               $nfriends[$i]['isconcern'] = false;
//           }
//           if(!empty($nfriends['$i']['birthday'])) {
//               $nfriends[$i]['age'] = date('Y', time()) - date('Y', $nfriends[$i]['birthday']);
//           }else{
//               $nfriends[$i]['age'] = "";
//           }
//        }
        if($ispage){
            $fdata['friend'] = $nfriends;
            return $fdata;
        }else {
            return $nfriends;
        }
    }

    /**
     * @Title: getNewFriends
     * @Description:todo(获取新朋友)
     * @Author: Kaiqi
     * @param $uid
     * @param $ban_uid
     * @return mixed 返回类型
     */
    public function getNewFriends($uid){
        $this->myfriends = $this->getFriends($uid);
        $notice = $this->getNoticeFriend($uid,false);//获取关注我的人
        $recommend = $this->getRecommendFriends($uid);//获取推荐的人
        $data = [
            'notice' => $notice,
            'recommend' =>$recommend
        ];
        return $data;
    }

    /**
     * @Title: checkConcernAndAge
     * @Description:todo(是否关注和添加年龄)
     * @Author: Kaiqi
     * @param $nfriends
     * @return void 返回类型
     */
    private function checkConcernAndAge(&$nfriends){

        for($i=0;$i<count($nfriends);$i++){
            for($j=0;$j<count($this->myfriends);$j++){
                if($this->myfriends[$j]['uid']==$nfriends[$i]['uid']){
                    $nfriends[$i]['isconcern'] = true;
                }
            }
            if(empty($nfriends[$i]['isconcern'])){
                $nfriends[$i]['isconcern'] = false;
            }
            if(!empty($nfriends[$i]['birthday'])) {
                $nfriends[$i]['age'] = date('Y', time()) - date('Y', $nfriends[$i]['birthday']);
            }else{
                $nfriends[$i]['age'] = "";
            }
        }
    }

    /**
     * @Title: searchFriends
     * @Description:todo(查找朋友)
     * @Author: Kaiqi
     * @param $uid
     * @param $search_word
     * @return null 返回类型
     */
    public function searchFriends($uid,$search_word,$tag){
        if(empty($search_word))return [
            'count_page' => 0,
            'friend' =>[]
        ];
//        $uid = $_SESSION['uid'];
        $user = K('user')->getUserInfo($uid,['hometown_id']);
        $con[] = 'nickname like "%'.$search_word.'%" or username like "%'.$search_word.'%"';
        if($tag == 2){
//            $sql[] = 'name = "'.$search_word .'" or pinyin = "'.$search_word.'"';
//            $city = M("city")->where($sql)->find();
//            $con['hometown_id'] = $city['id'];
              $con[0] .= 'or hometown like "%'.$search_word.'%"';
        }
        if($tag == 3){
            $con[0] .= 'or company_name like "%'.$search_word.'%"';
        }
        $count = $this->user->where($con)->count();
        $page = new page($count,10);
        $field = $this->myfield;
        $friends = $this->user->where($con)->field($field)->findall($page->limit());
        $this->myfriends = $this->getFriends($uid);

        $this->checkConcernAndAge($friends);
        $fdata['count_page'] = $page->total_page;
        $fdata['friend'] = $friends;
        return $fdata;
    }

    /**
     * @Title: getSameTown
     * @Description:todo(获取老乡推荐)
     * @Author: Kaiqi
     * @param $uid
     * @param int $limit
     * @return array 返回类型
     */
    public function getSameTown($uid,$hometown = null,$limit=3){

        if(empty($hometown)) {
            $user = K('user')->getUserInfo($uid, ['hometown_id']);
            if(empty($user['hometown_id']))return [];
            $hometown_id = $user['hometown_id'];
            $hometown = M('city')->where(['id'=> $user['hometown_id']])->find();
            $hometown = $hometown['name'];
        }else{
            $sql1[] = "pid > 0 ";
            $sql1[] = "pid < 39";
            $sql1[] = "name = '".$hometown."'";
            $temps= M('city')->where($sql1)->find();
            $hometown_id =$temps['id'];
        }
        $friends = $this->guanzhu->field(['buid'])->where(['uid'=>$uid])->findall();
        $sql = "";
        foreach ($friends as $friend){
            $sql.=$friend['buid'].',';
        }
        $sql = trim($sql,',');
        if(!empty($sql)) {
            $con[] = 'uid not in (' . $sql . ")";
        }
        $con[]='uid !='.$uid;
        $con[]='hometown_id = '.$hometown_id;

        $field = $this->myfield;
        $result =  $this->user->where($con)->field($field)->limit(200)->findall();

        if(empty($result)){
            $final_ans = [];
            $final_ans['ans'] = [];
            $final_ans['hometown_name'] = $hometown;
            return $final_ans;
        }

        $arr =[];
        $ans =[];
        $count = count($result);
        $max_arg = max($limit,min($count,199));
        for($i=0;$i<$limit;$i++){
            while (true) {
                $temp = rand(0, $max_arg-1);
                if (empty($arr[$temp])) {
                    $arr[$temp] = 1;
                    break;
                }
            }
            if(!empty($result[$temp])) {
                if(!empty($result[$temp]['birthday'])) {
                    $result[$temp]['age'] = date('Y', time()) - date('Y', $result[$temp]['birthday']);
                }else{
                    $result[$temp]['birthday'] = 0;
                    $result[$temp]['age'] = "";
                }
                array_push($ans, $result[$temp]);
            }
        }
        $final_ans['ans'] = $ans;
        $final_ans['hometown_name'] = $hometown;
        return $final_ans;
    }

    /**
     * @Title: allowJoinGroup
     * @Description:todo(批准入群)
     * @Author: Kaiqi
     * @param $gid
     * @param $huanxinId
     * @return bool 返回类型
     */
    public function allowJoinGroup($gid,$huanxinId){
        $group = K('public')->getGroupInfo($gid,['group_id','owner']);
        $sql[] = "username in (".$huanxinId.") or phone in (".$huanxinId.")";
//        $user = M('user')->where('username ='.$huanxinId.' or phone = '.$huanxinId)->find();
        $users = M("user")->where($sql)->findall();
        if(empty($users)){

            return false;
        }
        $state = 1;
        foreach ($users as $user){
            $data = [
                'gid' => $gid,
                'group_id'=>$group['group_id'],
                'grouper'=>$group['owner'],
                'uid' => $user['uid'],
                'username'=>$user['username'],
                'nickname'=>$user['nickname'],
                'avatar' =>$user['avatar'],
                'is_admin'=>0,
                'user_huanxin'=>empty($user['phone'])?$user['username']:$user['phone'],
                'created'=>time()
            ];

            if(!M('group_member')->insert($data)){
                $state = 0;
            }
        }

        if($state > 0) return true;
        else return false;
    }

    /**
     * @Title: checkAuth
     * @Description:todo(判断用户是否有批准入群和删除成员的权限)
     * @Author: Kaiqi
     * @param $gid
     * @param $uid
     * @return bool 返回类型
     */
    public function checkAuth($gid,$uid){
        $group = K('public')->getGroupInfo($gid);
        if($group['uid'] == $uid)return true;
        return false;
    }

    /**
     * @Title: deleteGroupMember
     * @Description:todo(删除群成员)
     * @Author: Kaiqi
     * @param $gid
     * @param $uid
     * @return mixed 返回类型
     */
    public function deleteGroupMember($gid,$uid){
        $state = M("group_member")->where('gid ='.$gid.' AND uid in('.$uid.')')->delall();
        return $state;
    }

    /**
     * @Title: findSameCompany
     * @Description:todo(找同事)
     * @Author: Kaiqi
     * @param $uid
     * @param $company
     * @return mixed 返回类型
     */
    public function findSameCompany($uid,$company){
        $con[] = 'company_name like "%'.$company.'%"';
        $count = $this->user->where($con)->count();
        $page = new page($count,10);
        $field = $this->myfield;
        $friends = $this->user->where($con)->field($field)->findall($page->limit());
        $this->myfriends = $this->getFriends($uid);
        $this->checkConcernAndAge($friends);
        $fdata['count_page'] = $page->total_page;
        $fdata['friend'] = $friends;
        return $fdata;
    }

    /**
     * @Title: getCompanyFriendRecommend
     * @Description:todo(同事朋友推荐)
     * @Author: Kaiqi
     * @return mixed 返回类型
     */
    public function getCompanyFriendRecommend($uid,$limit=3){
        $user = K('user')->getUserInfo($uid,['company_name']);
        if(empty($user['company_name']))return [];
        $friends = $this->guanzhu->field(['buid'])->where(['uid'=>$uid])->findall();
        $sql = "";
        foreach ($friends as $friend){
            $sql.=$friend['buid'].',';
        }
        $sql = trim($sql,',');
        if(!empty($sql)) {
            $con[] = 'uid not in (' . $sql . ")";
        }
        $con[]='uid !='.$uid;
        $con[]='company_name like "%'.$user['company_name'] .'%"';
        $field = $this->myfield;
        $result =  $this->user->where($con)->field($field)->limit(200)->findall();
        $arr =[];
        $ans =[];
        $count = count($result);
        $max_arg = max($limit,min($count,199));
        for($i=0;$i<$limit;$i++){
            while (true) {
                $temp = rand(0, $max_arg-1);
                if (empty($arr[$temp])) {
                    $arr[$temp] = 1;
                    break;
                }
            }
            if(!empty($result[$temp])) {
                if(!empty($result[$temp]['birthday'])) {
                    $result[$temp]['age'] = date('Y', time()) - date('Y', $result[$temp]['birthday']);
                }else{
                    $result[$temp]['birthday'] = 0;
                    $result[$temp]['age'] = "";
                }
                array_push($ans, $result[$temp]);
            }
        }
        $final_ans['ans'] = $ans;
        $final_ans['company'] = $user['company_name'];
        return $final_ans;
    }
}