<?php

/*
 * Describe   : 抢购管理
 */
class goodsControl extends myControl {

    /**
     * @Title: lists
     * @Description: todo(抢购列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function lists(){
        $db = M('goods');
        $count = $db->count();
        $page = new page($count,20);
        $goods = $db->order('type asc')->findall($page->limit());
        $this->assign('goods',$goods);
        $this->assign('pages',$page->show());
        $this->display();
    }

    /**
     * @Title: add
     * @Description: todo(添加抢购)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['goods_title'];
            $desc = $_POST['goods_desc'];
            $price = $_POST['goods_price'];
            $amount = $_POST['goods_amount'];
            $start_time = $_POST['goods_start_time'];
            //$end_time = $_POST['goods_end_time'];
            $status = $_POST['status'];
            $type = $_POST['type'];
            $path = $_POST['path'];
            $img = $path[1][0];
            if(!empty($img)){
                $img =__ROOT__.'/'.$img;
            }else{
                $img = '';
            }
            $db = M('goods');
            $arr = array(
                'title'=>$title,
                'desc' => $desc,
                'amount' => $amount,
                'price' => $price,
                'start_time' => strtotime($start_time),
                //'end_time' => strtotime($end_time),
                'status'=>$status,
                'type'=>$type,
                'img'=>$img,
                'created' => time()
            );
        $goods = $db->add($arr);
        if($goods){
            $this->success('添加成功');
        }
        }
        $this->display();
    }

    /**
     * @Title: delGoods
     * @Description: todo(删除抢购)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function delGoods(){
        $gid = $_GET['gid'];
        $db = M('goods');
        $result = $db->where('gid=' . $gid)->del();
        if($result){
            $this->success('删除成功');
        }
    }

    /**
     * @Title: editGoods
     * @Description: todo(修改抢购信息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editGoods(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $gid = $_POST['gid'];
            $title = $_POST['title'];
            $desc = $_POST['desc'];
            $price = $_POST['price'];
            $amount = $_POST['amount'];
            $start_time = $_POST['start_time'];
            //$end_time = $_POST['end_time'];
            $status = $_POST['status'];
            $type = $_POST['type'];
            $path = $_POST['path'][1][0];
            $img = substr($path,0,4)=='http'?$path:__ROOT__.'/'.$path;
            $db = M('goods');
            $arr = array(
                'title'=>$title,
                'desc' => $desc,
                'amount' => $amount,
                'price' => $price,
                'start_time' => strtotime($start_time),
                //'end_time' => strtotime($end_time),
                'status'=>$status,
                'type'=>$type,
                'img'=>$img
            );
            $goods = $db->where('gid=' . $gid)->update($arr);
            if($goods){
                $this->success('修改成功');
            }
        }else{
            $gid = $_GET['gid'];
            $goods = M('goods')->where(array('gid'=>$gid))->find();
            $this->assign('goods',$goods);
            $this->display('edit');
        }

    }

    /**
     * @Title: orderLists
     * @Description: todo(订单列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function orderLists(){
        $_GET = urldecode_array($_GET);

        $db = M('order_info');

        if(!empty($_GET['username'])){
            $cond[] = 'username like "%'.ltrim($_GET['username']).'%"';
        }

        if(!empty($_GET['start_time']) && !empty($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'created >' . $start_time . ' and created <' . $end_time;
        }

        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'created >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'created <' . $end_time;
        }

        if(isset($_GET['is_contact'])){
            $cond[] = 'is_contact = ' . $_GET['is_contact'];
        }
        $count = $db->where($cond)->count();
        $page = new page($count,20);
        $order_lists = $db->where($cond)->order('is_contact asc,created desc')->findall($page->limit());
        foreach ($order_lists as $key=>$value){
            $user = M('user')->where('uid=' . $value['uid'])->find();
            $resume_basic = M('resume_basic')->where('uid=' . $value['uid'])->find();
            $user_info = M('user_info')->where('uid=' . $value['uid'])->find();
            $order_lists[$key]['name'] = $resume_basic['name'];
            $order_lists[$key]['gender'] = $user['gender'];
            $order_lists[$key]['id_num'] = $user_info['id_number'];
        }
        if(!empty($order_lists)){
            $this->assign('orderLists',$order_lists);
            $this->assign('pages',$page->show());
            $this->display();
        }
    }

    /**
     * @Title: isContact
     * @Description: todo(设为已联系)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function isContact(){

        $order_id = $_GET['order_id'];
        $db = M('order_info');
        $order = $db->where('order_id=' . $order_id)->find();
        if($order['is_contact']==0){
            if($db->where('order_id=' . $order_id)->update(array('is_contact'=>'1','contact_time'=>time(),'contact'=>$_SESSION['username']))){
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
        $db=M('order_info');
        if($db->in($_POST)->update(array('is_contact'=>'1','contact_time'=>time(),'contact'=>$_SESSION['username']))){
            echo 1;
            exit;
        }
    }

    /**
     * @Title: orderRestore
     * @Description: todo(恢复未联系)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function orderRestore(){
        $order_id = $_GET['order_id'];
        $db = M('order_info');
        $order = $db->where('order_id=' . $order_id)->find();
        if($order['is_contact']==1){
            if($db->where('order_id=' . $order_id)->update(array('is_contact'=>'0','contact_time'=>'','contact'=>''))){
                $this->success('操作成功');
            };
        }
    }

    /**
     * @Title: editPrize
     * @Description: todo(修改奖品)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function editPrize()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $probability = $_POST['probability'];
            $point = $_POST['point'];
            $type = $_POST['type'];
            $data = array(
                'name'=>$name,
                'probability'=>$probability,
                'point'=>$point,
                'type'=>$type
            );
            if(M('prize')->where(array('prize_id' => $id))->update($data)){
                $this->success('修改成功');
            }else{
                $this->error('系统错误');
            }
        }
    }
    /**
     * @Title: prizeLists
     * @Description: todo(奖品列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function prizeLists(){

        $db = M('prize');
        $prizes = $db->order('time desc')->findall();
        if($prizes){
            $this->assign('prizes',$prizes);
            $this->display();
        }
    }

    /**
     * @Title: userPrizeLists
     * @Description: todo(用户抽奖列表)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function userPrizeLists(){
        $_GET = urldecode_array($_GET);
        $db = M('prize_log');

        if(!empty($_GET['username'])){
            $cond[] = 'username like "%'.ltrim($_GET['username']).'%"';
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

        if(isset($_GET['prize_contact'])){
            $cond[] = 'prize_contact = ' . $_GET['prize_contact'];
        }

        $count = $db->where($cond)->count();
        $page = new page($count,20);
        $prizes = $db->where($cond)->order('prize_contact ASC,time DESC')->findall($page->limit());
        foreach ($prizes as $key=>$value){
            $pid = $value['pid'];
            $prize = M('prize')->where('prize_id='.$pid)->find();
            //$user = M('user')->where('uid=' . $value['uid'])->find();
            //$resume_basic = M('resume_basic')->where('uid=' . $value['uid'])->find();
            $user_info = M('user_info')->where('uid=' . $value['uid'])->find();
            $prizes[$key]['rel_name'] = $user_info['name'];
            $prizes[$key]['gender'] = $user_info['gender'];
            $prizes[$key]['id_num'] = $user_info['id_number'];
            $prizes[$key]['type'] = $prize['type'];
        }
        if($prizes){
            $this->assign('prizesLists',$prizes);
            $this->assign('pages',$page->show());
            $this->display();
        }
    }

    /**
     * @Title: prizeContact
     * @Description: todo(奖品已联系)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function prizeContact(){

        $lid = $_GET['id'];
        $db = M('prize_log');
        $prize = $db->where('lid=' . $lid)->find();
        if($prize['prize_contact']==0){
            if($db->where('lid=' . $lid)->update(array('prize_contact'=>'1','contact_time'=>time(),'contact'=>$_SESSION['username']))){
                $this->success('操作成功');
            };
        }
    }

    /**
     * @Title: onekey_contact
     * @Description: todo(一键奖品联系)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function onekey_contact(){
        $db=M('prize_log');
        if($db->in($_POST)->update(array('prize_contact'=>'1','contact_time'=>time(),'contact'=>$_SESSION['username']))){
            echo 1;
            exit;
        }
    }

    /**
     * @Title: restore
     * @Description: todo(恢复未联系状态)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function restore(){

        $lid = $_GET['id'];
        $db = M('prize_log');
        $prize = $db->where('lid=' . $lid)->find();
        if($prize['prize_contact']==1){
            if($db->where('lid=' . $lid)->update(array('prize_contact'=>'0','contact_time'=>'','contact'=>''))){
                $this->success('操作成功');
            };
        }
    }

    /**
     * @Title: addPrizeMess
     * @Description: todo(添加中奖消息)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function addPrizeMess()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $username = $_POST['username'];
            $data = array(
                'name'=>$name,
                'username'=>$username,
                'time'=>time(),
                'prize_contact'=>2
            );
            if(M('prize_log')->insert($data)){
                $this->success('操作成功');
            }else{
                $this->error('系统错误');
            }
        }
    }

    public function exportLogs(){

        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename=抽奖清单.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo iconv("UTF-8", "GBK","用户名")."\t";
        echo iconv("UTF-8", "GBK","奖品")."\t";
        echo iconv("UTF-8", "GBK","抽奖时间")."\t";
        echo iconv("UTF-8", "GBK","是否联系")."\t";
        echo iconv("UTF-8", "GBK","联系时间")."\t";
        echo iconv("UTF-8", "GBK","联系人")."\t";
        echo "\n";

        $cond = '';

        if(empty($_POST['start_time']) || empty($_POST['end_time'])){
            $cond = '';
        }

        if(!empty($_POST['start_time']) && !empty($_POST['end_time'])) {
            $start_time = strtotime($_POST['start_time']);

            $end_time = strtotime($_POST['end_time']);

            $cond = ' time > ' . $start_time . ' and time < ' . $end_time;
        }

        $infos = M('prize_log')->where($cond)->findall();

        foreach ($infos as $info){

            echo iconv("UTF-8", "GBK",$info['username'])."\t";
            echo iconv("UTF-8", "GBK",$info['name'])."\t";
            echo iconv("UTF-8", "GBK",date('Y-m-d H:i:s',$info['time']))."\t";
            if($info['prize_contact']==0){
                echo iconv("UTF-8", "GBK","未联系")."\t";
            }elseif($info['prize_contact']==1){
                echo iconv("UTF-8", "GBK","已联系")."\t";
            }elseif($info['prize_contact']==2){
                echo iconv("UTF-8", "GBK","开心添加")."\t";
            }
            echo iconv("UTF-8", "GBK",date('Y-m-d H:i:s',$info['contact_time']))."\t";
            echo iconv("UTF-8", "GBK",$info['contact'])."\t";
            echo "\n";
        }

    }
}

