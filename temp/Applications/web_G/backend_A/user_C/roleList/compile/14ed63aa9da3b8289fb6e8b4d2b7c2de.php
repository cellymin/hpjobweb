<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/public.css"/>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
</head>
<body>
	<form action="http://www.hap-job.com/index.php/backend/user/roleList" method="post">
	<table class="table">
		<tr>
			<th width="3%"><input type="checkbox" class="select-all input-checkbox" id="" /> </th>
			<th>ID</th>
			<th>角色名</th>
			<th>排序</th>
			<th>状态</th>
			<th>系统用户组</th>
			<th>操作</th>
		</tr>
		<?php if(is_array($roles)):?><?php  foreach($roles as $role){ ?>
		<tr>
			<td><input type="checkbox" name="role" class="input-checkbox" value="<?php echo $role['rid'];?>" /> </td>
			<td><?php echo $role['rid'];?></td>
			<td><?php echo $role['title'];?></td>
		<td><input type="text" name="sort[<?php echo $role['rid'];?>]" value="<?php echo $role['sort'];?>" class="input-min" /> </td>
			<td class="state"><?php if($role['state']){?> <span class="success">已开启</span> <?php  }else{ ?> <span class="warning">已关闭</span><?php }?></td>
			<td>
				<?php if($role['is_sys']){?>
				<span class="sys">是</span>
				<?php  }else{ ?>
				<span class="tips">否</span>
				<?php }?>
			</td>
			<td>
				<a href="http://www.hap-job.com/index.php/backend/user/configPermission/rid/<?php echo $role['rid'];?>"><i class="icon-user"></i>配置权限</a>
			</td>
		</tr>
		<?php }?><?php endif;?>
		<tr class="well">
			<td><input type="checkbox" class="select-all input-checkbox" id="" /> </td>
			<td colspan="2" class="opt">
				<a class="btn btn-mini btn-danger" action="del"><i class="icon-trash icon-white"></i> 删除</a>
            			<a class="btn btn-mini btn-info" action="ban"><i class="icon-ban-circle icon-white"></i> 关闭</a>
            			<a class="btn btn-mini btn-success" action="unban"><i class="icon-ok icon-white"></i> 开启</a>
			</td>
			<td>
				<button class="btn btn-mini btn-primary" type="submit"><i class="icon-refresh icon-white"></i> 排序</button>
			</td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	</form>
	<script type="text/javascript">
		$('.select-all').click(function(){
			if($(this).attr('checked')){
				$('input[name="role"],.select-all').attr('checked',true);
			}else{
				$('input[name="role"],.select-all').attr('checked',false);
			}
		});
		$('.opt a').click(function(){
			var _checked=$('input[name="role"]:checked')
				_id=[],
				_action=$(this).attr('action'),
				_msg='删除',
				_ban_msg='',
				_url='http://www.hap-job.com/index.php/backend/user/delRole';
				if(_checked.length==0){
					alert('请选中你要操作的用户组!');
					return false;
				}
				switch(_action){
					case "ban":_msg="禁止";_ban_msg="\n用户组关闭后用户登录后将失去用户组的权限,\n请谨慎操作!";_url="http://www.hap-job.com/index.php/backend/user/banRole/state/0";break;
					case "unban":_msg="开启";_url="http://www.hap-job.com/index.php/backend/user/banRole/state/1";break;
				}
				_checked.each(function(){
					_id.push($(this).val());
				});
				if(confirm("确认"+_msg+"选中用户组？"+_ban_msg)){
					$.post(_url,{rid:_id},function(data){
						if(data==1){
							switch(_action){
								case "del":_checked.parents('tr').fadeOut('slow',function() {_checked.parents('tr').remove();});;break;
								case "ban":_checked.parent().siblings('.state').html('<span class="warning">已关闭</span>');break;
								case "unban":_checked.parent().siblings('.state').html('<span class="success">已开启</span>');break;
							}
						}
					},'html');
				}
			return false;
		});
	</script>
</body>
</html>