<?php

/*
 * Describe   : 自定义扩展函数
 */
//去除数组中所有字符串两边空格
function TrimArray($welfare){
    if (!is_array($welfare))
        return trim($welfare);
    return array_map('TrimArray', $welfare);
}

function alert($msg) {
    echo '<script>alert("' . $msg . '");window.history.back();</script>';
}
//创建一个32位随机令牌码
function token()
{
    return md5(str_shuffle(chr(mt_rand(32, 126)) . uniqid() . microtime(TRUE)));
}
/**
 * 扣除/增加用户积分。减少SESSION和数据库
 * @param int $point 扣除的积分
 * @param int $user 扣除的用户ID
 */
function deductPoint($point,$user=NULL){

    if(is_null($user)){
        $user=$_SESSION['uid'];
    }
    $db=M('user_point');
    $user_point = $db->where('uid ='.$user)->find();

    if(empty($user_point)){
        $db->insert(array('uid'=>$user,'point'=>$point));
    }else{
        $db->inc('point','uid='.$user,$point);
    }

    $_SESSION['point']+=$point;
}

/**
 * @Title: getPointRule
 * @Description: todo(获取积分)
 * @author nipeiquan
 * @param $type
 * @return  mixed  返回类型
 */
function getPointRule($type){
    $db=K('backend/point_rule');
    $data=$db->getPointRule($type);
    return $data['operator'];
}

/**
 * 写入用户操作日志
 * @param type $con     操作内容
 * @param type $point   积分变化
 * @param type $uid     用户ID
 */
function writeOptLog($con,$point=0,$uid=NULL){
    if(is_null($uid)){
        $uid=$_SESSION['uid'];
    }
    $data=array(
        'uid'=>$uid,
        'content'=>$con,
        'point'=>$point,
        'created'=>time(),
        'ip'=>ip_get_client(),//操作ip
        'username'=>$_SESSION['username'],
        'time'=>time(),
    );
    $db=M('opt_log');
    $db->insert($data);
}

/**
 * @Title: writeComLog
 * @Description: todo(用户佣金操作日志)
 * @author liuzhipeng
 * @param $con     操作内容
 * @param null $uid    用户ID
 * @return  void  返回类型
 */
function writeComLog($cont,$cos,$company=NULL,$job_time=NULL){
    $uid=$_SESSION['uid'];
    $data=array(
        'uid'=>$uid,
        'commission'=>$cos,
        'create_time'=>time(),
        'company'=>$company,
        'job_time'=>$job_time,
        'content'=>$cont,
        'ip'=>ip_get_client(),//操作ip
        'username'=>$_SESSION['username'],
    );
    $db=M('commission_log');
    $db->insert($data);

}

/**
 * @Title: sendSmsMsg
 * @Description: todo(发送验证码)
 * @author nipeiquan
 * @param $mobile
 * @param $con
 * @param string $new_msg
 * @param int $type
 * @return  int  返回类型
 */
function sendSmsMsg($mobile,$con,$new_msg='',$type = 0){
    if (empty($mobile)) {
        return false;
    }
    //2.发送短信
    $post_data = array();
    $post_data['account'] = iconv('GB2312', 'GB2312',"kaixinwang");
    $post_data['pswd'] = iconv('GB2312', 'GB2312',"Kaixinwang123");
    $post_data['mobile'] =$mobile;

    $_SESSION['smscode'] = mt_rand(1000,9999);

    $_SESSION['phone'] = $mobile;
    if(empty($con) && !empty($new_msg)){
        $msg = $new_msg;
    }else{
        $msg = '尊敬的' . $mobile . '用户，您好，本次'.$con.'的验证码是' . $_SESSION['smscode'].',有效期为5分钟。';
    }
    $post_data['msg']=mb_convert_encoding("$msg",'UTF-8', 'auto');
    //$post_data['msg'] = iconv('GBK', 'UTF-8//IGNORE', $msg);
    $url='http://222.73.117.156/msg/HttpBatchSendSM?';
    $o="";
    foreach ($post_data as $k=>$v)
    {
        $o.= "$k=".urlencode($v)."&";
    }
    $post_data=substr($o,0,-1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);

    $code = M('code');

    $code->add(['type'=>$type,'code'=>$_SESSION['smscode'],'mobile'=>$mobile,'created'=>time()]);
    //preg_match("#,(\d+)$#iUs", $result,$res);
    $res = explode(",", $result);

    return isset($res[1])?$res[1]:0 ;
}


