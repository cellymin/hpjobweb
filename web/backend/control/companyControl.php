<?php
include '../libs/phpqrcode.php';
include '../libs/excel_reader2.php';
class companyControl extends myControl {

    private $spread_cate;
    private $spread;
    private $web_config;

    function __construct() {
        parent::__construct();
        $this->spread_cate = M('spread_cate');
        $this->spread=M('spread');
        $this->web_config = K('webConfigModel');
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
                    'created'=>time(),
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
                    'company_tel' => $_POST['company_tel'],
                    'created' => time()
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
     * @Title: insertReward
     * @Description: todo(导入供应商)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function insertSuppliers(){

        $errors = array();

        $data = parseExcel($_FILES['file']['tmp_name']);

        unset($data[1]);

        $db = M('supplier');

        foreach($data as $k=>$val){

            $arr = array(
                'number'=>$val[1],
                'name_co'=>$val[2],
                'username'=>$val[3],
                'mobile'=>$val[4],
                'identity'=>$val[5],
                'card_num'=>$val[6],
                'bank'=>$val[7],
                'address'=>$val[8],
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
     * @Title: supplierList
     * @Description: todo(供应商列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function supplierList(){

        $_GET = urldecode_array($_GET);

        $cond=array();

        if(!empty($_GET['number'])){

            $cond[]='number like "%'.ltrim($_GET['number']).'%"';
        }

        if(!empty($_GET['name_co'])){

            $cond[]='name_co like "%'.ltrim($_GET['name_co']).'%"';
        }

        if(!empty($_GET['bank'])){

            $cond[]='bank like "%'.ltrim($_GET['bank']).'%"';
        }

        $count = M('supplier')->where($cond)->count();

        $page = new page($count,15);

        $suppliers = M('supplier')->where($cond)->order('time desc')->findall($page->limit());

        $this->assign('pages',$page->show());

        $this->assign('suppliers',$suppliers);

        $this->display();
    }

    /**
     * @Title: editSupplier
     * @Description: todo(编辑供应商)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editSupplier(){

        $sid = $_GET['sid'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $edit = M('supplier')->where(['sid'=>$sid])->update($_POST);

            if($edit){

                go('supplierList');
            }
        }

        $supplier = M('supplier')->where(['sid'=>$sid])->find();

        $this->assign('supplier', $supplier);

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
        $db=M('deliver');//新加的字段会暂时读不到要清除一下相应控制器的缓存，才会起作用
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
//        echo '<pre/>';
//        var_dump($deliver);
//        die();
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
    public  function  entry()
    {
        $id = intval($_GET['id']);//投递id
        $entry_time = $_GET['entry_time'];//入职时间
        $companyname = $_GET['companyname'];//公司名称
        $recruit_id = intval($_GET['recruitid']);//职位id
        $uid = intval($_GET['uid']);//用户id
        $info = [];
        $info['id'] = $id;
        $companyname = urldecode($companyname);//公司名称
        $info['entry_time'] = strtotime($entry_time);//用户入职时间x年x月x日0:0:0
        $info['company'] = $companyname;//
        $info['recruitid'] = $recruit_id;//职位id
        $info['uid='] = $uid;//用户id

        //点入职之后
        //职位id 入职时间  公司名称
        //入职操作更改 投递记录入职状态入职时间
        //@param entry_status入职状态   entry_time入职时间
        //点击入职之后更新投递记录的入职状态，入职时间
        var_dump($info) ;
        $db = M('deliver');
        //更新投递记录中的入职状态
        $res1 = $db->exe('update hp_deliver set entry_status=1,entry_time=' . $info['entry_time'] . ' where id=' . $id);
        if ($res1) {//更新人员基础表入职时间，入职状态，入职公司
            $res2 = $db->exe("update hp_user set entry_status=1,entry_time=" . $info['entry_time'] . ",company_name='".$companyname."' where uid=".$uid);
            //$res2 = $db2->where(array('uid'=>$uid))->update(array('entry_status'=>1,'entry_time'=>$info['entry_time'],'company_name'=>$companyname));
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

            $recruit_ids = implode(',',$_POST['recruit_id']);

            if($recruit_count >2){

                $recruits= M('recruit')->where('recruit_id in('.$recruit_ids.')')->limit(2)->findall();

                $content = '系统为您推荐了'.$recruits[0]['recruit_name'].'、'.$recruits[1]['recruit_name'].'等职位，点击立即查看！！';
            }else{

                $recruits= M('recruit')->where('recruit_id in('.$recruit_ids.')')->findall();

                $name = '';
                foreach ($recruits as $key=>$recruit){

                    $name.= $recruit['recruit_name'].'、';
                }

                $name = rtrim($name, "、");

                $content = '系统为您推荐了'.$name.'职位，点击立即查看！！';
            }

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

            $user_group = array_chunk($user,1000);

            for ($i=0; $i<=count($user_group)-1; $i++) {

                $values = '';
                foreach ($user_group[$i] as $k=>$value){

                    $values .= ',' . '('.$value['uid']. ',"' .$content. '",' .time(). ',"' .$title. '","' .$recruit_ids.'")';

                }

                $values = ltrim($values,',');

                $sql = "INSERT INTO hp_user_message (uid,content,created,title,is_mess) VALUES $values;";

                M('user_message')->exe($sql);
            }
//            $values = '';
            foreach ($user as $key=>$value){

//                $values .= ',' . '('.$value['uid']. ',"' .$content. '",' .time(). ',"' .$title. '","' .$recruit_ids.'")';

                $client_id[] = $value['client_id'];

            }

            $client_id = array_unique(array_filter($client_id));

//            $values = ltrim($values,',');

//            $sql = "INSERT INTO hp_user_message (uid,content,created,title,is_mess) VALUES $values;";

//            if(M('user_message')->exe($sql)){

                $hidden = array(
                    'type'=>2,
                    'data_type'=>202,
                    'title'=>$title,
                    'content'=>$content_show,
                    'is_mess'=>$recruit_ids//职位ids
                );

                $client_id_group = array_chunk($client_id,100);

                for ($i=0; $i<=count($client_id_group)-1; $i++) {

                    push($client_id_group[$i],array('hidden'=>$hidden,'title'=>$content_show,'content'=>'系统为您推荐了新的职位，点击立即查看！！'));
                }

                $this->success('发送成功');
//            }else{
//                $this->error('发送失败');
//            }

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

    /**
     * @Title: returnMoney
     * @Description: todo(招聘返款列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function returnMoney(){

        $_GET = urldecode_array($_GET);
        $db=M('return_money');
        //组合条件
        $cond=array();

        if(!empty($_GET['originer'])){

            $cond[]='originer like "%'.ltrim($_GET['originer']).'%"';
        }

        if(!empty($_GET['membership_mediation'])){

            $cond[]='membership_mediation like "%'.ltrim($_GET['membership_mediation']).'%"';
        }

        if(!empty($_GET['company'])){

            $cond[]='company like "%'.ltrim($_GET['company']).'%"';
        }

        if(!empty($_GET['name'])){

            $cond[]='name like "%'.ltrim($_GET['name']).'%"';
        }

        if(!empty($_GET['identity'])){

            $cond[]='identity like "%'.ltrim($_GET['identity']).'%"';
        }

        if(!empty($_GET['interview_time'])){
            $interview_time = strtotime($_GET['interview_time']);
            $cond[] = 'interview_time =' . $interview_time;
        }

        if(!empty($_GET['pay_time'])){
            $pay_time = strtotime($_GET['pay_time']);
            $cond[] = 'pay_time =' . $pay_time;
        }

        $count = M('return_money')->where($cond)->count();

        $page = new page($count,20);

        $company = M('return_money')->where($cond)->order('create_time desc')->findall($page->limit());

        $this->assign('pages',$page->show());

        $this->assign('returnMoneys',$company);

        $this->display();
    }

    /**
     * @Title: result_two
     * @Description: todo(结果格式2)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function resultTwo(){

        M('return_money2')->delall();

        $sql = "SELECT a.*,b.return_money,b.salary_month,b.reward_way,b.feedback_time FROM hp_return_money AS a RIGHT JOIN hp_return_reward AS b ON a.start_time = b.join_office_date AND a.identity = b.identity AND a.company = b.company WHERE a.id > 0";

        $join = M('return_money')->query($sql);

        if(empty($join)){

            echo '没有匹配到数据！';die;
        }
        $values = '';

        foreach ($join as $key=>$value){

            $values .= ',' . '("'.$value['origin']. '","' .$value['originer']. '","' .$value['membership_mediation']. '","' .$value['company'].
                '","' .$value['number']. '","' .$value['name']. '","' .$value['sex']. '","' .$value['identity'].
                '",' .$value['interview_time']. ',' .$value['start_time']. ',' .$value['leave_time']. ',"' .$value['salary'].
                '","' .$value['month']. '","' .$value['work_time']. '","' .$value['price']. '","' .$value['award']. '","' .$value['num']. '",'.$value['pay_time'].',"' .$value['membership_company'].
                '","' .$value['return_money_way']. '","'.$value['note'].'","'.$value['type']. '","'.$value['nature']. '","'.$value['school'].
                '","' .$value['resource_place'].'","'.$value['pnote'].'","'.$value['return_money']. '","'.$value['salary_month']. '","'.$value['reward_way']. '",'.$value['feedback_time']. ','.time().')';

        }

        $values = ltrim($values,',');

        $sql2 = "INSERT INTO hp_return_money2 (origin,originer,membership_mediation,company,number,name,sex,identity,interview_time,
                 start_time,leave_time,salary,month,work_time,price,award,num,pay_time,membership_company,return_money_way,note,type,
                 nature,school,resource_place,pnote,return_money,salary_month,reward_way,feedback_time,create_time) VALUES $values;";

        M('return_money2')->exe($sql2);

        $count = M('return_money2')->count();
        $page = new page($count,20);
        $company = M('return_money2')->order('create_time desc')->findall($page->limit());
        $this->assign('pages',$page->show());
        $this->assign('resultTwos',$company);
        $this->display();
    }

    /**
     * @Title: insertReward
     * @Description: todo(导入招聘奖励)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function insertReward(){

        $errors = array();

        $data = parseExcel($_FILES['file1']['tmp_name']);

        unset($data[1]);

        $db = M('return_reward');

        foreach($data as $k=>$val){

            $arr = array(
                'num'=>$val[1],
                'name'=>$val[2],
                'sex'=>$val[3],
                'join_office_date'=>strtotime($val[4]),
                'leave_office_date'=>strtotime($val[5]),
                'identity'=>$val[6],
                'company'=>$val[7],
                'salary_month'=>$val[8],
                'return_money'=>$val[9],
                'feedback_time'=>strtotime($val[10]),
                'reward_way'=>$val[11],
                'create_time'=>time()
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
     * @Title: insertReturnMoney
     * @Description: todo(导入返款信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function insertReturnMoney()
    {
        $errors = array();
//var_dump(parseExcel($_FILES['file1']['tmp_name']));die;
        foreach ($_FILES as $key=>$value){

            $data = parseExcel($value['tmp_name']);

            unset($data[1]);

            switch ($key)
            {
                case 'file1':
                    $db = M('return_salary');
                    $db_copy = M('return_salary_copy');
                    foreach($data as $k=>$val){
                        $arr = array(
                            'number'=>$val[1],
                            'name'=>$val[2],
                            'sex'=>$val[3],
                            'start_time'=>strtotime($val[4]),
                            'leave_time'=>strtotime($val[5]),
                            'identity'=>$val[6],
                            'company'=>$val[7],
                            'salary'=>$val[8],
                            'month'=>$val[9],
                            'work_time'=>$val[10],
                            'type'=>$val[11],
                            'nature'=>$val[12],
                            'membership_company'=>$val[13],
                            'create_time'=>time()
                        );

                        $db_copy->insert($arr);

                        if(!$db->insert($arr)){
                            $errors[] = array(
                                'err_col'=>$k,
                                'err_msg'=>$val[1].''
                            );
                        }
                    }
                break;
                case 'file2':
                    $db = M('return_price');
                    $db_copy = M('return_price_copy');
                    foreach($data as $k=>$val){
                        $arr = array(
                            'num'=>$val[1],
                            'origin'=>$val[2],
                            'interview_time'=>strtotime($val[3]),
                            'company'=>$val[4],
                            'sex'=>$val[5],
                            'return_money_way'=>$val[6],
                            'membership_company'=>$val[7],
                            'type'=>$val[8],
                            'nature'=>$val[9],
                            'resource_place'=>$val[10],
                            'price'=>$val[11],
                            'award'=>$val[12],
                            'note'=>$val[13],
                            'school'=>$val[14],
                            'salary_line'=>$val[15],
                            'create_time'=>time()
                        );

                        $db_copy->insert($arr);
                        if(!$db->insert($arr)){
                            $errors[] = array(
                                'err_col'=>$k,
                                'err_msg'=>$val[1].''
                            );
                        }
                    }
                    break;
                case 'file3':
                    $db = M('return_interview');
                    $db_copy = M('return_interview_copy');
                    foreach($data as $k=>$val){

                        $arr = array(
                            'interview_time'=>strtotime($val[1]),
                            'originer'=>$val[2],
                            'name'=>$val[3],
                            'sex'=>$val[4],
                            'identity'=>$val[5],
                            'membership_mediation'=>$val[6],
                            'company'=>$val[7],
                            'nature'=>$val[8],
                            'membership_company'=>$val[9],
                            'note'=>$val[10],
                            'resource_place'=>$val[11],
                            'create_time'=>time()
                        );

                        $db_copy->insert($arr);

                        if(!$db->insert($arr)){
                            $errors[] = array(
                                'err_col'=>$k,
                                'err_msg'=>$val[1].''
                            );
                        }
                    }
            }

        }

        $sql = "SELECT a.*,b.number,b.start_time,b.leave_time,b.salary,b.`month`,b.work_time,b.type FROM hp_return_interview as a RIGHT JOIN hp_return_salary as b ON a.name = b.name AND a.sex = b.sex AND a.identity = b.identity AND a.company = b.company AND a.nature = b.nature AND a.membership_company = b.membership_company WHERE a.id > 0";

        $join = M('return_interview')->query($sql);

        $values = '';

        if(empty($join)){

            echo '没有匹配到任何数据1！';die;
        }
        foreach ($join as $key=>$value){

            $values .= ',' . '('.$value['interview_time']. ',"' .$value['originer']. '","' .$value['name']. '","' .$value['sex'].
                '","' .$value['identity']. '","' .$value['membership_mediation']. '","' .$value['company']. '","' .$value['nature'].
                '","' .$value['membership_company']. '","' .$value['note']. '","' .$value['resource_place']. '","' .$value['number'].
                '",' .$value['start_time']. ',' .$value['leave_time']. ',"' .$value['salary']. '","' .$value['month']. '","' .$value['work_time']. '","' .$value['type']. '",' .time().')';

        }

        $values = ltrim($values,',');

        $sql2 = "INSERT INTO hp_return_result1 (interview_time,originer,name,sex,identity,membership_mediation,company,nature,
                 membership_company,note,resource_place,number,start_time,leave_time,salary,month,work_time,type,create_time) VALUES $values;";

        if(M('return_result1')->exe($sql2)){
            M('return_interview')->delall();
            M('return_salary')->delall();
        }else{
            $errors[] = array(
                'err_col'=>1,
                'err_msg'=>'请检查面试名单与在职工资数据是否正确'.''
            );
        }

        $sql3 = "SELECT a.*,b.num,b.origin,b.return_money_way,b.price,b.award,b.school,b.salary_line FROM hp_return_result1 AS a LEFT JOIN hp_return_price AS b ON a.interview_time = b.interview_time AND a.company = b.company AND a.sex = b.sex AND a.membership_company = b.membership_company AND a.type = b.type AND a.nature = b.nature AND a.resource_place = b.resource_place WHERE a.id > 0";

        $join2 = M('return_result1')->query($sql3);

        if(empty($join2)){

            echo '没有匹配到任何数据2！';die;
        }
        $values2 = '';

        foreach ($join2 as $key=>$value){

            $values2 .= ',' . '("'.$value['origin']. '","' .$value['originer']. '","' .$value['membership_mediation']. '","' .$value['company'].
                '","' .$value['number']. '","' .$value['name']. '","' .$value['sex']. '","' .$value['identity'].
                '",' .$value['interview_time']. ',' .$value['start_time']. ',' .$value['leave_time']. ',"' .$value['salary'].
                '","' .$value['month']. '","' .$value['work_time']. '","' .$value['price']. '","' .$value['award']. '","' .$value['salary_line'].'","' .$value['num']. '","' .$value['membership_company'].
                '","' .$value['return_money_way']. '","'.$value['note'].'","'.$value['type']. '","'.$value['nature']. '","'.$value['school'].
                '","' .$value['resource_place'].'",' .time().')';

        }

        $values2 = ltrim($values2,',');

        $sql4 = "INSERT INTO hp_return_money (origin,originer,membership_mediation,company,number,name,sex,identity,interview_time,
                 start_time,leave_time,salary,month,work_time,price,award,salary_line,num,membership_company,return_money_way,note,type,
                 nature,school,resource_place,create_time) VALUES $values2;";

        if(M('return_money')->exe($sql4)) {
            M('return_price')->delall();
            M('return_result1')->delall();
        }else{
            $errors[] = array(
                'err_col'=>1,
                'err_msg'=>'请检查价格表数据是否正确'.''
            );
        }

        $results = M('return_money')->findall();
//
        foreach($results as $key=>$result){
            $renturn_days = $this->web_config->returnMoneyWay($result['return_money_way']);

            if($result['salary'] < $result['salary_line']){

                if(($result['leave_time']-$result['start_time'])/86400<$renturn_days && $renturn_days!=0){
                    M('return_money')->where('id ='.$result['id'])->update(['price'=>0,'award'=>0]);
                }
            }

        }
        $this->assign('errors',$errors);
        $this->display('excelInfo');
    }

    /**
     * @Title: returnMoneyInfo
     * @Description: todo(返款信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function returnMoneyInfo(){

        $id = $_GET['id'];

        $result = M('return_money')->where('id ='.$id)->find();

        $this->assign('returnMoneyInfo', $result);

        $this->display();
    }

    /**
     * @Title: returnMoneyInfo
     * @Description: todo(返款2信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function resultTwoInfo(){

        $id = $_GET['id'];

        $result = M('return_money2')->where('id ='.$id)->find();

        $this->assign('resultTwoInfo', $result);

        $this->display();
    }

    /**
     * @Title: editReturnMoney
     * @Description: todo(编辑返款)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editReturnMoney(){

        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $_POST['pay_time'] = strtotime($_POST['pay_time']);
            $_POST['interview_time'] = strtotime($_POST['interview_time']);
            $_POST['start_time'] = strtotime($_POST['start_time']);
            $_POST['leave_time'] = strtotime($_POST['leave_time']);
//            $_POST['feedback_time'] = strtotime($_POST['feedback_time']);

            $edit = M('return_money')->where('id ='.$id)->update($_POST);

            if($edit){

                go('returnMoney');
            }

        }
        $returnMoney = M('return_money')->where('id ='.$id)->find();

        $this->assign('returnMoney', $returnMoney);

        $this->display();

    }

    /**
     * @Title: editResultTwo
     * @Description: todo(编辑返款格式2)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editResultTwo(){

        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $_POST['pay_time'] = strtotime($_POST['pay_time']);
            $_POST['interview_time'] = strtotime($_POST['interview_time']);
            $_POST['start_time'] = strtotime($_POST['start_time']);
            $_POST['leave_time'] = strtotime($_POST['leave_time']);
            $_POST['feedback_time'] = strtotime($_POST['feedback_time']);

            $edit = M('return_money2')->where('id ='.$id)->update($_POST);

            if($edit){

                go('resultTwo');
            }

        }
        $returnMoney = M('return_money2')->where('id ='.$id)->find();

        $this->assign('resultTwo', $returnMoney);

        $this->display();

    }

    /**
     * @Title: selectField
     * @Description: todo(选择字段)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function selectField(){

        $this->display();
    }

    public function selectField1(){

        $this->display();
    }

    /**
     * @Title: exportResult
     * @Description: todo(导出返款结果1)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function exportResult1(){

        if(empty($_POST)){
            echo('请选择字段！');die;
        }
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename= 结果格式1.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        foreach($_POST as $key=>$value){

            echo iconv("UTF-8", "GBK","$value")."\t";

        }

        echo iconv("UTF-8", "GBK","供应商编号")."\t";
        echo iconv("UTF-8", "GBK","职介全称")."\t";
        echo iconv("UTF-8", "GBK","供应商联系方式")."\t";
        echo iconv("UTF-8", "GBK","供应商身份证号码")."\t";
        echo iconv("UTF-8", "GBK","供应商卡号")."\t";
        echo iconv("UTF-8", "GBK","银行类别")."\t";
        echo iconv("UTF-8", "GBK","供应商联系地址")."\t";
        echo "\n";

        $infos = M('return_money')->findall();

        foreach ($infos as $info){

            foreach($_POST as $key=>$value){

                $supplier = M('supplier')->where(['username'=>$info['originer']])->find();

                if(in_array($key,['interview_time','start_time','leave_time','pay_time','feedback_time','create_time'])){

                    if(empty($info[$key])){
                        echo iconv("UTF-8", "GBK",'')."\t";
                    }else{
                        echo iconv("UTF-8", "GBK",date('Y-m-d',$info[$key]))."\t";

                    }
                }else{

                    echo iconv("UTF-8", "GBK",$info[$key])."\t";
                }
            }

            echo iconv("UTF-8", "GBK",$supplier['number'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['name_co'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['mobile'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['identity'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['card_num'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['bank'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['address'])."\t";

            echo "\n";
        }

    }

    /**
     * @Title: exportResult
     * @Description: todo(导出返款结果2)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function exportResult(){

        if(empty($_POST)){
           echo('请选择字段！');die;
        }
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename= 结果格式2.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        foreach($_POST as $key=>$value){

            echo iconv("UTF-8", "GBK","$value")."\t";

        }

        echo iconv("UTF-8", "GBK","供应商编号")."\t";
        echo iconv("UTF-8", "GBK","职介全称")."\t";
        echo iconv("UTF-8", "GBK","供应商联系方式")."\t";
        echo iconv("UTF-8", "GBK","供应商身份证号码")."\t";
        echo iconv("UTF-8", "GBK","供应商卡号")."\t";
        echo iconv("UTF-8", "GBK","银行类别")."\t";
        echo iconv("UTF-8", "GBK","供应商联系地址")."\t";
        echo "\n";

        $infos = M('return_money2')->findall();

        foreach ($infos as $info){

            foreach($_POST as $key=>$value){

                $supplier = M('supplier')->where(['username'=>$info['originer']])->find();

                if(in_array($key,['interview_time','start_time','leave_time','pay_time','feedback_time','create_time'])){

                    if(empty($info[$key])){
                        echo iconv("UTF-8", "GBK",'')."\t";
                    }else{
                        echo iconv("UTF-8", "GBK",date('Y-m-d',$info[$key]))."\t";

                    }
                }else{

                    echo iconv("UTF-8", "GBK",$info[$key])."\t";
                }
            }

            echo iconv("UTF-8", "GBK",$supplier['number'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['name_co'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['mobile'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['identity'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['card_num'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['bank'])."\t";
            echo iconv("UTF-8", "GBK",$supplier['address'])."\t";

            echo "\n";
        }

    }

    /**
     * @Title: returnWay
     * @Description: todo(返款方式列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function returnWay(){

        $ways = M('return_way')->findall();

        $this->assign('ways', $ways);

        $this->display();
    }

    /**
     * @Title: addReturnWay
     * @Description: todo(添加方式)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function addReturnWay(){

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name = $_POST['name'];

            $day_num = $_POST['day_num'];

            $add = M('return_way')->add(['name'=>$name,'day_num'=>$day_num,'state'=>1]);

            if($add){

                go('returnWay');
            }
        }

        $ways = M('return_way')->findall();

        $this->assign('ways', $ways);

        $this->display();
    }

    /**
     * @Title: editReturnWay
     * @Description: todo(修改返款方式)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editReturnWay(){

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name = $_POST['name'];

            $day_num = $_POST['day_num'];

            $edit = M('return_way')->update(['name'=>$name,'day_num'=>$day_num]);

            if($edit){

                go('returnWay');
            }
        }

        $ways = M('return_way')->findall();

        $this->assign('ways', $ways);

        $this->display();
    }

//面试名单记录
    public function returnInterview(){

        $_GET = urldecode_array($_GET);
        //组合条件
        $cond=array();

        if(!empty($_GET['originer'])){

            $cond[]='originer like "%'.ltrim($_GET['originer']).'%"';
        }

        if(!empty($_GET['name'])){

            $cond[]='name like "%'.ltrim($_GET['name']).'%"';
        }

        if(!empty($_GET['company'])){

            $cond[]='company like "%'.ltrim($_GET['company']).'%"';
        }

        if(!empty($_GET['interview_time'])){
            $interview_time = strtotime($_GET['interview_time']);
            $cond[] = 'interview_time =' . $interview_time;
        }

        $count = M('return_interview_copy')->where($cond)->count();

        $page = new page($count,20);

        $interview = M('return_interview_copy')->where($cond)->order('create_time desc')->findall($page->limit());

        $this->assign('pages',$page->show());

        $this->assign('interviews', $interview);

        $this->display();
    }

    //价格流水记录
    public function returnPrice(){

        $_GET = urldecode_array($_GET);
        //组合条件
        $cond=array();

        if(!empty($_GET['origin'])){

            $cond[]='origin like "%'.ltrim($_GET['origin']).'%"';
        }

        if(!empty($_GET['company'])){

            $cond[]='company like "%'.ltrim($_GET['company']).'%"';
        }

        if(!empty($_GET['interview_time'])){
            $interview_time = strtotime($_GET['interview_time']);
            $cond[] = 'interview_time =' . $interview_time;
        }

        $count = M('return_price_copy')->where($cond)->count();

        $page = new page($count,20);

        $price = M('return_price_copy')->where($cond)->order('create_time desc')->findall($page->limit());

        $this->assign('pages',$page->show());

        $this->assign('prices', $price);

        $this->display();
    }

    //在职工资记录
    public function returnSalary(){

        $_GET = urldecode_array($_GET);
        //组合条件
        $cond=array();

        if(!empty($_GET['number'])){

            $cond[]='number like "%'.ltrim($_GET['number']).'%"';
        }

        if(!empty($_GET['name'])){

            $cond[]='name like "%'.ltrim($_GET['name']).'%"';
        }

        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'start_time =' . $start_time;
        }

        $count = M('return_salary_copy')->where($cond)->count();

        $page = new page($count,20);

        $salary = M('return_salary_copy')->where($cond)->order('create_time desc')->findall($page->limit());

        $this->assign('pages',$page->show());

        $this->assign('salarys', $salary);

        $this->display();
    }

    public function delReturnMoney(){

        $db=M('return_money');
        if($db->in($_POST)->del()){

            echo '1';
        }
    }

    public function delPrice(){

        $db=M('return_price_copy');
        if($db->in($_POST)->del()){

            echo '1';
        }
    }

    public function delInterview(){

        $db=M('return_interview_copy');
        if($db->in($_POST)->del()){

            echo '1';
        }
    }

    public function delSalary(){

        $db=M('return_salary_copy');
        if($db->in($_POST)->del()){

            echo '1';
        }
    }

    public function editInterview(){

        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $edit = M('return_interview_copy')->where(['id'=>$id])->update($_POST);

            if($edit){

                go('returnInterview');
            }
        }

        $interview = M('return_interview_copy')->where(['id'=>$id])->find();

        $this->assign('interview', $interview);

        $this->display();
    }

    public function editPrice(){

        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $edit = M('return_price_copy')->where(['id'=>$id])->update($_POST);

            if($edit){

                go('returnPrice');
            }
        }

        $price = M('return_price_copy')->where(['id'=>$id])->find();

        $this->assign('price', $price);

        $this->display();
    }

    public function editSalary(){

        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $edit = M('return_salary_copy')->where(['id'=>$id])->update($_POST);

            if($edit){

                go('returnSalary');
            }
        }

        $salary = M('return_salary_copy')->where(['id'=>$id])->find();

        $this->assign('salary', $salary);

        $this->display();
    }

    /**
     * @Title: salaryQueryLog
     * @Description:todo(薪资查询日志)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function salaryQueryLog(){
        if($_SERVER['REQUEST_METHOD']=='GET'){
            $_GET = urldecode_array($_GET);
            $where = '';
            $name = $_GET['company'];
            if(!empty($name)){
                $where[]= 'company like "%' . ltrim($name) . '%"';
            }
            $start_time = $_GET['start_time'];
            if(!empty($start_time)){
                $where[]="created_at > ".strtotime($start_time);
            }
            $end_time = $_GET['end_time'];
            if(!empty($end_time)){
                $where[]="created_at < ".strtotime($end_time);
            }
            $count = M("salary_query_log")->where($where)->count();
            $page = new page($count,20);
            $log = M("salary_query_log")->where($where)->findall($page->limit());
            $this->assign("count",$count);
            $this->assign("log",$log);
            $this->assign("pages",$page->show());
            $this->display();
        }
    }

    /**
     * @Title: pushRecruit
     * @Description:todo(职位推送)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function pushRecruit()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = $_GET['id'];
            $this->assign("id",$id);
            $this->display();
        } else {
            $id = $_POST['id'];;
            $uid = $_SESSION['uid'];
            if (empty($id)) {
                $this->error("请选择职位");
            }
            $title = $_POST['title'];
            $content = $_POST['content'];
            if(empty($title)){
                $title = "职位推荐";
            }
            if(empty($content)){
                $content ="系统为你推荐优秀职位";
            }
            $recruit = M('recruit')->where(['recruit_id'=>$id])->find();

            if(!empty($recruit)){

                $company = M('company_info')->where(['uid'=>$recruit['uid']])->find();

                $logo = $company['logo'];
            }

            $state = K('message')->createMission($uid, [
                'data_type' => 104,
                'title' => $title,
                'content' => $content,
                'data_avatar' => empty($logo)?"http://120.55.165.117/uploads/logo.png":$logo,
                'data_id' => $id
            ], 0, "系统推荐了新的职位，快来查看吧!!");
            if ($state) $this->success("推送完成");
            else {
                $this->success("推送失败");
            }
        }
    }

    /**
     * @Title: companyAddress
     * @Description:todo(获取公司地址)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function companyAddress(){

        if($_SERVER["REQUEST_METHOD"]=="GET"){
            $_GET = urldecode_array($_GET);
            $key = $_GET['content'];
            $state = $_GET['state'];
            if(!empty($key)){
                $con[] = 'name like "%'.ltrim($key).'%"';
            }
            if(!empty($state)){
                $con[] ='state = '.$state;
            }
            $db = M("user_add_company");
            if(empty($con))$count = $db->count();
            else{
                $count = $db->where($con)->count();
            }
            $page = new page($count,20);
            if(empty($con)){
                $company_add = $db->where()->findall($page->limit());
            }else{
                $company_add = $db->where($con)->findall($page->limit());
            }
            $this->assign('company',$company_add);
            $this->assign('pages',$page);
            $this->display();
        }

    }

    /**
     * @Title: delcompanyAddress
     * @Description:todo(删除公司地址)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function delCompanyAddress(){
        $id = $_GET['id'];
        if(M('user_add_company')->where(['company_id'=>$id])->del()){
            $this->success("删除成功");
        }else{
            $this->error("系统错误");
        }
    }

    /**
     * @Title: acceptCompanyAddress
     * @Description:todo(通过公司地址)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function acceptCompanyAddress(){
        $id = $_GET['id'];
        if(M('user_add_company')->where(['company_id'=>$id])->update(['state'=>1])){
            $this->success("审核通过");
        }else{
            $this->error("系统错误");
        }
    }

    /**
     * @Title: refuseCompanyAddress
     * @Description:todo(拒绝公司地址)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function refuseCompanyAddress(){
        $id = $_GET['id'];
        if(M('user_add_company')->where(['company_id'=>$id])->update(['state'=>2])){
            $this->success("已拒绝");
        }else{
            $this->error("系统错误");
        }
    }

    /**
     * @Title: addCompanyAddress
     * @Description:
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function addCompanyAddress(){
        if($_SERVER['REQUEST_METHOD']=="GET"){
            $this->display();
        }else{
            $address = $_POST['address'];
            $name = $_POST['name'];
            $data = [
                'address'=>$address,
                'name'=>$name,
                'state' => 1,
                'created'=>time()
            ];
            if(M('user_add_company')->insert($data)){
                $this->success("添加成功");
            }else{
                $this->error("添加失败");
            }
        }
    }

    /**
     * @Title: updateCompanyAddress
     * @Description:todo(修改公司地址)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function updateCompanyAddress(){
        if($_SERVER['REQUEST_METHOD']=="GET"){
            $_GET = urldecode_array($_GET);
            $id = $_GET['id'];
            $address = $_GET['address'];
            $name=$_GET['name'];
            $this->assign("id",$id);
            $this->assign("name",$name);
            $this->assign("address",$address);
            $this->display();
        }else if($_SERVER['REQUEST_METHOD']=="POST"){
            $id = $_POST['id'];
            $name = $_POST['name'];
            $address = $_POST['address'];
            $data = [
               'name'=>$name,
               'address'=>$address
            ];
            M("user_add_company")->where(['company_id'=>$id])->update($data);
            $this->success("修改成功");
        }
    }
}

