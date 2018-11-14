<?php  if(!defined('PATH_LC')){exit;}

class AuthToken{
    /**
     * @Title: encode
     * @Description:todo(将数据加密)
     * @Author: Zhouchao
     * @param $array (加密数据)
     * @param $expire(过期时间)
     * @param string $key(加密秘钥)
     * @return string 返回类型
     */
    public static function encode($array,$expire,$key= 'Common2015'){
        return self::auth_code(json_encode($array,JSON_UNESCAPED_UNICODE),'ENCODE',$key,$expire);
    }

    /**
     * @Title: decode
     * @Description:todo(将字符串解密)
     * @Author: Zhouchao
     * @param $string(字符串)
     * @param string $key(秘钥)
     * @param bool $reArr(是否返回数组)
     * @return mixed 返回类型
     */
    public static function decode($string,$key= 'Common2015',$reArr = true){
        return json_decode(self::auth_code($string,'DECODE',$key),$reArr);
    }

    /**
     * @Title: auth_code
     * @Description:todo(加密算法)
     * @Author: Zhouchao
     * @param $string
     * @param string $operation
     * @param $key
     * @param int $expiry
     * @return string 返回类型
     */
    private static function auth_code($string, $operation = 'DECODE', $key , $expiry = 0) {

        $ckey_length = 4;

        $key = md5( $key ); //将密码md5散列

        $keya = md5(substr($key, 0, 16));//散列后的值前16位再次散列

        $keyb = md5(substr($key, 16, 16));//散列后的值后16位再次散列

        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        //解密时将返回信息的前4位赋给keyc 如果是加密将当前的时间减去4秒
        $cryptkey = $keya.md5($keya.$keyc);
        //keya与keyc拼接的字符串散列后与keya连接
        $key_length = strlen($cryptkey);
        //加密字符串长度
        $string = $operation == 'DECODE' ? base64_decode(str_replace('=','+',substr($string, $ckey_length))) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        //解密是为字符串前4位，并且将=替换成+ 加密时 前10位过期时间.md5(原本字符串.keyb前16位.原本字符串)
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        //生成255位随机数
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            }else{
                return '';
            }
        }else{
            $auth =  $keyc.str_replace('+','=',str_replace('=', '', base64_encode($result)));
            return $auth;
        }
    }
}
/*
 * Describe   : 数据库备份
 */

/**
 * Class DBManage
 * Describe 数据库备份
 */
class DBManage{

    private $db;//数据库链接


    function __construct(){
        $this->db = M(null);
    }

    /**
     * @Title: getInfo
     * @Description: todo(得到数据库表信息)
     * @author zhouchao
     * @return  array  返回类型
     * name => 表的名称
     * comment => 表注释
     * engine => 存储引擎
     * Collation => 字符集和整序
     * rows => 记录数
     * size => 大小
     * free => 碎片
     */
    public function getInfo(){
        $sql = 'SHOW TABLE STATUS';
        $result = $this->db->query($sql);
        $arr = array();
        foreach ($result as $k => $v) {
            $arr[$k] = array(
                'name' => $v['Name'],
                'comment' => $v['Comment'],
                'engine' => $v['Engine'],
                'collation' => $v['Collation'],
                'rows' => $v['Rows'],
                'size' => get_size($v['Data_length']),
                'free' => get_size($v['Data_free'])
            );
        }
        return $arr;
    }

    /**
     * @Title: getSize
     * @Description: todo(得到数据库大小)
     * @author zhouchao
     * @return  mixed  返回类型
     */
    public function getSize() {
        return $this->db->get_size();
    }

    /**
     * @Title: backup
     * @Description: todo(备份数据库)
     * @author zhouchao
     * @param $tables   //备份数据库表
     * @param $path     //备份路径
     * @param $speed    //每次写入的条数
     * @param $size     //每个写入文件的大小(字节)
     * @return  bool|void  返回类型
     */
    public function backup($tables, $path, $speed, $size) {
        if (!is_array($tables)) {
            $this->error = '备份的表名必须以数组方式传递';
            return false;
        }
        if (empty($path)) {
            $this->error = '没有传递保存备份文件的路径';
            return false;
        }
        $speed = empty($speed) ? 200 : $speed;
        $size = empty($size) ? 2095104 : $size;
        $index = isset($_GET['index']) ? $_GET['index'] : 0;
        $vol = isset($_GET['vol']) ? $_GET['vol'] : 0;
        $total = $this->db->table($tables[$index], true)->count();
        $page = new page($total, $speed);

        $limit = $page->limit();
        if (!isset($_GET['page'])) {
            $_GET['page'] = 1;//初始时为第一次
        }
        if ($_GET['page'] > 1) {
            $fileSize = filesize($path . '/' . $tables[$index] . '_' . $vol . '.sql');
            if ($fileSize > $size) {
                $vol ++ ;//卷大小大于参数时递增卷索引
            }
        }
        $sql = '';//组合SQL
        if (!file_exists($path . '/' . $tables[$index] . '_0.sql')) {
            $sql .= $this->getCreateSql($tables[$index]);//卷为0时添加创建表SQL
            $sql .= 'DELETE FROM `' .$tables[$index] . '`';//清空原数据
            $sql .=";\n\n---------lcphp-------------\n\n";
        }
        $sql .= $this->getInsertSql($tables[$index],$limit['limit']);//插入数据SQL
        $filePath = $path . '/' . $tables[$index] . '_' . $vol . '.sql';


        $tableFile = fopen($filePath,'a');//写入文件
        fwrite($tableFile,$sql);
        if ($_GET['page'] >= $page->total_page) {//换表
            $_GET['page'] = 0;//页码初始化为0
            $vol = 0;//卷索引归0
            $index++;//递增表索引
        }
        if($index < count($tables)){
            //备份进行时提示信息
            echo "<div style='width:400px;margin:220px auto;padding:0px;background-color:#fff;border: 3px solid #ccc;'>
                    <h2 style='margin:0;height: 28px; color:#444;background-color:#dcdcdc;font-size:13px; font-weight: bold; text-indent: 5px; line-height: 28px;'>正在备份数据库，请不要进行其他操作</h2>
                    <p style='font-size:13px;color:#B5B5B5;border-bottom: solid 1px #dcdcdc;padding-bottom: 10px; margin-bottom:10px;text-align:center;line-height:40px;'>
                    正在备份：" . $tables[$index] ." 第" . ($vol + 1) . "卷数据</p>
                </div>";
            //按传递的备份速度每次进行跳转
            $url = url_remove_param(array('page','index','vol')) . '/index/' . $index . '/vol/' . $vol .  '/page/' . ($_GET['page'] + 1);
            echo '<script>window.location = "' . $url . '";</script>';
            return;
        }
        return true;
    }

    /**
     * @Title: restore
     * @Description: todo(还原数据文件至数据库)
     * @author zhouchao
     * @param $sqls SQL文件   必须是数组
     * @return  bool|void  返回类型
     */
    public function restore($sqls){
        if (!is_array($sqls)) {
            $this->error = '还原SQL文件参数必须是数组';
            return false;
        }
        $index = isset($_GET['index']) ? $_GET['index'] : 0;
        $keys = array_keys($sqls);
        $values = array_values($sqls);
        if ($index < count($sqls)) {
            //还原数据进行时提示信息
            echo "<div style='width:400px;margin:220px auto;padding:0px;background-color:#fff;border: 3px solid #ccc;'>
                    <h2 style='margin:0;height: 28px; color:#444;background-color:#dcdcdc;font-size:13px; font-weight: bold; text-indent: 5px; line-height: 28px;'>正在还原数据库，请不要进行其他操作</h2>
                    <p style='font-size:13px;color:#B5B5B5;border-bottom: solid 1px #dcdcdc;padding-bottom: 10px; margin-bottom:10px;text-align:center;line-height:40px;'>
                    正在还原：" . $keys[$index] . "</p>
                </div>";
            $sqlFile=array_filter(array_map('trim', explode('---------lcphp-------------', file_get_contents($values[$index]))));
            foreach ($sqlFile as $v) {
                if (empty($v))  continue;
                    $this->db->exe($v);
            }
            $url = url_remove_param('index') . '/index/' . ($index + 1);
            echo $url;
            echo '<script>window.location = "' . $url . '"</script>';
            return;
        }
        return true;
    }

    /**
     * @Title: showCreateTable
     * @Description: todo(显示表结构)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function showCreateTable($table){
        return $this->getCreateSql($table);
    }

    /**
     * @Title: optimizeTable
     * @Description: todo(优化表)
     * @author zhouchao
     * @param $table
     * @return  void  返回类型
     */
    public function optimizeTable($table){
        if (empty($table)) {
            return false;
        }
        $sql = 'OPTIMIZE TABLE  `' . $table . '`';
        return $this->db->query($sql);
    }

    /**
     * @Title: repairTable
     * @Description: todo(修复表)
     * @author zhouchao
     * @param $table
     * @return  bool|mixed  返回类型
     */
    public function repairTable($table) {
        if (empty($table)) {
            return false;
        }
        $sql = 'REPAIR TABLE  `' . $table . '`';
        return $this->db->query($sql);
    }


    /**
     * @Title: getCreateSql
     * @Description: todo(取得建表语句)
     * @author zhouchao
     * @param $table
     * @return  bool|mixed  返回类型
     */
    private function getCreateSql($table) {
        $result=$this->db->query('SHOW CREATE TABLE '.$table);
        return str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $result[0]['Create Table'].";\n\n---------lcphp-------------\n\n");

    }

    /**
     * @Title: getInsertSql
     * @Description: todo(取得插入语句)
     * @author zhouchao
     * @param $table
     * @param $limit
     * @return  string  返回类型
     */
    private function getInsertSql($table, $limit) {
        $data = $this->db->table($table, true)->limit($limit)->all();
        if (!$data) {
            return '';
        }
        $sql="INSERT INTO `".$table."` (".$this->db->table($table, true)->db->opt['field'].") VALUES";
        foreach ($data as $value) {
            $sql.='(';
            foreach($value as $v){
                $sql.=is_numeric($v)?$v.',':"'".addslashes($v)."'".',';
            }
            $sql=rtrim($sql,',');
            $sql.="),";
        }
        $sql=rtrim($sql,',');
        $sql.=";\n\n---------lcphp-------------\n\n";
        return $sql;
    }



}

/*
 * Describe   : 自定义标签
 */

class MyTags {

    public $tag = array(
        'js' => array('block' => 0),
        'css' => array('block' => 0),
        'linkage' => array('block' => 1, 'level' => 3),
        'editor' => array('block' => 0),
        'getLinkage' => array('block' => 0, 'level' => 3),
        '_field' => array('block' => 1),
        'new_recruit' => array('block' => 1),
        'recruit_list' => array('block' => 1),
        'spread' => array('block' => 1),
        'resume_has_downloaded'=>array('block' => 0),//企业是否下载简历
        'company'=>array('block'=>1,'level'=>3),//是否企业用户
        'logged_in'=>array('block'=>1,'level'=>3),//是否登录
        '_keyword'=>array('block'=>1),
        '_nav'=>array('block'=>1),
        'new_resume'=>array('block'=>1),
        '_ads'=>array('block'=>1),
        '_recruit'=>array('block'=>1),
        '_link'=>array('block'=>1),
        'open_city'=>array('block'=>1),
        'arc_list'=>array('block'=>1),
        '_channel'=>array('block'=>1),
        '_page'=>array('block'=>1),
        '_page_seo'=>array('block'=>0),
        '_seo'=>array('block'=>0),
    );

    public function _css($attr, $content) {
        $file = trim($attr['file']);
        switch ($file) {
            case 'alice':
                return '<link type="text/css" rel="stylesheet" href="' . __ROOT__ . '/public/css/base.css"/>';
            case 'bootstrap':
                return '<link type="text/css" rel="stylesheet" href="' . __ROOT__ . '/public/css/bootstrap/bootstrap.min.css"/>';
            case 'jqueryUI.bootstrap':
                return '<link type="text/css" rel="stylesheet" href="' . __ROOT__ . '/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>';
        }
        return '<link type="text/css" rel="stylesheet" href="' . $file . '"/>';
    }

    public function _js($attr, $content) {
        $file = buildAttrVar(trim($attr['file']));
        switch ($file) {
            case 'bootstrap':
                return '<script type="text/javascript" src="' . __ROOT__ . '/public/js/bootstrap/bootstrap.min.js"></script>';
            case 'jquery':
                return '<script type="text/javascript" src="' . __ROOT__ . '/public/js/jquery-1.7.2.min.js"></script>';
            case 'jqueryUI':
                return '<script type="text/javascript" src="' . __ROOT__ . '/public/js/jquery-ui-1.8.21.custom.min.js"></script>';
            case 'jqueryUI.dialog':
                return '<script type="text/javascript" src="' . __ROOT__ . '/public/js/jqueryUI/jquery-ui-1.8.22.dialog.min.js"></script>';
            case 'jquery.validate':
                return '<script type="text/javascript" src="' . __ROOT__ . '/public/js/jqueryValidate/jquery.validate.min.js"></script>';
            case 'datepicker':
                return '<script type="text/javascript" src="' . __ROOT__ . '/public/js/My97DatePicker/WdatePicker.js"></script>';
            case 'jquery.treeview':
                return '<link type="text/css" rel="stylesheet" href="' . __ROOT__ . '/public/js/treeview/jquery.treeview.css"/><script type="text/javascript" src="' . __ROOT__ . '/public/js/treeview/jquery.treeview.js"></script>';
                break;
            case 'dialog':
                $str = '';
                $str.= '<script type="text/javascript" src="' . __ROOT__ . '/public/js/jquery-1.7.2.min.js"></script>';
                $str.= "<script type='text/javascript' src='" . __LCPHP__ . "/org/js/dialog.js'></script>\n";
                $str.="<link  rel='stylesheet' type='text/css' href='" . __LCPHP__ . "/org/js/css/dialog.css'/>";
                return $str;
        }
        return '<script type="text/javascript" src="' . $file . '"></script>';
    }