/**
 * 删除编译模板，格式:应用/控制器/方法
 * @param type $path 
 */
function delCompileTpl($path) {
    $path_array = explode('/', trim($path, '/'));
    $path_nums = count($path_array);
    switch ($path_nums) {
        case 1:$temp = APP_GROUP . '_G/' . $path_array[0];
            break;
        case 3:$temp = APP_GROUP . '_G/' . $path_array[0] . '_A/' . $path_array[1] . '_C/' . $path_array[2];
            break;

        default:
            break;
    }
    $path = PATH_TEMP . '/Applications/' . $temp;
    dir::del($path);
}

function array_filter_trim($value) {
    if (trim($value) == '') {
        return FALSE;
    }
    return TRUE;
}

/*
 * 格式化层级数据
 */

function formatLevelData($data, $pid = 0) {
    $arr = array();
    foreach ($data as $v) {
        if ($v['pid'] == $pid) {
            $arr[$v['laid']] = $v;
            $arr[$v['laid']]["data"] = formatLevelData($data, $v['laid']);
        }
    }
    return $arr;
}

/**
 * 格式化数据为层级化数据
 * @param type $data 需要格式化的
 * @param type $format 数据的原索引格式[主键字段名(例如id),层级字段名(例如pid)]
 * @param type $pid
 * @return type 
 */
function formatLevelData2($data, $format, $pid = 0) {
    $arr = array();
    foreach ($data as $v) {
        if ($v[$format[1]] == $pid) {
            $arr[$v[$format[0]]] = $v;
            $arr[$v[$format[0]]]["son_data"] = formatLevelData2($data, $format, $v[$format[0]]);
        }
    }
    return $arr;
}

/**
 * 将数组格式化为父子级关系（2级）
 * @param type $data 原数据
 * @param type $format 数据的原索引格式[主键字段名(例如id),层级字段名(例如pid)]
 * @return type 格式化后的数据 
 */
function formatParentData($data) {
    $data_num = count($data);
    $new_data = array();
    for ($i = 0; $i < $data_num; $i++) {
        if ($data[$i]['pid'] == 0) {
            $new_data['first'][$data[$i]['laid']] = $data[$i]['title'];
        } else {
            $new_data[$data[$i]['pid']][$data[$i]['laid']] = $data[$i]['title'];
        }
    }
    return $new_data;
}

/**
 * 将数组格式化为父子级关系（2级）
 * @param type $data 原数据
 * @param type $format 数据的原索引格式[主键字段名(例如id),层级字段名(例如pid),主要字段名(例如title、name)]
 * @return type 格式化后的数据 
 */
function formatParentData2($data,$format) {
    $data_num = count($data);
    $new_data = array();
    for ($i = 0; $i < $data_num; $i++) {
        if ($data[$i][$format[1]] == 0) {
            $new_data['first'][$data[$i][$format[0]]] = $data[$i][$format[2]];
        } else {
            $new_data["{$data[$i][$format[1]]}"][$data[$i][$format[0]]] = $data[$i][$format[2]];
        }
    }
    return $new_data;
}

function urlencode_array($var)
{
    if (is_array($var))
    {
        return array_map('urlencode_array', $var);
    }
    else
    {
        return urlencode($var);
    }
}
function urldecode_array($var)
{
    if (is_array($var))
    {
        return array_map('urldecode_array', $var);
    }
    else
    {
        return urldecode($var);
    }
}
//不编码中文
function json_encode_cn($arr){
    return urldecode(json_encode(urlencode_array($arr)));
}

/**
 * 组合属性变量 
 */
function buildAttrVar($attr) {
    $str = '';
    $attr = str_replace(' ', '', $attr);
    if (isset($attr)) {
        if (strpos($attr, '$') === FALSE) {
            $str = $attr;
        } else {
            $preg = '/(\$[a-zA-Z_]\w*)(\[(\'|")\w+(\'|")\])*/';
            $str = preg_replace($preg, '<?php echo ${0};?>', $attr);
        }
    }
    return $str;
}

