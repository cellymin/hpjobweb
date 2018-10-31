<?php
include '../libs/phpqrcode.php';
include '../libs/excel_reader2.php';
class companyControl extends myControl {

    private $spread_cate;
    private $spread;

    function __construct() {
        parent::__construct();
        $this->spread_cate = M('spread_cate');
        $this->spread=M('spread');
    }

    function spreadList() {
        $db=V('spread');
        $db->view=array(
            'spread_cate'=>array(
                'type'=>'inner',
                'on'=>'spread.cate_id=spread_cate.id',
                'field'=>'cate_name'
            ),
            'recruit'=>array(
                'type'=>'left',
                'on'=>'spread.recruit_id=recruit.recruit_id',
                'field'=>'recruit_name,company_name'
            )
        );
        $spreads = $this->spread_cate->findall();
        $spread_lists=$db->findall();
        $this->assign('spreads', $spreads);
        $this->assign('spread_lists', $spread_lists);
        $this->display();
    }

    function qrCode(){
        QRcode::png ('www.baidu.com');
    }

    function findRecruit() {
        $db=M('recruit');
        if($_GET['way']=='company_name'){
            $cond='company_name like "%'.$_GET['value'].'%"';
        }
        if($_GET['way']=='uid'){
            $cond='uid='.(int)$_GET['value'];
        }
        if($_GET['way']=='recruit_name'){
            $cond='recruit_name like "%'.$_GET['value'].'%"';
        }
        if($_GET['way']=='recruit_id'){
            $cond='recruit_id='.(int)$_GET['value'];
        }
        $recruits=$db->field('recruit_id,recruit_name,company_name,uid,created,expiration_time,refresh_date')->where($cond)->findall();
        $this->assign('recruits', $recruits);
        $this->display();
        exit;
    }

    function addSpread() {
        $db=M('spread');
        $_POST['starttime']=time();
        $_POST['endtime']=strtotime($_POST['days'].'days');
        if($db->insert($_POST)){
            $this->success('添加推广成功。');
        }
    }
    public function delSpread()
    {
        $db=M('spread');
        if($db->in($_POST)->del()){
            echo 1;
            exit;
        }
    }


