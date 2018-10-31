<?php
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

