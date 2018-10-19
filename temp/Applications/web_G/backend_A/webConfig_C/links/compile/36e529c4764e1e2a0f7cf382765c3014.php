<?php if(!defined("PATH_LC"))exit;?>
<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>后台管理</title>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/public.css"/>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/web/backend/templates/js/public.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.validate.min.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.metadata.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/My97DatePicker/WdatePicker.js"></script>
</head>
<body>
<style type="text/css">
    .input-mini{
        width: 25px;
        text-align:center;
    }
    .waring{
        color: #B94A48;
    }
    	#dialog{
		width: 400px;
	}
	#dialog table{
		width: 100%;
	}
	#dialog table th{
		width: 100px;
	}
	#dialog table label{
		float: left;
		margin-right: 10px;
	}
	#dialog table label input{
		float: left;
		margin-right: 5px;
	}
	#nav-button{
		height: 36px;
	}
	.nav{
		width: 200px;
		float: left;
	}
</style>
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1">链接列表</a></li>
			<li><a href="#tabs-2">添加链接</a></li>
			<li><a href="#tabs-3">连接分类</a></li>
			<li><a href="#tabs-4">添加分类</a></li>
		</ul>
		<div id="tabs-1">
			<table class="table">
				<tr>
					<th>网站名称</th>
					<th>链接地址</th>
					<th>logo</th>
					<th>状态</th>
					<th>联系Email</th>
					<th>广告分类</th>
					<th>操作</th>
				</tr>
				<?php if(is_array($links)):?><?php  foreach($links as $link){ ?>
				<tr>
					<td><?php echo $link['web_name'];?></td>
					<td><?php echo $link['href'];?></td>
					<td><?php echo $link['logo'];?></td>
					<td><?php if($link['state']==0){?>
						<span class="warning">已关闭</span>
						<?php  }elseif($link['state']==2){ ?>
						<span class="sys">审核中</span>
						<?php  }else{ ?>
						<span class="success">显示中</span>
						<?php }?></td>
					<td><?php echo $link['email'];?></td>
					<td><?php echo $link['title'];?></td>
					<td>
						<!-- <i class="icon-edit"></i><a href="">修改</a><i class="icon-edit"></i> -->
					<a href="<?php echo $link['lid'];?>" class="del-item" type="ads">删除</a>
					</td>
				</tr>
				<?php }?><?php endif;?>
			</table>
		</div>
		<div id="tabs-2">
			<form validate="true" action="http://www.hap-job.com/index.php/backend/webConfig/links" method="post" enctype="multipart/form-data">
				<table>
					<tr>
						<th>网站名称：</th>
						<td><input type="text" validate="{required:true}" name="web_name" id="" /></td>
					</tr>
					<tr>
						<th>链接地址：</th>
						<td><input type="text" validate="{required:true}" name="href" id="" /> </td>
					</tr>
					<tr>
						<th>LOGO：</th>
						<td><input type="file" name="logo" id="" /></td>
					</tr>
					<tr>
						<th>连接分类：</th>
						<td><select name="cate_id" id="">
							<option value="">请选择</option>
							<?php if(is_array($cates)):?><?php  foreach($cates as $cate){ ?>
							<option value="<?php echo $cate['lcid'];?>"><?php echo $cate['title'];?></option>
							<?php }?><?php endif;?>
						</select></td>
					</tr>
					<tr>
						<th>联系人Email：</th>
						<td><input type="text" name="email" id="" validate="{email:true}" /></td>
					</tr>
					<tr>
						<th>排序：</th>
						<td><input type="text" name="sort" value="0" class="input-mini" /></td>
					</tr>
					<tr style="height:30px;">
						<th>状态：</th>
						<td><label class="radio pull-left" style="margin-right:30px;"><input type="radio" name="state" checked="checked" value="1" id="" />显示</label><label class="radio"><input type="radio" name="state" value="0" id="" />关闭</label></td>
					</tr>
					<tr class="well" style="height:50px">
						<th></th>
						<td><input type="submit" value="添加" class="btn btn-primary" /></td>
					</tr>
				</table>
			</form>
		</div>
		<div id="tabs-3">
			<table class="table">
				<tr>
					<th>ID</th>
					<th>名称</th>
					<th>操作</th>
				</tr>
				<?php if(is_array($cates)):?><?php  foreach($cates as $cate){ ?>
				<tr>
					<td><?php echo $cate['lcid'];?></td>
					<td><?php echo $cate['title'];?></td>
					<td><i class="icon-edit"></i><a href="<?php echo $cate['lcid'];?>" class="edit-item" type="<?php echo $cate['lcid'];?>">修改</a> <i class="icon-trash"></i><a href="<?php echo $cate['lcid'];?>" class="del-item">删除</a></td>
				</tr>
				<?php }?><?php endif;?>
			</table>
		</div>
		<div id="tabs-4">
			<form validate="true" action="http://www.hap-job.com/index.php/backend/webConfig/addLinkCate" method="post" class="well form-inline">
		        <label for="cate-title">分类名：</label><input id="cate-title" validate="{required:true}" type="text" name="title" />&nbsp;&nbsp;
		        <button type="submit" class="btn btn-primary">添加</button>
			</form>
		</div>
	</div>
	<div id="dialog" title="修改友情连接分类" class="hide">
		<form validate="true" action="http://www.hap-job.com/index.php/backend/webConfig/editLinkCate" method="post" class="well form-inline" style="margin-bottom: 0px;">
			<input type="hidden" name="lcid" value="" />
		    <label for="cate-title">分类名：</label><input id="cate-title" validate="{required:true}" type="text" name="title" />
			</form>
	</div>
<script type="text/javascript">
$('.del-item').click(function() {
	var _obj=$(this),
	_url='http://www.hap-job.com/index.php/backend/webConfig/delLinkCate';
	if($(this).attr('type')=='ads'){
		_url='http://www.hap-job.com/index.php/backend/webConfig/delLink';
	}
	if(confirm('确认删除？')){
		$.post(_url,{id:$(this).attr('href')},function(data){
			if(data==1){
				_obj.parents('tr').fadeOut(300);
			}
		});
	}
	return false;
});
$('.edit-item').click(function(){
	$('#dialog input[name="lcid"]').val($(this).attr('href'));
	$('#dialog input[name="title"]').val($(this).parent().prev().text());
	$('#dialog').dialog({
		modal:true,
		width:340,
		buttons:{
			'确定':function(){
				$('#dialog form').submit();
			}
		}
	});
	return false;
});
$('#tabs').tabs({selected:0});
</script>
</body>
</html>