/**
 * 取得除了a c m 等的GET参数
 * @param type $unset 除了acm需要删除的参数
 */
function getCleanUriArg($unset = array()) {
    $get = $_GET;
    unset($get['a']);
    unset($get['c']);
    unset($get['m']);
    if (!empty($unset)) {
        foreach ($unset as $value) {
            unset($get[$value]);
        }
    }
    return $get;
}

/**
 * 取得Email模板
 * @param type $data 数据数组
 * @param type $type 模板类型
 * @return type 
 */
function getEmailTpl($type, $data = array()) {
    if (!isset($data['web_name'])) {
        $data['web_name'] = C('WEB_NAME');
    }
    if (!isset($data['web_host'])) {
        $data['web_host'] = __ROOT__;
    }
    $filename = PATH_ROOT . '/caches/email/' . $type . '.php';
    if (file_exists($filename)) {
        $tpl = include $filename;
    } else {
        $db = M('mail_tpl');
        $tpl = $db->field('subject,content')->where("type='$type'")->find();
    }
    foreach ($data as $key => $value) {
        $tpl['subject'] = str_replace('{' . $key . '}', $value, $tpl['subject']);
        $tpl['content'] = str_replace('{' . $key . '}', $value, $tpl['content']);
    }
    return $tpl;
}

/**
 * 获取节点的所有子节点ID
 * @param int $nid 节点ID
 * @param array $field 主键字段名(如：nid、id)、父字段名(如：pid)。默认：array('nid','pid');
 * @param $data 默认的数据,一般不传
 * @return 子节点数组
 */
function node_son_id($nid,$field=array('nid','pid'),$data=NULL){
    $db=M('node');
    if($nid!==FALSE){
        $n=$db->field($field[0])->where($field[0].'='.$nid)->findall();
    }else{
        $n=$data;
    }
    $nodes=array($field[0]=>array());
    foreach ($n as $value) {
            $v=$db->field($field[0])->where($field[1].'='.$value[$field[0]])->findall();
            if($v){
                $a=node_son_id(FALSE,$field,$v);
                foreach ($a[$field[0]] as $v_a) {
                    $nodes[$field[0]][]=$v_a;
                }
            }
            $nodes[$field[0]][]=$value[$field[0]];
    }
    return $nodes;
}

/**
 * @Title: filePutArray
 * @Description: todo(把数组写入文件)
 * @author zhouchao
 * @param $array    写入的数组
 * @param $path     写入的路径
 * @return  int  返回类型   成功TRUE，失败FALSE
 */
function filePutArray($array, $path) {
    $str = "<?php \r\n";
    $str.= "if (!defined('PATH_LC'))exit;\nreturn ";
    $str.= var_export($array, true);
    $str.= "; \r\n?>";
    return file_put_contents($path, $str);
}


/**
 * post请求
 * @param string $url
 * @param array $data $data = array("firmid" => $store['uid']);
 * @return mixed
 */
function dopost($url,$data=null){
	
	header("Content-type:text/html;charset=utf-8");
	include './Snoopy.class.php';
	$snoopy = new Snoopy();
	$snoopy->submit($url, $data);
	$results = $snoopy->results;
	$results = iconv('gbk', 'utf-8', $results);
	$arr = json_decode($results,true);
	return $arr[0];
}
/**
 * 通过地址得到经纬度
 * @param string $adress
 * @return multitype:unknown
 */
function getLocation($adress){

	$url='http://api.map.baidu.com/geocoder/v2/?address='.$adress.'&output=json&ak=VvLKaDZCCalQjBFzWhKaaNyi';
	$html = file_get_contents($url);
	$location = json_decode($html,true);
	$lng = $location['result']['location']['lng'];
	$lat = $location['result']['location']['lat'];
	$info = array(
			'lng'=>$lng,
			'lat'=>$lat
	);
	return $info;
}
/**
 * 得到城市名称
 * @param float $lat
 * @param float $lng
 * @return unknown
 */
function getCity($lat,$lng,$type=1){

    if($type!=1){//是否转成高德坐标
        $point = gaodeConvertedBaidu($lat,$lng);
        $lng = $point[0]['x'];
        $lat = $point[0]['y'];
    }

	$url='http://api.map.baidu.com/geocoder/v2/?ak=VvLKaDZCCalQjBFzWhKaaNyi&location='.$lat.','.$lng."&output=json";
	$html = file_get_contents($url);
	$location = json_decode($html,true);
	$city = $location['result'];
	return $city;
}

