<?php


class userModel extends Model {

    public $user_r_model;
    private $role_model;
    private $user_role_model;
    private $user_temp_model;
    public $user_model;

    function __construct() {
        $this->user_r_model = R('user');
        $this->user_model = M('user');
        $this->user_temp_model = M('user_temp');
        $this->user_r_model->join = array(
            "role" => array(
                "type" => "MANY_TO_MANY", //关联类型多对多关联
                "relation_table_parent_key" => "uid", //主表 user关联字段
                "relation_table_foreign_key" => "rid", //关联表 role关联字段
                "relation_table" => "user_role", //多对多的中间表
                //'where'=>'role.rid=1'
            ),
        );
        $this->role_model = M('role');
        $this->user_role_model = M('user_role');
    }

    function roleList($cond=array(),$field=array()) {
        $role_list = $this->role_model->field($field)->where($cond)->order('sort')->findall();
        return $role_list;
    }

    /**
     * 添加用户
     * @param type $user_data POST数据
     * @return boolean 
     */
    function addUser($user_data = '', $table = 'user') {
        if ($user_data == '') {
            $user_data = $_POST;
        }
        $table.='_model';
        $uid = $this->$table->insert($user_data);
        if ($table == 'user_temp_model' && $uid) {
            return TRUE;
        }
        if ($table == 'user_model' && $uid) {
            $user_role = array();
            $user_role['uid'] = $uid;
            $user_role['rid'] = $user_data['rid'];
            $ur_id = $this->user_role_model->insert($user_role);
            return TRUE;
        }
        return FALSE;
    }
    /**
     * 更新角色信息
     */
    public function updateRole($cond,$data)
    {
        return $this->role_model->in($cond)->update($data)>=0;
    }
    /**
     * 删除角色
     */
    public function delRole($id,$cond=array())
    {
        $db=R('role');
        $db->join=array(
            'access'=>array(
                'type'=>HAS_ONE,
                'foreign_key'=>'rid'
            )
        );
        return $db->where($cond)->in($id)->del();
    }

