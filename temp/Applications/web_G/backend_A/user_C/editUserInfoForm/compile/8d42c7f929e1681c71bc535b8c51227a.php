<?php if(!defined("PATH_LC"))exit;?>
 <!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title></title>
	<link type="text/css" rel="stylesheet" href="http://localhost//hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="http://localhost/hpjobweb/web/backend/templates/css/public.css"/>
    <link type="text/css" rel="stylesheet" href="http://localhost//hpjobweb/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
    <script type="text/javascript" src="http://localhost//hpjobweb/public/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="http://localhost//hpjobweb/public/js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="http://localhost//hpjobweb/public/js/jqueryValidate/jquery.validate.min.js"></script>
	<script type="text/javascript" src="http://localhost//hpjobweb/public/js/jqueryValidate/jquery.metadata.js"></script>
</head>
<body>
	<div class="user-info">

	<form action="http://localhost/hpjobweb/index.php/backend/user/editUserInfo/id/<?php echo $userinfo['uid'];?>" validate="true" method="post" class="sub">
	<table class="table well">
		<tr>
			<th>用户名</th>
			<td><input type="text" name="username" value="<?php echo $userinfo['username'];?>"></td>
			<td><input type="hidden" name="uid" value="<?php echo $userinfo['uid'];?>"></td>
		</tr>
		<tr>
			<th>积分</th>
			<td><input type="text" name="point" value="<?php echo $userinfo['point'];?>" class="input-mini"> </td>
		</tr>
        <tr>
            <th>所属部门</th>
            <th>
                <select name="desc" value="<?php echo $userinfo['desc'];?>">
                <option value="">请选择</option>
                <option value="1" <?php if($userinfo['desc']==1){?>selected<?php }?>>业务部</option>
                <option value="2" <?php if($userinfo['desc']==2){?>selected<?php }?>>业务代理部</option>
                <option value="3" <?php if($userinfo['desc']==3){?>selected<?php }?>>人事代理部</option>
                <option value="4" <?php if($userinfo['desc']==4){?>selected<?php }?>>财务部</option>
                <option value="5" <?php if($userinfo['desc']==5){?>selected<?php }?>>管理部</option>
                <option value="6" <?php if($userinfo['desc']==6){?>selected<?php }?>>后勤部</option>
                <option value="7" <?php if($userinfo['desc']==7){?>selected<?php }?>>市场部</option>
                <option value="8" <?php if($userinfo['desc']==8){?>selected<?php }?>>企划部</option>
                <option value="9" <?php if($userinfo['desc']==9){?>selected<?php }?>>服务部</option>
                <option value="10" <?php if($userinfo['desc']==10){?>selected<?php }?>>信息部</option>
                <option value="11" <?php if($userinfo['desc']==11){?>selected<?php }?>>南京分公司</option>
                <option value="12" <?php if($userinfo['desc']==12){?>selected<?php }?>>昆山分公司</option>
                <option value="13" <?php if($userinfo['desc']==13){?>selected<?php }?>>外包部</option>
                <option value="14" <?php if($userinfo['desc']==14){?>selected<?php }?>>推广部</option>
                </select>
            </th>
        </tr>

		<?php if($role['rid'] == 7){?>
		<?php if($userinfo['branchname']){?>
			<tr>
	            <th>所属门店</th>
	            <th>
	                <select name="branchname" value="<?php echo $userinfo['branchname'];?>">
		                <?php if(is_array($branch_list)):?><?php  foreach($branch_list as $role){ ?>
		                <option value="<?php echo $role['name'];?>" <?php if($userinfo['branchname'] == $role['name']){?>selected<?php }?>><?php echo $role['name'];?></option>
		                <?php }?><?php endif;?>
	                </select>
	            </th>
	        </tr>	
		

		<?php  }else{ ?>	
			<tr>
	            <th>所属门店</th>
	            <th>
	                <select name="branchname">
	                	<option value="">请选择</option>
		                <?php if(is_array($branch_list)):?><?php  foreach($branch_list as $role){ ?>
		                <option value="<?php echo $role['name'];?>" <?php if($userinfo['branchname'] == $role['name']){?>selected<?php }?>><?php echo $role['name'];?></option>
		                <?php }?><?php endif;?>
	                </select>
	            </th>
	        </tr>	

		<?php }?>

		<?php }?>


        
		<tr>
			<th>修改密码</th>
			<td>
				<input type="password" name="password" validate="{minlength:6}" id="pwd" value="">
				<p class="help-inline">若密码为空则不修改密码</p>
			</td>
		</tr>
		<tr>
			<th>重复密码</th>
			<td><input type="password" name="re_password" validate="{equalTo:'#pwd'}" value="" id="re-pwd"></td>
		</tr>
		<tr>
			<th>在职公司</th>
			<td><input type="text" name="company_name" value="<?php echo $userinfo['company_name'];?>"></td>
		</tr>

		<!--<tr>-->
			<!--<th>禁止</th>-->
			<!--<td class="ban-user">-->
				<!--<label class="radio pull-left" action="ban" style="margin-right:25px;"><input type="radio" name="banned" value="1" checked>禁止</label>-->
				<!--<label class="radio" action="unban"><input type="radio" name="banned" value="0" <?php if(!$userinfo['banned']){?>checked<?php }?>>不禁止</label>-->
			<!--</td>-->
		<!--</tr>-->
		<!--<tr class="ban-reason<?php if(!$userinfo['banned']){?> hide<?php }?>">-->
			<!--<th>禁止原因</th>-->
			<!--<td><textarea name="ban_reason" class="tips"><?php echo $userinfo['ban_reason'];?></textarea></td>-->
		<!--</tr>-->
		<script type="text/javascript">
		$('.ban-user label').click(function(){
			if($(this).attr('action')=='ban'){
				$('.ban-reason').show();
			}else{
				$('.ban-reason').hide();
			}
		});

		</script>
	</table>
	</form>
	</div>
</body>
</html>