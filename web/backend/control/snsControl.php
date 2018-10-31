<?php

/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/5/8
 * Time: 13:31
 */
class snsControl extends myControl{
    private $table_new_sns;
    private $table_new_comment;
    public function __construct()
    {
        $this->table_new_sns = M('new_sns');
        $this->table_new_comment = M('new_comment');
    }

    /**
     * @Title: snsNewLists
     * @Description:todo(帖子列表)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function snsNewLists(){
        $_GET = urldecode_array($_GET);
        if(!empty($_GET['content'])) {
            $cond[] = 'content like "%' . ltrim($_GET['content']) . '%"';
        }
        if(isset($_GET['state'])){
            $cond[] = 'del_state = '.$_GET['state'];
        }
        $db = M('new_sns');
        if(empty($cond)) $count = $db->where()->count();
        else{
            $count = $db->where($cond)->count();
        }
        $page = new page($count,20);
        if(empty($cond))$sns = $db->where()->order('is_top desc,create_at desc')->findall($page->limit());
        else{
            $sns = $db->where($cond)->order('is_top desc,create_at desc')->findall($page->limit());
        }
        foreach ($sns as $k=>$v) {
            $sns[$k]['images'] = explode('#',$sns[$k]['images']);
            if(empty($sns[$k]['images'][0]))$sns[$k]['images']=null;
        }

        $this->assign('sns',$sns);
        $this->assign('pages',$page->show());
        $this->display();
    }

    /**
     * @Title: delListSns
     * @Description:todo(删除帖子)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function delListSns(){
        $id = $_GET['id'];
        if($this->table_new_sns->where(array('sid'=>$id))->update(['del_state'=>1])){
            $this->success('删除成功');
        }else{
            $this->error('系统错误');
        }
    }

    /**
     * @Title: pushSns
     * @Description:todo(推送帖子)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function pushSns(){
        if($_SERVER['REQUEST_METHOD']=="GET"){
            $id = $_GET['sid'];
            $this->assign('id',$id);
            $this->display();
        }else {
            $id = $_POST['id'];
            $uid = $_SESSION['uid'];
            if (empty($id)) {
                $this->error("请选择帖子");
            }
            $sns = M('new_sns')->where(['sid' => $id])->find();
            $title = $_POST['title'];
            $content = $_POST['content'];
            if (empty($content)) {
                if (strlen($sns['content']) <= 20) {
                    $content = $sns['content'];
                } else {
                    $content = mb_substr($sns['content'], 0, 20, 'utf-8');;
                }
                if (empty($title)) $title = $content;
            }
            if (empty($sns['images'])) {
                $images = "http://120.55.165.117/uploads/logo.png";
            } else {
                $temp = explode("#", $sns['images']);
                $images = $temp[0];
            }

            $state = K('message')->createMission($uid, [
                'data_type' => 102,
                'title' => $title,
                'content' => $content,
                'images' => $images,
                'data_id' => $id
            ], 0, $title);
            if ($state) $this->success("推送完成");
            else {
                $this->success("推送失败");
            }
        }
    }
    /**
     * @Title: commentLists
     * @Description:todo(获取帖子评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function commentLists(){
        $sns_id = $_GET['sns_id'];
        $count = $this->table_new_comment->where(['sid'=>$sns_id])->count();
        if($count>0){
            $page = new page($count,20);
            $comment = $this->table_new_comment->where(['sid'=>$sns_id])->order('create_at desc')->findall($page->limit());
            $this->assign('comment',$comment);
            $this->assign('pages',$page->show());
            $this->display();
        }else{
            $this->success('该帖子暂时没有评论');
        }
    }

    /**
     * @Title: topListSns
     * @Description:todo(置顶帖子)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function topListSns(){
        $sid = $_GET['id'];
        if($this->table_new_sns->where(['sid'=>$sid])->update(['is_top'=>1,'update_at'=>time()])){
            $this->success("置顶成功");
        }else{
            $this->error("置顶失败");
        }

    }

    /**
     * @Title: chanelTopListSns
     * @Description:todo(取消置顶)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function chanelTopListSns(){
        $sid = $_GET['id'];
        if($this->table_new_sns->where(['sid'=>$sid])->update(['is_top'=>0,'update_at'=>time()])){
            $this->success("取消置顶成功");
        }else{
            $this->success("取消置顶失败");
        }
    }

    /**
     * @Title: delComment
     * @Description:todo(删除评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function delComment(){
        $cid = $_GET['cid'];
        if($this->table_new_comment->where(['cid'=>$cid])->del()){
            $this->success("评论删除成功");
        }else{
            $this->error("评论删除失败");
        }
    }
}