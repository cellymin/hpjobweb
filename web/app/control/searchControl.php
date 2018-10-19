<?php
class searchControl extends Control {

    private $company;
    private $recruit;

    function __construct()
    {
        parent::__construct();
        $this->company = K('company');
        $this->recruit = K('recruit');
    }

    /**
     * @Title: getLinkage
     * @Description: todo(得到职位柄)
     * @author nipeiquan
     * @return  void  返回类型
     */

    public function getLinkage(){
        $linkage = M('linkage')->where('lcgid=4 and pid = 0 ')->field('laid,title')->findall();
        foreach($linkage as $key=>$value){
            $linkage[$key]['child'] = M('linkage')->where('pid=' . $value['laid'])->field('laid,title')->order('sort desc')->findall();
        }
        if(!empty($linkage)){
            Json_success('获取成功',$linkage);
        }else{
            Json_success('没有数据');
        }
    }

    /**
     * @Title: recruit
     * @Description: todo(职位列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function recruit() {
        $class = $_POST['laid'];
        $address = $_POST['address'];
        if(!empty($address)){
            $db = M('city');
            $lists = $db->where('name LIKE "%' . $address . '%"')->find();
            $id = $lists['id'];
            $where[] ='(address = ' . $id .' or city = '. $id.' or town='.$id.')';
        }

        $where[] = 'class_two=' . $class . ' AND expiration_time > '.time().' AND `check`=1';
        $result = $this->company->recruitList($where);
        if(empty($result['count_page'])){
            Json_error('该分类下暂无职位');
        }
        $recruits = $result['result'];
        $data = new data();

        foreach ($recruits as $key =>$value){
            $recruits[$key] = $data->convert($value);
            $recruits[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $result['result'] = $recruits;

        Json_success('获取成功',$result);
    }

    /**
     * @Title: hot
     * @Description: todo(热门职位分类)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function hot(){
        $db = M('linkage');
        $linkage = $db->where('ishot=1')->field('laid,title,img')->order('hot_time desc')->findall();
        if(!empty($linkage)){
            Json_success('获取成功',$linkage);
        }else{
            Json_success('没有数据');
        }
    }

    /**
     * @Title: hotRecruit
     * @Description: todo(热门职位列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function hotRecruit() {
        $class = $_POST['class'];
        $address = $_POST['address'];
        if(!empty($address)){
            $db = M('city');
            $lists = $db->where('name LIKE "%' . $address . '%"')->find();
            $id = $lists['id'];
            $where[] ='(address = ' . $id .' or city = '. $id.' or town='.$id.')';
        }

        $where[] = 'class=' . $class . ' AND expiration_time > '.time().' AND `check`=1';
        $where[] = 'state=1';
        $result = $this->company->recruitList($where);
        if($result['count_page']==0){
            Json_error('该分类下暂无职位');
        }
        $recruits = $result['result'];
        $data = new data();

        foreach ($recruits as $key =>$value){
            $recruits[$key] = $data->convert($value);
            $recruits[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $result['result'] = $recruits;

        Json_success('获取成功',$result);
    }

    /**
     * @Title: recruitInfo
     * @Description: todo(职位详情/公司详情)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function recruitInfo(){
        $recruit_id = $_POST['recruit_id'];
        $recruit = M('recruit')->where('recruit_id=' . $recruit_id)->find();
        $uid = $recruit['uid'];
        $this->recruit->incViews('recruit_id=' . $recruit_id);
        $company = $this->company->getCompanyDataById($uid,'uid,name,company_industry,company_property,logo,company_scope,desc,street,logo,lat,lng,star,relieved');
        $recruit = $this->company->getRecruitInfo($recruit_id,'recruit_id,verify,work_time,rece_mail,recruit_name,salary,start_time,looks,address,city,town,company_name,recruit_num,degree,work_exp,jobs_property,welfare,return_money,job_desc,contact,phone,company_tel');
        if(!empty($_SESSION['uid'])){
            $favorite = M('favorite')->where('uid=' . $_SESSION['uid'] . ' AND recruit_id=' . $recruit_id)->find();
            if(!empty($favorite)){
                $type = $favorite['type'];
            }else{
                $type = 0;
            }
        }else{
            $type = 0;
        }
        $info = array(
            'company'=>$company,
            'recruit'=>$recruit
        );
        $data = new data();
        foreach ($info as $key => $value) {
            $info[$key] = $data->convert($value);
            $info['recruit']['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $welfare = $info['recruit']['welfare'];
        $welfare = explode('-',$welfare);
        $welfare = TrimArray($welfare);
        $re = in_array('入职返现',$welfare);
        if($re){
            $key = array_search('入职返现',$welfare);
//            $welfare[$key]='<html><body><font color=red>入职返现' . $recruit['return_money'] . '元</font></body></html>';
            $welfare[$key]='入职返现' . $recruit['return_money'] . '元';
            $welfare = implode('-',$welfare);
            $info['recruit']['welfare'] = $welfare;
        }
        $info['type'] = $type;
        //职位评论(author:kaiqi)
        $info['comment'] = $this->company->getComment(0,$recruit_id,false);
        if(!empty($info)){
            Json_success('获取成功',$info);
        }else{
            Json_error('获取失败');
        }
    }

    /**
     * @Title: getOthers
     * @Description: todo(得到公司其他职位列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getOthers(){
        $uid = $_POST['uid'];
        $db = M('recruit');
        $count = $db->where('uid=' . $uid . ' AND expiration_time > '.time().' AND verify=1')->count();
        $page = new page($count,20);
        $others = $db->where('uid=' . $uid . ' AND expiration_time > '.time().' AND verify=1 AND state=1')->field('recruit_id,return_money,jobs_property,recruit_name,recruit_num,address,city,town,uid,return_money,start_time,istop,company_name,welfare,work_exp,degree,salary,star,verify')->findall($page->limit());
        $data = new data();
        foreach ($others as $key =>$value){
            $others[$key] = $data->convert($value);
            $others[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $others = $data->convert($others);
        $result = array(
            'count_page'=>$page->total_page,
            'others'=>$others
        );
        if(!empty($result)){
            Json_success('获取成功',$result);
        }else{
            Json_success('没有其他职位了');
        }
    }

    /**
     * @Title: schoolJob
     * @Description: todo(校园招聘)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function schoolJob(){
        $address = $_POST['address'];
        if(!empty($address)){
            $db = M('city');
            $lists = $db->where('name LIKE "%' . $address . '%"')->find();
            $id = $lists['id'];
            $where[] ='(address = ' . $id .' or city = '. $id.' or town='.$id.')';
        }
        $where[] = 'graduates=1 AND expiration_time > '.time().' AND `check`=1';
        $where[] = 'state=1';
        $result = $this->company->recruitList($where);
        $recruits = $result['result'];

        $data = new data();

        foreach ($recruits as $key =>$value){
            $recruits[$key] = $data->convert($value);
            $recruits[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $result['result'] = $recruits;
        if(!empty($recruits)){
            Json_success('获取成功',$result);
        }else{
            Json_error('暂时没有校园招聘');
        }
    }

    /**
     * @Title: partTimeJob
     * @Description: todo(兼职工作)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function partTimeJob(){
        $address = $_POST['address'];
        if(!empty($address)){
            $db = M('city');
            $lists = $db->where('name LIKE "%' . $address . '%"')->find();
            $id = $lists['id'];
            $where[] ='(address = ' . $id .' or city = '. $id.' or town='.$id.')';
        }
        $where[] = 'jobs_property =1 AND expiration_time > '.time().' AND `check`=1';
        $where[] = 'state=1';
        $result = $this->company->recruitList($where);
        $recruits = $result['result'];
        $data = new data();

        foreach ($recruits as $key =>$value){
            $recruits[$key] = $data->convert($value);
            $recruits[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $result['result'] = $recruits;
        if(!empty($recruits)){
            Json_success('获取成功',$result);
        }else{
            Json_error('暂时没有兼职工作');
        }
    }

    /**
     * @Title: eatJob
     * @Description: todo(包吃住职位)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function eatJob(){
        $address = $_POST['address'];
        if(!empty($address)){
            $db = M('city');
            $result = $db->where('name LIKE "%' . $address . '%"')->find();
            $address = $result['id'];
            $where[] ='(address = ' . $address .' or city = '. $address.' or town='.$address.')';
        }
        $where[] = "welfare like '%945%' AND expiration_time > ".time().' AND `check`=1';
        $where[] = 'state=1';
        $result = $this->company->recruitList($where);
        $recruits = $result['result'];
        $data = new data();

        foreach ($recruits as $key =>$value){
            $recruits[$key] = $data->convert($value);
            $recruits[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $result['result'] = $recruits;
        if(!empty($recruits)){
            Json_success('获取成功',$result);
        }else{
            Json_error('暂时没有包吃住工作');
        }
    }

    /**
     * @Title: returnMoney
     * @Description: todo(入职返现)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function returnMoney(){
        $address = $_POST['address'];
        if(!empty($address)){
            $db = M('city');
            $result = $db->where('name LIKE "%' . $address . '%"')->find();
            $address = $result['id'];
            $where[] ='(address = ' . $address .' or city = '. $address.' or town='.$address.')';
        }
        $where[] = "welfare like '%948%' AND expiration_time > ".time().' AND `check`=1';
        $where[] = 'state=1';
        $result = $this->company->recruitList($where);
        $recruits = $result['result'];
        $data = new data();

        foreach ($recruits as $key =>$value){
            $recruits[$key] = $data->convert($value);
            $recruits[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $result['result'] = $recruits;
        if(!empty($recruits)){
            Json_success('获取成功',$result);
        }else{
            Json_error('暂时没有入职返现工作');
        }
    }
    /**
     * @Title: nearJobs
     * @Description: todo(附近的工作)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function nearJobs(){
        $lng = $_POST['lng'];
        $lat = $_POST['lat'];
        $squares = returnSquarePoint($lng, $lat,10);
        $con = "lat<>0 and lat>".$squares['minLat']." and lat<".$squares['maxLat']." and lng>".$squares['minLng']." and lng<".$squares['maxLng'];
        $company_id = M('company_info')->field('uid')->where($con)->findall();
        if(!empty($company_id)){
            $con = '(';
            foreach ($company_id as $key => $value) {
                $con.=$value['uid'].',';
            }
            $con = rtrim($con,',');
            $con.=")";
            $condition[]= 'uid in '.$con.' AND state=1';
            $condition[]= 'expiration_time > '.time();
        }else{
            Json_error('附近暂时没有工作');
        }
        $where = $condition ;
        $result = $this->company->recruitList($where);
        $recruits = $result['result'];
        if($recruits){
            $distance = array();
            foreach ($recruits as $key=>$recruit){

                $company  = M('company_info')->field('lng,lat')->where(array('uid'=>$recruit['uid']))->find();

                $distances = getDistanceBetweenPoints($lat, $lng, $company['lat'], $company['lng']);

                $recruits[$key]['distance'] = $distances['kilometers'];
            }

            foreach ($recruits as $key=>$recruit){

                $distance[$key] = $recruit['distance'];
            }
            array_multisort($distance,$recruits);
        }
        $data = new data();

        foreach ($recruits as $key =>$value){
            $recruits[$key] = $data->convert($value);
            $recruits[$key]['start_time'] = date('Y-m-d',$value['start_time']);

        }


        $result['result'] = $recruits;
        if(!empty($recruits)){
            Json_success('获取成功',$result);
        }else{
            Json_error('附近暂时没有工作');
        }

    }
    /**
     * @Title: relievedCompany
     * @Description: todo(放心企业)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function relievedCompany(){
        $db=V('recruit');
        $db->view=array(
            'company_info'=>array(
                'type'=>'inner',
                'on'=>'company_info.uid=recruit.uid'
            )
        );
        $address = $_POST['address'];
        if(!empty($address)){
            $result = M('city')->where('name LIKE "%' . $address . '%"')->find();
            $address = $result['id'];
            $where[] ='( recruit.address = ' . $address .' or recruit.city = '. $address.' or recruit.town='.$address.')';
        }
        $where[]= "company_info.relieved=1 AND expiration_time > ".time().' AND `check`=1';
        $where[] = 'state=1';
        $count = $db->where($where)->count();
        $page = new page($count,20);
        $result = $db->where($where)->field('recruit.recruit_id,recruit.welfare,recruit.return_money,recruit.recruit_name,recruit.recruit_num,recruit.address,recruit.city,recruit.town,recruit.uid,recruit.return_money,recruit.start_time,recruit.istop,recruit.company_name,recruit.work_exp,recruit.degree,recruit.salary,recruit.star,recruit.verify')->order('istop DESC,start_time DESC')->findall($page->limit());
        $data = new data();
        foreach ($result as $key =>$value){
            if(strpos($value['welfare'],'948')!=false){
                $value['fan']=1;
            }else{
                $value['fan']=0;
            }
            $result[$key] = $data->convert($value);
            $result[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $result = array(
            'result'=>$result,
            'count_page'=>$page->total_page
        );
        if(!empty($result)){
            Json_success('获取成功',$result);
        }else{
            Json_error('该分类下没有放心企业职位');
        }

    }

    /**
     * @Title: highSalary
     * @Description: todo(高薪职位)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function highSalary(){
        $db = M('recruit');
        $address = $_POST['address'];
        if(!empty($address)){
            $result = M('city')->where('name LIKE "%' . $address . '%"')->find();
            $address = $result['id'];
            $where[] ='(address = ' . $address .' or city = '. $address.' or town='.$address.')';
        }
        $where[] ='high_salary=1 AND expiration_time > '.time().' AND `check`=1';
        $where[] = 'state=1';
        $nums = $db->where($where)->count();
        $page = new page($nums,10);
        $result = $db->where($where)->field('recruit_id,return_money,jobs_property,recruit_name,high_salary,recruit_num,address,city,town,uid,return_money,start_time,istop,company_name,welfare,work_exp,degree,salary,star,verify')->order('istop desc,start_time desc')->findall($page->limit());
        $data = new data();
        foreach ($result as $key =>$value){
            if(strpos($value['welfare'],'948')!=false){
                $value['fan']=1;
            }else{
                $value['fan']=0;
            }
            $result[$key] = $data->convert($value);
            $result[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
        $arr = array(
            'list'=>$result,
            'total'=>$page->total_page
        );
        //$result[]['count_page'] = $page->total_page;
        if(!empty($result)){
            Json_success('获取成功',$arr);
        }else{
            Json_success('暂时没有热门工作',$arr);
        }
    }

    /**
     * @Title: allWelfares
     * @Description: todo(获得全部福利)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function allWelfares(){
        $db = M('linkage');
        $result = $db->where('lcgid=23')->field('laid,title')->findall();
        Json_success('获取成功',$result);
    }

    /**
     * @Title: lifeService
     * @Description: todo(生活服务)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function lifeService(){
        $city = $_POST['city'];
        $city_id = M('channel')->where("title like '%".$city."%' and pid = 31")->field('title,id')->find();
        if(empty($city_id)){
            Json_error('您所在的城市暂无生活服务');
        }
        $channel = M('channel')->where('pid = ' . $city_id['id'])->field('title,img,url')->findall();
        Json_success('获取成功',$channel);
    }

    /**
     * @Title: getCity
     * @Description: todo(得到生活服务城市列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getCitys(){
        $city = M('channel')->where('pid = 31')->field('id,title')->findall();
        if(empty($city)){
            Json_error('还没有添加任何城市');
        }else{
            Json_success('获取成功',$city);
        }
    }

    /**
     * @Title: index
     * @Description: todo(搜索职位)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function index() {

        $order = 'istop DESC,start_time DESC';

        //处理GET参数
        $keywords = array();
        $db = M('keywords');
        //处理关键字
        if (!empty($_POST['keywords'])) {
            $_POST['keywords']=urldecode(strip_tags($_POST['keywords']));
            //$keywords = string::split_word($_POST['keywords']);
            if (!empty($_POST['keywords'])) {
                //将关键字插入数据库
                foreach ($keywords as $value) {
                    $encode = mb_detect_encoding($value, array("ASCII","UTF-8","GB2312","GBK","BIG5","CP936"));
                    if($encode=='utf-8'){
                        $has = $db->table('keywords')->where("keyword='$value'")->find();
                        if (!$has) {
                           // $db->table('keywords')->insert(array('keyword' => $value));
                        }
                        $db->table('keywords')->inc('nums', "keyword='$value'", 1); //再更新nums
                    }
                }
            }
        }

        $condition = array_map('intval', getCleanUriArg(array('keywords', C('PAGE_VAR'))));

        //职位类型(校园招聘还是...)
        if($_POST['jobType']==1){//全职
            $condition[] = '1=1';
            }elseif($_POST['jobType']==3){//附近
                $lng = $_POST['lng'];
                $lat = $_POST['lat'];
                $squares = returnSquarePoint($lng, $lat,10);
                $con = "lat<>0 and lat>".$squares['minLat']." and lat<".$squares['maxLat']." and lng>".$squares['minLng']." and lng<".$squares['maxLng'];
                $company_id = M('company_info')->field('uid')->where($con)->findall();

                if(!empty($company_id)){
                    $con = '(';
                    foreach ($company_id as $key => $value) {
                        $con.=$value['uid'].',';
                    }
                    $con = rtrim($con,',');
                    $con.=")";
                    $condition[]= 'uid in '.$con;
            }
            }elseif($_POST['jobType']==4){//兼职
                $condition[] = 'jobs_property =1';
            }elseif($_POST['jobType']==5){//入职返现
                $condition[] = "welfare like '%948%'";
            }elseif($_POST['jobType']==6){//包吃住
                $condition[] = "welfare like '%945%'";
            }elseif($_POST['jobType']==7){//校园招聘
                $condition[] = 'graduates=1';
        }
        //处理时间条件
        if (!empty($condition['refresh_date'])) {
            $condition['refresh_date'] = array(
                'lt' => time(),
                'gt' => strtotime('-' . $condition['refresh_date'] . 'days')
            );
        }
        //按第三级分类
        if(!empty($_POST['laid'])){
            $condition[] = "class_two= ".$_POST['laid'];
        }
        //按二级分类
        if(!empty($_POST['class'])&&empty($_POST['laid'])){
            $condition[] = "class= ".$_POST['class'];
        }

        //按来源
        if(!empty($_POST['origin'])){
            $condition[] = 'origin=' . $_POST['origin'];
        }
        //按福利
        if(!empty($_POST['welfare'])){
            $db = M('linkage');
            $result = $db->where(array('title'=>$_POST['welfare']))->find();
            $welfare = $result['laid'];
            if(!empty($welfare)){
                $condition[] = "welfare like '%".$welfare."%'";
            }else{
                Json_error('没有查到相应职位');
            }
           // unset($condition['welfare']);
        }
        //按薪资
        if(!empty($_POST['salary'])){
            $condition[] = 'salary =' . $_POST['salary'];
        }

        //按地区
        if(!empty($_POST['address'])){
            $db = M('city');
            $result = $db->where('name LIKE "%' . $_POST['address'] . '%"')->find();
            $address = $result['id'];
            if(!empty($address)){
                $condition[] ='( hp_recruit.address = ' . $address .' or hp_recruit.city = '. $address.' or hp_recruit.town='.$address.')';
            }else{
                Json_error('没有查到相应职位');
            }
        }
        //按信誉度
        if(!empty($_POST['star'])){
            $db = M('recruit');
            $result = $db->where('star=' . $_POST['star'])->find();
            if(!empty($result)){
                $condition[] = 'recruit.star=' . $_POST['star'];
            }else{
                Json_error('没有查到相应职位');
            }
        }
        //按工作经验
        if(!empty($_POST['work_exp'])){
            $db = M('recruit');
            $result = $db->where('work_exp=' . $_POST['work_exp'])->find();
            if(!empty($result)){
                $condition[] = 'work_exp=' . $_POST['work_exp'];
            }else{
                Json_error('没有查到相应职位');
            }
        }
        //按关键词
        if (!empty($_POST['keywords'])) {
            $keyword_cond = '(';
            $keyword_cond.='CONCAT(recruit_name,company_name) LIKE "%' . $_POST['keywords'] . '%" OR ';
            $keyword_cond = rtrim($keyword_cond, 'OR ');
            $condition[] = $keyword_cond . ')';
        }

        //职位附加条件
        $condition[] = 'expiration_time >' . time();//未过期
        $condition[]='state=1';//已开启
        $condition[]='`check`=1';//审核成功

        //查找职位信息
        $field = 'recruit_id,recruit_name,recruit_num,origin,return_money,recruit.address,recruit.city,recruit.town,recruit.uid,start_time,istop,company_name,welfare,work_exp,degree,salary,recruit.star,verify';
        $lnum = 20; //每页显示数量

        if($_POST['jobType']==2){

            $condition[]= "company_info.relieved=1";

            $db=V('recruit');
            $db->view=array(
                'company_info'=>array(
                    'type'=>'inner',
                    'on'=>'company_info.uid=recruit.uid'
                )
            );
        }else{
            $db = M('recruit');
        }

        $count = $db->field($field)->where($condition)->count();
        $page = new page($count, $lnum);
        $jobs = $db->field($field)->order($order)->where($condition)->findall($page->limit());
        //处理选项和联动数据为具体值
        $convert = new data;
        if ($jobs) {
            foreach ($jobs as $key => $value) {
                $jobs[$key] = $convert->convert($value);
            }
        }

        $db = M('model_field');
        $linkages = $db->field('title,lcgid,field_name')->where('join_index=1 and field_type="linkage" and dmid=5')->findall();

        //处理选项条件
        $switchs = array();
        foreach ($convert->fields as $key => $value) {
            if ($value['join_index'] && $value['field_type'] == 'switch') {
                $switchs[$key]['title'] = $value['title'];
                $switchs[$key]['switch'] = $value['data'];
            }
        }

        foreach ($jobs as $key => $value) {
            $company = M('company_info')->field('logo')->where(array('uid'=>$value['uid']))->find();
            $jobs[$key]['logo'] = $company['logo'];
            if(strpos($value['welfare'],'入职返现')!==false){
                $jobs[$key]['fan']=1;
            }else{
                $jobs[$key]['fan']=0;
            }

            $jobs[$key]['start_time'] = date('Y-m-d',$jobs[$key]['start_time']);
        }

        $info['jobs']=$jobs;
        $info['count_page']=$page->total_page;
        if(empty($info['jobs'])){
            Json_error('对不起，没找到您所需的职位');
        }else{
            Json_success('获取成功',$info);
        }
    }

    /**
     * 获取筛选条件
     */
    function getFilterCond(){
        $filterCond=array();
        $db=M('city');
        $filterCond['address']=$db->cache(86400)->field('id,name')->where('pid=0')->findall();//地区
        if(isset($_POST['address'])){
            $filterCond['sonAddress']=$db->cache(86400)->field('id,name')->where('pid='.intval($_POST['address']))->findall();//地区子类
        }
        $filterCond['industry']=$db->table('linkage')->cache(86400)->field('laid,title')->where('lcgid=3 AND pid!=0')->findall();//职位行业
        $need_class=array();
        $class_level_one=$db->cache(86400)->table('linkage')->field('laid')->where('lcgid=4 AND pid=0')->findall();//职位分类
        foreach ($class_level_one as $value) {
            $need_class[]=$value['laid'];
        }
        $filterCond['jobClass']=$db->table('linkage')->cache(86400)->field('laid,title')->where('lcgid=4')->in(array('pid'=>$need_class))->findall();//职位分类
        if(isset($_POST['class'])){
            $filterCond['sonJobClass']=$db->table('linkage')->cache(86400)->field('laid,title')->where('lcgid=4 AND pid='.$_POST['class'])->findall();//分类子类
        }
        Json_success('获取成功',$filterCond['jobClass']);
    }