function gaodeConvertedBaidu($lat,$lng){
    $url='http://api.map.baidu.com/geoconv/v1/?coords='.$lng.','.$lat.'&from=3&to=5&ak=EF06cfb26173665ad80b8edf6a328192&output=json';
    $html = file_get_contents($url);
    $location = json_decode($html,true);
    $city = $location['result'];
    return $city;

}
/**
 * 得到公里圆
 * @param float $myLng
 * @param float $myLat
 * @param real $distance
 * @return multitype:number
 */
function returnSquarePoint($myLng, $myLat,$distance = 5){
	
	$range = 180 / pi() * $distance / 6372.797;
	$lngR = $range / cos($myLat * pi() / 180);
	$maxLat = $myLat + $range;//最大纬度
	$minLat = $myLat - $range;//最小纬度
	$maxLng = $myLng + $lngR;//最大经度
	$minLng = $myLng - $lngR;//最小经度

	$info = array(
			'maxLat'=>$maxLat,
			'minLat'=>$minLat,
			'maxLng'=>$maxLng,
			'minLng'=>$minLng
	);
	return $info;
}

/**
 * 向app输出成功json信息
 * @param String $msg
 * @param array $data
 */
function Json_success($msg,$data=array(),$session_id=''){


	$info = array(
		'status'=>1,
		'msg'=>$msg,
        'sessionid' =>empty($session_id)?(empty($_POST['sessionid'])?$_SESSION['sessionid']:$_POST['sessionid']):$session_id,
		'data'=>$data
	);
	echo json_encode($info);

    $data = '成功返回----'.__URL__.' time='.date("Y-m-d H:i:s").' json='.json_encode($info)." \r\n ";

    file_put_contents(PATH_TEMP.'/log/json.log','参数:'.json_encode($_POST,JSON_UNESCAPED_UNICODE),FILE_APPEND);
    file_put_contents(PATH_TEMP.'/log/json.log',$data,FILE_APPEND);

	exit;
}

/**
 * 向app输出失败json信息
 * @param String $msg
 * @param array $data
 */
function Json_error($msg,$data=array(),$status=0){

	$info = array(
		'status'=>$status,
		'msg'=>$msg,
        'sessionid' =>empty($session_id)?(empty($_SESSION)?session_id():$_POST['sessionid']):$session_id,
		'data'=>$data
	);
	echo json_encode($info);

    $data = '错误返回----'.__URL__.' time='.date("Y-m-d H:i:s").' json='.json_encode($info)." \r\n ";
    file_put_contents(PATH_TEMP.'/log/json.log','参数:'.json_encode($_POST,JSON_UNESCAPED_UNICODE),FILE_APPEND);
    file_put_contents(PATH_TEMP.'/log/json.log',$data,FILE_APPEND);
	exit;
}

/**
 * 根据指定的键对数组排序
 *
 * 用法：
 * @code php
 * $rows = array(
 *           array('id' => 1, 'value' => '1-1', 'parent' => 1),
 *           array('id' => 2, 'value' => '2-1', 'parent' => 1),
 *           array('id' => 3, 'value' => '3-1', 'parent' => 1),
 *           array('id' => 4, 'value' => '4-1', 'parent' => 2),
 *           array('id' => 5, 'value' => '5-1', 'parent' => 2),
 *           array('id' => 6, 'value' => '6-1', 'parent' => 3),
 * );
 *
 * $rows = sortByCol($rows, 'id', SORT_DESC);
 * dump($rows);
 * // 输出结果为：
 * // array(
 * //         array('id' => 6, 'value' => '6-1', 'parent' => 3),
 * //         array('id' => 5, 'value' => '5-1', 'parent' => 2),
 * //         array('id' => 4, 'value' => '4-1', 'parent' => 2),
 * //         array('id' => 3, 'value' => '3-1', 'parent' => 1),
 * //         array('id' => 2, 'value' => '2-1', 'parent' => 1),
 * //         array('id' => 1, 'value' => '1-1', 'parent' => 1),
 * // )
 * @endcode
 *
 * @param array $array 要排序的数组
 * @param string $keyname 排序的键
 * @param int $dir 排序方向
 *
 * @return array 排序后的数组
 */