    public function _linkage($attr, $content) {
        //处理标签参数
        if (!isset($attr['field'])) {
            error('linkage标签必须设置field属性。');
        }
        $field_array = explode('#', $attr['field']); //表单的依次name值
        $attr['name'] = trim($field_array[0]); //表单名称
        $defaults = isset($attr['defaults']) ? ",defaults:'" . buildAttrVar($attr['defaults']) . "'" : ''; //确保属性中有变量也解析
        $attr['data'] = isset($attr['data']) ? $attr['data'] : error('linkage标签必须设置data属性，查看<a href="###">linkage标签</a>使用方法。'); //联动数据分类ID
        $attr['style'] = isset($attr['style']) ? $attr['style'] : 1; //联动风格
        $attr['attr'] = isset($attr['attr']) ? $attr['attr'] : ''; //附加html属性
        $attr['tips']=isset($attr['tips']) ? ',tips:'.'"'.$attr['tips'].'"' : ''; //附加html属性
        $checkbox = isset($attr['checkbox']) && $attr['checkbox']=='true' ? ',checkbox:true': ''; //是否可以多选
        $max = isset($attr['max']) ? ',max:' . intval($attr['max']) : ''; //最多选择几项
        $data="linkage_{$attr['data']}";
        if($attr['data']=='city'){
            $data='city';
        }
        $str = '';
        $style='linkage_style_'.$attr['style'];
        //生成HTML表单
        if ($attr['style'] == 1) {
            $str.='<select style="margin-right:3px;" id="' . $attr['name'] . '" name="' . $attr['name'] . '" ' . $attr['attr'] . '></select>';
        } else {
            $str.='<input type="text" id="' . $attr['name'] . '" title="" value="" ' . $attr['attr'] . ' />';
        }
        $str.='<script>$(function(){$("#' . $attr['name'] . "\").$style({
                data:$data,
                field:'{$attr['field']}',
                html_attr:'{$attr['attr']}'" . $defaults .$attr['tips']. $checkbox . $max . '
                })});</script>';
        return $str;
    }
    /**
     * 单网页
     * @param attr['field'] 取出的字段
     * @param attr['pid'] 父级栏目
     */
    public function __page($attr,$content)
    {
        $field='title,href,id';
        if(isset($attr['field'])){
            $field.=','.$attr['field'];
        }
        $pid='pid!=0';
        if(isset($attr['pid'])){
            $pid='pid ='.$attr['pid'];
        }
        $limit='';
        if(isset($attr['nums'])){
            $limit=$attr['nums'];
        }
        $str="<?php \$db=M('channel');\$pages=\$db->field('".$field."')->where('type=2 && ".$pid."')->limit(".$limit.")->findall();?>";
        $str.='<?php if(is_array($pages)):?>';
        $str.='<?php foreach($pages as $page):?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }

    //模型字段
    public function __field($attr, $content) {
        if(!isset($attr['model'])){
            error('_field标签必须设置model属性。即模型表ID，如不清楚使用方式。请查看系统部署手册');
        }
        $model = intval($attr['model']);
        $type = 'add'; //edit 编辑or发布
        if (isset($attr['type'])) {
            $type = $attr['type'];
        }
        $db = M('model');
        $mdata = $db->find($model);
        $upload = '';
        $validate = '';
        if ($mdata['upload']) {
            $upload = 'enctype="multipart/form-data"';
        }
        if ($mdata['validate']) {
            $validate = 'validate="true"';
        }
        $tpl = '{include file="' . PATH_ROOT . '/caches/model/model_' . $model . '_' . $type . '.html" /}';
        if(!file_exists(PATH_ROOT.'/caches/model/model_'.$model.'_'.$type.'.html')){
            $field=new field($model);
            $field->build_field();
            header('Location:'.__URL__);
        }
        /*$tpl = '<?php include \'./caches/model/model_' . $model . '_' . $type . '.html\';?>';*/
        $str = '<form ' . $validate . ' method="post" action="' . __METH__ . '" ' . $upload . '>';
        if ($type == 'edit') {
            $str = '<?php if(is_array($field)){extract($field);}?><form ' . $validate . ' method="post" action="' . __METH__ . '" ' . $upload . ' />';
        }
        $str.=$tpl;
        $str.=$content;
        $str .= '</form>';
        return $str;
    }

    //招聘信息attr: order排序方式，limit数目
    function __recruit($attr, $content){
        $order=isset($attr['order']) ? $attr['order'] : 'refresh_date DESC,views DESC';
        $limit=isset($attr['nums']) ? $attr['nums'] : 10;
        $where=isset($attr['where']) ? $attr['where'].".' AND state=1 AND verify=1 AND expiration_time > '.time()" : "'state=1  AND verify=1 AND expiration_time > '.time()";
        if(isset($attr['city']) && !empty($attr['city'])){
            $where.=".' AND city='.".$attr['city'];
        }
        $field='recruit_id,address,city,town,salary,refresh_date,class,class_two,recruit_name,company_name,uid';
        if(isset($attr['field'])){
            $field.=','.rtrim($attr['field'],',');
        }
        $str="<?php \$db=M('recruit');
        \$recruits=\$db->field('".$field."')->where(".$where.")
        ->order('".$order."')->limit(".$limit.")->findall();?>";
        $str.='<?php if(is_array($recruits)):?>';
        $str.='<?php foreach($recruits as $recruit):?>';
        $str.='<?php $data=new data();$recruit=$data->convert($recruit);?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }

    function _spread($attr, $content) {
        $str = '<?php $db = M("spread");';
        $str.='$spread_cate=$db->field("cate_id,color")->where("recruit_id=".' . $attr['id'] . ')->findall();?>';
        $str.=$content;
        return $str;
    }

    //最新简历,attr : flag="avatar"
    function _new_resume($attr, $content){
        $field='resume.resume_id,resume_name,created,avatar,views';
        $cond='';
        if(isset($attr['flag']) && $attr['flag']='avatar'){
            $cond='avatar !=""';
        }
        if(isset($attr['field'])){
            $field.=','.trim($attr['field'],',');
        }
        $convert='';
        $views='';
        $limit='';
        if(isset($attr['nums'])){
            $limit="->limit({$attr['nums']})";
        }
        if(isset($attr['join'])){
            $join=$attr['join'];
            $views="\$db->view=array('".$attr['join']."'=>array('type'=>'left','on'=>'resume.resume_id=".$attr['join'].".resume_id'));";
            $convert='<?php $data=new data("'.$join.'");$resume=$data->convert($resume);?>';
        }
        $str="<?php \$db=V('resume');".$views."\$resumes=\$db->field('".$field."')->where('".$cond."')->order('created DESC')".$limit."->findall();?>";
        $str.='<?php if(is_array($resumes)):?>';
        $str.='<?php foreach($resumes as $resume):?>';
        $str.=$convert;
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }

    //最新招聘
    public function _new_recruit($attr, $content) {
        $cond = isset($attr['cond']) ? $attr['cond'] : "''";
        $nums = isset($attr['nums']) ? ',' . $attr['nums'] : ",5";
        $order = isset($attr['order']) ? ",'{$attr['order']}'" : ",'start_time desc'";
        $field = isset($attr['field']) ? ",'{$attr['field']}'" : ",'recruit_id,recruit_name,recruit_num,start_time,expiration_time,effective_time,verify'";
        $str = '<?php
            $db=K(\'company\');
            $lists=$db->newRecruit(' . $cond . $nums . $order . $field . ');
            ?>';
        $str.='<?php if(is_array($lists)):?>';
        $str.='<?php foreach($lists as $list):?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }
    /**
     * 栏目标签
     * @param attr['pid'] 取某个栏目的子栏目
     * @param attr['type'] 1,分类栏目 2 单网页
     * @param attr['nums'] 数量
     */
    public function __channel($attr,$content)
    {
        $field='id,title,pinyin,href';
        if(isset($attr['field'])){
            $field.=','.$attr['field'];
        }
        $pid=0;
        $type=1;
        $nums=10;
        if(isset($attr['nums'])){
            $nums= $attr['nums'];
        }
        if(isset($attr['pid'])){
            $pid= $attr['pid'];
        }
        if(isset($attr['type'])){
            $pid= $attr['type'];
        }
        $cond='pid='.$pid.' AND state=1 AND type='.$type;
        $str="<?php \$db=M('channel');
        \$channels=\$db->cache(86400)->field('".$field."')->where('".$cond."')->order('sort')->limit($nums)->findall();?>";
        $str.='<?php if(is_array($channels)):?>';
        $str.='<?php foreach($channels as $channel):?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }
    /**
     * 文章标签
     * @param attr['field']  需要的字段
     * @param attr['cid']    栏目ID
     * @param attr['page']   是否需要分页，true 可以使用{$page}调出页码
     */
    public function _arc_list($attr, $content)
    {
        $field='title,id,created,updated,href';
        if(isset($attr['field'])){
            $field.=','.$attr['field'];
        }
        $cond="'state=1'";
        if(isset($attr['cid'])){
            if(strpos($attr['cid'],'$')===FALSE){
                $cond="'state=1 AND cid={$attr['cid']}'";
            }else{
                $cond="'state=1 AND cid='.".$attr['cid'];
            }
        }
        $nums=5;
        if(isset($attr['nums'])){
            $nums=$attr['nums'];
        }
        if(isset($attr['page']) && $attr['page']=='true'){
            $limit='';
            $page_nums=10;
            if(isset($attr['nums'])){
                $page_nums=$attr['nums'];
            }
            $str_page="\$nums=\$db->where($cond)->count();
            \$page_c=new page(\$nums,".$page_nums.");\$page=\$page_c->show();";
            $page_limit="\$page_c->limit()";
        }else{
            $page_limit=$str_page='';
            $limit="->limit($nums)";
        }
        $str="<?php \$db=M('article');".$str_page."
            \$arc_list=\$db->field('".$field."')->where($cond)".$limit."->order('updated desc')->findall(".$page_limit.");?>";
        $str.='<?php if(is_array($arc_list)):?>';
        $str.='<?php foreach($arc_list as $arc):?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }

    //关键字
    function __keyword($attr, $content){
        $limit=8;
        if(isset($attr['nums'])){
            $limit=$attr['nums'];
        }
        $str="<?php \$db=M('keywords');\$keywords=\$db->field('keyword,red')->order('nums DESC')->limit(".$limit.")->findall();?>";
        $str.='<?php if(is_array($keywords)):?>';
        $str.='<?php foreach ($keywords as $keyword):?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }

    //广告
    function __ads($attr, $content){
        $limit = isset($attr['nums']) ? $attr['nums'] : 3;
        $where='';
        if(!isset($attr['cate'])){
            error('_ads标签必须设置cate属性，即广告位置。');
        }elseif(preg_match('/^[a-z]+$/i', $attr['cate'])){
            $db=M('ads_cate');
            $cate=$db->field('id')->where("tname='{$attr['cate']}'")->find();
            $attr['cate']=$cate['id'];
        }
        if(isset($attr['city'])){
            $where.=' AND city=".'.$attr['city'].'."';
        }

        $str="<?php \$db = M('ads');";
        $str.="\$adss =\$db->field('href,text,path,color,width,height,uid')
        ->where(\" state=1 AND cate =" . $attr['cate'].$where." AND endtime >\".time())
        ->limit(".$limit.")->findall();?>";
        $str.='<?php if(is_array($adss)):?>';
        $str.='<?php foreach ($adss as $ads):?>';
        $str.=$content;
        $str.='<?php endforeach;?>';
        $str.='<?php endif;?>';
        return $str;
    }
    function _open_city($attr, $content){
        $limit=isset($attr['nums']) ? $attr['nums'] : 15;
        $cache=isset($attr['cache']) ? $attr['cache'] : 86400;
        $str="<?php \$db=M('city');\$citys=\$db->cache($cache)->field('name,pinyin')->where('is_open=1')->limit(".$limit.")->findall();?><?php if (is_array(\$citys)):?><?php foreach (\$citys as \$city):?>";
        $str.=$content;
        $str.="<?php endforeach;endif;?>";
        return $str;
    }

    function __link($attr, $content){
        $limit=8;
        if(isset($attr['nums'])){
            $limit=$attr['nums'];
        }
        $cate='';
        if(isset($attr['cate']) && (empty($attr['cate']))){
            $cate=' and cate_id="'.$attr['cate'].'"';
        }
        $cache=isset($attr['cache']) ? $attr['cache'] : 86400;
        $str="<?php \$db=M('link');\$links=\$db->cache($cache)->field('web_name,href,logo')->where('state=1 $cate')->order('sort')->limit(".$limit.")->findall();?>";
        $str.='<?php if(is_array($links)):?>';
        $str.='<?php foreach ($links as $link):?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }

    //职位列表
    public function _recruit_list($attr, $content) {
        $condition = isset($attr['condition']) ? $attr['condition'] : "''";
        $_GET['nums'] = isset($_GET['nums']) ? $_GET['nums'] : 10;
        $nums = isset($attr['nums']) ? ',' . $attr['nums'] : ',$_GET["nums"]';
        $order = isset($attr['order']) ? ",'{$attr['order']}'" : ",'start_time desc'";
        $field = isset($attr['field']) ? ",'{$attr['field']}'" : ",'recruit_id,recruit_name,recruit_num,start_time,expiration_time,effective_time'";
        $str = '<?php
            $db=K(\'company\');
            $lists=$db->recruitList(' . $condition . $nums . $order . $field . ');
                $page=$lists["page"];
            ?>';
        $str.='<?php if(is_array($lists["data"])):?>';
        $str.='<?php foreach($lists["data"] as $list):?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }


    //简历是否被企业下载
    function _resume_has_downloaded($attr, $content){
        $resume_id="\$_GET['id']";
        if(isset($attr['id'])){
            $resume_id=$attr['id'];//如果有设置简历ID
        }
        $str="<?php \$db=M('resume_download');\$downloaded=\$db->where('resume_id='.".$resume_id.".' AND company_id='.\$_SESSION['uid'])->count();?>";
        return $str;
    }

    //是否为企业用户
    function _company($attr, $content){
        $str ="<?php if(in_array(3,\$_SESSION['role']['rid'])){?>";
        $str.=$content;
        $str.='<?php } ?>';
        return $str;
    }

    //是否登录
    function _logged_in($attr, $content){
        $str="<?php if(isset(\$_SESSION['logged_in']) && \$_SESSION['logged_in']){?>";
        $str.=$content;
        $str.='<?php } ?>';
        return $str;
    }

    //导航
    function __nav($attr, $content){
        $limit=8;
        if(isset($attr['nums'])){
            $limit=$attr['nums'];
        }
        $cache=isset($attr['cache']) ? $attr['cache'] : 86400;
        $str="<?php \$db=M('nav');\$navs=\$db->cache($cache)->field('href,title,target,sort')->where('state=1')->order('sort ASC')->limit($limit)->findall();?>";
        $str.='<?php if(is_array($navs)):?>';
        $str.='<?php foreach ($navs as $nav):?>';
        $str.=$content;
        $str.='<?php endforeach;endif;?>';
        return $str;
    }


    //调用默认seo设置
    public function __seo($attr, $content)
    {
        $str='<meta name="keywords" content="'.C('KEYWORD').'" />
<meta name="description" content="'.C('DESC').'" />';
        return $str;
    }
    //页面seo设置
    function __page_seo($attr, $content){
        if(!isset($attr['cate'])){
            error('_page_seo标签必须设置cate属性，即导航英文名。');
        }
        $db=M('nav');
        $seos=$db->field('seo_keywords,seo_desc')
            ->where('state=1 AND mark="'.$attr['cate'].'"')->find();
        $str='<meta name="keywords" content="'.$seos['seo_keywords'].'" />
<meta name="description" content="'.$seos['seo_desc'].'" />';
        return $str;
    }

    public function _editor($attr, $content) {
        $type = C("EDITOR_TYPE");
        $content = isset($attr['content']) ? (strpos($attr['content'], "$") !== FALSE ? '<?php echo htmlspecialchars_decode(' . $attr['content'] . ');?>' : $attr['content']) : ''; //默认内容
        $maximumWords = isset($attr['max']) ? $attr['max'] : C("EDITOR_MAX_STR"); //允许输入的最大字数
        $width = isset($attr['width']) ? 'style=" width:' . intval($attr['width']) . 'px"' : 'style="width:' . c("EDITOR_WIDTH") . '"';
        $height = isset($attr['height']) ? intval($attr['height']) : intval(C("EDITOR_HEIGHT"));
        $filenum = C("EDITOR_FILE_NUM"); //上传文件数量
        $filesize = intval(C("EDITOR_FILE_SIZE")); //上传文件大小
        if (!isset($attr['name'])) {
            error(L("basetag__editor"), false);
        }
        $name = $attr['name'];
        $str = '';
        switch ($type) {
            case 1:
                $toole = '';
                if (isset($attr['style'])) {//精简或全部toole
                    switch ($attr['style']) {
                        case 1:
                            $toole = "toolbars:[['Undo', 'Redo','Bold','Italic','Underline','JustifyLeft', 'JustifyCenter', 'JustifyRight','InsertOrderedList','InsertUnorderedList','FormatMatch','Link','Horizontal']],";
                            break;
                        case 2:
                            $toole = "";
                            break;
                    }
                }
                $path ='__LCPHP__/org/ueditor/';
                $str.='<script type="text/javascript">LC_UEDITOR_ROOT="' . $path . '";</script>';
                $str.='<script type="text/javascript" src="' . $path . 'editor_config.js"></script>
		<script type="text/javascript" src="' . $path . 'editor_all.js"></script>
		<link rel="stylesheet" href="' . $path . 'themes/default/ueditor.css">';
                $str.='<script type="text/plain" name="' . $name . '" id="' . $attr['id'] . '" ' . $width . '>' .
                    $content . '</script>';
                $str.='<script type="text/javascript" >';
                $str.= 'var editorOption = {
                         ' . $toole . '
                         imageUrl:"__CONTROL__/ueditorupload",
                         imagePath:"",
                         fileUrl:"__CONTROL__/ueditorupload",
                         filePath:"",
                         maximumWords:' . $maximumWords . ',
                         minFrameHeight:' . $height . '
                    };';
                $str.='var editor = new baidu.editor.ui.Editor(editorOption);
                    editor.render("' . $attr['id'] . '")';
                $str.='</script>';
                return $str;
        }
    }

}

/*************************************************
 *
 * Snoopy - the PHP net client
 * Author: Monte Ohrt <monte@ohrt.com>
 * Copyright (c): 1999-2014, all rights reserved
 * Version: 2.0.0
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * You may contact the author of Snoopy by e-mail at:
 * monte@ohrt.com
 *
 * The latest version of Snoopy can be obtained from:
 * http://snoopy.sourceforge.net/
 *************************************************/
class Snoopy
{
    /**** Public variables ****/

    /* user definable vars */

    var $scheme = 'http'; // http or https
    var $host = "www.php.net"; // host name we are connecting to
    var $port = 80; // port we are connecting to
    var $proxy_host = ""; // proxy host to use
    var $proxy_port = ""; // proxy port to use
    var $proxy_user = ""; // proxy user to use
    var $proxy_pass = ""; // proxy password to use

    var $agent = "Snoopy v2.0.0"; // agent we masquerade as
    var $referer = ""; // referer info to pass
    var $cookies = array(); // array of cookies to pass
    // $cookies["username"]="joe";
    var $rawheaders = array(); // array of raw headers to send
    // $rawheaders["Content-type"]="text/html";

    var $maxredirs = 5; // http redirection depth maximum. 0 = disallow
    var $lastredirectaddr = ""; // contains address of last redirected address
    var $offsiteok = true; // allows redirection off-site
    var $maxframes = 0; // frame content depth maximum. 0 = disallow
    var $expandlinks = true; // expand links to fully qualified URLs.
    // this only applies to fetchlinks()
    // submitlinks(), and submittext()
    var $passcookies = true; // pass set cookies back through redirects
    // NOTE: this currently does not respect
    // dates, domains or paths.

    var $user = ""; // user for http authentication
    var $pass = ""; // password for http authentication

    // http accept types
    var $accept = "image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*";

    var $results = ""; // where the content is put

    var $error = ""; // error messages sent here
    var $response_code = ""; // response code returned from server
    var $headers = array(); // headers returned from server sent here
    var $maxlength = 500000; // max return data length (body)
    var $read_timeout = 0; // timeout on read operations, in seconds
    // supported only since PHP 4 Beta 4
    // set to 0 to disallow timeouts
    var $timed_out = false; // if a read operation timed out
    var $status = 0; // http request status

    var $temp_dir = "/tmp"; // temporary directory that the webserver
    // has permission to write to.
    // under Windows, this should be C:\temp

    var $curl_path = false;
    // deprecated, snoopy no longer uses curl for https requests,
    // but instead requires the openssl extension.

    // send Accept-encoding: gzip?
    var $use_gzip = true;

    // file or directory with CA certificates to verify remote host with
    var $cafile;
    var $capath;

    /**** Private variables ****/

    var $_maxlinelen = 4096; // max line length (headers)

    var $_httpmethod = "GET"; // default http request method
    var $_httpversion = "HTTP/1.0"; // default http request version
    var $_submit_method = "POST"; // default submit method
    var $_submit_type = "application/x-www-form-urlencoded"; // default submit type
    var $_mime_boundary = ""; // MIME boundary for multipart/form-data submit type
    var $_redirectaddr = false; // will be set if page fetched is a redirect
    var $_redirectdepth = 0; // increments on an http redirect
    var $_frameurls = array(); // frame src urls
    var $_framedepth = 0; // increments on frame depth

    var $_isproxy = false; // set if using a proxy server
    var $_fp_timeout = 30; // timeout for socket connection

    /*======================================================================*\
        Function:	fetch
        Purpose:	fetch the contents of a web page
                    (and possibly other protocols in the
                    future like ftp, nntp, gopher, etc.)
        Input:		$URI	the location of the page to fetch
        Output:		$this->results	the output text from the fetch
    \*======================================================================*/

    function fetch($URI)
    {

        $URI_PARTS = parse_url($URI);
        if (!empty($URI_PARTS["user"]))
            $this->user = $URI_PARTS["user"];
        if (!empty($URI_PARTS["pass"]))
            $this->pass = $URI_PARTS["pass"];
        if (empty($URI_PARTS["query"]))
            $URI_PARTS["query"] = '';
        if (empty($URI_PARTS["path"]))
            $URI_PARTS["path"] = '';

        $fp = null;

        switch (strtolower($URI_PARTS["scheme"])) {
            case "https":
                if (!extension_loaded('openssl')) {
                    trigger_error("openssl extension required for HTTPS", E_USER_ERROR);
                    exit;
                }
                $this->port = 443;
            case "http":
                $this->scheme = strtolower($URI_PARTS["scheme"]);
                $this->host = $URI_PARTS["host"];
                if (!empty($URI_PARTS["port"]))
                    $this->port = $URI_PARTS["port"];
                if ($this->_connect($fp)) {
                    if ($this->_isproxy) {
                        // using proxy, send entire URI
                        $this->_httprequest($URI, $fp, $URI, $this->_httpmethod);
                    } else {
                        $path = $URI_PARTS["path"] . ($URI_PARTS["query"] ? "?" . $URI_PARTS["query"] : "");
                        // no proxy, send only the path
                        $this->_httprequest($path, $fp, $URI, $this->_httpmethod);
                    }

                    $this->_disconnect($fp);

                    if ($this->_redirectaddr) {
                        /* url was redirected, check if we've hit the max depth */
                        if ($this->maxredirs > $this->_redirectdepth) {
                            // only follow redirect if it's on this site, or offsiteok is true
                            if (preg_match("|^https?://" . preg_quote($this->host) . "|i", $this->_redirectaddr) || $this->offsiteok) {
                                /* follow the redirect */
                                $this->_redirectdepth++;
                                $this->lastredirectaddr = $this->_redirectaddr;
                                $this->fetch($this->_redirectaddr);
                            }
                        }
                    }

                    if ($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0) {
                        $frameurls = $this->_frameurls;
                        $this->_frameurls = array();

                        while (list(, $frameurl) = each($frameurls)) {
                            if ($this->_framedepth < $this->maxframes) {
                                $this->fetch($frameurl);
                                $this->_framedepth++;
                            } else
                                break;
                        }
                    }
                } else {
                    return false;
                }
                return $this;
                break;
            default:
                // not a valid protocol
                $this->error = 'Invalid protocol "' . $URI_PARTS["scheme"] . '"\n';
                return false;
                break;
        }
        return $this;
    }

    /*======================================================================*\
        Function:	submit
        Purpose:	submit an http(s) form
        Input:		$URI	the location to post the data
                    $formvars	the formvars to use.
                        format: $formvars["var"] = "val";
                    $formfiles  an array of files to submit
                        format: $formfiles["var"] = "/dir/filename.ext";
        Output:		$this->results	the text output from the post
    \*======================================================================*/

    function submit($URI, $formvars = "", $formfiles = "")
    {
        unset($postdata);

        $postdata = $this->_prepare_post_body($formvars, $formfiles);

        $URI_PARTS = parse_url($URI);
        if (!empty($URI_PARTS["user"]))
            $this->user = $URI_PARTS["user"];
        if (!empty($URI_PARTS["pass"]))
            $this->pass = $URI_PARTS["pass"];
        if (empty($URI_PARTS["query"]))
            $URI_PARTS["query"] = '';
        if (empty($URI_PARTS["path"]))
            $URI_PARTS["path"] = '';

        switch (strtolower($URI_PARTS["scheme"])) {
            case "https":
                if (!extension_loaded('openssl')) {
                    trigger_error("openssl extension required for HTTPS", E_USER_ERROR);
                    exit;
                }
                $this->port = 443;
            case "http":
                $this->scheme = strtolower($URI_PARTS["scheme"]);
                $this->host = $URI_PARTS["host"];
                if (!empty($URI_PARTS["port"]))
                    $this->port = $URI_PARTS["port"];
                if ($this->_connect($fp)) {
                    if ($this->_isproxy) {
                        // using proxy, send entire URI
                        $this->_httprequest($URI, $fp, $URI, $this->_submit_method, $this->_submit_type, $postdata);
                    } else {
                        $path = $URI_PARTS["path"] . ($URI_PARTS["query"] ? "?" . $URI_PARTS["query"] : "");
                        // no proxy, send only the path
                        $this->_httprequest($path, $fp, $URI, $this->_submit_method, $this->_submit_type, $postdata);
                    }

                    $this->_disconnect($fp);

                    if ($this->_redirectaddr) {
                        /* url was redirected, check if we've hit the max depth */
                        if ($this->maxredirs > $this->_redirectdepth) {
                            if (!preg_match("|^" . $URI_PARTS["scheme"] . "://|", $this->_redirectaddr))
                                $this->_redirectaddr = $this->_expandlinks($this->_redirectaddr, $URI_PARTS["scheme"] . "://" . $URI_PARTS["host"]);

                            // only follow redirect if it's on this site, or offsiteok is true
                            if (preg_match("|^https?://" . preg_quote($this->host) . "|i", $this->_redirectaddr) || $this->offsiteok) {
                                /* follow the redirect */
                                $this->_redirectdepth++;
                                $this->lastredirectaddr = $this->_redirectaddr;
                                if (strpos($this->_redirectaddr, "?") > 0)
                                    $this->fetch($this->_redirectaddr); // the redirect has changed the request method from post to get
                                else
                                    $this->submit($this->_redirectaddr, $formvars, $formfiles);
                            }
                        }
                    }

                    if ($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0) {
                        $frameurls = $this->_frameurls;
                        $this->_frameurls = array();

                        while (list(, $frameurl) = each($frameurls)) {
                            if ($this->_framedepth < $this->maxframes) {
                                $this->fetch($frameurl);
                                $this->_framedepth++;
                            } else
                                break;
                        }
                    }

                } else {
                    return false;
                }
                return $this;
                break;
            default:
                // not a valid protocol
                $this->error = 'Invalid protocol "' . $URI_PARTS["scheme"] . '"\n';
                return false;
                break;
        }
        return $this;
    }

    /*======================================================================*\
        Function:	fetchlinks
        Purpose:	fetch the links from a web page
        Input:		$URI	where you are fetching from
        Output:		$this->results	an array of the URLs
    \*======================================================================*/

    function fetchlinks($URI)
    {
        if ($this->fetch($URI) !== false) {
            if ($this->lastredirectaddr)
                $URI = $this->lastredirectaddr;
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++)
                    $this->results[$x] = $this->_striplinks($this->results[$x]);
            } else
                $this->results = $this->_striplinks($this->results);

            if ($this->expandlinks)
                $this->results = $this->_expandlinks($this->results, $URI);
            return $this;
        } else
            return false;
    }

    /*======================================================================*\
        Function:	fetchform
        Purpose:	fetch the form elements from a web page
        Input:		$URI	where you are fetching from
        Output:		$this->results	the resulting html form
    \*======================================================================*/

    function fetchform($URI)
    {

        if ($this->fetch($URI) !== false) {

            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++)
                    $this->results[$x] = $this->_stripform($this->results[$x]);
            } else
                $this->results = $this->_stripform($this->results);

            return $this;
        } else
            return false;
    }


    /*======================================================================*\
        Function:	fetchtext
        Purpose:	fetch the text from a web page, stripping the links
        Input:		$URI	where you are fetching from
        Output:		$this->results	the text from the web page
    \*======================================================================*/

    function fetchtext($URI)
    {
        if ($this->fetch($URI) !== false) {
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++)
                    $this->results[$x] = $this->_striptext($this->results[$x]);
            } else
                $this->results = $this->_striptext($this->results);
            return $this;
        } else
            return false;
    }

    /*======================================================================*\
        Function:	submitlinks
        Purpose:	grab links from a form submission
        Input:		$URI	where you are submitting from
        Output:		$this->results	an array of the links from the post
    \*======================================================================*/

    function submitlinks($URI, $formvars = "", $formfiles = "")
    {
        if ($this->submit($URI, $formvars, $formfiles) !== false) {
            if ($this->lastredirectaddr)
                $URI = $this->lastredirectaddr;
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++) {
                    $this->results[$x] = $this->_striplinks($this->results[$x]);
                    if ($this->expandlinks)
                        $this->results[$x] = $this->_expandlinks($this->results[$x], $URI);
                }
            } else {
                $this->results = $this->_striplinks($this->results);
                if ($this->expandlinks)
                    $this->results = $this->_expandlinks($this->results, $URI);
            }
            return $this;
        } else
            return false;
    }

    /*======================================================================*\
        Function:	submittext
        Purpose:	grab text from a form submission
        Input:		$URI	where you are submitting from
        Output:		$this->results	the text from the web page
    \*======================================================================*/

    function submittext($URI, $formvars = "", $formfiles = "")
    {
        if ($this->submit($URI, $formvars, $formfiles) !== false) {
            if ($this->lastredirectaddr)
                $URI = $this->lastredirectaddr;
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++) {
                    $this->results[$x] = $this->_striptext($this->results[$x]);
                    if ($this->expandlinks)
                        $this->results[$x] = $this->_expandlinks($this->results[$x], $URI);
                }
            } else {
                $this->results = $this->_striptext($this->results);
                if ($this->expandlinks)
                    $this->results = $this->_expandlinks($this->results, $URI);
            }
            return $this;
        } else
            return false;
    }


    /*======================================================================*\
        Function:	set_submit_multipart
        Purpose:	Set the form submission content type to
                    multipart/form-data
    \*======================================================================*/
    function set_submit_multipart()
    {
        $this->_submit_type = "multipart/form-data";
        return $this;
    }


    /*======================================================================*\
        Function:	set_submit_normal
        Purpose:	Set the form submission content type to
                    application/x-www-form-urlencoded
    \*======================================================================*/
    function set_submit_normal()
    {
        $this->_submit_type = "application/x-www-form-urlencoded";
        return $this;
    }




    /*======================================================================*\
        Private functions
    \*======================================================================*/


    /*======================================================================*\
        Function:	_striplinks
        Purpose:	strip the hyperlinks from an html document
        Input:		$document	document to strip.
        Output:		$match		an array of the links
    \*======================================================================*/

    function _striplinks($document)
    {
        preg_match_all("'<\s*a\s.*?href\s*=\s*			# find <a href=
						([\"\'])?					# find single or double quote
						(?(1) (.*?)\\1 | ([^\s\>]+))		# if quote found, match up to next matching
													# quote, otherwise match up to next space
						'isx", $document, $links);


        // catenate the non-empty matches from the conditional subpattern

        while (list($key, $val) = each($links[2])) {
            if (!empty($val))
                $match[] = $val;
        }

        while (list($key, $val) = each($links[3])) {
            if (!empty($val))
                $match[] = $val;
        }

        // return the links
        return $match;
    }

    /*======================================================================*\
        Function:	_stripform
        Purpose:	strip the form elements from an html document
        Input:		$document	document to strip.
        Output:		$match		an array of the links
    \*======================================================================*/

    function _stripform($document)
    {
        preg_match_all("'<\/?(FORM|INPUT|SELECT|TEXTAREA|(OPTION))[^<>]*>(?(2)(.*(?=<\/?(option|select)[^<>]*>[\r\n]*)|(?=[\r\n]*))|(?=[\r\n]*))'Usi", $document, $elements);

        // catenate the matches
        $match = implode("\r\n", $elements[0]);

        // return the links
        return $match;
    }


    /*======================================================================*\
        Function:	_striptext
        Purpose:	strip the text from an html document
        Input:		$document	document to strip.
        Output:		$text		the resulting text
    \*======================================================================*/

    function _striptext($document)
    {

        // I didn't use preg eval (//e) since that is only available in PHP 4.0.
        // so, list your entities one by one here. I included some of the
        // more common ones.

        $search = array("'<script[^>]*?>.*?</script>'si", // strip out javascript
            "'<[\/\!]*?[^<>]*?>'si", // strip out html tags
            "'([\r\n])[\s]+'", // strip out white space
            "'&(quot|#34|#034|#x22);'i", // replace html entities
            "'&(amp|#38|#038|#x26);'i", // added hexadecimal values
            "'&(lt|#60|#060|#x3c);'i",
            "'&(gt|#62|#062|#x3e);'i",
            "'&(nbsp|#160|#xa0);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&(reg|#174);'i",
            "'&(deg|#176);'i",
            "'&(#39|#039|#x27);'",
            "'&(euro|#8364);'i", // europe
            "'&a(uml|UML);'", // german
            "'&o(uml|UML);'",
            "'&u(uml|UML);'",
            "'&A(uml|UML);'",
            "'&O(uml|UML);'",
            "'&U(uml|UML);'",
            "'&szlig;'i",
        );
        $replace = array("",
            "",
            "\\1",
            "\"",
            "&",
            "<",
            ">",
            " ",
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            chr(174),
            chr(176),
            chr(39),
            chr(128),
            "ä",
            "ö",
            "ü",
            "Ä",
            "Ö",
            "Ü",
            "ß",
        );

        $text = preg_replace($search, $replace, $document);

        return $text;
    }

    /*======================================================================*\
        Function:	_expandlinks
        Purpose:	expand each link into a fully qualified URL
        Input:		$links			the links to qualify
                    $URI			the full URI to get the base from
        Output:		$expandedLinks	the expanded links
    \*======================================================================*/

    function _expandlinks($links, $URI)
    {

        preg_match("/^[^\?]+/", $URI, $match);

        $match = preg_replace("|/[^\/\.]+\.[^\/\.]+$|", "", $match[0]);
        $match = preg_replace("|/$|", "", $match);
        $match_part = parse_url($match);
        $match_root =
            $match_part["scheme"] . "://" . $match_part["host"];

        $search = array("|^http://" . preg_quote($this->host) . "|i",
            "|^(\/)|i",
            "|^(?!http://)(?!mailto:)|i",
            "|/\./|",
            "|/[^\/]+/\.\./|"
        );

        $replace = array("",
            $match_root . "/",
            $match . "/",
            "/",
            "/"
        );

        $expandedLinks = preg_replace($search, $replace, $links);

        return $expandedLinks;
    }

    /*======================================================================*\
        Function:	_httprequest
        Purpose:	go get the http(s) data from the server
        Input:		$url		the url to fetch
                    $fp			the current open file pointer
                    $URI		the full URI
                    $body		body contents to send if any (POST)
        Output:
    \*======================================================================*/

    function _httprequest($url, $fp, $URI, $http_method, $content_type = "", $body = "")
    {
        $cookie_headers = '';
        if ($this->passcookies && $this->_redirectaddr)
            $this->setcookies();

        $URI_PARTS = parse_url($URI);
        if (empty($url))
            $url = "/";
        $headers = $http_method . " " . $url . " " . $this->_httpversion . "\r\n";
        if (!empty($this->host) && !isset($this->rawheaders['Host'])) {
            $headers .= "Host: " . $this->host;
            if (!empty($this->port) && $this->port != '80')
                $headers .= ":" . $this->port;
            $headers .= "\r\n";
        }
        if (!empty($this->agent))
            $headers .= "User-Agent: " . $this->agent . "\r\n";
        if (!empty($this->accept))
            $headers .= "Accept: " . $this->accept . "\r\n";
        if ($this->use_gzip) {
            // make sure PHP was built with --with-zlib
            // and we can handle gzipp'ed data
            if (function_exists('gzinflate')) {
                $headers .= "Accept-encoding: gzip\r\n";
            } else {
                trigger_error(
                    "use_gzip is on, but PHP was built without zlib support." .
                    "  Requesting file(s) without gzip encoding.",
                    E_USER_NOTICE);
            }
        }
        if (!empty($this->referer))
            $headers .= "Referer: " . $this->referer . "\r\n";
        if (!empty($this->cookies)) {
            if (!is_array($this->cookies))
                $this->cookies = (array)$this->cookies;

            reset($this->cookies);
            if (count($this->cookies) > 0) {
                $cookie_headers .= 'Cookie: ';
                foreach ($this->cookies as $cookieKey => $cookieVal) {
                    $cookie_headers .= $cookieKey . "=" . urlencode($cookieVal) . "; ";
                }
                $headers .= substr($cookie_headers, 0, -2) . "\r\n";
            }
        }
        if (!empty($this->rawheaders)) {
            if (!is_array($this->rawheaders))
                $this->rawheaders = (array)$this->rawheaders;
            while (list($headerKey, $headerVal) = each($this->rawheaders))
                $headers .= $headerKey . ": " . $headerVal . "\r\n";
        }
        if (!empty($content_type)) {
            $headers .= "Content-type: $content_type";
            if ($content_type == "multipart/form-data")
                $headers .= "; boundary=" . $this->_mime_boundary;
            $headers .= "\r\n";
        }
        if (!empty($body))
            $headers .= "Content-length: " . strlen($body) . "\r\n";
        if (!empty($this->user) || !empty($this->pass))
            $headers .= "Authorization: Basic " . base64_encode($this->user . ":" . $this->pass) . "\r\n";

        //add proxy auth headers
        if (!empty($this->proxy_user))
            $headers .= 'Proxy-Authorization: ' . 'Basic ' . base64_encode($this->proxy_user . ':' . $this->proxy_pass) . "\r\n";


        $headers .= "\r\n";

        // set the read timeout if needed
        if ($this->read_timeout > 0)
            socket_set_timeout($fp, $this->read_timeout);
        $this->timed_out = false;

        fwrite($fp, $headers . $body, strlen($headers . $body));

        $this->_redirectaddr = false;
        unset($this->headers);

        // content was returned gzip encoded?
        $is_gzipped = false;

        while ($currentHeader = fgets($fp, $this->_maxlinelen)) {
            if ($this->read_timeout > 0 && $this->_check_timeout($fp)) {
                $this->status = -100;
                return false;
            }

            if ($currentHeader == "\r\n")
                break;

            // if a header begins with Location: or URI:, set the redirect
            if (preg_match("/^(Location:|URI:)/i", $currentHeader)) {
                // get URL portion of the redirect
                preg_match("/^(Location:|URI:)[ ]+(.*)/i", chop($currentHeader), $matches);
                // look for :// in the Location header to see if hostname is included
                if (!preg_match("|\:\/\/|", $matches[2])) {
                    // no host in the path, so prepend
                    $this->_redirectaddr = $URI_PARTS["scheme"] . "://" . $this->host . ":" . $this->port;
                    // eliminate double slash
                    if (!preg_match("|^/|", $matches[2]))
                        $this->_redirectaddr .= "/" . $matches[2];
                    else
                        $this->_redirectaddr .= $matches[2];
                } else
                    $this->_redirectaddr = $matches[2];
            }

            if (preg_match("|^HTTP/|", $currentHeader)) {
                if (preg_match("|^HTTP/[^\s]*\s(.*?)\s|", $currentHeader, $status)) {
                    $this->status = $status[1];
                }
                $this->response_code = $currentHeader;
            }

            if (preg_match("/Content-Encoding: gzip/", $currentHeader)) {
                $is_gzipped = true;
            }

            $this->headers[] = $currentHeader;
        }

        $results = '';
        do {
            $_data = fread($fp, $this->maxlength);
            if (strlen($_data) == 0) {
                break;
            }
            $results .= $_data;
        } while (true);

        // gunzip
        if ($is_gzipped) {
            // per http://www.php.net/manual/en/function.gzencode.php
            $results = substr($results, 10);
            $results = gzinflate($results);
        }

        if ($this->read_timeout > 0 && $this->_check_timeout($fp)) {
            $this->status = -100;
            return false;
        }

        // check if there is a a redirect meta tag

        if (preg_match("'<meta[\s]*http-equiv[^>]*?content[\s]*=[\s]*[\"\']?\d+;[\s]*URL[\s]*=[\s]*([^\"\']*?)[\"\']?>'i", $results, $match)) {
            $this->_redirectaddr = $this->_expandlinks($match[1], $URI);
        }

        // have we hit our frame depth and is there frame src to fetch?
        if (($this->_framedepth < $this->maxframes) && preg_match_all("'<frame\s+.*src[\s]*=[\'\"]?([^\'\"\>]+)'i", $results, $match)) {
            $this->results[] = $results;
            for ($x = 0; $x < count($match[1]); $x++)
                $this->_frameurls[] = $this->_expandlinks($match[1][$x], $URI_PARTS["scheme"] . "://" . $this->host);
        } // have we already fetched framed content?
        elseif (is_array($this->results))
            $this->results[] = $results;
        // no framed content
        else
            $this->results = $results;

        return $this;
    }

    /*======================================================================*\
        Function:	setcookies()
        Purpose:	set cookies for a redirection
    \*======================================================================*/

    function setcookies()
    {
        for ($x = 0; $x < count($this->headers); $x++) {
            if (preg_match('/^set-cookie:[\s]+([^=]+)=([^;]+)/i', $this->headers[$x], $match))
                $this->cookies[$match[1]] = urldecode($match[2]);
        }
        return $this;
    }


    /*======================================================================*\
        Function:	_check_timeout
        Purpose:	checks whether timeout has occurred
        Input:		$fp	file pointer
    \*======================================================================*/

    function _check_timeout($fp)
    {
        if ($this->read_timeout > 0) {
            $fp_status = socket_get_status($fp);
            if ($fp_status["timed_out"]) {
                $this->timed_out = true;
                return true;
            }
        }
        return false;
    }

    /*======================================================================*\
        Function:	_connect
        Purpose:	make a socket connection
        Input:		$fp	file pointer
    \*======================================================================*/

    function _connect(&$fp)
    {
        if (!empty($this->proxy_host) && !empty($this->proxy_port)) {
            $this->_isproxy = true;

            $host = $this->proxy_host;
            $port = $this->proxy_port;

            if ($this->scheme == 'https') {
                trigger_error("HTTPS connections over proxy are currently not supported", E_USER_ERROR);
                exit;
            }
        } else {
            $host = $this->host;
            $port = $this->port;
        }

        $this->status = 0;

        $context_opts = array();

        if ($this->scheme == 'https') {
            // if cafile or capath is specified, enable certificate
            // verification (including name checks)
            if (isset($this->cafile) || isset($this->capath)) {
                $context_opts['ssl'] = array(
                    'verify_peer' => true,
                    'CN_match' => $this->host,
                    'disable_compression' => true,
                );

                if (isset($this->cafile))
                    $context_opts['ssl']['cafile'] = $this->cafile;
                if (isset($this->capath))
                    $context_opts['ssl']['capath'] = $this->capath;
            }
                    
            $host = 'ssl://' . $host;
        }

        $context = stream_context_create($context_opts);

        if (version_compare(PHP_VERSION, '5.0.0', '>')) {
            if($this->scheme == 'http')
                $host = "tcp://" . $host;
            $fp = stream_socket_client(
                "$host:$port",
                $errno,
                $errmsg,
                $this->_fp_timeout,
                STREAM_CLIENT_CONNECT,
                $context);
        } else {
            $fp = fsockopen(
                $host,
                $port,
                $errno,
                $errstr,
                $this->_fp_timeout,
                $context);
        }

        if ($fp) {
            // socket connection succeeded
            return true;
        } else {
            // socket connection failed
            $this->status = $errno;
            switch ($errno) {
                case -3:
                    $this->error = "socket creation failed (-3)";
                case -4:
                    $this->error = "dns lookup failure (-4)";
                case -5:
                    $this->error = "connection refused or timed out (-5)";
                default:
                    $this->error = "connection failed (" . $errno . ")";
            }
            return false;
        }
    }

    /*======================================================================*\
        Function:	_disconnect
        Purpose:	disconnect a socket connection
        Input:		$fp	file pointer
    \*======================================================================*/

    function _disconnect($fp)
    {
        return (fclose($fp));
    }


    /*======================================================================*\
        Function:	_prepare_post_body
        Purpose:	Prepare post body according to encoding type
        Input:		$formvars  - form variables
                    $formfiles - form upload files
        Output:		post body
    \*======================================================================*/

    function _prepare_post_body($formvars, $formfiles)
    {
        settype($formvars, "array");
        settype($formfiles, "array");
        $postdata = '';

        if (count($formvars) == 0 && count($formfiles) == 0)
            return;

        switch ($this->_submit_type) {
            case "application/x-www-form-urlencoded":
                reset($formvars);
                while (list($key, $val) = each($formvars)) {
                    if (is_array($val) || is_object($val)) {
                        while (list($cur_key, $cur_val) = each($val)) {
                            $postdata .= urlencode($key) . "[]=" . urlencode($cur_val) . "&";
                        }
                    } else
                        $postdata .= urlencode($key) . "=" . urlencode($val) . "&";
                }
                break;

            case "multipart/form-data":
                $this->_mime_boundary = "Snoopy" . md5(uniqid(microtime()));

                reset($formvars);
                while (list($key, $val) = each($formvars)) {
                    if (is_array($val) || is_object($val)) {
                        while (list($cur_key, $cur_val) = each($val)) {
                            $postdata .= "--" . $this->_mime_boundary . "\r\n";
                            $postdata .= "Content-Disposition: form-data; name=\"$key\[\]\"\r\n\r\n";
                            $postdata .= "$cur_val\r\n";
                        }
                    } else {
                        $postdata .= "--" . $this->_mime_boundary . "\r\n";
                        $postdata .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
                        $postdata .= "$val\r\n";
                    }
                }

                reset($formfiles);
                while (list($field_name, $file_names) = each($formfiles)) {
                    settype($file_names, "array");
                    while (list(, $file_name) = each($file_names)) {
                        if (!is_readable($file_name)) continue;

                        $fp = fopen($file_name, "r");
                        $file_content = fread($fp, filesize($file_name));
                        fclose($fp);
                        $base_name = basename($file_name);

                        $postdata .= "--" . $this->_mime_boundary . "\r\n";
                        $postdata .= "Content-Disposition: form-data; name=\"$field_name\"; filename=\"$base_name\"\r\n\r\n";
                        $postdata .= "$file_content\r\n";
                    }
                }
                $postdata .= "--" . $this->_mime_boundary . "--\r\n";
                break;
        }

        return $postdata;
    }

    /*======================================================================*\
    Function:	getResults
    Purpose:	return the results of a request
    Output:		string results
    \*======================================================================*/

    function getResults()
    {
        return $this->results;
    }
}



/**
 * Created by JetBrains PhpStorm.
 * User: taoqili
 * Date: 12-7-18
 * Time: 上午11: 32
 * UEditor编辑器通用上传类
 */
class Uploader
{
    private $fileField; //文件域名
    private $file; //文件上传对象
    private $base64; //文件上传对象
    private $config; //配置信息
    private $oriName; //原始文件名
    private $fileName; //新文件名
    private $fullName; //完整文件名,即从当前配置目录开始的URL
    private $filePath; //完整文件名,即从当前配置目录开始的URL
    private $fileSize; //文件大小
    private $fileType; //文件类型
    private $stateInfo; //上传状态信息,
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    );

    /**
     * 构造函数
     * @param string $fileField 表单名称
     * @param array $config 配置项
     * @param bool $base64 是否解析base64编码，可省略。若开启，则$fileField代表的是base64编码的字符串表单名
     */
    public function __construct($fileField, $config, $type = "upload")
    {
        $this->fileField = $fileField;
        $this->config = $config;
        $this->type = $type;
        if ($type == "remote") {
            $this->saveRemote();
        } else if($type == "base64") {
            $this->upBase64();
        } else {
            $this->upFile();
        }
        //var_dump($this->stateMap['ERROR_TYPE_NOT_ALLOWED']);die;
        //$this->stateMap['ERROR_TYPE_NOT_ALLOWED'] = iconv('unicode', 'utf-8', $this->stateMap['ERROR_TYPE_NOT_ALLOWED']);
    }

    /**
     * 上传文件的主处理方法
     * @return mixed
     */
    private function upFile()
    {
        $file = $this->file = $_FILES[$this->fileField];
        if (!$file) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return;
        }
        if ($this->file['error']) {
            $this->stateInfo = $this->getStateInfo($file['error']);
            return;
        } else if (!file_exists($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
            return;
        } else if (!is_uploaded_file($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMPFILE");
            return;
        }

        $this->oriName = $file['name'];
        $this->fileSize = $file['size'];
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        //检查是否不允许的文件格式
        if (!$this->checkType()) {
            $this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
            return;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return;
        }

        //移动文件
        if (!(move_uploaded_file($file["tmp_name"], $this->filePath) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
        }
    }

    /**
     * 处理base64编码的图片上传
     * @return mixed
     */
    private function upBase64()
    {
        $base64Data = $_POST[$this->fileField];
        $img = base64_decode($base64Data);

        $this->oriName = $this->config['oriName'];
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
        }

    }

    /**
     * 拉取远程图片
     * @return mixed
     */
    private function saveRemote()
    {
        $imgUrl = htmlspecialchars($this->fileField);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_LINK");
            return;
        }

        preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
        $host_with_protocol = count($matches) > 1 ? $matches[1] : '';

        // 判断是否是合法 url
        if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
            $this->stateInfo = $this->getStateInfo("INVALID_URL");
            return;
        }

        preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
        $host_without_protocol = count($matches) > 1 ? $matches[1] : '';

        // 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
        $ip = gethostbyname($host_without_protocol);
        // 判断是否是私有 ip
        if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            $this->stateInfo = $this->getStateInfo("INVALID_IP");
            return;
        }

        //获取请求头并检测死链
        $heads = get_headers($imgUrl, 1);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            $this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
            return;
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(strrchr($imgUrl, '.'));
        if (!in_array($fileType, $this->config['allowFiles']) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
            return;
        }

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            array('http' => array(
                'follow_location' => false // don't follow redirects
            ))
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

        $this->oriName = $m ? $m[1]:"";
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
        }

    }

    /**
     * 上传错误检查
     * @param $errCode
     * @return string
     */
    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    private function getFileExt()
    {
        return strtolower(strrchr($this->oriName, '.'));
    }

    /**
     * 重命名文件
     * @return string
     */
    private function getFullName()
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = $this->config["pathFormat"];
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $format = str_replace("{hh}", $d[4], $format);
        $format = str_replace("{ii}", $d[5], $format);
        $format = str_replace("{ss}", $d[6], $format);
        $format = str_replace("{time}", $t, $format);

        //过滤文件名的非法自负,并替换文件名
        $oriName = substr($this->oriName, 0, strrpos($this->oriName, '.'));
        $oriName = preg_replace("/[\|\?\"\<\>\/\*\\\\]+/", '', $oriName);
        $format = str_replace("{filename}", $oriName, $format);

        //替换随机字符串
        $randNum = rand(1, 10000000000) . rand(1, 10000000000);
        if (preg_match("/\{rand\:([\d]*)\}/i", $format, $matches)) {
            $format = preg_replace("/\{rand\:[\d]*\}/i", substr($randNum, 0, $matches[1]), $format);
        }

        $ext = $this->getFileExt();
        return $format . $ext;
    }

    /**
     * 获取文件名
     * @return string
     */
    private function getFileName () {
        return substr($this->filePath, strrpos($this->filePath, '/') + 1);
    }

    /**
     * 获取文件完整路径
     * @return string
     */
    private function getFilePath()
    {
        $fullname = $this->fullName;
        $rootPath = $_SERVER['DOCUMENT_ROOT'];

        if (substr($fullname, 0, 1) != '/') {
            $fullname = '/' . $fullname;
        }

        return $rootPath . $fullname;
    }

    /**
     * 文件类型检测
     * @return bool
     */
    private function checkType()
    {
        return in_array($this->getFileExt(), $this->config["allowFiles"]);
    }

    /**
     * 文件大小检测
     * @return bool
     */
    private function  checkSize()
    {
        return $this->fileSize <= ($this->config["maxSize"]);
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function getFileInfo()
    {
        return array(
            "state" => $this->stateInfo,
            "url" => $this->fullName,
            "title" => $this->fileName,
            "original" => $this->oriName,
            "type" => $this->fileType,
            "size" => $this->fileSize
        );
    }

}

