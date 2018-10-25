<?php

/*
 * Describe   : 后台简历管理
 */

class resumeControl extends myControl {
	private $resume;
	public function __construct()
	{
		parent::__construct();
		$this->resume=M('resume');
	}
	
	/**
	 * 简历列表
	 */
	public function resumeList()
	{
		$db=V('resume');
		$db->view=array(
			'resume_basic'=>array(
				'type'=>'inner',
				'on'=>'resume.uid=resume_basic.uid',
//				'field'=>'name,telephone'
			),
            'user'=>array(
                'type'=>'inner',
                'on'=>'resume.uid=user.uid',
				'field'=>'uid,type,branchname,salesmanname,salesmanid,salesmanphoneno,normalmanid'
            )
		);
		$cond=array();

        $_GET = urldecode_array($_GET);

		if(isset($_GET['name'])){
			$cond[]='resume_basic.name like "%'.ltrim($_GET['name']).'%"';
		}

        if(isset($_GET['telephone'])){
            $cond[]='resume_basic.telephone like "%'.ltrim($_GET['telephone']).'%"';
        }

		if(isset($_GET['created'])){
            $cond[] = 'hp_resume.created >' . strtotime($_GET['created']);
            $cond[] = 'hp_resume.created <' . time();
        }
        if(!empty($_GET['start_time'])){
            $start_time = strtotime($_GET['start_time']);
            $cond[] = 'created >' . $start_time;
        }

        if(!empty($_GET['end_time'])){
            $end_time = strtotime($_GET['end_time']);
            $cond[] = 'created <' . $end_time;
        }
        if(isset($_GET['updated'])){
            $cond['updated']=array(
                'gt'=>strtotime($_GET['updated']),
                'lt'=>time()
            );
        }
        if(isset($_GET['verify'])){
            $cond['verify']=$_GET['verify'];
        }
//        var_dump($_GET);
//        die();
        $nums=$db->where($cond)->count();
        $page = new page($nums,13);
		$resumes=$db->where($cond)->findall($page->limit());
//		echo '<pre/>';
//        var_dump($resumes);die();
		$this->assign('resumes',$resumes);
		$this->assign('page',$page->show());
		$this->display();
	}

	/**
	 * 审核简历
	 */
	public function verifyResume()
	{
		C('DEBUG',1);
		$data=array();
		if($_POST['type']=='verify-unpass'){//不通过
			$data['verify']=0;
		}else{
			$data['verify']=1;
		}
		$this->resume->in(array('resume_id'=>$_POST['resume_id']))->update($data);
		echo 1;
		exit();
	}

	/**
	 * 删除简历
	 */
	public function delResume()
	{
		$db=M('model');
		$resume_table=$db->field('name')->where('mcid=2')->findall();//查找简历模型中的表
		$db->table('resume')->in(array('resume_id'=>$_POST['resume_id']))->del();
		foreach ($resume_table as $value) {
			$db->table($value['name'])->in(array('resume_id'=>$_POST['resume_id']))->del();
		}
		echo 1;
		exit;
	}
    /**
     * @Title: export_users
     * @Description: todo(导出简历列表)
     * @author liuzhipeng
     * @return  void  返回类型
     */
    public function export_resumes(){

        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename=简历列表.xls');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=gb2312\" /></head><body><table border='1'><tr><th>";
        echo iconv("UTF-8", "GBK","创建者")."</th><th>";
        echo iconv("UTF-8", "GBK","手机号码")."</th><th>";
        echo iconv("UTF-8", "GBK","创建时间")."</th><th>";
        echo iconv("UTF-8", "GBK","更新时间")."</th><th>";
        echo iconv("UTF-8", "GBK","来源")."</th><th>";
        echo iconv("UTF-8", "GBK","浏览次数")."</th><th>";
        echo iconv("UTF-8", "GBK","是否默认简历")."</th><th>";
        echo iconv("UTF-8", "GBK","审核")."</th></tr>";

        $db=V('resume');
        $db->view=array(
            'resume_basic'=>array(
                'type'=>'inner',
                'on'=>'resume.uid=resume_basic.uid',
//				'field'=>'name,telephone'
            ),
            'user'=>array(
                'type'=>'inner',
                'on'=>'resume.uid=user.uid',
                'field'=>'uid,type,branchname,salesmanname,salesmanid,salesmanphoneno,normalmanid'
            )
        );
        $cond=array();
        if(!empty($_POST['created'])){
            $cond[] = 'hp_resume.created >' . strtotime($_POST['created']);
            $cond[] = 'hp_resume.created <' . time();
        }
        if(isset($_POST['updated'])){
                $cond['updated']=array(
                    'gt'=>strtotime($_POST['updated']),
                    'lt'=>time()
                );
        }
        $resumes=$db->where($cond)->findall();
        foreach ($resumes as $k=>$v){
               echo "<tr><td>".iconv("UTF-8", "GBK",$v['name'])."</td><td>";
               echo $v['telephone']."</td><td>";
               echo date('Y-m-d',$v['created'])."</td><td>";
               echo date('Y-m-d',$v['updated'])."</td><td>";
               if(!empty($v['type'])){
                 if(!empty($v['branchname'])){
                   echo iconv('UTF-8', 'GBK',$v['branchname']);
                 }
                 if(!empty($v['salesmanname'])){
                   echo iconv('UTF-8', 'GBK',$v['salesmanname']).'[ID:'.$v['salesmanid'].']';
                 }
                  if(!empty($v['salesmanphoneno'])){
                   echo $v['salesmanphoneno'];
                 }
                  if(!empty($v['normalmanid'])){
                    $normalman = M('user')->where(array('uid'=>$v['normalmanid']))->find();
                    echo iconv('UTF-8', 'GBK',$normalman['username']).'[ID:'.$v['normalmanid'].']';
                 }
               }else{
                   echo iconv('UTF-8', 'GBK','无');
               }
               echo "</td><td>";
               echo $v['views']."</td><td>";
               if($v['default']==1){
                   echo "<font color='green'>".iconv('UTF-8', 'GBK','是')."</font></td><td>";
               }else if($v['default']==0){
                   echo "<font color='red'>".iconv('UTF-8', 'GBK','否')."</font></td><td>";
               }
              if($v['verify']==1){
                  echo "<font color='green'>".iconv('UTF-8', 'GBK','通过')."</font></td><td>";
              }else if($v['verify']==2){
                  echo "<font color='red'>".iconv('UTF-8', 'GBK','审核中')."</font></td><td>";
              }else{
                  echo "<font color='red'>".iconv('UTF-8', 'GBK','未通过')."</font></td>";
              }
               echo "</tr>";
        }
        echo "</table></body></html>";

    }

}