    function jobs() {//职位内容页
        $id = intval($_POST['id']);
        $db = M('recruit');
        $verify=' AND `check`=1';
        if(in_array(1,$_SESSION['role']['rid']) or in_array(3,$_SESSION['role']['rid'])){
            $verify='';
        }
        $cond='recruit_id=' . $id.' AND state=1'.$verify;
        $job = $db->where($cond)->find();
        if(!$job){
            $this->error('对不起，没有该职位的信息！');
        }
        $db->inc('looks',$cond,1);
        $data = new data('recruit');
        $job = $data->convert($job);
        $job['job_desc']=htmlspecialchars_decode($job['job_desc']);
        $this->assign('job', $job);
        $this->display('jobs');
    }

    //搜索简历
    public function resume()
    {
        $auth = new auth;
        if (!$auth->is_logged_in()) {
            $this->error(L('please_login'), 'auth/index');
        }
        if (!$auth->check_uri_permissions()) {
            $this->error($auth->error);
        }

        $db=M('model_field');
        $filterCond=array();
        //处理选项搜索字段
        $switch_field=$db->field('title,field_name')->where('field_type="switch" and join_index=1 and dmid=6')->findall();
        $model_struct=include PATH_ROOT.'/caches/model/field/m_resume_basic.php';
        foreach ($switch_field as $value) {
            $filterCond['switchs'][$value['field_name']]=$model_struct[$value['field_name']]['data'];
        }

        //处理选项联动搜索字段
        $linkage_filed=$db->field('title,field_name,lcgid')->where('field_type="linkage" and join_index=1 and dmid=6')->findall();
        foreach ($linkage_filed as $value) {
            $data=$db->table('linkage')->cache(86400)->field('laid,title')->where('lcgid='.$value['lcgid'])->findall();;
            $filterCond['linkages'][$value['field_name']]=array('title'=>$value['title'],'data'=>$data);
        }

        //处理选项地区搜索字段
        $filterCond['address']=$db->table('city')->cache(86400)->field('id,name,direct')->where('pid=0')->findall();//地区
        if(isset($_POST['address'])){
            $filterCond['sonAddress']=$db->table('city')->cache(86400)->field('id,name')->where('pid='.$_POST['address'])->findall();//地区子类
        }

        $where=array('open'=>1,'`check`'=>1);//resume表的条件：公开已验证
        if(isset($_POST['address'])){//地址
            $where[]='hope_provice='.intval($_POST['address']);
        }
        if(isset($_POST['city'])){//地址
            $where[]='hope_city='.intval($_POST['city']);
        }
        if(isset($_POST['work_exp'])){//工作经验
            $where[]='work_exp='.intval($_POST['work_exp']);
        }
        if(isset($_POST['updated'])){//更新时间
            $where['updated']='updated >'.strtotime('-'.$_POST['updated'].'days');
        }
        $keywords=array();
        if(!empty($_POST['keywords'])){//关键字
            $_POST['keywords']=strip_tags($_POST['keywords']);
            //$keywords=array_keys(string::split_word($_POST['keywords']));
            $keyword_cond = '';
            foreach ($keywords as $value) {
                $keyword_cond.='resume_name LIKE "%' . $value . '%" OR ';
            }
            $keyword_cond = rtrim($keyword_cond, 'OR ');
            $where[]=$keyword_cond;
        }
        $db=V('resume');
        $db->view=array(
            'resume_basic'=>array(
                'type'=>'INNER',
                'on'=>'resume.resume_id=resume_basic.resume_id',
            )
        );
        $nums=$db->where($where)->count();
        $page=new page($nums,10);
        $resumes=$db->where($where)->findall($page->limit());
        if($resumes){
            $data_class=new data('resume_basic');
            foreach ($resumes as $key => $value) {
                $resumes[$key]=$data_class->convert($value);
            }
        }
        $this->assign('resumes',$resumes);
        $this->assign('filterCond',$filterCond);
        $this->assign('page',$page->show());
        $this->display('search-resume');
    }