function sortByCol($array, $keyname, $dir = SORT_ASC)
{
	return sortByMultiCols($array, array($keyname => $dir));
}
/**
 * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
 *
 * 用法：
 * @code php
 * $rows = Helper_Array::sortByMultiCols($rows, array(
 *           'parent' => SORT_ASC,
 *           'name' => SORT_DESC,
 * ));
 * @endcode
 *
 * @param array $rowset 要排序的数组
 * @param array $args 排序的键
 *
 * @return array 排序后的数组
 */
function sortByMultiCols($rowset, $args)
{
	$sortArray = array();
	$sortRule = '';
	foreach ($args as $sortField => $sortDir)
	{
		foreach ($rowset as $offset => $row)
		{
			$sortArray[$sortField][$offset] = $row[$sortField];
		}
		$sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
	}
	if (empty($sortArray) || empty($sortRule)) { return $rowset; }
	eval('array_multisort(' . $sortRule . '$rowset);');
	return $rowset;
}

function frameCallBack($msg,$data=array()){

    if(empty($data)){
        echo "<script type='text/javascript'>parent.callback('".$msg."')</script>";
        exit(1);
    }else{
        echo "<script type='text/javascript'>parent.callback('".$msg."','".json_encode($data)."')</script>";
        exit(1);
    }
}

/**
 * @Title: from_time
 * @Description: todo(精确时间间隔函数)
 * @author zhouchao
 * @param $time
 * @param $str
 * @return  bool|string  返回类型
 */
function from_time($time){

    $way = time() - $time;
    $r = '';
    if($way < 60){
        $r = '刚刚';
    }elseif($way >= 60 && $way <3600){
        $r = floor($way/60).'分钟前';
    }elseif($way >=3600 && $way <86400){
        $r = floor($way/3600).'小时前';
    }elseif($way >=86400 && $way <2592000){
        $r = floor($way/86400).'天前';
    }elseif($way >=2592000 && $way <15552000){
        $r = floor($way/2592000).'个月前';
    }else{
        $r = date("m-d",$time);
    }
    return $r;
}

/**
 * @Title: getDistanceBetweenPoints
 * @Description: todo(计算两个坐标间距离)
 * @author zhouchao
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @return  array  返回类型
 */
function getDistanceBetweenPoints($lat1, $lng1, $lat2, $lng2) {
    $theta = $lng1 - $lng2;
    $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
    $miles = acos($miles);
    $miles = rad2deg($miles);
    $miles = $miles * 60 * 1.1515;//英里
    $feet = $miles * 5280;//英尺
    $kilometers = $miles * 1.609344;//千米
    $kilometers = round($kilometers,2);//千米
    return compact('miles','feet','kilometers');
}

/**
 * @Title: parseExcel
 * @Description: todo(解析excel)
 * @author zhouchao
 * @param $file
 * @return  mixed  返回类型
 */
function parseExcel($file){

    $data = new Spreadsheet_Excel_Reader();

    $data->setOutputEncoding('utf-8');
    $data->read($file);

    return $data->sheets[0]['cells'];

}

/**
 * @Title: addMessages
 * @Description: todo(发消息)
 * @author nipeiquan
 * @return  void  返回类型
 */
function addMessages($uid,$content,$title,$is_mess=0,$mobile=0,$class=0,$address='',$salary=2,$is_hot=3){
    $db = M('user_message');
    if($is_mess==1 && !empty($mobile)){
        sendSmsMsg($mobile,$msg=$content);
    }
    $data = array(
        'uid'=>$uid,
        'content'=>$content,
        'title'=>$title,
        'is_mess'=>$is_mess,
        'class'=>$class,
        'address'=>$address,
        'salary'=>$salary,
        'is_hot'=>$is_hot,
        'created'=>time()
    );
    return $db->add($data);
}

/**
 * @Title: addNewMessages
 * @Description: todo(新发消息)
 * @author nipeiquan
 * @param $uid
 * @param $content
 * @param $title
 * @param $type
 * @param $data_type
 * @param $data_id
 * @param int $is_mess
 * @param int $mobile
 * @param int $class
 * @param string $address
 * @param int $salary
 * @param int $is_hot
 * @return  mixed  返回类型
 */
