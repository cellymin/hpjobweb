<?php

/**
 * Created by PhpStorm.
 * User: Kaiqi
 * Date: 2017/5/4
 * Time: 14:45
 */
class recruitCommentControl extends myControl
{
    private $table_new_recruit_comment;
    public function __construct()
    {
        $this->table_new_recruit_comment = M("new_recruit_comment");
    }

    /**
     * @Title: commentList
     * @Description:todo(获取评论列表(带筛选))
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function commentList(){
       $sql = [];
       if($_SERVER['REQUEST_METHOD']=="GET"){
           $name = urldecode($_GET['name']);
           $state = $_GET['state'];
           if(isset($name))$sql[]="company_name like '%".$name."%'";
           if(isset($state))$sql['state'] = $state;
           $count =$this->table_new_recruit_comment->where($sql)->count();
           $page = new page($count,10);
           $comments = $this->table_new_recruit_comment->where($sql)->order('create_at desc')->findall($page->limit());
           $data = new data();
           foreach ($comments as $k => $v){
               $comments[$k] = $data->convert($v);
           }
           $this->assign('comments',$comments);
           $this->assign('pages',$page->show());
           $this->display();
       }else {
           $this->display();
       }
    }

    /**
     * @Title: commentVerify
     * @Description:todo(评论审核)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function commentVerify(){
        $id = $_POST['id'];
        $state = $_POST['state'];
        if(empty($id)||empty($state))echo 0;
        echo M('new_recruit_comment')->in(['recruit_comment_id'=>$id])->update(['state'=>$state]);
    }

    /**
     * @Title: delComment
     * @Description:todo(删除评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function delComment(){
        $id = $_POST['id'];
        echo M("new_recruit_comment")->in(['recruit_comment_id'=>$id])->del();
    }

    /**
     * @Title: replyComment
     * @Description:todo(管理员回复评论)
     * @Author: Kaiqi
     * @return void 返回类型
     */
    public function replyComment(){
        if($_SERVER['REQUEST_METHOD']=="GET"){
            $id = $_GET['id'];
            $this->assign("id",$id);
            $this->display();
        }else if($_SERVER['REQUEST_METHOD']=="POST"){
            $id = $_POST['id'];
            $content = $_POST['content'];
            $uid = $_SESSION['uid'];
            $user = M('user')->where(['uid'=>$uid])->find();
            $comment = M("new_recruit_comment")->where(['recruit_comment_id'=>$id])->find();
            $data = [
                'uid' => $user['uid'],
                'uvatar' => $user['avatar'],
                'unickname' =>$user['nickname'],
                'bcid' => $id,
                'buid' => $comment['uid'],
                'bunickname' => $comment['unickname'],
                'buvatar' => $comment['uvatar'],
                'company_id' => $comment['company_id'],
                'company_name' => $comment['company_name'],
                'recruit_id' => $comment['recruit_id'],
                'recruit_name' => $comment['recruit_name'],
                'content' => $content,
                'state' => 2,
                'create_at' => time()
            ];
            M("new_recruit_comment")->insert($data);
            $this->success("回复成功");
        }
    }
}