    /**
     * @Title: commentRecruit
     * @Description:todo(评论职位)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function commentRecruit(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $recruit_id = $_POST['recruit_id'];
        if(empty($recruit_id))Json_error("缺少职位id");
        $company_id = $_POST['company_id'];
        if(empty($company_id))Json_error("缺少公司id");
        $content = $_POST['content'];
        if(empty($content))Json_error("评论内容不能为空");
        $state = $this->company->insertComment([
            'uid'=>$uid,
            'recruit_id'=>$recruit_id,
            'company_id'=>$company_id,
            'content'=>$content
        ]);
        if($state)Json_success("评论成功",$state);
        else{
            Json_error("评论失败",$state);
        }
    }

    /**
     * @Title: getRecruitComment
     * @Description:todo(获取职位评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function getRecruitComment(){
        $recruit_id = $_POST['recruit_id'];
        if(empty($recruit_id))Json_error("缺少职位参数");
        $data = $this->company->getComment(0,$recruit_id,true);
        Json_success("获取成功",$data);
    }

    /**
     * @Title: delRecruitComment
     * @Description:todo(删除职位评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function delRecruitComment(){
        $uid = $_SESSION['uid'];
        if(empty($uid))Json_error("请登录");
        $recruit_comment_id = $_POST["recruit_comment_id"];
        if(empty($recruit_comment_id))Json_error("缺少评论id");
        $state = $this->company->delComment($uid,$recruit_comment_id);
        if($state)Json_success("删除成功");
        else{
            Json_error("删除失败");
        }
    }
}