<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE HTML>
<html lang="en-US" xmlns="http://www.w3.org/1999/html">
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
<form action="http://www.hap-job.com/index.php/backend/company/licenseverify" method="get" id="search-form">
<table class="table well" style="margin-bottom:-18px;">
    <tr>
        <th>公司名称</th>
        <td><input type="text" name="name" value="<?php if(isset($_GET['name'])){?><?php echo urldecode($_GET['name']);?><?php }?>"></td>
        <th>审核状态</th>
        <td>
        	<select name="license_verify" <?php if(isset($_GET['license_verify'])){?>select="<?php echo $_GET['license_verify'];?>"<?php }?>>
        		<option value="">请选择</option>
        		<option value="0">未通过</option>
        		<option value="2">审核中</option>
        		<option value="1">已通过</option>
        	</select>
        </td>
        <td colspan="1">
            <button type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;&nbsp;查询</button>
            <a id="reset" class="btn btn-info"><i class="icon-refresh icon-white"></i>&nbsp;&nbsp;重置</a>
    	</td>
    <script type="text/javascript">
        $("select[select]").each(function(e,obj){
            $(this).val($(this).attr('select'));
        });
        $('#reset').click(function(){
            $('#search-form :input').val('');
            $('#search-form').submit();
        });
    </script> 
    </tr>
</table>
</form>
<table class="table" id="company-list">
	<tr>
		<th width="5%"><input type="checkbox" class="select-all input-checkbox"></th>
		<th>公司名称</th>
		<th>营业执照</th>
		<th>审核状态</th>
		<th>描述</th>
		<th>公司位置</th>
		<th>详细地址</th>
        <th>logo</th>
	</tr>
	<?php if(is_array($licenses)):?><?php  foreach($licenses as $license){ ?>
	<tr>
		<td><input type="checkbox" name="cid" class="input-checkbox" value="<?php echo $license['uid'];?>"> </td>
		<td><?php echo $license['name'];?> <span class="tips">[ID:<?php echo $license['uid'];?>]</span></td>
		<td><a href="<?php echo $license['license'];?>" uid="<?php echo $license['uid'];?>" class="view">查看</a></td>
		<td>
			<?php if($license['license_verify']==2){?>
			<span class="sys">审核中</span>
			<?php  }elseif($license['license_verify']==1){ ?>
			<span class="success">已通过</span>
			<?php  }else{ ?>
			<span class="warning">未通过</span>
			<?php }?>
		</td>
		<td width="350px"><?php echo $license['desc'];?></td>
        <td><?php echo $license['address'];?></td>
        <td><?php echo $license['street'];?></td>
		<td><?php if($license['logo']!=null){?><img src="<?php echo $license['logo'];?>" style="width: 40px;height: 40px;margin-left: 20px"><?php  }else{ ?>无<?php }?></td>
	</tr>
	<?php }?><?php endif;?>
	<tr class="well">
		<td><input type="checkbox" class="select-all input-checkbox"></td>
		<td colspan="4" class="opt">
            <a class="btn btn-mini btn-danger" action="del"><i class="icon-trash icon-white"></i> 删除</a>
			<a class="btn btn-mini btn-success" action="pass"><i class="icon-ok-sign icon-white"></i> 通过</a>
			<a class="btn btn-mini btn-danger" action="unpass"><i class="icon-ban-circle icon-white"></i>未通过</a>
		</td>
	</tr>
    <tr class="well">
        <td colspan="6"></td>
        <td><?php echo $pages;?></td>
    </tr>
</table>
<div id="dialog" class="hide" title="">
	<img src="" alt="">
</div>
<script type="text/javascript">
	$('.select-all').click(function(){
    	if($(this).attr('checked')){
    		$('.select-all').attr('checked',true);
    		$('.table :checkbox[name]').attr('checked',true);
    	}else{
    		$('.select-all').attr('checked',false);
    		$('.table :checkbox[name]').attr('checked',false);
    	}
    });
    //审核执照   id：选中的数组   type: 审核结果
    function verify_license (arg) {
    	$.post('http://www.hap-job.com/index.php/backend/company/licenseverify',{"id":arg.id,"license_verify":arg._type},function(data){
    		if(data==1){
    			window.location.reload();
    		}
    	},'html');
    }
    function del_company(arg){
        $.post('http://www.hap-job.com/index.php/backend/company/delCompany',{"id":arg.id},function(data){
            if(data==1){
                arg.checked_obj.parents('tr').fadeOut(function(){
                    arg.checked_obj.parents('tr').remove();
                });
            }
        },'json');
    }
    /**
     * 删除、通过、未通过
     */
    $('.opt a').click(function(){
        var _id=[],
                _confirm_msg='删除该公司及公司下的所有职位和收藏投递记录？？是否确定删除',
                _action=$(this).attr('action'),
                _fun="del_company",//处理函数
                _type="",
                _checked=$('#company-list :checkbox[name]:checked');
        if(_checked.length==0){
            alert("请选择你需要操作的公司!");
            return false;
        }
        _checked.each(function(){
            _id.push($(this).val());
        });
        switch(_action){
            case "pass":_confirm_msg="通过";_action=1;_fun="verify_license";break;
            case "unpass":_confirm_msg="不通过";_action=0;_fun="verify_license";break;
        }
        if(confirm("确定"+_confirm_msg+"选中公司？")){
            eval(_fun+"({id:_id,checked_obj:_checked,_type:_action})");
        }
        return false;
    });
	$('.view').click(function(){
            $('#dialog img').attr('src',$(this).attr('href'));
            _this=$(this);
            $('#dialog').dialog({
                "title":"查看营业执照-公司："+$(this).parent().prev().text(),
                "modal":true,
                "width":450,
                "height":450,

            });
            return false;
	});
</script>
</body>
</html>