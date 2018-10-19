<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/home.css"/>
    <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/public.css"/>
    <script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
  </head>
  <body style="padding:20px;">
    <?php
    $db=M();
    $version=$db->query('SELECT VERSION() AS MYSQL_VERSION');
    ?>
    <div id="panel-left">
      <div >
        <div class="box">
            <div class="title">
                <h4>
                    <span class="icon-user"></span>
                    <span>个人信息</span>
                </h4>
                <a href="#" class="minimize hide"></a>
            </div>
            <div class="content" style="display: block;">
                    <p>你好，<?php echo $_SESSION['username'];?></p>
                    <p>所属角色：<?php echo implode('、',$_SESSION['role']['rtitle']);?></p>
                    <p>上次登录时间：<?php echo date('Y-m-d H:i:s',$_SESSION['last_login']);?></p>
                    <p>上次登录IP：<?php echo $_SESSION['last_ip'];?></p>
                </table>
            </div>
        </div>
      </div>
        <div >
          <div class="box">
              <div class="title">
                  <h4>
                      <span class="icon-flag"></span>
                      <span>系统信息</span>
                  </h4>
                  <a href="#" class="minimize hide"></a>
              </div>
              <div class="content" style="display: block;">
                <p>快乐求职招聘系统 </p>
                <p class="hide" id="show-new-version"></p>
                <p>操作系统：<?php echo PHP_OS;?></p>
                <p>PHP：<?php echo PHP_VERSION;?></p>
                <p>服务器环境：<?php echo $_SERVER['SERVER_SOFTWARE'];?></p>
                <p>MySQL版本：<?php echo $version[0]['MYSQL_VERSION'];?></p>
              </div>
          </div>
        </div>
        </div>
        <div id="panel-right">
        <div >
          <div class="box">
              <div class="title">
                  <h4>
                      <span class="icon-tags"></span>
                      <span>快捷操作</span>
                  </h4>
                  <a href="#" class="minimize hide"></a>
              </div>
              <div class="content fast-opt" style="display: block;">
                 <a href="http://www.hap-job.com/index.php/backend/content/addArc">发布文章</a>
                 <a href="http://www.hap-job.com/index.php/backend/resume/resumeList">管理简历</a>
                 <a href="http://www.hap-job.com/index.php/backend/company/recruitList">管理招聘</a>
                 <a href="http://www.hap-job.com/index.php/backend/webConfig/emailConfig">邮箱配置</a>
                 <a href="http://www.hap-job.com/index.php/backend/dataModel/modelList">模型管理</a>
                 <a href="http://www.hap-job.com/index.php/backend/dataModel/category">地区管理</a>
                 <a href="http://www.hap-job.com/index.php/backend/dataModel/linkageCateList">联动数据</a>
                 <div style="clear:both"></div>
              </div>
          </div>
        </div>
        <div >
          <div class="box">
              <div class="title">
                  <h4>
                      <span class="icon-fire"></span>
                      <span>网站动态</span>
                  </h4>
                  <a href="#" class="minimize hide"></a>
              </div>
              <div class="content" style="display: block;">
                <p>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  今日注册：<span class="success"><?php echo $db->table('user')->where(array('created'=>array('gt'=>strtotime(date('Y-m-d')))))->count();?> </span>人&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  发布职位：<span class="success"><?php echo $db->table('recruit')->where(array('created'=>array('gt'=>strtotime(date('Y-m-d')))))->count();?> </span>个&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  新增简历：<span class="success"><?php echo $db->table('resume')->where(array('created'=>array('gt'=>strtotime(date('Y-m-d')))))->count();?> </span>份
                </p>
                <p>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  总会员&nbsp;&nbsp;：<span class="success"><?php echo $db->table('user')->count();?> </span>人&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  发布文章：<span class="success"><?php echo $db->table('article')->count();?> </span>篇&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  有效推广：<span class="success"><?php echo $db->table('spread')->where(array('endtime'=>array('gt'=>time())))->count();?> </span>个
                </p>
              </div>
          </div>
        </div>
</div>
        <style type="text/css">
        #panel-left{
          width: 45%;
          float: left;
          margin-right: 20px;
        }
        #panel-right{
          width: 45%;
          float: left;
        }
        .fast-opt a{
          padding:5px 20px;
        }
        </style>
<script type="text/javascript">
  $('.title').hover(function(){
    $(this).children('.minimize').removeClass('hide');
  },function(){
    $(this).children('.minimize').addClass('hide');
  });
  $('.minimize').toggle(function(){
    $(this).parent().next().slideUp();
  },function(){
    $(this).parent().next().slideDown();
  });
</script>
  </body>
</html>
