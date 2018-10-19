<?php
class commissionControl extends myControl {
    /**
     * @Title: invite
     * @Description: todo(提现记录)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function invite(){
        $_GET = urldecode_array($_GET);
        $cond = array();
        $cond['type'] =1;
        if(!empty($_GET['start_time']) && isset($_GET['end_time'])){
            $start_time = strtotime($_GET['start_time']);
            $end_time = strtotime($_GET['end_time']);
            $cond[] =' AND create_time > ' . $start_time . ' AND create_time < ' . $end_time;
        }
        $db = M('commission_log');
        $count = $db->where($cond)->count();
        $page = new page($count,20);
        $invite = $db->order('id desc,create_time desc')->where()->findall($page->limit());
        foreach($invite as $key=>$val){
            $uid = $val['uid'];
            $name = M('user_info')->where('uid = '.$uid)->find();
            $from = M('user')->where('uid = '.$uid)->find();
            $f_uid = $from['from_id'];

            if($f_uid > 0){// 被邀请人存在
                $f_name = M('user_info')->where('uid = '.$f_uid)->find();
                $invite[$key]['f_name'] = $f_name['name'];
                $invite[$key]['f_gender'] = $f_name['gender'];
                $invite[$key]['f_id_number'] = $f_name['id_number'];

            }

            $invite[$key]['name'] = $name['name'];
            $invite[$key]['gender'] = $name['gender'];
            $invite[$key]['id_number'] = $name['id_number'];

            if(empty($name['name'])){
                $invite[$key]['name'] = '暂无';
            }
            if(empty($name['id_number'])){
                $invite[$key]['id_number'] = '暂无';
            }
            if(empty($f_name['name'])){
                $invite[$key]['f_name'] = '暂无';
            }
            if(empty($f_name['id_number'])){
                $invite[$key]['f_id_number'] = '暂无';
            }

        }
        $this->assign('pages',$page->show());
        $this->assign('invite',$invite);
        $this->display();
    }
}