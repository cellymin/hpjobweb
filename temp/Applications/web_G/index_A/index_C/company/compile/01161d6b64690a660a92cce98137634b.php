<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $company['name'];?>-<?php echo $_CONFIG['web_name'];?></title>
<meta name="keywords" content="<?php echo $company['name'];?>" />
<meta name="description" content="<?php echo mb_substr($company['desc'],0,80,'utf-8');?>" />
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/base.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/templates/default/css/index.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/templates/company_tpl/skyblue/skyblue.css"/>
</head>
<body>
    <?php if(!defined("PATH_LC"))exit;?>
<div id="top">
<div id="top-ads">
    <?php $db = M('ads');$adss =$db->field('href,text,path,color,width,height,uid')
        ->where(" state=1 AND cate =1 AND endtime >".time())
        ->limit(3)->findall();?><?php if(is_array($adss)):?><?php foreach ($adss as $ads):?>
    <a href="<?php echo $ads['href'];?>"><img src="http://www.hap-job.com/<?php echo $ads['path'];?>" width="300" height="63" /></a>
    <?php endforeach;?><?php endif;?>
</div>
</div>
<!--nav-->
<div id="nav" class="round-case">
<div class="fn-left"></div>
<div class="nav-list">
    <ul>
        <?php $db=M('nav');$navs=$db->cache(86400)->field('href,title,target,sort')->where('state=1')->order('sort ASC')->limit(8)->findall();?><?php if(is_array($navs)):?><?php foreach ($navs as $nav):?>
          <li><a href="<?php if(substr($nav['href'],0,4)!='http'){?>http://www.hap-job.com/index.php<?php echo $nav['href'];?><?php  }else{ ?><?php echo $nav['href'];?><?php }?>" target="<?php echo $nav['target'];?>"><?php echo $nav['title'];?></a></li>
        <?php endforeach;endif;?>
    </ul>
    <div id="nav-help"><a href="###">企业服务</a><a href="###">招聘助手</a></div>
</div>
<div class="fn-right"></div>
</div>
<!--/nav--> 

    <div id="content">
        <div id="com-name">
            <h2><?php echo $company['name'];?><?php if($company['license_verify']==1){?><span class="auth-status">认证企业<img src="http://www.hap-job.com/templates/default/images/succ2.png" alt="" /></span><?php }?></h2>
        </div>
        <div id="shadow-top"></div>
        <div id="company">
            <div id="com-basic">
                <table>
                    <tr>
                        <th>公司性质：</th>
                        <td><?php echo $company['company_property'];?></td>
                        <td rowspan="3" class="logo"><?php if($company['logo']==''){?><img src="http://www.hap-job.com/templates/default/images/no_photo.gif" alt="" /><?php  }else{ ?><img src="http://www.hap-job.com/<?php echo $company['logo'];?>" alt="" /><?php }?></td>
                    </tr>
                    <tr>
                        <th>公司规模：</th>
                        <td><?php echo $company['company_scope'];?></td>
                    </tr>
                    <tr>
                        <th>公司行业：</th>
                        <td><?php echo $company['company_industry'];?></td>
                    </tr>
                </table>
            </div>
            <div class="cate-title">
                公司介绍
            </div>
            <div id="com-desc">
                <?php echo $company['desc'];?>
            </div>
            <div class="cate-title">
                联系方式
            </div>
            <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']){?>
            <div id="com-address">
                <table>
                    <tr>
                        <th>公司地址：</th>
                        <td><?php echo $company['address'];?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><?php echo $company['street'];?></td>
                    </tr>
                    <tr>
                        <th>联  系  人：</th>
                        <td><?php echo $company['contact_person'];?></td>
                    </tr>
                    <tr>
                        <th>Email：</th>
                        <td><?php echo $company['link_email'];?></td>
                    </tr>
                     <tr>
                        <th>联系电话：</th>
                        <td><?php echo $company['contact_tel'];?></td>
                    </tr>
                </table>
            </div>
            <?php  }else{ ?>
            <div class="not-login">请 <a href="http://www.hap-job.com/index.php/login">登录</a>后查看企业联系方式</div>
            <?php } ?>
            <div class="cate-title">
                招聘职位
            </div>
            <div class="job-list">
                <ul>
                <?php if(is_array($recruits)):?><?php  foreach($recruits as $recruit){ ?>
                    <li><?php echo date('Y-m-d',$recruit['start_time']);?><a href="http://www.hap-job.com/index.php/index/search/jobs/id/<?php echo $recruit['recruit_id'];?>" target="_blank"><?php echo $recruit['recruit_name'];?></a></li>
                <?php }?><?php endif;?>
                </ul>
            </div>
        </div>
        <div id="shadow-b"></div>
    </div>
<?php if(!defined("PATH_LC"))exit;?>
</body>
</html>