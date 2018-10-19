<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $job['recruit_name'];?>-<?php echo $_CONFIG['web_name'];?></title>
<meta name="keywords" content="<?php echo $job['seo_keywords'];?>" />
<meta name="description" content="<?php echo $job['seo_desc'];?>" />
<link type="text/css" rel="stylesheet" href="http://hap-job.com/public/css/base.css"/>
<link type="text/css" rel="stylesheet" href="http://hap-job.com/templates/default/css/jobs.css"/>
<link type="text/css" rel="stylesheet" href="http://hap-job.com/templates/default/css/common.css"/>
<link type="text/css" rel="stylesheet" href="http://hap-job.com/public/js/artDialog/skins/blue.css"/>
<script type="text/javascript" src="http://hap-job.com/public/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://hap-job.com/public/js/artDialog/jquery.artDialog.min.js"></script>
<script type="text/javascript">
    var web='http://hap-job.com/index.php',
        app='http://hap-job.com/index.php/index';
</script>
</head>

<body>
<?php if(!defined("PATH_LC"))exit;?>
<div id="top">
<div id="top-ads">
    <?php $db = M('ads');$adss =$db->field('href,text,path,color,width,height,uid')
        ->where(" state=1 AND cate =1 AND endtime >".time())
        ->limit(3)->findall();?><?php if(is_array($adss)):?><?php foreach ($adss as $ads):?>
    <a href="<?php echo $ads['href'];?>"><img src="http://hap-job.com/<?php echo $ads['path'];?>" width="300" height="63" /></a>
    <?php endforeach;?><?php endif;?>
</div>
</div>
<!--nav-->
<div id="nav" class="round-case">
<div class="fn-left"></div>
<div class="nav-list">
    <ul>
        <?php $db=M('nav');$navs=$db->cache(86400)->field('href,title,target,sort')->where('state=1')->order('sort ASC')->limit(8)->findall();?><?php if(is_array($navs)):?><?php foreach ($navs as $nav):?>
          <li><a href="<?php if(substr($nav['href'],0,4)!='http'){?>http://hap-job.com/index.php<?php echo $nav['href'];?><?php  }else{ ?><?php echo $nav['href'];?><?php }?>" target="<?php echo $nav['target'];?>"><?php echo $nav['title'];?></a></li>
        <?php endforeach;endif;?>
    </ul>
    <div id="nav-help"><a href="###">企业服务</a><a href="###">招聘助手</a></div>
</div>
<div class="fn-right"></div>
</div>
<!--/nav--> 

<div id="nav-ads">
    <?php $db = M('ads');$adss =$db->field('href,text,path,color,width,height,uid')
        ->where(" state=1 AND cate =2 AND endtime >".time())
        ->limit(3)->findall();?><?php if(is_array($adss)):?><?php foreach ($adss as $ads):?>
        <a href="<?php if(substr($ads['href'],0,4)=='http'){?><?php echo $ads['href'];?><?php  }else{ ?>http://hap-job.com/<?php echo $ads['href'];?><?php }?>"><img src="http://hap-job.com/<?php echo $ads['path'];?>" alt="" /></a>
    <?php endforeach;?><?php endif;?>
</div>
<div id="jobs">
    <div class="jobs-title">
        <h2><span class="job-name"><?php echo $job['recruit_name'];?></span> <span class="view-nums">浏览：<?php echo $job['views'];?>次</span></h2>
    </div>
    <div class="company-simple">
        <table>
            <tr class="c-header">
                <td colspan="6">
                    <a href="" class="company-name" c-id="<?php echo $job['uid'];?>"><?php echo $job['company_name'];?></a>
                    <a href="http://hap-job.com/index.php/index/index/company/id/<?php echo $job['uid'];?>" target="_blank" class="view-company">查看公司信息</a>
                </td>
            </tr>
            <tr>
                <th>公司规模：</th>
                <td><?php echo $job['company_scope'];?></td>
                <th>公司性质：</th>
                <td><?php echo $job['company_property'];?></td>
                <th></th>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="jobs-info">
        <ul>
            <li class="ui-active"><a href="#jobs-item">职位信息</a></li>
            <li><a href="#company-desc">公司简介</a></li>
        </ul>
        <div id="jobs-item">
            <table>
                <tr>
                    <th>职位行业：</th>
                    <td><?php echo $job['jobs_industry'];?></td>
                    <th>职位分类：</th>
                    <td colspan="3"><?php echo $job['class'];?></td>
                </tr>
                <tr>
                    <th>职位性质：</th>
                    <td><?php echo $job['jobs_property'];?></td>
                    <th>经验要求：</th>
                    <td><?php echo $job['work_exp'];?></td>
                    <th>更新日期：</th>
                    <td><?php echo date('Y-m-d',$job['refresh_date']);?></td>
                </tr>
                <tr>
                    <th>学历要求：</th>
                    <td><?php echo $job['degree'];?></td>
                    <th>工作地点：</th>
                    <td><?php echo $job['address'];?></td>
                    <th>截止日期：</th>
                    <td><?php echo date('Y-m-d',$job['expiration_time']);?></td>
                </tr>
                <tr>
                    <th>月薪范围：</th>
                    <td><?php echo $job['salary'];?></td>
                    <th>招聘人数：</th>
                    <td><?php if(!$job['recruit_num']){?>不限<?php  }else{ ?><?php echo $job['recruit_num'];?>人<?php }?></td>
                    <th></th>
                    <td></td>
                </tr>
            </table>
            <div class="apple" style="margin-left:0px"><a href="<?php echo $job['recruit_id'];?>" class="applyJob"><img src="http://hap-job.com/templates/default/images/list_36.gif" alt=""></a></div>
        </div>
        <div id="company-desc" class="fn-hide">
            <?php echo nl2br($job['company_desc']);?>
        </div>
    </div>
    <div id="job-desc">
        <div href="" class="list-title">职位描述</div>
        <div class="job-desci">
            <?php echo $job['job_desc'];?>
        </div>
        <div class="apple" style="margin-left:0px"><a href="<?php echo $job['recruit_id'];?>" class="applyJob"><img src="http://hap-job.com/templates/default/images/list_36.gif" alt=""></a></div>
    </div>
    <div id="company-contact">
        <div href="" class="list-title">联系方式</div>
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']){?>
        <table>
            <tr>
                <th>联系电话：</th>
                <td><?php echo $job['phone'];?></td>
            </tr>
            <tr>
                <th>电子邮件：</th>
                <td><?php echo $job['rece_mail'];?></td>
            </tr>
            <tr>
                <th>联系人：</th>
                <td><?php echo $job['contact'];?></td>
            </tr>
            <tr>
                <th>公司网站：</th>
                <td><a href="<?php echo $job['company_index'];?>" target="_blank"><?php echo $job['company_index'];?></a></td>
            </tr>
        </table>
        <?php  }else{ ?>
        <div class="waring">请<a href="http://hap-job.com/index.php/login.html">登录</a>后查看企业联系方式。如果你还没有账号，请先<a href="http://hap-job.com/index.php/register.html">注册</a>账号</div>
        <?php } ?>
    </div>
</div>
<script type="text/javascript" src="http://hap-job.com/templates/default/js/job.js"></script>
<?php if(!defined("PATH_LC"))exit;?>
</body>
</html>