    /**
     * @Title: highSalary
     * @Description: todo(设为高薪)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function highSalary(){
        $recruit_id = $_GET['recruit_id'];
        $recruit = M('recruit')->where('recruit_id=' . $recruit_id)->find();
        if($recruit['high_salary']==1){
            M('recruit')->where('recruit_id=' . $recruit_id)->update(array('high_salary'=>0,'refresh_date'=>time()));//0不是热门1是热门
        }else{
            M('recruit')->where('recruit_id=' . $recruit_id)->update(array('high_salary'=>1,'refresh_date'=>time()));;
        }
        $this->success('操作成功');
    }

    /**
     * @Title: isTop
     * @Description: todo(设为置顶)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function isTop(){
        $recruit_id = $_GET['recruit_id'];
        $recruit = M('recruit')->where('recruit_id=' . $recruit_id)->find();
        if($recruit['istop']==1){
            M('recruit')->where('recruit_id=' . $recruit_id)->update(array('istop'=>0,'refresh_date'=>time()));//0不是置顶1是置顶
        }else{
            M('recruit')->where('recruit_id=' . $recruit_id)->update(array('istop'=>1,'refresh_date'=>time()));;
        }
        $this->success('操作成功');
    }

    /**
     * @Title: relieved
     * @Description: todo(设为放心)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function relieved(){
        $id = $_GET['id'];
        $company = M('company_info')->where(array('uid'=>$id))->field('relieved')->find();
        if($company['relieved']==0){
            if(M('company_info')->where(array('uid'=>$id))->update(array('relieved'=>1))){
                $this->success('修改成功');
            }else{
                $this->error('系统错误');
            }
        }else{
            $this->error('已经是放心了');
        }
    }

    /**
     * @Title: unrelieved
     * @Description: todo(取消放心)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function unrelieved(){
        $id = $_GET['id'];
        $company = M('company_info')->where(array('uid'=>$id))->field('relieved')->find();
        if($company['relieved']==1){
            if(M('company_info')->where(array('uid'=>$id))->update(array('relieved'=>0))){
                $this->success('修改成功');
            }else{
                $this->error('系统错误');
            }
        }else{
            $this->error('已经取消放心了');
        }
    }

    /**
     * @Title: insertRecruits
     * @Description: todo(导入职位)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function insertRecruits()
    {
        $upload = new upload(PATH_ROOT . '/uploads/excel', array('xls', 'xlsx'));
        $info = $upload->upload();
        if ($info) {
            $file = $info[0]['path'];
        }
        $data = parseExcel($file);
        unset($data[1]);
        $db = M('recruit');
        $com = M('company_info');
        $errors = array();

        foreach($data as $k=>$val){

            $company = $com->where(array('uid' => $val[13]))->find();

            if(empty($company)){
                $errors[] = array(
                    'err_col'=>($k),
                    'err_msg'=>'导入失败该公司不存在'
                );
            }else{
                $arr = array(
                    'recruit_name'=>$val[1],
                    'jobs_property'=>$val[2],
                    'graduates'=>$val[3],
                    'jobs_industry'=>$val[4],
                    'class'=>$val[5],
                    'class_two'=>$val[6],
                    'job_desc'=>$val[7],
                    'work_exp'=>$val[8],
                    'degree'=>$val[9],
                    'recruit_num'=>$val[10],
                    'salary'=>$val[11],
                    'company_name'=>$company['name'],
                    'company_industry'=>$company['company_industry'],
                    'company_property'=>$company['company_property'],
                    'company_scope'=>$company['company_scope'],
                    'company_desc'=>$company['desc'],
                    'contact'=>$company['contact_person'],
                    'phone'=>$company['contact_tel'],
                    'rece_mail'=>$company['link_email'],
                    'start_time'=>strtotime($val[12]),
                    'uid'=>$val[13],
                    'expiration_time'=>strtotime($val[14]),
                    'refresh_date'=>time(),
                    'address'=>$val[15],
                    'city'=>$val[16],
                    'town'=>$val[17],
                    'state'=>$val[18],
                    'verify'=>$company['license_verify'],
                    'star'=>$company['star'],
                    'welfare'=>$val[19],
                    'return_money'=>$val[20],
                    'high_salary'=>$val[21],
                    'work_time'=>$val[22],
                    'check'=>$val[23],
                    'origin'=>$val[24],
                    'company_tel'=>$val[25]
                );

                if(!$db->insert($arr)){
                    $errors[] = array(
                        'err_col'=>$k,
                        'err_msg'=>$val[1].''
                    );
                }
            }

        }
        $this->assign('errors',$errors);
        $this->display('excelInfo');
    }

    /**
     * @Title: insertCompanys
     * @Description: todo(导入公司)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function insertCompanys()
    {
        $upload = new upload(PATH_ROOT . '/uploads/excel', array('xls', 'xlsx'));
        $info = $upload->upload();
        if ($info) {
            $file = $info[0]['path'];
        }
        $data = parseExcel($file);
        unset($data[1]);
        $db = M('company_info');
        $errors = array();

        foreach($data as $k=>$val){
            $arr = array(
                'name'=>$val[1],
                'company_industry'=>$val[2],
                'company_property'=>$val[3],
                'company_scope'=>$val[4],
                'desc'=>$val[5],
                'address'=>$val[6],
                'city'=>$val[7],
                'town'=>$val[8],
                'street'=>$val[9],
                'contact_person'=>$val[10],
                'link_email'=>$val[11],
                'contact_tel'=>$val[12],
                'license_verify'=>$val[13],
                'star'=>$val[14],
                'lng'=>$val[15],
                'lat'=>$val[16],
                'relieved'=>$val[17],
                'time'=>time()
            );

            if(!$db->insert($arr)){
                $errors[] = array(
                    'err_col'=>$k,
                    'err_msg'=>$val[1].''
                );
            }
        }

        $this->assign('errors',$errors);
        $this->display('excelInfo');
    }

    /**
     * @Title: exportCompanys
     * @Description: todo(导出公司列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function exportCompanys(){
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename=公司列表.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo iconv("UTF-8", "GBK","公司名称")."\t";
        echo iconv("UTF-8", "GBK","公司行业")."\t";
        echo iconv("UTF-8", "GBK","公司性质")."\t";
        echo iconv("UTF-8", "GBK","公司规模")."\t";
        echo iconv("UTF-8", "GBK","描述")."\t";
        echo iconv("UTF-8", "GBK","省")."\t";
        echo iconv("UTF-8", "GBK","市")."\t";
        echo iconv("UTF-8", "GBK","镇")."\t";
        echo iconv("UTF-8", "GBK","街道")."\t";
        echo iconv("UTF-8", "GBK","联系人")."\t";
        echo iconv("UTF-8", "GBK","邮箱")."\t";
        echo iconv("UTF-8", "GBK","电话")."\t";
        echo iconv("UTF-8", "GBK","企业认证")."\t";
        echo iconv("UTF-8", "GBK","公司星级")."\t";
        echo iconv("UTF-8", "GBK","经度")."\t";
        echo iconv("UTF-8", "GBK","纬度")."\t";
        echo iconv("UTF-8", "GBK","放心企业")."\t";
        echo "\n";

        $infos = M('company_info')->findall();

        foreach ($infos as $info){

            echo iconv("UTF-8", "GBK",$info['name'])."\t";
            echo iconv("UTF-8", "GBK",$info['company_industry'])."\t";
            echo iconv("UTF-8", "GBK",$info['company_property'])."\t";
            echo iconv("UTF-8", "GBK",$info['company_scope'])."\t";
            echo iconv("UTF-8", "GBK",$info['desc'])."\t";
            echo iconv("UTF-8", "GBK",$info['address'])."\t";
            echo iconv("UTF-8", "GBK",$info['city'])."\t";
            echo iconv("UTF-8", "GBK",$info['town'])."\t";
            echo iconv("UTF-8", "GBK",$info['street'])."\t";
            echo iconv("UTF-8", "GBK",$info['contact_person'])."\t";
            echo iconv("UTF-8", "GBK",$info['link_email'])."\t";
            echo iconv("UTF-8", "GBK",$info['contact_tel'])."\t";
            echo iconv("UTF-8", "GBK",$info['license_verify'])."\t";
            echo iconv("UTF-8", "GBK",$info['star'])."\t";
            echo iconv("UTF-8", "GBK",$info['lng'])."\t";
            echo iconv("UTF-8", "GBK",$info['lat'])."\t";
            echo iconv("UTF-8", "GBK",$info['relieved'])."\t";
            echo "\n";
        }

    }

    /**
     * @Title: addCompany
     * @Description: todo(添加企业)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function addCompany()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(empty($_POST['path'][1][0])){
                $logo = '';
            }else{
                $logo = __ROOT__.'/'.$_POST['path'][1][0];
            }
            if(empty($_POST['license'][1][0])){
                $license = '';
            }else{
                $license = __ROOT__.'/'.$_POST['license'][1][0];
            }
            $point = $_POST['point'];
            $points = explode(',',$point);
            $lng = $points[1];
            $lat = $points[0];
            $data = array(
                'name' => $_POST['name'],
                'company_industry' => $_POST['company_industry'],
                'company_property' => $_POST['company_property'],
                'company_scope' => $_POST['company_scope'],
                'desc' => $_POST['desc'],
                'address' => $_POST['address'],
                'city' => $_POST['city'],
                'town' => $_POST['town'],
                'street' => $_POST['street'],
                'contact_person' => $_POST['contact_person'],
                'star' => $_POST['star'],
                'contact_tel' => $_POST['contact_tel'],
                'link_email' => $_POST['link_email'],
                'logo' => $logo,
                'license'=>$license,
                'lng'=>$lng,
                'lat'=>$lat,
                'relieved' => $_POST['relieved'],
                'time'=>time()
            );
            M('company_info')->insert($data);
        }
        $this->display();
    }

    /**
     * @Title: editCompany
     * @Description: todo(修改企业)
     * @author nipeiquan
     * @return  void  返回类型
     */
    function editCompany()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $company = M('company_info')->where('uid=' . $_POST['uid'])->find();
            if(empty($_POST['path'][1][0])){
                $logo = $company['logo'];
            }else{
                $logo = __ROOT__.'/'.$_POST['path'][1][0];
            }
            if(empty($_POST['license'][1][0])){
                $license = $company['license'];
            }else{
                $license = __ROOT__.'/'.$_POST['license'][1][0];
            }
            $point = $_POST['point'];
            $points = explode(',',$point);
            $lng = $points[1];
            $lat = $points[0];
            $data = array(
                'name' => $_POST['name'],
                'company_industry' => $_POST['company_industry'],
                'company_property' => $_POST['company_property'],
                'company_scope' => $_POST['company_scope'],
                'desc' => $_POST['desc'],
                'address' => $_POST['address'],
                'city' => $_POST['city'],
                'town' => $_POST['town'],
                'street' => $_POST['street'],
                'contact_person' => $_POST['contact_person'],
                'star' => $_POST['star'],
                'contact_tel' => $_POST['contact_tel'],
                'link_email' => $_POST['link_email'],
                'logo' => $logo,
                'license'=>$license,
                'lng'=>$lng,
                'lat'=>$lat,
                'relieved' => $_POST['relieved']
            );
            $company = M('company_info')->where('uid=' . $_POST['uid'])->update($data);