    /**
     * 添加用户角色
     */
    function addRole($role_data) {
        $rid = $this->role_model->insert($role_data);
        if ($rid) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function userList($cond=array()) {
        if(!empty($cond['group'])){//如果有用户组条件
            $db=V('user');
            $db->view=array(
                'user_role'=>array(
                    'type'=>'inner',
                    'on'=>'user.uid =user_role.uid',
                ),
                'role'=>array(
                    'type'=>'inner',
                    'on'=>'user_role.rid=role.rid'
                )
            );
            $nums=$db->where($cond['user'],$cond['group'])->count();
            $page=new page($nums,12);
            $users=array();
            $users['user']=$db->where($cond['user'],$cond['group'])->findall();
            $users['page']=$page->show();
        }else{
            $nums=$this->user_r_model->where($cond['user'])->count();
            $page=new page($nums,12);
            $users=array();
            $users['user']=$this->user_r_model->where($cond['user'])->order('created desc')->findall($page->limit());
            $users['page']=$page->show();
        }
        return $users;
    }

    /**
     * 判断用户是否存在
     * @param type $username 用户名
     * @return boolean 存在返回TRUE
     */
    function userExist($username) {
        $result = $this->user_model->where(array('username' => $username))->find();
        $result2 = $this->user_temp_model->where(array('username' => $username))->find();
        if ($result || $result2) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 判断Email是否存在
     * @param type $username 用户名
     * @return boolean 存在返回TRUE
     */
    function emailExist($email) {
        $result = $this->user_model->where(array('email' => $email))->find();
        $result2 = $this->user_temp_model->where(array('email' => $email))->find();
        if ($result || $result2) {
            return TRUE;
        }
        return FALSE;
    }
    /**
     * 禁止用户
     * @param mixed $cond 条件
     * @param int $type  禁止类型,1:禁止,0：解禁
     */
    public function banUser($cond,$type=1)
    {
        return $this->user_model->in($cond)->update(array('banned'=>$type))>=0;
    }
    /**
     * 企业资料
     */
    public function companyInfo($uid)
    {
        $db=R('user');
        $db->join = array(
            'company_info'=>array(
                'type'=>'HAS_ONE',
                'foreign_key'=>'uid',
                "other" => TRUE
            ),
            'user_point'=>array(
                'type'=>'HAS_ONE',
                'foreign_key'=>'uid',
                "other" => TRUE
            )
        );
        $userinfo=$db->where('uid='.$uid)->join('company_info|user_point')->find();
        return $userinfo;
    }

    public function updateUserinfo($cond,$data)
    {
        $db=R('user');
        $db->join = array(
            'user_point'=>array(
                'type'=>'HAS_ONE',
                'foreign_key'=>'uid',
                "other" => TRUE
            )
        );
        return $db->where($cond)->join('user_point')->update($data)>=0;
    }
    /**
     * 用户资料
     */
    public function userInfo($uid,$field=array())
    {
        $db=R('user');
        $db->join = array(
            'user_info'=>array(
                'type'=>'HAS_ONE',
                'foreign_key'=>'uid',
                "other" => TRUE
            ),
            'user_point'=>array(
                'type'=>'HAS_ONE',
                'foreign_key'=>'uid',
                "other" => TRUE
            )
        );
        return $db->field($field)->where('uid='.$uid)->join('user_info|user_point')->find();
    }
    public function userInfo_V($uid,$field=array())
    {
        $db=V('user');
        $db->view = array(
            'user_info'=>array(
                'type'=>'INNER',
                'on'=>'user.uid=user_info.uid',
            )
        );
        return $db->field($field)->where('user.uid='.$uid)->find();
    }
    /**
     * 删除用户
     */
    function delUser($id)
    {
        $db=R('user');
        $db->join = array(
            "role" => array(
                "type" => "MANY_TO_MANY", //关联类型多对多关联
                "relation_table_parent_key" => "uid", //主表 user关联字段
                "relation_table_foreign_key" => "rid", //关联表 role关联字段
                "relation_table" => "user_role", //多对多的中间表
                "other" => TRUE
            ),
            'user_info'=>array(
                'type'=>'HAS_ONE',
                'foreign_key'=>'uid'
            ),
            'company_info'=>array(
                'type'=>'HAS_ONE',
                'foreign_key'=>'uid'
            ),
            'user_point'=>array(
                'type'=>'HAS_ONE',
                'foreign_key'=>'uid'
            )
        );
        $db->in($id)->del();
        return TRUE;
    }

    /**
     * @Title: fillInfo
     * @Description: todo(完善资料得积分)
     * @author nipeiquan
     * @return  void  返回类型
     */
   public function fillInfo($uid,$type)
    {
        switch($type){
            case 1://
                $con = '完善资料-头像';
                $count = M('opt_log')->field('time')->where(array('uid' => $uid, 'content' => $con))->count();
                if($count<1){
                    $this->addPointForFillInfo($uid,$con);
                }
                break;
            case 2:
                $con = '完善资料-家乡';
                $count = M('opt_log')->field('time')->where(array('uid' => $uid, 'content' => $con))->count();
                if($count<1){
                    $this->addPointForFillInfo($uid,$con);
                }
                break;
            case 3:
                $con = '完善资料-出生日期';
                $count = M('opt_log')->field('time')->where(array('uid' => $uid, 'content' => $con))->count();
                if($count<1){
                    $this->addPointForFillInfo($uid,$con);
                }
                break;
            case 4:
                $con = '完善资料-性别';
                $count = M('opt_log')->field('time')->where(array('uid' => $uid, 'content' => $con))->count();
                if($count<1){
                    $this->addPointForFillInfo($uid,$con);
                }
                break;
            case 5:
                $con = '完善资料-现居地址';
                $count = M('opt_log')->field('time')->where(array('uid' => $uid, 'content' => $con))->count();
                if($count<1){
                    $this->addPointForFillInfo($uid,$con);
                }
                break;
            case 6:
                $con = '完善资料-昵称';
                $count = M('opt_log')->field('time')->where(array('uid' => $uid, 'content' => $con))->count();
                if($count<1){
                    $this->addPointForFillInfo($uid,$con);
                }
                break;
        }
    }

    private function addPointForFillInfo($uid,$con){
        $_SESSION['point'] = $this->_getUserPoint($uid);
        $user = M('user')->where('uid='.$uid)->find();
        $username = $user['username'];
        $point = getPointRule('fillInfo');//获得应扣取积分
        deductPoint($point);//增加积分
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
        return M('opt_log')->insert($result);
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
     * @Title: changeModelNum
     * @Description: todo(更新userModel数字)
     * @author nipeiquan
     * @param $uid
     * @param $type
     * @param int $num
     * @return  bool|mixed  返回类型
     */
    function changeModelNum($uid,$type,$num=1){//type1关注数2被关注数3帖子数num正数为加负数为减

        $user = M('user')->where(['uid'=>$uid])->find();

        if($type == 1){

            $update = M('user')->where(['uid'=>$uid])->update(['attention_num'=>$user['attention_num'] + $num]);

        }elseif ($type == 2){

            $update = M('user')->where(['uid'=>$uid])->update(['fans_num'=>$user['fans_num'] + $num]);

        }elseif ($type ==3){

            $update = M('user')->where(['uid'=>$uid])->update(['sns_num'=>$user['sns_num'] + $num]);

        }else{

            return false;
        }

        return $update;
    }

    /**
     * @Title: getUserInfo
     * @Description:todo(获取用户信息)
     * @Author: Kaiqi
     * @param $uid
     * @param array $field
     * @return mixed 返回类型
     */
    public function getUserInfo($uid,$field=[]){
        return M('user')->where(['uid' => $uid])->field($field)->find();
    }

    /**
     * @Title: shieldSns
     * @Description: todo(屏蔽帖子)
     * @author nipeiquan
     * @param $sid
     * @param $uid
     * @return  bool  返回类型
     */
    public function shieldSns($sid,$uid){

        $user = M('user')->where(['uid'=>$uid])->find();

        if(empty($user)){

            return false;
        }

        if(!empty($user['not_read_sids'])){

            $ids_arr = explode(',',$user['not_read_sids']);

            if(in_array($sid,$ids_arr)){

                return true;
            }else{

                $not_read_sids = $user['not_read_sids'].','.$sid;

            }
        }else{

            $not_read_sids = $sid;
        }

        $update = M('user')->where(['uid'=>$uid])->update(['not_read_sids'=>$not_read_sids]);

        if($update){

            return true;
        }else{

            return false;
        }
    }

}