class auth {

    private $auth_model;
    private $_banned;
    private $_ban_reason;
    public $error;

    function __construct() {
        $this->auth_model = new authModel; //载入权限验证模型        
        $this->autologin(); //检测是否有自动登录
    }

    /**
     * 用户登录
     * @param type $login 登录POST参数
     * @param type $super_user 超级用户名
     */
    function auth_user_login($login, $password, $remember = 0, $super_user = 'root') {
        $result = FALSE; //
        if (!empty($login) AND !empty($password)) {
            $login_mode_func = 'get_login';
            $login_mode_func_temp = $login_mode_func . '_temp';
            $query = $this->auth_model->$login_mode_func($login);

            if($query) {

                if ($query['banned'] > 0) {
                    // 设置用户禁用
                    $this->_banned = TRUE;
                    $this->_ban_reason = $query['ban_reason'];
                } else {//匹配密码

                    if (md5_d($password) == $query['password']) {
                        $this->_auth_set_session($query);
                        if ($query['newpass']) {
                            // 清除重置密码
                            $this->auth_model->clear_newpass($query['uid']);
                        }
                        if ($remember) {
                            // 创建自动登录
                            $this->_auth_create_autologin($query['uid']);
                        }

                        // 设置上次登录ip和时间
                        $this->_set_last_ip_and_last_login($query['uid']);
                        // 清楚登录尝试
                        $this->_clear_login_attempts();
                        $result = TRUE;
                    } else {//密码错误，设置错误次数
                        $this->_increase_login_attempt();
                        $this->error = L('username_or_password_error');
                    }
                }
            } else if ($this->auth_model->$login_mode_func_temp($login)) {//查找用户是否已注册，但是未激活

                $this->error = sprintf(L('user_not_activation'), $login);
            } else {
                // 增加登录尝试
                $this->_increase_login_attempt();
                // 设置错误信息
                $this->error = L('login_username_not_exist'); //用户不存在
            }
        } else {
            $this->error = L('no_username_or_pwd');
        }
        return $result;
    }