            if($company){

                $ids = '';
                $recruits = M('recruit')->where('uid=' .$_POST['uid'])->findall();

                if(!empty($recruits)){

                    foreach ($recruits as $key=>$recruit){

                        $ids .= ',' . $recruit['recruit_id'];
                    }
                    $ids = ltrim($ids,',');

                    M('recruit')->where('recruit_id in ('.$ids.')')->update(array('star'=>$_POST['star']));
                }

                $this->success('修改成功','http://www.hap-job.com/index.php/backend/company/companyLists');
            }
        }else{
            $company = M('company_info')->where(array('uid'=>$_GET['uid']))->find();
            $lng_lat = array($company['lat'],$company['lng']);
            $point = implode(',',$lng_lat);
            $company['point'] = $point;
            $this->assign('company',$company);
            $this->display();
        }

    }

    /**
     * @Title: addRecruit
     * @Description: todo(添加职位)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function addRecruit(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $company = M('company_info')->where(array('uid' => $_POST['uid']))->find();
            if (empty($company)) {
                $this->error('没有查到所填公司信息！');
            } else {
                $data = array(
                    'recruit_name' => $_POST['recruit_name'],
                    'jobs_property' => $_POST['jobs_property'],
                    'graduates' => $_POST['graduates'],
                    'jobs_industry' => $_POST['jobs_industry'],
                    'class' => $_POST['class'],
                    'class_two' => $_POST['class_two'],
                    'job_desc' => $_POST['job_desc'],
                    'address' => $_POST['address'],
                    'city' => $_POST['city'],
                    'town' => $_POST['town'],
                    'work_exp' => $_POST['work_exp'],
                    'degree' => $_POST['degree'],
                    'recruit_num' => $_POST['recruit_num'],
                    'salary' => $_POST['salary'],
                    'contact' => $_POST['contact'],
                    'phone' => $_POST['phone'],
                    'start_time' => strtotime($_POST['start_time']),
                    'expiration_time' => strtotime($_POST['expiration_time']),
                    'uid' => $_POST['uid'],
                    'company_name' => $company['name'],
                    'company_industry' => $company['company_industry'],
                    'company_property' => $company['company_property'],
                    'company_scope' => $company['company_scope'],
                    'company_desc' => $company['desc'],
                    'star' => $company['star'],
                    'verify' => $company['license_verify'],
                    'contact' => $_POST['contact'],
                    'phone' => $_POST['phone'],
                    'rece_mail' => $_POST['rece_mail'],
                    'state' => 1,
                    'welfare' => $_POST['welfare'],
                    'return_money' => $_POST['return_money'],
                    'istop' => $_POST['istop'],
                    'high_salary' => $_POST['high_salary'],
                    'work_time' => $_POST['work_time'],
                    'check' => $_POST['check'],
                    'origin' => $_POST['origin'],
                    'company_tel' => $_POST['company_tel']
                );
                if(M('recruit')->insert($data)){
                    $this->success('发布成功');
                }else{
                    $this->error('系统错误');
                }
            }
        }
        $this->display();
    }

    /**
     * @Title: editRecruit
     * @Description: todo(修改职位)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editRecruit(){
        $recruit_id = $_GET['recruit_id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $company = M('company_info')->where(array('uid' => $_POST['uid']))->find();
            if (empty($company)) {
                $this->error('没有查到所填公司信息!');
            } else {
                $data = array(
                    'recruit_name' => $_POST['recruit_name'],
                    'jobs_property' => $_POST['jobs_property'],
                    'graduates' => $_POST['graduates'],
                    'jobs_industry' => $_POST['jobs_industry'],
                    'class' => $_POST['class'],
                    'class_two' => $_POST['class_two'],
                    'job_desc' => $_POST['job_desc'],
                    'address' => $_POST['address'],
                    'city' => $_POST['city'],
                    'town' => $_POST['town'],
                    'work_exp' => $_POST['work_exp'],
                    'degree' => $_POST['degree'],
                    'recruit_num' => $_POST['recruit_num'],
                    'salary' => $_POST['salary'],
                    'contact' => $_POST['contact'],
                    'phone' => $_POST['phone'],
                    'start_time' => strtotime($_POST['start_time']),
                    'expiration_time' => strtotime($_POST['expiration_time']),
                    'uid' => $_POST['uid'],
                    'company_name' => $company['name'],
                    'company_industry' => $company['company_industry'],
                    'company_property' => $company['company_property'],
                    'company_scope' => $company['company_scope'],
                    'company_desc' => $company['desc'],
                    'verify' => $company['license_verify'],
                    'star' => $company['star'],
                    'contact' => $_POST['contact'],
                    'phone' => $_POST['phone'],
                    'rece_mail' => $_POST['rece_mail'],
                    'state' => 1,
                    'welfare' => $_POST['welfare'],
                    'istop' => $_POST['istop'],
                    'return_money' => $_POST['return_money'],
                    'high_salary' => $_POST['high_salary'],
                    'work_time' => $_POST['work_time'],
                    'check' => $_POST['check'],
                    'origin' => $_POST['origin'],
                    'company_tel' => $_POST['company_tel']
                );
                if(M('recruit')->where(array('recruit_id'=>$_POST['recruit_id']))->update($data)){
                    $this->success('修改成功');
                }else{
                    $this->error('系统错误');
                }
            }
        }else{
            $recruit = M('recruit')->where(array('recruit_id'=>$recruit_id))->find();
            $recruit['start_time'] = date('Y-m-d H:i:s',$recruit['start_time']);
            $recruit['expiration_time'] = date('Y-m-d H:i:s',$recruit['expiration_time']);
            $this->assign('field',$recruit);
            $this->display();
        }
    }

    /**
     * @Title: companyLists
     * @Description: todo(公司列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function companyLists(){
        $_GET = urldecode_array($_GET);

        if(!empty($_GET['name'])){
            $cond[]='name like "%'.ltrim($_GET['name']).'%"';
        }

        if(!empty($_GET['start_time']) && !empty($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'time >' . $start_time . ' and time <' . $end_time;
        }

        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'time >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'time <' . $end_time;
        }

        $count = M('company_info')->where($cond)->count();
        $page = new page($count,15);
        $company = M('company_info')->where($cond)->order('time desc')->findall($page->limit());
        $data = new data();
        foreach ($company as $key =>$value){
            $company[$key] = $data->convert($value);
        }
        $this->assign('pages',$page->show());
        $this->assign('company',$company);
        $this->display();
    }

    /**
     * @Title: deliverLists
     * @Description: todo(用户投递列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function deliverLists(){
        $_GET = urldecode_array($_GET);
        $db=M('deliver');
        //组合条件
        $cond=array();

        if(!empty($_GET['username'])){

            $cond[]='username like "%'.ltrim($_GET['username']).'%"';
        }

        if(!empty($_GET['rel_name'])){

            $cond[]='rel_name like "%'.ltrim($_GET['rel_name']).'%"';
        }

        if(!empty($_GET['company_name'])){
            $_GET['company_name'] = ltrim($_GET['company_name']);

            $cond[]='company_name like "%'.$_GET['company_name'].'%"';
        }

        if(!empty($_GET['start_time']) && !empty($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'sendtime >' . $start_time . ' and sendtime <' . $end_time;
        }

        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'sendtime >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'sendtime <' . $end_time;
        }

        if(isset($_GET['gender'])){
            $cond[] = 'gender = ' . $_GET['gender'];
        }

        if(isset($_GET['is_contact'])){
            $cond[] = 'is_contact = ' . $_GET['is_contact'];
        }

        if(!empty($_GET['recruit_name'])){
            $cond[] = 'recruit_name like "%'.ltrim($_GET['recruit_name']).'%"';
        }
        $count = $db->where($cond)->count();
        $page = new page($count,20);
        $deliver = $db->where($cond)->order('is_contact asc,sendtime desc')->findall($page->limit());
        foreach ($deliver as $key=>$value){
            $resume_basic = M('resume_basic')->where('resume_id=' . $value['resume_id'])->find();
            $birthday = $resume_basic['birthday'];
            $deliver[$key]['age'] = date('Y',time())-date('Y',$birthday);
            $deliver[$key]['phone'] = $resume_basic['telephone'];
        }
        $this->assign('pages',$page->show());
        $this->assign('deliver',$deliver);
        $this->display();
    }
    /**
     * @Title: isContact
     * @Description: todo(设为已联系)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function isContact(){
        $id = $_GET['id'];
        $db = M('deliver');
        $deliver = $db->where('id=' . $id)->find();
        if($deliver['is_contact']==0){
            if($db->where('id=' . $id)->update(array('is_contact'=>'1','contact_time'=>time(),'contact'=>$_SESSION['username']))){
                $this->success('操作成功');
            };
        }
    }

    /**
     * @Title: contact
     * @Description: todo(一键联系)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function contact(){
        $db=M('deliver');
        if($db->in($_POST)->update(array('is_contact'=>'1','contact_time'=>time(),'contact'=>$_SESSION['username']))){
            echo 1;
            exit;
        }
    }

    /**
     * @Title: restore
     * @Description: todo(恢复为未联系)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function restore(){
        $id = $_GET['id'];
        $db = M('deliver');
        $deliver = $db->where('id=' . $id)->find();
        if($deliver['is_contact']==1){
            if($db->where('id=' . $id)->update(array('is_contact'=>'0','contact_time'=>'','contact'=>''))){
                $this->success('恢复成功');
            };
        }
    }
    /**
     * 修改推广
     */
    public function editSpread()
    {
        $endtime=$this->spread->field('days,endtime')->find($_GET['id']);
        $newtime=$endtime['endtime']+($_POST['days']*86400);
        $data=array();
        $data['endtime']=$newtime;
        $data['days']=$endtime['days']+$_POST['days'];
        $this->spread->where('id='.$_GET['id'])->update($data);
    }

    /**
     * 删除招聘
     */
    public function delRecruit()
    {
        $db=M('recruit');
        if($db->in($_POST)->del()){

            $recruit_ids = implode(',',$_POST['recruit_id']);

            M('favorite')->where('recruit_id in ('.$recruit_ids.')')->del();

            M('deliver')->where('recruit_id in ('.$recruit_ids.')')->del();
            echo 1;
            exit;
        }
    }

    /**
     * @Title: delRecruit
     * @Description: todo(删除公司)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delCompany()
    {
        $db=M('company_info');
        if($db->in(array('uid'=>$_POST['id']))->del()){

            $company_ids = implode(',',$_POST['id']);

            $recruits = M('recruit')->where('uid in ('.$company_ids.')')->findall();
            foreach ($recruits as $key=>$value){

                $recruit_id[] = $value['recruit_id'];
            }
            if(M('recruit')->where('uid in ('.$company_ids.')')->del()){
                $recruit_id_str = implode(',',$recruit_id);
                M('favorite')->where('recruit_id in ('.$recruit_id_str.')')->del();
                M('deliver')->where('recruit_id in ('.$recruit_id_str.')')->del();
                echo 1;

                exit;
            }

        }
    }

    /**
     * 审核、开启关闭招聘
     */
    public function verifyRecruit()
    {
        $db=M('recruit');
        $data=array();
        if($_POST['type']=='enable'){
            $data['state']=1;
        }
        if($_POST['type']=='close'){
            $data['state']=0;
        }
        if($_POST['type']=='verify-unpass'){
            $data['check']=0;
        }
        if($_POST['type']=='verify-pass'){
            $data['check']=1;
        }
        if($_POST['type']=='high-salary'){
            $data['high_salary']=1;
        }
        if($_POST['type']=='is-top'){
            $data['istop']=1;
        }
        if($_POST['type']=='not-high'){
            $data['high_salary']=0;
        }
        if($_POST['type']=='not-top'){
            $data['istop']=0;
        }
        if($_POST['type']=='push-user'){

            ini_set("memory_limit","-1");

            $recruit_count = count($_POST['recruit_id']);

            $title = '职位推荐';

            $content = '系统为您推荐了新的职位，点击立即查看！！';

            $recruit_ids = implode(',',$_POST['recruit_id']);

            if($recruit_count ==1){
                $recruit = M('recruit')->where('recruit_id ='.$recruit_ids)->find();
                $convert = new data;

                $recruit = $convert->convert($recruit);

                $content_show = '热门工作：'.$recruit['company_name'].' 职位：'.$recruit['recruit_name'].' 薪资待遇：'.$recruit['salary'];
            }else{
                $content_show = $content;
            }
            $user = M('user')->findall();

            //$user = M('user')->where('uid in(59,38)')->findall();
            $values = '';
            foreach ($user as $key=>$value){

                $values .= ',' . '('.$value['uid']. ',"' .$content. '",' .time(). ',"' .$title. '","' .$recruit_ids.'")';

                $client_id[] = $value['client_id'];

            }

            $client_id = array_unique(array_filter($client_id));

            $values = ltrim($values,',');

            $sql = "INSERT INTO hp_user_message (uid,content,created,title,is_mess) VALUES $values;";

            if(M('user_message')->exe($sql)){

                $hidden = array(
                    'type'=>1,
                    'data_type'=>101,
                    'title'=>$title,
                    'content'=>$content_show,
                    'is_mess'=>$recruit_ids//职位ids
                );

                $client_id_group = array_chunk($client_id,100);

                for ($i=0; $i<=count($client_id_group)-1; $i++) {

                    push($client_id_group[$i],array('hidden'=>$hidden,'title'=>$content_show,'content'=>'系统为您推荐了新的职位，点击立即查看！！'));
                }

                $this->success('发送成功');
            }else{
                $this->error('发送失败');
            }

        }
        $db->in($_POST['recruit_id'])->update($data);
        echo 1;
        exit;
    }

    /**
     * 招聘列表
     */
    public function recruitList()
    {
        $_GET = urldecode_array($_GET);
        $db=M('recruit');
        //组合条件
        $cond=array();

        if(!empty($_GET['recruit_name'])){
            $_GET['recruit_name'] = ltrim($_GET['recruit_name']);

            $cond[]='recruit_name like "%'.$_GET['recruit_name'].'%"';
        }

        if(!empty($_GET['company_name'])){
            $_GET['company_name'] = ltrim($_GET['company_name']);

            $cond[]='company_name like "%'.$_GET['company_name'].'%"';
        }

        if(!empty($_GET['start_time']) && !empty($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'start_time >' . $start_time . ' and created <' . $end_time;
        }

        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'start_time >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'start_time <' . $end_time;
        }

        if($_GET['state']!=""){
            if($_GET['state']==2){//已过期
                $cond[]='expiration_time <'.time();
            }else{
                $cond[]= 'state= ' . $_GET['state'];
            }
        }
        if($_GET['check']!=""){
            $cond[]= '`check`=' . $_GET['check'];
        }
        //组合条件
        $field='recruit_id,recruit_name,start_time,refresh_date,verify,company_name,state,check,expiration_time,looks,high_salary,istop,work_time';
        $nums=$db->where($cond)->count();
        $page=new page($nums,20);
        $recruits=$db->field($field)->where($cond)->order('start_time desc')->findall($page->limit());
        foreach ($recruits as $key=>$value){

            $recruit_id = $value['recruit_id'];
            $deliver_num = count(M('deliver')->where('recruit_id='.$recruit_id)->findall());
            $recruits[$key]['deliver_num'] = $deliver_num;
        }

        $this->assign('recruits', $recruits);
        $this->assign('page', $page->show());
        $this->display();
    }

    /**
     * 营业执照审核
     */
    public function licenseverify()
    {
        $db=M('company_info');
        if($_SERVER['REQUEST_METHOD']=='POST'){
            C('DEBUG',1);
            $db->in(array('uid' => $_POST['id']))->update(array('license_verify' => $_POST['license_verify']));
            $rid = M('recruit')->in(array('uid' => $_POST['id']))->field('recruit_id')->findall();
            if(!empty($rid)){
                $sum = 0;
                $count = count($rid);
                for($i = 0; $i < $count; $i++){
                    $sum .= $rid[$i]['recruit_id'].',';
                }
                $sum = substr($sum,1);
                $sum = substr($sum,0,-1);
                M('recruit')->in(array('recruit_id'=>$sum))->update(array('verify'=>$_POST['license_verify']));
            }

            echo 1;
            exit();
        }
        $cond=array();
        $name = urldecode($_GET['name']);
        if(isset($name)){
            $cond[]='name like "%'.$name.'%"';
        }
        if(isset($_GET['license_verify'])){
            $cond['license_verify']=$_GET['license_verify'];
        }
        $count = $db->where($cond)->count();
        $page = new page($count,10);
        $licenses=$db->field('uid,name,desc,logo,address,city,town,street,license,license_verify,relieved')->where($cond)->order('time desc')->findall($page->limit());
        $data = new data();
        foreach ($licenses as $key =>$value){
            $licenses[$key] = $data->convert($value);
        }
        $this->assign('licenses', $licenses);
        $this->assign('pages',$page->show());
        $this->display();
    }
    function addSpreadCate() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = $this->spread_cate->insert($_POST);
            $success = array('添加成功', 'spreadList?action=2');
            $error = array('添加失败');
            $this->success_error($result, $success, $error);
        }
    }

    function editSpreadCate() {
        $cate = $_GET['cate'];
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editSpread'])) {
            $result = $this->spread_cate->where('id=' . $cate)->update($_POST) >= 0;
            $success = array('修改成功', 'spreadList?action=2');
            $error = array('修改失败');
            $this->success_error($result, $success, $error);
        } else {
            $spread_cate = $this->spread_cate->find($cate);
            $this->assign('spread_cate', $spread_cate);
            $this->display();
        }
    }

    function delSpreadCate() {
        $cate = $_GET['cate'];
        $result = $this->spread_cate->del($cate);
        $success = array('删除成功', 'spreadList?action=2', 1);
        $error = array('删除失败');
        $this->success_error($result, $success, $error);
    }

    public function getRecruitQR(){

        include_once(__ROOT__.'/web/backend/libs/phpqrcode.php');
        $recruitId = $_GET['recruitId'];

        /*C("debug",1);
       ob_clean();
       header("Content-type:image/png;charset=utf-8");*/
        QRcode::png ($recruitId);
    }
}

