<?php


class recruitExtendControl extends Control {

    private $company;
    private $recruit;

    function __construct()
    {
        parent::__construct();
        $this->company = K('company');
        $this->recruit = K('recruit');
    }

    /**
     * @Title: recruitList
     * @Description: todo(搜索职位列表-二期)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function recruitList(){

        $keywords = $_POST['keywords'];//关键词

        $address = $_POST['address'];//区域(中文)
//薪资0=不限 1=面议 2000=2000元/月以下 200103000=2001-3000元/月 300105000=3001-5000元/月 500108000=5001-8000元/月
// 800110000=8001-10000元/月1000115000=10001-15000元/月1500025000=15001-25000元/月2500000000=25001元/月以上
        $salary = $_POST['salary'];//薪资

        $welfare = $_POST['welfare'];//945包住宿946班车947工作餐948入职返现949季度旅游950节日福利951五险一金952加班补助953年底双薪1010五险

        $class_id = $_POST['class_id'];//二级或三级分类id,传一个后台会查出二级或者三级的

        $origin = $_POST['origin'];//0全部1开心直招2企业直招3代招

        $star = $_POST['star'];//0不限12345各代表1~5星

        $work_exp = $_POST['work_exp'];//0=不限 1=无经验 2=1年以下 3=1-3年 4=3-5年 5=5-10年 6=10年以上

        $jobType = $_POST['jobType'];//1全职3附近4兼职5入职返现6包吃住7校园招聘8海外招聘

        $high_salary = $_POST['high_salary'];

        $where = 'expiration_time >' . time().' AND state=1 AND `check` = 1';

        if(!empty($address) && !intval($address)){
            $db = M('city');
            if($address!='新区'){
                $address = rtrim($address,'区');
            }
            $address = rtrim($address,'市');
            $lists = $db->where('name LIKE "%' . $address . '%"')->find();
            $id = $lists['id'];
            $where .=' AND ( hp_recruit.address = ' . $id .' or hp_recruit.city = '. $id.' or hp_recruit.town = '.$id.')';
        }

        if(!empty($address) && intval($address)){

            $where .=' AND ( hp_recruit.address = ' . $address .' or hp_recruit.city = '. $address.' or hp_recruit.town = '.$address.')';
        }

        if($high_salary == 1){

            $where .= ' AND high_salary = 1';
        }

        if(!empty($keywords)){

            $where .= ' AND CONCAT(recruit_name,company_name) LIKE "%' . $keywords . '%"';
        }

        if(!empty($salary)){

            $where .= ' AND salary =' . $salary;
        }

        if(!empty($welfare)){

            $where .= " AND welfare like '%".$welfare."%'";
        }

        if(!empty($class_id)){

            $where .= ' AND (class_two= '.$class_id .' or class = '.$class_id.')';
        }

        if(!empty($origin)){

            $where .= ' AND origin = '. $origin;
        }

        if(!empty($star)){

            $where .= ' AND recruit.star = ' . $star;

        }

        if(!empty($work_exp)){

            $where .= ' AND work_exp = '. $work_exp;
        }

        $db = M('recruit');

        if(!empty($jobType)){

            switch ($jobType)
            {
                case 2:
                    $where .= " AND company_info.relieved=1";

                    $db=V('recruit');
                    $db->view=array(
                        'company_info'=>array(
                            'type'=>'inner',
                            'on'=>'company_info.uid=recruit.uid'
                        )
                    );
                    break;
                case 3:
                    $lng = $_POST['lng'];
                    $lat = $_POST['lat'];
                    if(empty($lng)||empty($lat)){

                        Json_error('获取定位失败');
                    }
                    $squares = returnSquarePoint($lng, $lat,10);
                    $con = "lat<>0 and lat>".$squares['minLat']." and lat<".$squares['maxLat']." and lng>".$squares['minLng']." and lng<".$squares['maxLng'];
                    $company_ids = M('company_info')->field('uid')->where($con)->findall();
                    if(!empty($company_ids)){
                        $cids = [];
                        foreach ($company_ids as $key => $value) {
                            $cids [] = $value['uid'];
                        }
                        $cids_str = implode(',',$cids);
                        $where .= ' AND uid in ('.$cids_str.')';
                    }
                    break;
                case 4:

                    $where .= ' AND jobs_property =1';
                    break;
                case 5:

                    $where .= " AND welfare like '%948%'";
                    break;

                case 6:
                    $where .= " AND welfare like '%945%'";
                    break;

                case 7:
                    $where .= ' AND graduates=1';
                    break;

                case 8:
                    $where .= ' AND address=0';
            }
        }

        $field = 'created,istop,start_time,recruit_id,recruit_name,recruit_num,origin,return_money,recruit.address,recruit.city,recruit.town,recruit.uid,start_time,istop,company_name,welfare,work_exp,degree,salary,recruit.star,verify';
        $lnum = 20; //每页显示数量
        $order = 'istop DESC,start_time desc,recruit_id desc';
        $count = $db->field($field)->where($where)->count();
        $page = new page($count, $lnum);

        $jobs = $db->field($field)->order($order)->where($where)->findall($page->limit());
        //处理选项和联动数据为具体值
        $convert = new data;
        if ($jobs) {
            foreach ($jobs as $key => $value) {
                $jobs[$key] = $convert->convert($value);
            }
        }

        foreach ($jobs as $key => $value) {

            $company = M('company_info')->field('logo,lng,lat')->where(array('uid'=>$value['uid']))->find();
            $jobs[$key]['logo'] = $company['logo'];
            if(strpos($value['welfare'],'入职返现')!==false){
                $jobs[$key]['fan']=1;
            }else{
                $jobs[$key]['fan']=0;
            }

            $jobs[$key]['start_time'] = date('Y-m-d',$jobs[$key]['start_time']);

            if($jobType == 3){

                if(empty($lat) || empty($lng) || empty($company)){

                    $info['jobs']=[];
                    $info['count_page']=1;

                    Json_success('获取成功',$info);
                }
                $distances = getDistanceBetweenPoints($lat, $lng, $company['lat'], $company['lng']);

                $jobs[$key]['distance'] = $distances['kilometers'];
            }

        }

        if($jobType == 3 && !empty($lng) && !empty($lat)){

            foreach ($jobs as $key=>$job){

                $distance[$key] = $job['distance'];
            }
            array_multisort($distance,$jobs);
        }

        $info['jobs']=$jobs;
        $info['count_page']=$page->total_page;

        Json_success('获取成功',$info);
    }

    /**
     * @Title: getCityByParent
     * @Description: todo(得到该城市下的隶属城市)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function getCityByParent(){

        $pid = $_POST['id'];

        $city = M('city');

        $citys = $city->order('id ASC')->where('pid = '.$pid)->findall();

        Json_success('获取成功',$citys);
    }

    public function test()
    {

        $href = '/usr/share/nginx/html/hpjobweb';
        $data = validateIdCardImg(base64_encode(file_get_contents($href.'/'.$_POST['face_base'])),base64_encode(file_get_contents($href.'/'.$_POST['back_base'])));
        var_dump($data[0]['num']);
    }
}
