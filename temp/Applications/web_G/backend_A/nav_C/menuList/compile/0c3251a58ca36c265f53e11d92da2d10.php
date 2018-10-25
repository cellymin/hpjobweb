<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title></title>
	<link type="text/css" rel="stylesheet" href="http://192.168.3.131/hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
	<link type="text/css" rel="stylesheet" href="http://192.168.3.131/hpjobweb/web/backend/templates/css/public.css"/>
	<script type="text/javascript" src="http://192.168.3.131/hpjobweb/public/js/jquery-1.7.2.min.js"></script>
	<link type="text/css" rel="stylesheet" href="http://192.168.3.131/hpjobweb/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
	<script type="text/javascript" src="http://192.168.3.131/hpjobweb/public/js/jquery-ui-1.8.21.custom.min.js"></script>
</head>
<body style="padding:10px;">
	<form action="http://192.168.3.131/hpjobweb/index.php/backend/nav/menuList" method="post">
	<table class="table">
		<tr>
			<th width="2%"><input type="checkbox" class="select-all input-checkbox"> </th>
			<th width="15%">菜单名称</th>
			<th>操作路径</th>
			<th>query</th>
			<th>排序</th>
			<th>状态</th>
			<th>操作</th>
		</tr>
		<?php if(is_array($menus)):?><?php  foreach($menus as $value){ ?>
		<tr>
			<td><input type="checkbox" class="input-checkbox" m-id="<?php echo $value['id'];?>"></td>
			<td>├─<?php echo $value['menu_name'];?></td>
			<td>/<?php echo trim('/'.$value['app'].'/'.$value['control'].'/'.$value['method'],'/');?>/</td>
			<td><?php echo $value['query_string'];?></td>
			<td><input type="text" name="sort[<?php echo $value['id'];?>]" value="<?php echo $value['sort'];?>" class="input-min"></td>
			<td><?php if($value['state']){?> <span class="success">显示中</span> <?php  }else{ ?> <span class="warning">已关闭</span> <?php }?></td>
			<td>
				<a href="http://192.168.3.131/hpjobweb/index.php/backend/nav/editMenu/id/<?php echo $value['id'];?>" class="edit-item"><i class="icon-edit"></i>修改</a>
			</td>
		</tr>
		<?php if(is_array($value['son_data'])):?><?php  foreach($value['son_data'] as $two){ ?>
		<tr>
			<td><input type="checkbox" class="input-checkbox" m-id="<?php echo $two['id'];?>" /></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;└─<?php echo $two['menu_name'];?></td>
			<td>/<?php echo trim('/'.$two['app'].'/'.$two['control'].'/'.$two['method'],'/');?>/</td>
			<td><?php echo $two['query_string'];?></td>
			<td><input type="text" name="sort[<?php echo $two['id'];?>]" value="<?php echo $two['sort'];?>" class="input-min"></td>
			<td><?php if($two['state']){?> <span class="success">显示中</span> <?php  }else{ ?> <span class="warning">已关闭</span> <?php }?></td>
			<td>
				<a href="http://192.168.3.131/hpjobweb/index.php/backend/nav/editMenu/id/<?php echo $two['id'];?>" class="edit-item"><i class="icon-edit"></i>修改</a>
			</td>
		</tr>
		<?php if(is_array($two['son_data'])):?><?php  foreach($two['son_data'] as $three){ ?>
			<tr>
				<td><input type="checkbox" class="input-checkbox" m-id="<?php echo $three['id'];?>"></td>
				<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─<?php echo $three['menu_name'];?></td>
				<td>/<?php echo trim('/'.$three['app'].'/'.$three['control'].'/'.$three['method'],'/');?>/</td>
				<td><?php echo $three['query_string'];?></td>
				<td><input type="text" name="sort[<?php echo $three['id'];?>]" value="<?php echo $three['sort'];?>" class="input-min"></td>
				<td><?php if($three['state']){?> <span class="success">显示中</span> <?php  }else{ ?> <span class="warning">已关闭</span> <?php }?></td>
				<td>
					<a href="http://192.168.3.131/hpjobweb/index.php/backend/nav/editMenu/id/<?php echo $three['id'];?>" class="edit-item"><i class="icon-edit"></i>修改</a>
				</td>
			</tr>
			<?php }?><?php endif;?>
		<?php }?><?php endif;?>
		<?php }?><?php endif;?>
		<tr class="well">
			<td><input type="checkbox" class="select-all input-checkbox"></td>
			<td colspan="3" class="opt">
				<a class="btn btn-mini btn-danger" action="del"><i class="icon-trash icon-white"></i> 删除</a>
    			<!-- <a class="btn btn-mini btn-info" action="ban"><i class="icon-ban-circle icon-white"></i> 关闭</a>
    			<a class="btn btn-mini btn-success" action="unban"><i class="icon-ok icon-white"></i> 显示</a> -->
			</td>
			<td><button type="submit" class="btn btn-mini btn-info"><i class="icon-random icon-white"></i> 排序</button></td>
			<td></td>
			<td></td>
		</tr>
	</table>
	</form>
	<div id="dialog" class="hide"></div>
	<script type="text/javascript">
		$('.select-all').click(function(){
			if($(this).attr('checked')){
				$(':checkbox["m-id"],.select-all').attr('checked',true);
			}else{
				$(':checkbox["m-id"],.select-all').attr('checked',false);
			}
		});
		$('.opt a').click(function(){
			var _checked=$('input:checked[m-id]'),
				_id=[];
			if(_checked.length==0){
				alert('请选择你需要操作的菜单!');
				return false;
			}
			_checked.each(function(k,obj){
				_id.push($(this).attr('m-id'));
			});
			if($(this).attr('action')=='del' && confirm("确认删除选中后台菜单？")){
				$.post('http://192.168.3.131/hpjobweb/index.php/backend/nav/delMenu',{"id":_id},function(data){
					if(data==1){
						_checked.parents('tr').fadeOut(function(){
							_checked.parents('tr').remove();
						});
					}
				},'html');
			}
			return false;
		});
		$('.edit-item').click(function(){
			$.get($(this).attr('href'),function(data){
				$('#dialog').html(data);
				$('#dialog').dialog({
					"width":"500",
					"modal":true,
					"title":"修改菜单",
					"buttons":{
						"修改":function(){
							$('#dialog form').submit();
						}
					}
				});
			});
			return false;
		});
	</script>
</body>
</html>