<?php if(!defined("PATH_LC"))exit;?>
<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>后台管理</title>
<link type="text/css" rel="stylesheet" href="http://192.168.3.131/hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
<link type="text/css" rel="stylesheet" href="http://192.168.3.131/hpjobweb/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
<link type="text/css" rel="stylesheet" href="http://192.168.3.131/hpjobweb/web/backend/templates/css/public.css"/>
<script type="text/javascript" src="http://192.168.3.131/hpjobweb/public/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://192.168.3.131/hpjobweb/web/backend/templates/js/public.js"></script>
<script type="text/javascript" src="http://192.168.3.131/hpjobweb/public/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="http://192.168.3.131/hpjobweb/public/js/jqueryValidate/jquery.validate.min.js"></script>
<script type="text/javascript" src="http://192.168.3.131/hpjobweb/public/js/jqueryValidate/jquery.metadata.js"></script>
<script type="text/javascript" src="http://192.168.3.131/hpjobweb/public/js/My97DatePicker/WdatePicker.js"></script>
</head>
<body>
<style type="text/css">
.table th{
	text-align: right;
	padding-right: 20px;
	font-weight: normal;
}
.table .table-title{
	background-color: lavender;
}
.table .table-title th{
	text-align: center;
	font-weight: bold;
	font-size: 13px;
}
</style>
<form action="http://192.168.3.131/hpjobweb/index.php/backend/webConfig/userConfig" method="post">
<table class="table">
	<tr class="table-title">
		<th colspan="2">用户设置</th>
	</tr>
	<tr>
		<th >用户名登录</th>
		<td>
			<div class="switch <?php if(!$_CONFIG['auth_username_login']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="AUTH_USERNAME_LOGIN" value="<?php if($_CONFIG['auth_username_login']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" />
		</td>
	</tr>
	<tr>
		<th >Email登录</th>
		<td>
			<div class="switch <?php if(!$_CONFIG['auth_email_login']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="AUTH_EMAIL_LOGIN" value="<?php if($_CONFIG['auth_email_login']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" />
		</td>
	</tr>
	<tr>
		<th >统计登录尝试次数</th>
		<td>
			<div class="switch <?php if(!$_CONFIG['auth_count_login_attempts']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="AUTH_COUNT_LOGIN_ATTEMPTS" value="<?php if($_CONFIG['auth_count_login_attempts']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" />
		</td>
	</tr>
	<tr>
		<th >最大登录尝试次数</th>
		<td>
			<input type="text" name="AUTH_MAX_LOGIN_ATTEMPTS" value="<?php echo $_CONFIG['auth_max_login_attempts'];?>" class="input-mini">
		</td>
	</tr>
	<tr>
		<th >验证码登录</th>
		<td>
			<div class="switch <?php if(!$_CONFIG['auth_captcha_login']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="AUTH_CAPTCHA_LOGIN" value="<?php if($_CONFIG['auth_captcha_login']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" />
		</td>
	</tr>
	<tr>
		<th >验证码注册</th>
		<td>
			<div class="switch <?php if(!$_CONFIG['auth_reg_code']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="AUTH_REG_CODE" value="<?php if($_CONFIG['auth_reg_code']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" />
		</td>
	</tr>
	<tr>
		<th >允许注册</th>
		<td>
			<div class="switch <?php if(!$_CONFIG['allow_register']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="ALLOW_REGISTER" value="<?php if($_CONFIG['allow_register']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" />
		</td>
	</tr>
	<tr>
		<th >注册需Email激活</th>
		<td>
			<div class="switch <?php if(!$_CONFIG['auth_email_activate']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="AUTH_EMAIL_ACTIVATE" value="<?php if($_CONFIG['auth_email_activate']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" />
		</td>
	</tr>
	<tr>
		<th >Email激活失效时间</th>
		<td>
			<input type="text" name="EMAIL_ACTIVATE_EXPIRE" value="<?php echo $_CONFIG['email_activate_expire'];?>" class="input-mini">
		</td>
	</tr>
	<tr>
		<th >发送账户信息</th>
		<td>
			<div class="switch <?php if(!$_CONFIG['email_account_info']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="EMAIL_ACCOUNT_INFO" value="<?php if($_CONFIG['email_account_info']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" />
		</td>
	</tr>
	<tr>
		<th >自动登录COOKIE名</th>
		<td>
			<input type="text" name="AUTH_AUTOLOGIN_COOKIE_NAME" value="<?php echo $_CONFIG['auth_autologin_cookie_name'];?>" class="input-medium">
		</td>
	</tr>
	<tr>
		<th >自动登录失效时间</th>
		<td>
			<input type="text" name="AUTH_AUTOLOGIN_COOKIE_LIFE" value="<?php echo $_CONFIG['auth_autologin_cookie_life'];?>" class="input-mini">
		</td>
	</tr>
	<tr>
		<th>简历需审核</th>
		<td><div class="switch <?php if(!$_CONFIG['verify_resume']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="VERIFY_RESUME" value="<?php if($_CONFIG['verify_resume']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" /></td>
	</tr>
	<tr>
		<th>招聘需审核</th>
		<td><div class="switch <?php if(!$_CONFIG['verify_recruit']){?>switch-off<?php }?>"></div>
			<input type="hidden" name="VERIFY_RECRUIT" value="<?php if($_CONFIG['verify_recruit']){?>TRUE<?php  }else{ ?>FALSE<?php }?>" /></td>
	</tr>
	<tr>
		<th></th>
		<td><button class="btn btn-primary"><i class="icon-edit icon-white"></i>保存</button> </td>
	</tr>
</table>
</form>
</body>
</html>