    /**
     * @Title: auth_user
     * @Description: todo(第三方登录指定)
     * @author liuzhipeng
     * @param $login
     * @param $password
     * @param int $remember
     * @param string $super_user
     * @return  bool  返回类型
     */
    function auth_user($login,$password, $remember = 0, $super_user = 'root') {

        $result = FALSE; //
//        if (!empty($login) AND !empty($password)) {
            $login_mode_func = 'get_login';
            $login_mode_func_temp = $login_mode_func . '_temp';
            $query = $this->auth_model->$login_mode_func($login);
            if ($query) {
                if ($query['banned'] > 0) {
                    // 设置用户禁用
                    $this->_banned = TRUE;
                    $this->_ban_reason = $query['ban_reason'];
                } else {//匹配密码
//                    if (md5_d($password) == $query['password']) {
                        $this->_auth_set_session($query);
                        if ($query['newpass']) {
                            // 清除重置密码
                            $this->auth_model->clear_newpass($query['uid']);
                        }
                        if ($remember) {
                            // 创建自动登录
                            $this->_auth_create_autologin($query['uid']);
                        }

                        // 设置上次登录ip和时间
                        $this->_set_last_ip_and_last_login($query['uid']);
                        // 清楚登录尝试
                        $this->_clear_login_attempts();
                        $result = TRUE;
//                    } else {//密码错误，设置错误次数
//
//                        $this->_increase_login_attempt();
//                        $this->error = L('username_or_password_error');
//                    }
                }
            } else if ($this->auth_model->$login_mode_func_temp($login)) {//查找用户是否已注册，但是未激活
                $this->error = sprintf(L('user_not_activation'), $login);
            } else {
                // 增加登录尝试
                $this->_increase_login_attempt();
                // 设置错误信息
                $this->error = L('login_username_not_exist'); //用户不存在
            }
//        } else {
//            $this->error = L('no_username_or_pwd');
//        }
        return $result;
    }