function addNewMessages($uid,$content,$title,$type,$data_type,$data_id,$is_mess=0,$mobile=0,$class=0,$address='',$salary=2,$is_hot=3){
    $db = M('new_message');
    if($is_mess==1 && !empty($mobile)){
        sendSmsMsg($mobile,$msg=$content);
    }
    $data = array(
        'uid'=>$uid,
        'content'=>$content,
        'title'=>$title,
        'type'=>$type,
        'data_type'=>$data_type,
        'data_id'=>$data_id,
        'class'=>$class,
        'address'=>$address,
        'salary'=>$salary,
        'is_hot'=>$is_hot,
        'create_at'=>time()
    );

    $mid = $db->insert($data);

    if($mid){

        $data_info = [
            'mid'=>$mid,
            'buid'=>$uid,
            'create_at'=>time()
        ];

        return M('new_message_user')->add($data_info);

    }else{

        return false;
    }
}

function cut_str($content,$length){

    return mb_substr(trim(str_replace(array("\n","\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;','&nbsp;'), '', strip_tags($content))),0,$length,'utf-8');

}

/**
 * Enter 多用户推送
 * @param array $client_id 客户端id
 * @param array $param 推送参数
 * @param string $type 推送类型：用户或快递员
 * @return boolean
 */
function push($client_id=array(),$param=array('hidden'=>'','title'=>'','content'=>''),$type='default' ){

    if (empty($client_id) || empty($param)) {
        return false;
    }

    $path = PATH_ROOT . '/web/getui/' ;
    require_once($path. 'IGt.Push.php');
    require_once($path. 'igetui/IGt.AppMessage.php');
    require_once($path. 'igetui/IGt.APNPayload.php');
    require_once($path. 'igetui/template/IGt.BaseTemplate.php');
    require_once($path. 'IGt.Batch.php');
    $appConfig = C('appConfig');
    $appConfigType = $appConfig[$type];

    putenv("gexin_pushList_needDetails=true");
    putenv("gexin_pushList_needAsync=true");
    //多推接口
    $igt = new IGeTui($appConfig['host'],$appConfigType['appkey'],$appConfigType['mastersecret']);

    $template = IGtNotificationTemplateDemo($appConfigType,$param);
    $template = IGtTransmissionTemplateDemo($appConfigType,$param);

    //个推信息体
    $message = new IGtListMessage();


    $message->set_isOffline(true);//是否离线
    $message->set_offlineExpireTime(3600*12*1000);//离线时间
    $message->set_data($template);//设置推送消息类型
    //$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送

    try {
        $contentId = $igt->getContentId($message);	//根据TaskId设置组名，支持下划线，中文，英文，数字

        $targetList= array();
        foreach ($client_id as $k=>$cid) {
            //接收方1
            $target1 = new IGtTarget();
            $target1->set_appId($appConfigType['appid']);
            $target1->set_clientId(trim($cid));
            //$target1->set_alias(Alias);
            $targetList[] = $target1;
        }
        $rep = $igt->pushMessageToList($contentId, $targetList);
    } catch (Exception $e) {
        $rep = array();
//			print_r($e);
        $rep['result'] = $e->getTraceAsString();
    }


    return $rep ;
}

//通知透传消息模板　
function IGtNotificationTemplateDemo($appConfigType,$param=array('hidden'=>'','title'=>'','content'=>'')){
    $template =  new IGtNotificationTemplate();
    $template->set_appId($appConfigType['appid']);//应用appid
    $template->set_appkey($appConfigType['appkey']);//应用appkey
    $template->set_transmissionType(1) ;     //透传消息类型
    $template->set_transmissionContent($param['hidden']);//透传内容
    $template->set_title($param['title']);//通知栏标题
    $template->set_text($param['content']);//通知栏内容
    $template->set_logo("logo.png");
    $template->set_logoURL("http://121.40.251.178/uploads/logo.png"); //通知栏logo链接
    $template->set_isRing(true);//是否响铃
    $template->set_isVibrate(true);//是否震动
    $template->set_isClearable(true);//通知栏是否可清除
    return $template;
}

function IGtTransmissionTemplateDemo($appConfigType,$param=array('hidden'=>'','title'=>'','content'=>'')){

    $template =  new IGtTransmissionTemplate();
    $template->set_appId($appConfigType['appid']);//应用appid
    $template->set_appkey($appConfigType['appkey']);//应用appkey
    $template->set_transmissionType(2);//透传消息类型
    $template->set_transmissionContent(json_encode_cn($param['hidden']));//透传内容
    //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息

    //APN简单推送
    $apn = new IGtAPNPayload();
    $alertmsg=new SimpleAlertMsg();
    $alertmsg->alertMsg = $param['title'];
    $apn->alertMsg = $alertmsg;
    $apn->badge=1;
    $apn->sound="";
    $apn->add_customMsg("payload","payload");
    $apn->contentAvailable=0;
    $apn->category="ACTIONABLE";
    $template->set_apnInfo($apn);


    //PushApn老方式传参
//	    $template = new IGtAPNTemplate();
//		$template->set_pushInfo("", 1, "", "default", "", "", "", ""); // default是默认铃声，静音的话：com.gexin.ios.silence


    return $template;
}

/**
 * @Title: validateSmsCode
 * @Description: todo(判断验证码)
 * @author nipeiquan
 * @param $mobile
 * @param $code
 * @return  bool  返回类型
 */
function validateSmsCode($mobile,$code){

    $codeArr = M('code')->where(['mobile'=>$mobile])->order('created desc')->find();

    if(!$codeArr ||  time() >  $codeArr['created'] +300 || $codeArr['code'] != $code){

        return false;
    }

    return true;
}

 function validateIdCardImg($face_base,$back_base){

    $host = "https://dm-51.data.aliyun.com";
    $path = "/rest/160601/ocr/ocr_idcard.json";
    $method = "POST";
    $appcode = "c62c0021f0de4aa1a58dedf5a688f072";
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    //根据API的要求，定义相对应的Content-Type
    array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
    $body = [
        'inputs'=>[
            [
                'image'=>[
                    'dataType'=>50,
                    'dataValue'=>$face_base
                ],
                'configure'=>[
                    'dataType'=>50,
                    'dataValue'=>"{\"side\": \"face\"}",
                ]
            ]
        ],
    ];

    $body = json_encode($body);

    $url = $host . $path;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt ($curl , CURLOPT_TIMEOUT ,50);

    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

    $data = curl_exec($curl);


    $data = json_decode($data,true);

    $outputs_face = $data['outputs'];

    foreach ($outputs_face as $key=>$output){

        $outputs_face[$key] = json_decode($output['outputValue']['dataValue'],true);

    }

    $body = [
        'inputs'=>[
            [
                'image'=>[
                    'dataType'=>50,
                    'dataValue'=>$back_base
                ],
                'configure'=>[
                    'dataType'=>50,
                    'dataValue'=>"{\"side\": \"back\"}",
                ]
            ]
        ]
    ];

    $body = json_encode($body);

    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

    $data = curl_exec($curl);

    curl_close($curl);

    $data = json_decode($data,true);

    $outputs_back = $data['outputs'];

    foreach ($outputs_back as $key=>$output){

        $outputs_back[$key] = json_decode($output['outputValue']['dataValue'],true);

    }

    $outputs = array_merge($outputs_face,$outputs_back);

    return $outputs;
}

function random($length)
{
    $hash = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($chars) - 1;
    mt_srand((double)microtime() * 1000000);
    for($i = 0; $i < $length; $i++)
    {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

    /**
     * @Title: openRegister
     * @Description: todo(环信注册)
     * @author nipeiquan
     * @param $username
     * @param $password
     * @param $nickname
     * @return  mixed  返回类型
     */
    function openRegister($username,$password,$nickname) {
        $url = "https://a1.easemob.com/ttouch/kuaile" . "/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => 'YXA6tfPWgFtsEeWzNxtHA-A9OA',
            'client_secret' => 'YXA6hhR-xo8PGuE3BAqMfTQlfNAkYLs'
        );
        $rs = json_decode(curl($url, $data), true);
        $token = $rs['access_token'];
        $url="https://a1.easemob.com/ttouch/kuaile".'/users';
        $username_str = substr($username,0,7);
        $arr=array(
            'username'=>$username,
            'password'=>$password,
            'nickname'=> empty($nickname) ? '开心工作'.rand(10000,99999) : $nickname,
        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );
        return json_decode(curl($url, $arr, $header, "POST"),true);
    }

    function curl($url, $data, $header = false, $method = "POST"){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $ret = curl_exec($ch);
        return $ret;
    }

/**
 * @Title: getMissionType
 * @Description: todo(任务分类)
 * @author nipeiquan
 * @return  array  返回类型
 */
    function getMissionType(){

        return $mission_types = [
            1=>'每日-自拍签到',
            2=>'每日-普通签到',
            3=>'每日-限时抢兑',
            4=>'每日-回复帖子',
            5=>'每日-发布帖子',
            6=>'每日-分享',
            7=>'每日-点赞帖子',
            8=>'每日-关注好友',
            9=>'每日-抽奖',
            10=>'新手-抽奖',
            11=>'新手-完善简历',
            12=>'新手-参与抢兑',
            13=>'新手-申请工作',
            14=>'新手-注册成功',
            15=>'新手-完善个人信息',
            16=>'新手-首次发帖',
            17=>'新手-首次回复',
        ];
    }

/**
 * @Title: addOptLog
 * @Description: todo(添加积分记录)
 * @author nipeiquan
 * @param $uid
 * @param $content
 * @param $point
 * @param $username
 * @param $type
 * @param string $lng
 * @param string $lat
 * @return  bool|mixed  返回类型
 */
    function addOptLog($uid,$content,$point,$username,$type,$lng='',$lat=''){

        $data=array(
            'uid'=>$uid,
            'content'=>$content,
            'point'=> '+'.$point,
            'created'=>time(),
            'ip'=>ip_get_client(),//操作ip
            'username'=>$username,
            'time'=>time(),
            'type'=>$type,
            'lng'=>$lng,
            'lat'=>$lat
        );

        $db=M('opt_log');

        return $db->insert($data);

    }

/**
 * @Title: addSignLog
 * @Description: todo(添加签到记录)
 * @author nipeiquan
 * @param $uid
 * @param $username
 * @param $type
 * @param $num
 * @param $title
 * @param $icon
 * @param $href
 * @return  bool|mixed  返回类型
 */
    function addSignLog($uid,$username,$type,$num,$title,$icon,$href){

        $data=array(
            'uid'=>$uid,
            'username'=>$username,
            'type'=>$type,
            'num'=> $num,
            'title'=>$title,
            'icon'=>$icon,
            'href'=>$href,
            'created'=>time(),
        );

        $db=M('sign_log');

        return $db->insert($data);
    }

function getDeviceType()
{
    //全部变成小写字母
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $type = 'other';
    //分别进行判断
    if(strpos($agent, 'iphone') || strpos($agent, 'ipad'))
    {
        $type = 'ios';
    }

    if(strpos($agent, 'android'))
    {
        $type = 'android';
    }
    return $type;
}

/**
 * @Title: addMissionLog
 * @Description: todo(往每日任务添加记录)
 * @author nipeiquan
 * @param $uid
 * @param $type
 * @param $data_id
 * @return  mixed  返回类型
 */
function addMissionLog($uid,$type,$data_id = 0){

    $data = [
        'type'=>$type,
        'uid'=>$uid,
        'data_id'=>$data_id,
        'create_at'=>time(),
    ];

    return M('mission_log')->add($data);
}

/**
 * @Title: queryExistMissionLog
 * @Description: todo(查询是否有记录)
 * @author nipeiquan
 * @param $uid
 * @param $type
 * @return  mixed  返回类型
 */
function queryExistMissionLog($uid,$type){

    return M('mission_log')->where('uid ='.$uid.' AND type in('.$type.')')->find();
}

/**
 * @Title: formatImages
 * @Description: todo(格式化图片字符串)
 * @author nipeiquan
 * @param $old_images
 * @return  string  返回类型
 */
function formatImages($old_images){

    $old_images = array_unique(explode('#',$old_images));

    $images = '';
    foreach ($old_images as $k=>$image){//格式化

        if(!empty($image)){

            $images .= $image.'#';
        }
    }

    return rtrim($images,'#');
}


