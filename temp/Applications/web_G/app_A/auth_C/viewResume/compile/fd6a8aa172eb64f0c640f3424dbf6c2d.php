<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>薪资</title>
    <link href="http://www.hap-job.com/templates/default/app/css/css.css" rel="stylesheet" type="text/css">
    <script src="js/open.js" type="text/javascript"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
</head>
<body class="body">
    <div class="page">
        <article class="jl_nr">
            <div class="jl_title">基本信息</div>
            <p class="tx_jl">
                <span>头&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;像：</span>
                <span><img src="<?php echo $resume['resume']['avatar'];?>" width="70" height="70"></span>
            </p>
            <div class="clear"></div>
            <p>姓&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;名：<span><?php echo $resume['basic']['name'];?></span></p>
            <p>性&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;别：<span><?php if($resume['basic']['gender']==0){?>男<?php  }else{ ?>女<?php }?>&nbsp;</span> </p>
            <p>出生日期：<span><?php echo date('Y年m月',$resume['basic']['birthday']);?>&nbsp;</span></p>
            <p>工作年限：<span><?php echo $resume['basic']['work_exp'];?></span></p>
            <p>联系电话：<span><?php echo $resume['basic']['telephone'];?>&nbsp;</span> </p>
            <p>最高学历：<span><?php if($resume['basic']['degree']!=''){?><?php echo $resume['basic']['degree'];?><?php }?></span></p>
            <p>所学专业：<span><?php echo $resume['basic']['major'];?></span></p>
            <div class="jl_title">求职意向</div>
            <p>期望工作区域：<span><?php echo $resume['basic']['hope_city'];?></span></p>
            <p>期望薪资：<span><?php echo $resume['basic']['hope_salary'];?></span></p>
            <p>期望职位：<span><?php echo $resume['basic']['hope_career'];?></span></p>
            <div class="jl_title">工作经验</div>
            <?php if(is_array($resume['exp']['exp'])):?><?php  foreach($resume['exp']['exp'] as $exp){ ?>
                <p>公司名称：<span><?php echo $exp['company_name'];?></span></p>
                <p>任职时间：<span><?php echo date('Y年m月',$exp['job_start']);?>至<?php echo date('Y年m月',$exp['job_end']);?></span></p>
                <p>职位类别：<span><?php echo $exp['department'];?></span></p>
                <p>职位名称：<span><?php echo $exp['job_name'];?></span></p>
                <p>工作描述：<span class="gzms"><?php echo nl2br($exp['job_desc']);?></span></p>
                <p style="display: block; height: 5px; background:#EAEAEA;"></p>
            <?php }?><?php endif;?>
        </article>
    </div>
</body>
</html>