    /**
     * @Title: register
     * @Description: todo()
     * @author zhouchao
     * @param $data  注册的信息 用户名密码神马之类的。
     * @return  bool  返回类型
     */
    function register($data) {
        $result = FALSE;
        $new_user = $data;
        $new_user['password'] = md5_d($data['password']);
        $new_user['is_bind'] = $data['is_bind'];
        $new_user['last_ip'] = ip_get_client();
        $new_user['rid'] = 8;//创建为求职者

        $new_user['unionid'] = $data['unionid'];
        $new_user['type'] = $data['type'];

        // 发送电子邮件来激活用户
        if (C('AUTH_EMAIL_ACTIVATE')) {
            // 添加激活密钥到new_user array
            $new_user['activation_key'] = token();
            // 在数据库中创建的临时用户,用户仍然未激活。
            $insert = $this->auth_model->create_temp($new_user);
        } else {
            // 创建用户
            $insert = $this->auth_model->create_user($new_user);
            //创建用户资料
        }
        if ($insert) {
            // 原始密码
            $new_user['password'] = $data['password'];

            $result = $new_user;

            // 按照配置文件中的信息发送电子邮件
            // 如果用户需要使用电子邮件激活帐户
            if (C('AUTH_EMAIL_ACTIVATE')) {
                // 创建Email
                $from = C('email_username');
                $subject = sprintf(L('auth_activate_subject'), C('WEB_NAME'));
                // 激活链接
                $new_user['activate_url'] = __WEB__ . '/index/auth/activate/username/' . $new_user['username'] . '/key/' . $new_user['activation_key'];

                // 触发事件，并获得电子邮件的内容
                $new_user['expire'] = (C('EMAIL_ACTIVATE_EXPIRE') / 3600) . '小时';
                $content = getEmailTpl('register_active', $new_user);

                // 发送激活链接到邮件
                $this->_send_email($new_user['email'], $from, $content['subject'], $content['content']);
            } else {
                // 没有开启邮箱验证注册，但是注册后会发送账户信息
                if (C('EMAIL_ACCOUNT_INFO')) {
                    $from = C('email_username');
                    $subject = sprintf(L('auth_account_subject'), C('WEB_NAME'));
                    $content = getEmailTpl('register_info', $new_user);
                    $this->_send_email($new_user['email'], $from, $content['subject'], $content['content']);
                }
            }
        }

        return $result;
    }

