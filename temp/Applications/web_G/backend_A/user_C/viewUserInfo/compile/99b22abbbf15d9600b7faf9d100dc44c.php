<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title></title>
	<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/public.css"/>
    <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
    <script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-ui-1.8.21.custom.min.js"></script>
</head>
<body>
	<div class="user-info">
	<?php
		$data=new data('resume_basic');
		$userinfo=$data->convert($userinfo);
	?>
		<div id="tabs">

			<div id="tabs-1">
				<table class="table">
					<tr>
						<th>用户ID</th>
						<td><?php echo $userinfo['uid'];?></td>
					</tr>
					<tr>
						<th>手机号码</th>
						<td><?php echo $userinfo['username'];?></td>
					</tr>
					<tr>
						<th>创建时间</th>
						<td><?php echo date('Y-m-d H:i:s',$userinfo['created']);?></td>
					</tr>
					<tr>
						<th>用户积分</th>
						<td> <span class="success"><?php echo $userinfo['point'];?></span></td>
					</tr>
                    <tr>
                        <th>用户佣金</th>
                        <td> <span class="success"><?php echo $userinfo['commission'];?></span></td>
                    </tr>
					<tr>
						<th>公司名称</th>
						<td> <span class="success"><?php echo $userinfo['company_name'];?></span></td>
					</tr>
					<tr>
						<th>最后登录</th>
						<td><?php echo date('Y-m-d H:i:s',$userinfo['last_login']);?></td>
					</tr>
					<tr>
						<th>登录IP</th>
						<td><?php echo $userinfo['last_ip'];?></td>
					</tr>
					<?php if($userinfo['banned']){?>
						<tr>
							<th>禁止</th>
							<td><span class="warning">账户已禁止：</span> <span class="tips"><?php echo $userinfo['ban_reason'];?></span></td>
						</tr>
					<?php }?>
				</table>
			</div>
			<div id="tabs-2">
				<table class="table">
					<tr>
						<th>姓名</th>
						<td><?php echo $userinfo['name'];?></td>
					</tr>

					<tr>
						<th>出生年份</th>
						<td><?php echo $userinfo['birthday'];?></td>
					</tr>
                    <tr>
                        <th>身份证号</th>
                        <td><?php echo $userinfo['id_number'];?></td>
                    </tr>
					<tr>
						<th>身份证地址</th>
						<td><?php echo $userinfo['card_address'];?></td>
					</tr>

                    <tr>
                        <th>照片(旧版)</th>
                        <td><?php if($userinfo['photo']==null){?>
                        <span>暂无</span>
                            <?php  }else{ ?>
                            <img src="<?php echo $userinfo['photo'];?>" width="350px" height="200px">
                            <?php }?>
                        </td>
                    </tr>
					<tr>
						<th>身份证脸面</th>
						<td><?php if($userinfo['face_base']==null){?>
							<span>暂无</span>
							<?php  }else{ ?>
							<a href="<?php echo $userinfo['face_base'];?>"><img src="<?php echo $userinfo['face_base'];?>" width="350px" height="200px"></a>
							<?php }?>
						</td>
					</tr>
					<tr>
						<th>身份证国徽面</th>
						<td><?php if($userinfo['back_base']==null){?>
							<span>暂无</span>
							<?php  }else{ ?>
							<a href="<?php echo $userinfo['back_base'];?>"><img src="<?php echo $userinfo['back_base'];?>" width="350px" height="200px"></a>
							<?php }?>
						</td>
					</tr>
                    <tr>
                        <th>身份证是否认证</th>
                        <td>
                            <?php if($userinfo['verify']==0){?>
                            <span class="warning">未认证</span>
                            <?php  }elseif($userinfo['verify']==1){ ?>
                            <span class="warning">待审核</span>
                            <?php  }elseif($userinfo['verify']==3){ ?>
                            <span class="success">已认证</span>
                            <?php }?>
                        </td>
                    </tr>

				</table>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		$('#tabs').tabs();
	</script>
</body>
</html>