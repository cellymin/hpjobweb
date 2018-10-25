<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
<link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/web/backend/templates/css/public.css"/>
<link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
<script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jqueryValidate/jquery.validate.min.js"></script>
<script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jqueryValidate/jquery.metadata.js"></script>
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
	.alert-success{
		float: left;
		margin-bottom: 0px;
	}
</style>
</head>
<body>
<div id="tabs">
<div id="nav-button"><ul class="nav nav-pills" style="margin-bottom:10px;"><li class="active"><a href="" id="addNav"><i class="icon-plus-sign icon-white"></i> 添加导航</a></li></ul>
<div class="alert alert-success hide">
        <a class="close" data-dismiss="alert">×</a>
        <strong>快乐招聘系统提示：</strong>
        <span></span>
</div></div>
<div id="tabs-1" style="clear:both">
	<form action="http://www.192.168.3.131/hpjobweb/index.php/backend/webConfig/sortNavigation" method="post">
	<table class="table">
		<tr>
			<th>ID</th>
			<th>标记</th>
			<th>导航名称</th>
			<th>页面状态</th>
                        <th>打开方式</th>
			<th width="10%">排序</th>
                        <th>操作</th>
		</tr>
            <?php if(is_array($navs)):?><?php  foreach($navs as $nav){ ?>
                <tr>
			<td><?php echo $nav['id'];?></td>
			<td><?php echo $nav['mark'];?></td>
			<td><a href="<?php if(substr($nav['href'],0,4)!='http'){?>http://www.192.168.3.131/hpjobweb/index.php<?php echo $nav['href'];?><?php  }else{ ?><?php echo $nav['href'];?><?php }?>" target="<?php echo $nav['target'];?>"><?php echo $nav['title'];?></a></td>
                        <td><?php if($nav['state']){?> <span class="success">显示</span><?php  }else{ ?><span class="waring">不显示</span><?php }?></td>
                        <td><?php if($nav['target']=='_blank'){?>新窗口<?php  }else{ ?>本页<?php }?></td>
			<td><input type="text" name="sort[<?php echo $nav['id'];?>]" class="input-mini" value="<?php echo $nav['sort'];?>" /></td>
                        <td>
                            <a href="http://www.192.168.3.131/hpjobweb/index.php/backend/webConfig/editNavigation/id/<?php echo $nav['id'];?>" class="edit-item"><i class="icon-edit"></i>编辑</a>
                            <a href="<?php echo $nav['id'];?>" class="del-item"><i class="icon-trash"></i>删除</a>
                        </td>
		</tr>
            <?php }?><?php endif;?>
            <tr>
                <td colspan="5"></td>
                <td><input type="submit" value="更新排序" class="btn btn-mini btn-primary" /></td>
                <td></td>
            </tr>
	</table>
	</form>
</div>
<div id="dialog" class="hide">
<form action="http://www.192.168.3.131/hpjobweb/index.php/backend/webConfig/addNavigation" method="post">
<div id="tabs-add" title="添加导航">
	<ul>
		<li><a href="#tabs-1">基本设置</a></li>
		<li><a href="#tabs-2">SEO设置</a></li>
	</ul>
	<div id="tabs-1"><table>
		<tr>
	<th width="10%">导航标记</th>
	<td><input type="text" name="mark" validate="{required:true,regexp:/^\w+$/,messages:'只能输入数字、字母、下划线'}" /></td>
</tr>
<tr>
	<th width="10%">导航名称</th>
	<td><input type="text" name="title" /></td>
</tr>
<tr>
	<th>链接地址</th>
	<td><input type="text" name="href" /></td>
</tr>
<tr>
	<th>排序</th>
	<td><input type="text" name="sort" class="input-mini" /></td>
</tr>
<tr>
	<th>打开方式</th>
	<td><label><input type="radio" name="target" value="" checked />本页打开</label><label><input type="radio" name="target" value="_blank" />新窗口打开</label></td>
</tr>
<tr>
	<th>是否显示</th>
	<td><label><input type="radio" name="state" value="1" checked />显示</label><label><input type="radio" name="state" value="0" />不显示</label></td>
</tr>
</table>
</div>
	<div id="tabs-2">
		<table>
			<tr>
				<th>SEO关键字</th>
				<td><textarea name="seo_keywords"></textarea></td>
			</tr>
			<tr>
				<th>SEO描述</th>
				<td><textarea name="seo_desc" cols="100" rows="5"></textarea></td>
			</tr>
		</table>
	</div>
</div>
</form>
</div>
</div>
<script type="text/javascript">
$('.del-item').click(function(){
	var _obj=$(this).parents('tr');
	if(confirm("确认删除此导航？")){
	$.post('http://www.192.168.3.131/hpjobweb/index.php/backend/webConfig/delNavigation',{id:$(this).attr('href')},function(data){
		if(data==1){
			_obj.fadeOut();
		}else{
			alert('删除失败');
		}
	},'html');
	}
	return false;
});
$('#dialog form').validate();
$('#addNav').click(function(){
	var _obj=$('#dialog form');
	$('#tabs-add').tabs();
	$('#dialog').dialog({
		width:400,
		modal:true,
		title:'添加导航',
		buttons:{
			'添加':function(){
				if($('#dialog input[name="mark"]').val()==''){
					alert('请输入调用标记');
					return false;
				}
				 $.post('http://www.192.168.3.131/hpjobweb/index.php/backend/webConfig/addNavigation',_obj.serialize(),function(data){
                                     if(data==1){
                                         $('#dialog').dialog( "close" );
                                         $('.alert-success').slideDown().children('span').html('添加导航成功！');
                                         setTimeout(function(){$('.alert-success').fadeOut();location.reload();},2500);
                    }
				 },'html');
			}
		}
	});
	return false;
});
$('.close').click(function(){$('.alert-success').slideUp();return false;});
</script>
</body>
</html>