    /**
     * @Title: _send_email
     * @Description: todo(发送邮件)
     * @author zhouchao
     * @param $tomail
     * @param $toname
     * @param $title
     * @param $body
     * @return  void  返回类型
     */
    private function _send_email($tomail, $toname, $title, $body) {
        $email = new mail();
        $email->send($tomail, $toname, $title, $body);
    }
    

    //修改密码
    function change_password($data){
        $result=FALSE;
        $info=$this->auth_model->get_user_by_id($_SESSION['uid']);
        if(md5_d($data['old_pwd'])==$info['password']){
            if($this->auth_model->set_user($_SESSION['uid'],array('password'=>md5_d($data['password'])))>=0){
                $result=TRUE;
            }
        }else{
            $this->error='原密码错误！';
        }
        return $result;
    }

    //自动登录
    function autologin() {
        $result = FALSE;
        $auto = isset($_COOKIE[C('AUTH_AUTOLOGIN_COOKIE_NAME')]) ? $_COOKIE[C('AUTH_AUTOLOGIN_COOKIE_NAME')] : FALSE;
        if (!$this->is_logged_in() && $auto) {
            $auto = unserialize($auto);
            if (isset($auto['key_id']) AND $auto['key_id'] AND $auto['user_id']) {

                $query = $this->auth_model->get_key($auto['key_id'], $auto['user_id']);

                if ($query) {
                    $this->_set_last_ip_and_last_login($auto['user_id']);//设置登录IP和时间
                    $this->_auth_set_session($query);
                    $this->_auto_cookie($auto);
                    $result = TRUE;
                }
            }
        }
        return $result;
    }

    //增加登录错误次数
    private function _increase_login_attempt() {
        if (C('AUTH_COUNT_LOGIN_ATTEMPTS') AND !$this->is_max_login_attempts_exceeded()) {
            $this->auth_model->increase_attempt(ip_get_client());
        }
    }

    //是否超过最大登录尝试次数
    function is_max_login_attempts_exceeded() {
        return ($this->auth_model->check_attempts(ip_get_client())) >= C('AUTH_MAX_LOGIN_ATTEMPTS');
    }

    private function _set_last_ip_and_last_login($uid) {
        $login_log = array(
            'last_ip' => ip_get_client(),
            'last_login' => time()
        );
        $this->auth_model->set_user($uid, $login_log);
    }

    //清楚登录尝试
    private function _clear_login_attempts() {
        if (C('AUTH_COUNT_LOGIN_ATTEMPTS')) {
            $this->auth_model->clear_attempts(ip_get_client());
        }
    }

    /**
     * 创建自动登录
     * @param type $uid 
     */
    private function _auth_create_autologin($uid) {
        $result = FALSE;

        //如果用户想要记住登录
        $user = array(
            'key_id' => substr(token(), 0, 16),
            'user_id' => $uid
        );
        // 先清楚用户之前的自动登录信息
        $this->auth_model->prune_keys($user['user_id']);
        if ($this->auth_model->store_key($user['key_id'], $user['user_id'])) {
            //设置用户自动登录cookie
            $this->_auto_cookie($user);
            $result = TRUE;
        }

        return $result;
    }

    private function _auto_cookie($data) {

        $cookie = array(
            'name' => C('AUTH_AUTOLOGIN_COOKIE_NAME'),
            'value' => serialize($data),
            'expire' => C('AUTH_AUTOLOGIN_COOKIE_LIFE')
        );
        setcookie($cookie['name'], $cookie['value'], time() + $cookie['expire'], '/');
    }

    /**
     * 用户登录成功设置SESSION值
     * @param type $data 
     */
    private function _auth_set_session($data) {
        $role_data = $this->_get_role_data($data['uid']);
        
        $_SESSION = array(
            'uid' => $data['uid'],
            'username' => $data['username'],
            'real_name' => $data['real_name'],
            'phone'=>$data['phone'],
            'avatar'=>$data['avatar'],
            'last_login' => $data['last_login'],
            'logged_in' => TRUE,
            'role' => array(
                'rid' => $role_data['role_id'],
            )
        );

        $_SESSION['sessionid'] = AuthToken::encode($_SESSION,1728000);

        $_SESSION['auth'] = array(
            'permission' => $role_data['permission'],
            'parent_permissions' => $role_data['parent_permissions'],
        );

    }

    private function _auth_set_session2($data) {
        $role_data = $this->_get_role_data($data['uid']);

        $_SESSION = array(
            'uid' => $data['uid'],
            'username' => $data['username'],
            'real_name' => $data['real_name'],
            'phone'=>$data['phone'],
            'email'=>$data['email'],
            'avatar'=>$data['avatar'],
            'last_ip' => $data['last_ip'],
            'last_login' => $data['last_login'],
            'logged_in' => TRUE,
            'role' => array(
                'rid' => $role_data['role_id'],
                'rname' => $role_data['role_name'],
                'rtitle' => $role_data['role_title'],
                'parent_rid' => $role_data['parent_roles_id'],
                'parent_rname' => $role_data['parent_roles_name'],
                'parent_rtitle' => $role_data['parent_roles_title'],
            )
        );
        $_SESSION['auth'] = array(
            'permission' => $role_data['permission'],
            'parent_permissions' => $role_data['parent_permissions'],
        );
    }

    private function _get_role_data($uid) {
        //初始化变量
        $data = array();
        $role_name = array();
        $role_id = array();
        $role_title = array();
        $parent_roles_id = array();
        $parent_roles_name = array();
        $parent_roles_title = array();
        $permission = array();
        $parent_permissions = array();

        $query = $this->auth_model->get_user_role_by_id($uid); //通过id获取用户角色
        if ($query) {
            foreach ($query as $key => $value) {
                $role_id[] = $value['rid'];
                $role_name[] = $value['rname'];
                $role_title[] = $value['title'];
                if ($value['pid'] > 0) {
                    $parent_roles_id[] = $value['pid'];
                    $finished = FALSE;
                    $parent_id = $value['pid'];
                    //获取所有的父级角色
                    while ($finished == FALSE) {
                        $i_query = $this->auth_model->get_role_by_rid($parent_id);
                        // 如果角色存在
                        if ($i_query) {
                            // 保存值
                            $i_role = $i_query;

                            // 如果角色没有父级角色
                            if ($i_role['pid'] == 0) {
                                // 取得最后的父级角色信息
                                $parent_roles_name[] = $i_role['rname'];
                                $parent_roles_title[] = $i_role['title'];
                                // 停止循环
                                $finished = TRUE;
                            } else {
                                // 改变parent id 开始下次循环
                                $parent_id = $i_role['pid'];
                                $parent_roles_id[] = $parent_id;
                                $parent_roles_name[] = $i_role['rname'];
                                $parent_roles_title[] = $i_role['title'];
                            }
                        } else {
                            //parent_id没有找到，删除最后的parent_roles_id
                            array_pop($parent_roles_id);
                            $finished = TRUE;
                        }
                    }
                }
            }
        }
        //获取用户的权限
        $permission = $this->auth_model->get_permissions_data($role_id);
        //获取用户父级角色权限
        if (!empty($parent_roles_id)) {
            $parent_permissions = $this->auth_model->get_permissions_data($parent_roles_id);
        }
        $data['role_name'] = $role_name;
        $data['role_id'] = $role_id;
        $data['role_title'] = $role_title;
        $data['parent_roles_id'] = $parent_roles_id;
        $data['parent_roles_name'] = $parent_roles_name;
        $data['parent_roles_title'] = $parent_roles_title;
        $data['permission'] = $permission;
        $data['parent_permissions'] = $parent_permissions;
        return $data;
    }

    /**
     * 检验验证码是否正确
     */
    function captcha_check($validate_code) {
        $result = TRUE;
        if (strtolower($validate_code) != strtolower($_SESSION['code'])) {
            $result = FALSE;
        }
        return $result;
    }

    function is_banned() {
        return $this->_banned;
    }

    function get_ban_reason() {
        return $this->_ban_reason;
    }

    /**
     * 用户是否登录 
     */
    function is_logged_in() {

        return isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : FALSE;
        //return FALSE;
    }

