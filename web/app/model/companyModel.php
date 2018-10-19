<?php

class companyModel extends Model {

    public $company;
    public $recruit;
    public $interview;
    public $linkage;

    function __construct() {
        $this->company = M('company_info');
        $this->recruit = M('recruit');
        $this->interview = M('interview');
        $this->linkage = M('linkage');
    }


    /**
     * 通过企业ID取得公司资料
     * @param type $condition 查找的条件,可以为企业ID，企业用户名，企业名称
     * @return array 企业资料return '你好';
     */
    function getCompanyDataById($uid = '', $field = '') {
        if (empty($uid)) {
            $uid = $_SESSION['uid'];
        }
        $data = $this->company->field($field)->where('uid=' . $uid)->find();
        return $data;
    }

    /**
     * 更新企业资料
     * @param type $data  更新资料
     * @param type $condition 更新条件
     */
    function updateCompanyData($data, $condition) {
        $result = $this->company->where($condition)->update($data);
        return $result;
    }

    /**
     * 获取企业发布的最新招聘列表
     * @param mixed $condition 查询条件
     * @param int $limit 数目
     * @param string $order 排序
     * @param string $field 字段
     * @return array 招聘信息列表
     */
    function newRecruit($condition = array(), $limit = 5, $order = 'start_time desc', $field = 'recruit_id,recruit_name,recruit_num,start_time,expiration_time,effective_time,verify') {
        if (empty($condition)) {
            $condition = array(
                'uid' => $_SESSION['uid']
            );
        }
        $data = $this->recruit->field($field)->where($condition)->order($order)->limit($limit)->findall();
        return $data;
    }

    /**
     * @Title: recruitList
     * @Description: todo(得到职位列表)
     * @author nipeiquan
     * @param $jobs_industry
     * @param int $page_nums
     * @param string $order
     * @param string $field
     * @return  mixed  返回类型
     */
    function recruitList($where,$page_nums = 20,$order = 'istop desc,start_time desc',$field = 'recruit_id,return_money,graduates,town,jobs_property,recruit_name,recruit_num,address,city,uid,return_money,start_time,istop,company_name,welfare,work_exp,degree,salary,star,check,verify')
    {
        $db = R('recruit');
        $nums = $this->recruit->where($where)->count();
        $page = new page($nums, $page_nums);
        $data['result'] = $db->field($field)->where($where)->order($order)->findall($page->limit());
        foreach($data['result'] as $key=>$val){

            if(strpos($val['welfare'],'948')!=false){
                $data['result'][$key]['fan']=1;
            }else{
                $data['result'][$key]['fan']=0;
            }
        }

        $data['count_page'] = $page->total_page;
        $data['cur_page'] = $page->self_page;
        return $data;
    }

    /**
     * 获取企业发布招聘的数目
     * @param type $cond 查询条件
     */
    function recruitNums($cond = array()) {
        if (empty($cond)) {
            $cond = array('uid' => $_SESSION['uid']);
        }
        return $this->recruit->where($cond)->count();
    }
    /**
     * 收到的职位申请数目
     */
    public function receiveNums($cond=array())
    {
        if (empty($cond)) {
            $cond = 'company_id='.$_SESSION['uid'];
        }
        return $this->interview->where($cond)->count();//收到的职位申请数目
    }
    /**
     * 企业操作日志
     */
    public function optLog($cond)
    {
        $db=M('opt_log');
        $nums=$db->where($cond)->count();
        $page=new page($nums,15);
        $logs=array();
        $logs['log']=$db->where($cond)->order('created desc')->findall($page->limit());
        $logs['page']=$page->show();
        return $logs;
    }
    function recruitItem($cond=array(),$field='recruit_name,recruit_id'){
        if(empty($cond)){
            $cond=array(
                'uid' => $_SESSION['uid'],
                'expiration_time'=>array(
                    'gt'=>time()
                ),
            );
        }
        return $this->recruit->field($field)->where($cond)->order('refresh_date DESC,created DESC')->findall();
    }

    /**
     * @Title: getRecruitInfo
     * @Description: todo(得到职位详情)
     * @author nipeiquan
     * @param string $recruit_id
     * @param string $field
     * @return  mixed  返回类型
     */

    function getRecruitInfo($recruit_id = '', $field = '') {
        $data = $this->recruit->field($field)->where('recruit_id=' . $recruit_id)->find();
        return $data;
    }
    /**
     * @Title: getRecruitInfo
     * @Description: todo(得到该公司其他职位)
     * @author nipeiquan
     * @param string $recruit_id
     * @param string $field
     * @return  mixed  返回类型
     */
    public function getCompanyRecruits($uid = '',$field = ''){
        $data = $this->recruit->field($field)->where('uid=' . $uid . ' AND expiration_time > '.time().' AND verify=1')->findall();
        return $data;
    }

    /**
     * @Title: insertComment
     * @Description:todo(插入职位评论)
     * @Author: Kaiqi
     * @param $data
     * @return bool|mixed 返回类型
     */
    public function insertComment($data){
        $user = K('user')->getUserInfo($data['uid'],['avatar','nickname']);
        $recruit = $this->recruit->where(['recruit_id'=>$data['recruit_id']])->field(['recruit_name','company_name'])->find();
        if(empty($recruit))Json_error("不存在的职位");
        $data['uvatar'] = $user['avatar'];
        $data['unickname']=$user['nickname'];
        $data['recruit_name']=$recruit['recruit_name'];
        $data['company_name']=$recruit['company_name'];
        $data['create_at']=time();
        $data['state'] = 0;
        return M('new_recruit_comment')->insert($data);
    }

    /**
     * @Title: getComment
     * @Description:todo(获取职位评论)
     * @Author: Kaiqi
     * @param int $company_id
     * @param int $recruit_id
     * @param bool $ispage
     * @return bool|mixed 返回类型
     */
    public function getComment($company_id=0,$recruit_id=0,$ispage=true,$limit=5){
        if($company_id==0&&$recruit_id==0)return false;
        $sql[] = "state != 0";
        if($company_id)$sql['company_id']=$company_id;
        if($recruit_id)$sql['recruit_id']=$recruit_id;
        $sql['bcid'] = 0;
        if($ispage) {
            $count = M('new_recruit_comment')->where($sql)->count();
            $page = new page($count,10);
            $data = M('new_recruit_comment')->where($sql)->findall($page->limit());
        }else{
            $data = M('new_recruit_comment')->where($sql)->limit($limit)->findall();
        }
        foreach ($data as $k => $v){
            $comment = M("new_recruit_comment")->where(['bcid'=>$data[$k]['recruit_comment_id']])->find();
            if(!empty($comment)) {
                $data[$k]['reply'] = [
                    'name' => "开心客服",
                    'uvatar' => "http://120.55.165.117/uploads/logo.png",
                    'content' => $comment['content'],
                    'time' => time()
                ];
            }else{
                $data[$k]['reply']=(object)null;
            }
        }
        if($ispage){
            $result['count_page'] = $page->total_page;
            $result['comment'] = $data;
            return $result;
        }else{
            return $data;
        }
    }

    /**
     * @Title: delComment
     * @Description:todo(删除职位评论)
     * @Author: Kaiqi
     * @param $uid
     * @param $recruit_comment_id
     * @return mixed 返回类型
     */
    public function delComment($uid,$recruit_comment_id){
        return M("new_recruit_comment")->where(['uid'=>$uid,"recruit_comment_id"=>$recruit_comment_id])->del();
    }
}