    /**
     * 数组中任意项是否在另外的数组中
     * @param type $needle
     * @param type $haystack 
     */
    function _array_in_array($needle, $haystack) {
        if (!is_array($needle)) {
            $needle = array($needle);
        }
        //合并允许访问的权限
        $haystack = array_unique(array_merge($haystack['permission'], $haystack['parent_permissions']));
        foreach ($needle as $value) {
            if (in_array($value, $haystack)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    //检查uri是否是不需要验证的方法
    private function _check_no_auth_uri($method) {
        $no_auth_method = C('NO_AUTH_METHOD');
        if (!is_array(C('NO_AUTH_METHOD'))) {
            $no_auth_method = array($no_auth_method);
        }

        if (in_array($method, $no_auth_method)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @Title: check_uri_permissions
     * @Description: todo(检查访问权限)
     * @author zhouchao
     * @return  bool  返回类型
     */
    function check_uri_permissions() {
        $app_name = '/' . APP . '/';
        $control = $app_name . CONTROL . '/';
        $method = $control . METHOD . '/';

        if ($this->_check_no_auth_uri(METHOD) OR $this->_array_in_array(array('/', $method), $_SESSION['auth'])) {
            return TRUE;
        } else {
            $this->error = L('no_permission');
        }
        return false;
    }


}


class authModel extends Model {

    public $user;
    private $user_role;
    private $role;
    private $user_autologin;
    private $login_attempts;
    private $user_agent;
    private $user_temp;

    function __construct() {
        $this->user_agent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
        $this->user = M('user');
        $this->user_temp = M('user_temp');
        $this->role = M('role');
        $this->login_attempts = M('login_attempts');
        $this->user_autologin = M('user_autologin');
        $this->user_role = V('user_role');
        $this->user_role->view = array(
            'role' => array(//定义 user_info表规则
                'join_type' => "inner", //指定连接方式
                "field" => 'rid,rname,pid,title', //字段，email字段起别
                "on" => "user_role.rid=role.rid", //关联条件
            ),
        );
    }

    function get_role_by_rid($rid) {
        return $this->role->where(array('rid' => $rid))->find();
    }

    function get_login($login) {
        return $this->user->where("username = '$login' OR email = '$login' OR authid_qq = '$login'OR authid_wx ='$login' OR authid_wb='$login' OR phone = '$login' OR unionid='$login'")->find();
    }
    function get_user_by_id($id) {
        return $this->user->where(array("uid" => $id))->find();
    }

    function get_user_by_email($email) {
        return $this->user->where(array("email" => $email))->find();
    }

    function get_user_by_username($username) {
        return $this->user->where(array("username" => $username))->find();
    }

    //user_temp
    function get_login_temp($login) {
        return $this->user_temp->where("username = '$login' OR email = '$login' OR authid_qq = '$login'OR authid_wx ='$login' OR authid_wb='$login' OR phone = '$login' OR unionid='$login'")->find();
    }

    function get_user_by_email_temp($email) {
        return $this->user_temp->where(array("email" => $email))->find();
    }

    function get_user_by_username_temp($username) {
        return $this->user_temp->where(array("username" => $username))->find();
    }

    //user_temp结束
    function get_user_role_by_id($uid) {
        return $this->user_role->where("role.state=1")->where(array('uid' => $uid))->findall();
    }

    function set_user($user_id, $data) {
        return $this->user->where(array('uid' => $user_id))->update($data);
    }

    function clear_newpass($user_id) {
        $data = array(
            'newpass' => NULL,
            'newpass_key' => NULL,
            'newpass_time' => NULL
        );
        return $this->set_user($user_id, $data);
    }

    function get_permissions($roles_id) {
        $db = M('access');
        $data=$db->field('permissions')->in(array('rid'=>$roles_id))->findall();
        $permissions = array();
        if($data){
            foreach ($data as $value) {
                $permissions=array_merge($permissions,json_decode($value['permissions'],true));
            }
        }
        return $permissions;
    }

    //获取访问权限节点
    function get_permissions_data($roles_id) {
        return $this->get_permissions($roles_id);
    }

    //自动登录
    function prune_keys($uid) {
        $data = array(
            'user_id' => $uid,
            'user_agent' => substr($this->user_agent, 0, 149),
            'last_ip' => ip_get_client()
        );
        return $this->user_autologin->where($data)->delete();
    }

    //自动登录
    function prune_all_keys($uid) {
        $data = array(
            'user_id' => $uid,
        );
        return $this->user_autologin->where($data)->delete();
    }

    function store_key($key, $user_id) {
        $user = array(
            'key_id' => md5_d($key),
            'user_id' => $user_id,
            'user_agent' => substr($this->user_agent, 0, 149),
            'last_ip' => ip_get_client(),
            'last_login' => time()
        );
        $this->user_autologin->insert($user);
        return $this->user_autologin->get_affected_rows();
    }

    function get_key($key, $user_id) {
        $db=V('user');
        $db->view=array(
            'user_autologin'=>array(
                'type'=>'inner',
                'on'=>'user_autologin.user_id=user.uid'
            )
        );
        $result =$db->field('user.uid,user.username,user.last_ip,user.last_login')
                    ->where("user.uid=$user_id AND user_autologin.key_id='" . md5_d($key) . "'")
                    ->find();
        //$result = $this->user_autologin->query($sql);
        return $result;
    }

    //尝试错误登录次数
    function increase_attempt($ip_address) {
        $data = array(
            'ip_address' => $ip_address,
            'time' => time()
        );
        $this->login_attempts->insert($data);
    }

    function check_attempts($ip) {
        $resule = $this->login_attempts->where(array('ip_address' => $ip))->findall();
        return $this->login_attempts->get_affected_rows();
    }

    function clear_attempts($ip) {
        $this->login_attempts->where(array('ip_address' => $ip))->delete();
    }

    function create_temp($data) {
        $data['created'] = time();
        return $this->user_temp->insert($data);
    }

    function create_user($data) {
        $data['created'] = time();
//        $data['last_login']=time();
        $data['last_ip']=ip_get_client();
        $id = $this->user->insert($data);
        if ($id) {
            $this->user_role->insert(array('uid' => $id, 'rid' =>8));
            return $id;
        }
        return FALSE;
    }

    /**
     * 删除到期的激活账户信息 
     */
    function delExpireActivate() {
        $this->user_temp->where('created <' . time() - C('EMAIL_ACTIVATE_EXPIRE'))->del();
    }

    function activate_user_info($username, $key) {
        return $this->user_temp->where("username='$username' AND activation_key='$key'")->find();
    }

    function delete_temp_user($id) {
        $this->user_temp->del($id);
    }

}


class data {

    private $data_model;
    private $db_prefix; //数据库前缀
    public $fields; //模型的字段结构数组
    private $data; //原始数据

    /**
     * 
     * @param type $model 模型名
     */

    function __construct($model = 'recruit') {
        $this->data_model = K('backend/data');
        $this->db_prefix = rtrim(C('DB_PREFIX'), '_') . '_';
        $this->fields = include PATH_ROOT . '/caches/model/field/m_' . $model . '.php';
    }

    function convert($data) {
        $this->data = $data;
        foreach ($this->fields as $key => $value) {
        	
            if (!isset($data[$key])) {
                continue;
            }
            $method = 'convert' . ucfirst($value['field_type']);
            if (method_exists($this, $method)) {
                $this->$method($key, $data[$value['field_name']]);
            }
        }
        return $this->data;
    }

    /**
     * 转换选项数据
     * @param type $field
     * @param type $value 
     */
    function convertSwitch($field, $value) {
        if (strpos($value, '#') !== FALSE) {
            $value_arr = explode('#', $value);
            $new_v = '';
            foreach ($value_arr as $v) {
                if(isset($this->fields[$field]['data'][$v])){
                    $new_v.= $this->fields[$field]['data'][$v] . '、';
                }
            }
            $this->data[$field] = rtrim($new_v, '、');
        } else {
            if(isset($this->fields[$field]['data'][$value])){
                $this->data[$field] = $this->fields[$field]['data'][$value];
            }
        }
    }

    /**
     * 取得联动分类或城市的缓存数据值
     * @param string $type city or linkage
     * @return 换成数据数组
     */
    private function _getLinkageCache($type='linkage')
    {
        $field=array('laid','title');
        if($type=='city'){
            $field=array('id','name');
        }
        $cache=array();
        $path=PATH_ROOT.'/caches/linkage';
        $file=$path.'/'.$type.'.php';
        if(file_exists($file)){
            $cache=include $file;
        }else{
            dir::create($path);
            $db = M($type);
            $linkages = $db->field(implode(',', $field))->findall();
            foreach ($linkages as $value) {
                $cache[$value[$field[0]]]=$value[$field[1]];
            }
            file_put_contents($file, '<?php if(!defined("PATH_LC")){exit;}return '.var_export($cache,TRUE).';');
        }
        return $cache;
    }

    /**
     * 转换联动数据
     * @param type $field
     * @param type $value 
     */
    function convertLinkage($field, $value) {
        $attached = json_decode($this->fields[$field]['attached'], TRUE);
        if (strpos($value, '#') !== FALSE) {
            $id = explode('#', $value);
        } else {
            $id = array($value);
        }
        //查找附属字段的
        foreach ($attached as $value) {
            if (strpos($this->data[$value], '#') !== FALSE) {
               $id = array_merge($id,explode('#', $this->data[$value]));
            } else {
                $id[] = $this->data[$value];
            }
        }
        $type='linkage';
        if($this->fields[$field]['lcgid']=='city'){//是城市数据,从城市表中取得数据转换
            $type='city';
        }
        $linkage=$this->_getLinkageCache($type);

        $cn = '';
        $delimiter = ' - ';
        foreach ($id as $value) {
            if(isset($linkage[$value])){
                $cn.=$linkage[$value] . $delimiter;
            }
        }
        $this->data[$field] = rtrim($cn, $delimiter);
    }

    //将所有的分类数据写入JS文件，形如：var city={},var linkage1={},var linkage2={}
    function writeCatesToJs() {
        C('DEBUG', 0);
        $db=M('linkage_category');
        $lcgid=$db->field('lcgid')->findall();
        $city=$db->table('city')->field('id,pid,name')->findall();
        $str='var city='.json_encode_cn(formatParentData2($city,array('id','pid','name'))).',';//城市的数据
        foreach ($lcgid as $value) {
            $condition = array(
                'lcgid' => $value['lcgid']
            );
            $result = $this->data_model->getCateLinkage($condition,'laid,title,pid');
           $str.='linkage_'.$value['lcgid'].' = '.json_encode_cn(formatParentData($result)).',';
        }
        $str=rtrim($str,',').';';

        $file_name = PATH_ROOT . '/caches/js/linkage_data.js';
        var_dump($file_name);die;
        file_put_contents($file_name, $str);
    }

}

/*
 * Describe   : 生成、更新模型视图。更新模型结构、缓存模型字段
 */

class field extends Control {
    private $model_info;
    private $model;
    private $model_field;
    private $html_struct=array();
    private $model_id;
    /**
     * @param int $model模型表 ID
     */
    public function __construct($model=null)
    {
        $this->model_id=$model;
        $this->model=M('model');
        $this->model_info=$this->model->find($model);
        if(!$this->model_info){
            $this->error('不存在该模型。');
        }
        $this->model_field=include PATH_ROOT.'/caches/model/field/m_'.$this->model_info['name'].'.php';
    }

    /**
     * 生成每个字段的机构。
     */
    public function build_field()
    {
        foreach ($this->model_field as $field) {
            if($field['state']){
                $this->html_struct[$field['field_name']]=array(
                    'title'=>$field['title'],
                    'field_tips'=>$field['field_tips'],
                    'add_html'=>'',
                    'edit_html'=>''
                );
                $this->build_struct($field);
            }
        }
        $data='<?php return '.var_export($this->html_struct,true).';';
        file_put_contents(PATH_ROOT.'/caches/model/struct/'.$this->model_info['name'].'.php', $data);
        $this->generate_html();
    }

    public function build_struct($data)
    {
        $data['rule']=json_decode($data['rule']);
        $tags = new MyTags;
        $rule = '';
        $validate = '';
        $width = empty($data['width']) ? '' : ' width:' . $data['width'] . 'px;';
        $height = empty($data['height']) ? '' : ' height:' . $data['height'] . 'px;';
        $style = '';
        if ($width . $height != '') {
            $style = ' style="' . $width . $height . '" ';
        }
        if (!empty($data['rule'])) {
            $validate = ' validate={';
            foreach ($data['rule'] as $rule_key => $rule_value) {
                $rule.='"' . $rule_key . '"' . ':' . $rule_value . ',';
            }
//组合错误消息
            if (trim($data['error_tips']) != '') {
                $rule.='"messages":"' . $data['error_tips'] . '"';
            }
            $validate .= trim($rule, ',') . '} ';
        }
        if (substr($data['field_type'], 0, 6) == 'switch' && $data['setting']['type'] == 'option') {
            $this->html_struct[$data['field_name']]['add_html'] = '<select name="' . $data['field_name'] . '" ' . $data['js_event'] . ' ' . $validate . '><option value="">请选择</option>';
            $this->html_struct[$data['field_name']]['edit_html'] = '<select name="' . $data['field_name'] . '" ' . $data['js_event'] . ' ' . $validate . '><option value="">请选择</option>';
        }
        if (substr($data['field_type'], 0, 5) == 'input') {
            $this->html_struct[$data['field_name']]['add_html'] = '<input type="text" name="' . $data['field_name'] . '" ' . $data['js_event'] . ' ' . $data['html_attr'] . $validate . ' value="' . $data['default_val'] . '" />';
            $this->html_struct[$data['field_name']]['edit_html'] = "<input type=\"text\" name=\"" . $data['field_name'] . '" ' . $data['js_event'] . ' ' . $data['html_attr'] . $validate . $style . " value=\"<?php echo $" . $data['field_name'] . ";?>\" />";
        }
        if ($data['field_type'] == 'switch') {
            foreach ($data['data'] as $radio_v => $radio_n) {
                if ($radio_v == $data['default_val']) {
                    $checked = 'checked="checked"';
                    $selected = 'selected="selected"';
                } else {
                    $checked = '';
                    $selected = $checked;
                }
                $edit_checked = '<?php if(in_array("'.$radio_v.'",explode("#",$' . $data['field_name'] . '))):?>checked<?php endif;?>';
                $edit_selected = '<?php if($' . $data['field_name'] . '=="' . $radio_v . '"):?>selected<?php endif;?>';
                if ($data['setting']['type'] == 'radio') {
                    $this->html_struct[$data['field_name']]['add_html'] .= '<label><input type="' . $data['setting']['type'] . "\" name=\"{$data['field_name']}\" {$data['js_event']} value=\"{$radio_v}\"{$validate} $checked />{$radio_n}</label>";

                    $this->html_struct[$data['field_name']]['edit_html'] .= '<label><input type="' . $data['setting']['type'] . "\" name=\"{$data['field_name']}\" {$data['js_event']} value=\"{$radio_v}\"{$validate} $edit_checked />{$radio_n}</label>";
                }else if($data['setting']['type'] == 'checkbox'){
                    $this->html_struct[$data['field_name']]['add_html'] .= '<label><input type="' . $data['setting']['type'] . "\" name=\"{$data['field_name']}[]\" {$data['js_event']} value=\"{$radio_v}\"{$validate} $checked />{$radio_n}</label>";
                    $this->html_struct[$data['field_name']]['edit_html'] .= '<label><input type="' . $data['setting']['type'] . "\" name=\"{$data['field_name']}[]\" {$data['js_event']} value=\"{$radio_v}\"{$validate} $edit_checked />{$radio_n}</label>";
                }else {//下拉列表
                    $this->html_struct[$data['field_name']]['add_html'] .= '<option value="' . $radio_v . '" ' . $selected . '>' . $radio_n . '</option>';
                    $this->html_struct[$data['field_name']]['edit_html'] .= '<option value="' . $radio_v . '" ' . $edit_selected . '>' . $radio_n . '</option>';
                }
            }
        }
        if (substr($data['field_type'], 0, 6) == 'switch' && $data['setting']['type'] == 'option') {
            $this->html_struct[$data['field_name']]['add_html'] .= '</select>';
            $this->html_struct[$data['field_name']]['edit_html'] .= '</select>';
        }
        if ($data['field_type'] == 'editor') {//编辑器
            $editor_attr = array(
                'name' => $data['field_name'],
                'id' => $data['field_name'],
                'content' => $data['default_val'],
                'width' => $data['width'],
                'height' => $data['height'],
                'style' => $data['editor_style'],
            );
            if(isset($data['rule']['maxlength'])){
                $editor_attr['max'] = $data['rule']['maxlength']; //最大输入字数
            }
            $this->html_struct[$data['field_name']]['add_html'] = $tags->_editor($editor_attr, '');
            $editor_attr['content'] = '$' . $data['field_name'];
            $this->html_struct[$data['field_name']]['edit_html'] = $tags->_editor($editor_attr, '');
        }
        if ($data['field_type'] == 'textarea') {//文本域
            $this->html_struct[$data['field_name']]['add_html'] = '<textarea name = "' . $data['field_name'] . '" ' . $data['js_event'] . ' ' . $data['html_attr'] . $validate . $style . ' >' . $data['default_val'] . '</textarea>';
            $this->html_struct[$data['field_name']]['edit_html'] = '<textarea name = "' . $data['field_name'] . '" ' . $data['js_event'] . ' ' . $data['html_attr'] . $validate . $style . ' ><?php echo $' . $data['field_name'] . ';?></textarea>';
        }
        if ($data['field_type'] == 'linkage') {//联动数据
            $attr = array();
            $data['attached']=json_decode($data['attached']);
            if (!empty($data['attached'])) {
                $attr['field'] = $data['field_name'] . '#';
                $attr['edit_field'] = '$' . $data['field_name'] . '#$';
                $attr['field'].=implode('#', $data['attached']);
                $attr['edit_field'].=implode('#$', $data['attached']);
            } else {
                $attr['field'] = $data['field_name'];
                $attr['edit_field'] = '$' . $data['field_name'];
            }
            $attr['edit_field'] = rtrim($attr['edit_field'], '#$');

            $attr['data'] = $data['lcgid'];
            $attr['style'] = $data['linkage_style'];
            $attr['attr'] = $data['html_attr'] . $validate;
            $attr['checkbox'] = isset($data['setting']['checkbox']) ? 'true' : 'false';
            if (!empty($data['default_val'])) {
                $attr['defaults'] = $data['default_val'];
            }
            $this->html_struct[$data['field_name']]['add_html'] = $tags->_linkage($attr, '');
            $attr['defaults'] = $attr['edit_field'];
            $this->html_struct[$data['field_name']]['edit_html'] = $tags->_linkage($attr, '');
        }
    }

    /**
     * 根据组合好的模型表单结构、生成HTML视图
     * @param int $model 模型ID
     */
    public function generate_html()
    {
        $db=M('model_field');
        $cond = array('dmid' => $this->model_id);
        $struct = include PATH_ROOT.'/caches/model/struct/'.$this->model_info['name'].'.php';
        $tpl_path = PATH_ROOT . '/caches/model/model_';
        //查找联动数据id的和风格样式
        $cond[]='lcgid > 0';
        $linkage = $db->where($cond)->count();
        $style = $db->field('linkage_style')->where('lcgid != "0" AND dmid="' . $this->model_id . '"')->group('linkage_style')->findall();
        $script='';
        if($linkage) {
            $script='<script type="text/javascript" src="__ROOT__/caches/js/linkage_data.js"></script>';
        }
        if(is_array($style)){
            foreach ($style as $value) {
                $script.='<script type="text/javascript" src="__ROOT__/public/js/linkage/linkage_style_' . $value['linkage_style'] . '.js"></script>';
            }
        }
        $fields = array();
        foreach ($struct as $k => $v) {
            $fields[$k] = $v;
            $fields[$k]['html'] = $v['add_html'];
            unset($fields[$k]['add_html']);
            unset($fields[$k]['edit_html']);
        }


        //获取前端配置文件
        $front_config=include PATH_ROOT.'/config/app.php';
        $style=$front_config['TPL_DIR'].'/'.$front_config['TPL_STYLE'];

        //更新发布模板
        $this->assign('fields', $fields);
        $field_add = $this->display($style .'/model/'. $this->model_info['issue_tpl'], NULL, 'text/html', 'utf-8', FALSE);
        file_put_contents($tpl_path . $this->model_id . '_add.html', $script . $field_add);

        $fields = array();
        foreach ($struct as $k => $v) {
            $fields[$k] = $v;
            $fields[$k]['html'] = $v['edit_html'];
            unset($fields[$k]['add_html']);
            unset($fields[$k]['edit_html']);
        }
        $this->assign('fields', $fields);
        //更新修改模板
        $field_edit= $this->display($style .'/model/'. $this->model_info['edit_tpl'], NULL, 'text/html', 'utf-8', FALSE);
        file_put_contents($tpl_path . $this->model_id . '_edit.html', $script . $field_edit);
    }
    /**
     * 过滤前台表单的一些输入。如在标题输入<script></script>登不安全代码
     * @param array $fieldValue 前台处理值
     * @param array 过滤后的数组
     */
    public function filterField($fieldValue)
    {
        if(!is_array($fieldValue)){
            $this->error('数据有误！');
        }
        foreach ($this->model_field as $field_name => $field) {
            if($field['state']){
                switch ($field['field_type']) {
                    case 'input_varchar'://单行文本框
                        $fieldValue[$field_name]=strip_tags($fieldValue[$field_name]);
                        break;
                    case 'input_char'://单行文本框
                        $fieldValue[$field_name]=strip_tags($fieldValue[$field_name]);
                        break;
                    case 'input_float':
                        $fieldValue[$field_name]=floatval($fieldValue[$field_name]);
                        break;
                    case 'input_decimal':
                        $fieldValue[$field_name]=floatval($fieldValue[$field_name]);
                        break;
                    case 'input_double':
                        $fieldValue[$field_name]=floatval($fieldValue[$field_name]);
                        break;
                    case 'linkage'://联动数据
                        if(!isset($field['setting']['checkbox'])){
                            $fieldValue[$field_name]=intval($fieldValue[$field_name]);
                        }
                        break;
                    case 'editor'://编辑器
                        $fieldValue[$field_name]=htmlspecialchars($fieldValue[$field_name]);
                        break;
                    case 'textarea'://文本域
                        $fieldValue[$field_name]=htmlspecialchars($fieldValue[$field_name]);
                        break;
                    case 'input_int'://联动数据
                        $fieldValue[$field_name]=intval($fieldValue[$field_name]);
                        break;
                    case 'switch':
                        $fieldValue[$field_name]=strip_tags($fieldValue[$field_name]);
                        break;
                    default:
                        $fieldValue[$field_name]=htmlspecialchars($fieldValue[$field_name]);
                        break;
                }
            }
        }
        return $fieldValue;
    }
}
class JSSDK {

  public function getSignPackage($url='')
  {
      $jsapiTicket = $this->getJsApiTicket();

      // 注意 URL 一定要动态获取，不能 hardcode.
      if (empty($url)) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      }

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => weiXinConfig::APPID,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("jsapi_ticket.json"));
    if ($data->expire_time < time()) {
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
        $data->expire_time = time() + 7000;
        $data->jsapi_ticket = $ticket;
        $fp = fopen("jsapi_ticket.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $ticket = $data->jsapi_ticket;
    }

    return $ticket;
  }

  private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("access_token.json"));
    if ($data->expire_time < time()) {
      // 如果是企业号用以下URL获取access_token
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".weiXinConfig::APPID."&secret=".weiXinConfig::APPSECRET;
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $data->expire_time = time() + 7000;
        $data->access_token = $access_token;
        $fp = fopen("access_token.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
}

/*
 * Describe   : 扩展核心控制器
 */

class myControl extends Control {

    protected $auth;

    public function __construct()
    {
        $this->auth = new auth;
        if (!$this->auth->is_logged_in()) {
            $this->go('auth/index');
        }
        if (!$this->auth->check_uri_permissions()) {
            $this->error($this->auth->error,__APP__);
            die;
        }
        
    }

    function is_logged_in() {
        return $this->auth->is_logged_in();
    }

    /**
     * 成功,失败时做的工作
     * @param type $success $this->success的参数
     * @param type $error
     */
    function success_error($result, $success = array('成功', '', 3), $error = array('失败', '', 3)) {
        $success[1] = isset($success[1]) ? $success[1] : '';
        $success[2] = isset($success[2]) ? $success[2] : 3;
        $error[1] = isset($error[1]) ? $error[1] : '';
        $error[2] = isset($error[2]) ? $error[2] : 3;
        if ($result) {
            $this->success($success[0], $success[1], $success[2]);
        } else {
            $this->error($error[0], $error[1], $error[2]);
        }
    }


}

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

class weiXinConfig
{
    //=======【基本信息设置】=====================================
    //
    /**
     * TODO: 修改这里配置为您自己申请的商户信息
     * 微信公众号信息配置
     *
     * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
     *
     * MCHID：商户号（必须配置，开户邮件中可查看）
     *
     * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
     * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
     *
     * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），
     * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
     * @var string
     */
    const APPID = 'wxe47acd935fae527a';//通渔
    const MCHID = '1337548901';
    const KEY = '566d78a9804684020be146f4fa7efd09';
    const APPSECRET = '810ffa1b26866c11000196c9ddc5864e';
    const NOTIFY_URL  = 'http://www.letusport.com/index/member/callBack';

    //=======【证书路径设置】=====================================
    /**
     * TODO：设置商户证书路径
     * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
     * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
     * @var path
     */
    const SSLCERT_PATH = '../cert/apiclient_cert.pem';
    const SSLKEY_PATH = '../cert/apiclient_key.pem';

    //=======【curl代理设置】===================================
    /**
     * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
     * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
     * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
     * @var unknown_type
     */
    const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
    const CURL_PROXY_PORT = 0;//8080;

    //=======【上报信息配置】===================================
    /**
     * TODO：接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
     * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
     * 开启错误上报。
     * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
     * @var int
     */
    const REPORT_LEVENL = 1;

    const PAY_TYPE_JSAPI = "JSAPI";
    const PAY_TYPE_NATIVE = "NATIVE";
    const PAY_TYPE_APP = "APP";
}

//namespace vendor\weixin;

use \Exception;

class weixinHelper {


    /**
     * @Title: createNonceStr
     * @Description: todo(产生的随机字符串)
     * @author zhouchao
     * @param int $length
     * @return  string  返回类型
     */
    public static function createNonceStr($length = 32) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * @Title: toUrlParams
     * @Description: todo(拼接签名地址 key=value)
     * @author zhouchao
     * @param $urlObj
     * @return  string  返回类型
     */
    public static function toUrlParams($urlObj){

        $buff = "";

        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){

                $buff .= $k . "=" . $v . "&";

            }
        }

        $buff = trim($buff, "&");

        return $buff;
    }

    /**
     * @Title: formatBizQueryParaMap
     * @Description: todo(格式化参数)
     * @author zhouchao
     * @param $paraMap
     * @param $urlEncode
     * @return  string  返回类型
     */
    public static function formatBizQueryParaMap($paraMap, $urlEncode){

        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if($urlEncode)
            {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';

        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * @Title: httpGet
     * @Description: todo(发起Get请求)
     * @author zhouchao
     * @param $url
     * @return  mixed  返回类型
     */
    public static function httpGet($url) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    /**
     * @Title: postXmlCurl
     * @Description: todo(以post请求提交Xml)
     * @author zhouchao
     * @param $xml
     * @param $url
     * @param bool $useCert
     * @param int $second
     * @return  mixed  返回类型
     */
    public static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new Exception("curl出错，错误码:$error");
        }
    }

    /**
     * @Title: ArrayToXml
     * @Description: todo(数组转成xml)
     * @author zhouchao
     * @param $data
     * @return  string  返回类型
     */
    public static function ArrayToXml($data){

        if(is_array($data)){

            $xml = "<xml>";
            foreach ($data as $key=>$val)
            {
                if (is_numeric($val)){
                    $xml.="<".$key.">".$val."</".$key.">";
                }else{
                    $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
                }
            }
            $xml.="</xml>";
            return $xml;

        }else{
            return "<xml></xml>";
        }
    }

    public static function XmlToArray($xml){
        if(!$xml){
            throw new Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

    }

    /**
     * @Title: checkSign
     * @Description: todo(验证签名)
     * @author zhouchao
     * @param $xml
     * @return  bool|mixed  返回类型
     */
    public static function checkSign($xml){

        $params = weixinHelper::XmlToArray($xml);

        if($params['return_code'] != 'SUCCESS'){
            return $params;
        }

        if(empty($params['sign'])){
            throw new Exception("数据异常");
        }

        $sign = self::makeSign($params);

        if($sign!=$params['sign']){

            throw new Exception("签名错误！");
        }

        return $params;

    }

    /**
     * @Title: makeSign
     * @Description: todo(生成签名)
     * @author zhouchao
     * @param $params
     * @return  string  返回类型
     */
    public static function makeSign($params){
        //签名步骤一：按字典序排序参数
        ksort($params);
        $string = weixinHelper::toUrlParams($params);

        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".weixinConfig::KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * @Title: reportCostTime
     * @Description: todo(上报数据， 上报的时候将屏蔽所有异常流程)
     * @author nipeiquan
     * @param $url
     * @param $startTimeStamp
     * @param $data
     * @return  void  返回类型
     */
    public static function reportCostTime($url, $startTimeStamp, $data){

        if(weixinConfig::REPORT_LEVENL == 0){//关闭上报

            return;
        }
        //如果仅失败上报
        if(weixinConfig::REPORT_LEVENL == 1 &&
            array_key_exists("return_code", $data) &&
            $data["return_code"] == "SUCCESS" &&
            array_key_exists("result_code", $data) &&
            $data["result_code"] == "SUCCESS")
        {
            return;
        }

        $endTimeStamp = self::getMillisecond();

        $params = array(
            'execute_time_'=>$endTimeStamp - $startTimeStamp,//执行时间
            'interface_url'=>$url,//上报对应的接口的完整URL
        );


        //返回状态码
        if(array_key_exists("return_code", $data)){
            $params['return_code'] = $data['return_code'];
        }
        //返回信息
        if(array_key_exists("return_msg", $data)){
            $params['return_msg'] = $data["return_msg"];
        }
        //业务结果
        if(array_key_exists("result_code", $data)){
            $params['result_code'] = $data["result_code"];
        }
        //错误代码
        if(array_key_exists("err_code", $data)){
            $params['err_code'] = $data["err_code"];
        }
        //错误代码描述
        if(array_key_exists("err_code_des", $data)){
            $params['err_code_des'] = $data["err_code_des"];
        }
        //商户订单号
        if(array_key_exists("out_trade_no", $data)){
            $params['out_trade_no'] = $data["out_trade_no"];
        }
        //设备号
        if(array_key_exists("device_info", $data)){
            $params['device_info'] = $data["device_info"];
        }

        try{
            self::report($params);
        } catch (Exception $e){
            //不做任何处理
        }

    }

    /**
     * @Title: report
     * @Description: todo(上报)
     * @author zhouchao
     * @param $inputObj
     * @param int $timeOut
     * @return  mixed  返回类型
     */
    public static function report($params, $timeOut = 1)
    {
        $url = "https://api.mch.weixin.qq.com/payitil/report";
        //检测必填参数
        if(empty($params['interface_url'])) {
            throw new Exception("接口URL，缺少必填参数interface_url！");
        }
        if(empty($params['return_code'])) {
            throw new Exception("返回状态码，缺少必填参数return_code！");
        }
        if(empty($params['return_code'])) {
            throw new Exception("业务结果，缺少必填参数result_code！");
        }
        if(empty($params['user_ip'])) {
            throw new Exception("访问接口IP，缺少必填参数user_ip！");
        }
        if(empty($params['execute_time_'])) {
            throw new Exception("接口耗时，缺少必填参数execute_time_！");
        }
        $params['appid'] = weixinConfig::APPID;//公众账号ID
        $params['mch_id'] = weixinConfig::MCHID;//公众账号ID
        $params['mch_id'] = $_SERVER['REMOTE_ADDR'];//终端ip
        $params['time'] = date("YmdHis");//商户上报时间
        $params['nonce_str'] = self::createNonceStr();//随机字符串

        $params['sign'] = self::makeSign($params);

        $xml = self::ArrayToXml($params);

        $response = self::postXmlCurl($xml,$url,false,30);

        return $response;
    }

    /**
     * @Title: get_php_file
     * @Description: todo(得到cache的php文件)
     * @author zhouchao
     * @param $filename
     * @return  string  返回类型
     */
    public static function get_php_file($filename) {

        return trim(substr(file_get_contents(__DIR__.'/cache/'.$filename), 15));

    }

    /**
     * @Title: set_php_file
     * @Description: todo(设置cache的php文件)
     * @author zhouchao
     * @param $filename
     * @param $content
     * @return  void  返回类型
     */
    public static function set_php_file($filename, $content) {

        $fp = fopen(__DIR__.'/cache/'.$filename, "w");

        fwrite($fp, "<?php exit();?>" . $content);

        fclose($fp);
    }

    /**
     * 获取毫秒级别的时间戳
     */
    public static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }



}

class weixin {

    public $code;

    /**
     * @Title: createOauthUrlForCode
     * @Description: todo(生成可以获得code的url)
     * @author zhouchao
     * @param $redirectUrl
     * @return  string  返回类型
     */
    public function createOauthUrlForCode($redirectUrl,$scope='snsapi_base'){
        $urlObj["appid"] = weiXinConfig::APPID;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = $scope;
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }


    /**
     * @Title: getOpenid
     * @Description: todo(过curl向微信提交code，以获取openid)
     * @author zhouchao
     * @return  mixed  返回类型
     */
    public function getOpenid(){

        $url = $this->createOauthUrlForOpenid();
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);

        //{
        //   "access_token":"ACCESS_TOKEN",
        //   "expires_in":7200,
        //   "refresh_token":"REFRESH_TOKEN",
        //   "openid":"OPENID",
        //   "scope":"SCOPE"
        //}
        return $data;

    }

    /**
     * @Title: getUserInfo
     * @Description: todo(通过access_token和openid拉取用户信息)
     * @author zhouchao
     * @param $access_token
     * @param $openid
     * @return  mixed  返回类型
     */
    public function getUserInfo($access_token,$openid){

        $url = $this->createOauthUrlFornSapiUserInfo($access_token,$openid);


        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以json形式返回
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res,true);

//        {
//            "openid":" OPENID",
//            " nickname": NICKNAME,
//          "sex":"1",
//          "province":"PROVINCE"
//          "city":"CITY",
//          "country":"COUNTRY",
//          "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
//	        "privilege":[
//              "PRIVILEGE1"
//	            "PRIVILEGE2"
//          ],
//         "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
//      }
        return $data;
    }


    /**
     * @Title: formatBizQueryParaMap
     * @Description: todo(格式化参数)
     * @author zhouchao
     * @param $paraMap
     * @param $urlencode
     * @return  string  返回类型
     */
    private function formatBizQueryParaMap($paraMap, $urlencode){

        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';

        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * @Title: createOauthUrlForOpenid
     * @Description: todo(生成可以获得openid的url)
     * @author zhouchao
     * @return  string  返回类型
     */
    private function createOauthUrlForOpenid(){
        $urlObj["appid"] = weiXinConfig::APPID;;
        $urlObj["secret"] = weiXinConfig::APPSECRET;
        $urlObj["code"] = $this->code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    /**
     * @Title: createOauthUrlFornSapiUserInfo
     * @Description: todo(生成可以获得用户信息的url)
     * @author zhouchao
     * @param $access_token
     * @param $openid
     * @return  string  返回类型
     */
    private function createOauthUrlFornSapiUserInfo($access_token,$openid){
        $urlObj["access_token"] = $access_token;
        $urlObj["openid"] = $openid;
        $urlObj["lang"] = 'zh_CN';
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://api.weixin.qq.com/sns/userinfo?".$bizString;
    }



} ?>