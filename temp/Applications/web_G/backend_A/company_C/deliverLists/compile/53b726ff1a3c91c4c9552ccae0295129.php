<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE HTML>
<html lang="en-US" xmlns="http://www.w3.org/1999/html">
<head>
<meta charset="UTF-8">
<title></title>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/public.css"/>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/web/backend/templates/js/public.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.validate.min.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.metadata.js"></script>
</head>
<body>
<form action="http://www.hap-job.com/index.php/backend/company/deliverLists" method="get" id="search-form">
    <table class="table well" style="margin-bottom:-18px;">
        <tr>
            <th></th>
            <th>用户名</th>
            <th>姓名</th>
            <th>投递公司</th>
            <th>开始时间</th>
            <th>结束时间</th>
            <th>性别</th>
            <th>职位名称</th>
            <th>联系状态</th>
            <th></th>
        </tr>
        <tr>
            <td></td>
            <td><input type="text" name="username" class="input-medium" value="<?php if(isset($_GET['username'])){?><?php echo $_GET['username'];?><?php }?>"></td>
            <td><input type="text" name="rel_name" class="input-medium" value="<?php if(isset($_GET['rel_name'])){?><?php echo urldecode($_GET['rel_name']);?><?php }?>"></td>
            <td><input type="text" name="company_name" class="input-medium" value="<?php if(isset($_GET['company_name'])){?><?php echo urldecode($_GET['company_name']);?><?php }?>"></td>
            <td><input type="text" name="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})" value="<?php if(isset($_GET['start_time'])){?><?php echo urldecode($_GET['start_time']);?><?php }?>"></td>
            <td><input type="text" name="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})" value="<?php if(isset($_GET['end_time'])){?><?php echo urldecode($_GET['end_time']);?><?php }?>"></td>
            <td>
                <select name="gender" class="input-mini" <?php if(isset($_GET['gender'])){?>select="<?php echo $_GET['gender'];?>"<?php }?>>
                <option value="">请选择</option>
                <option value="0">男</option>
                <option value="1">女</option>
                </select>
            </td>
            <td><input type="text" name="recruit_name" class="input-medium" value="<?php if(isset($_GET['recruit_name'])){?><?php echo urldecode($_GET['recruit_name']);?><?php }?>"></td>
            <td>
                <select name="is_contact" class="input-mini" <?php if(isset($_GET['is_contact'])){?>select="<?php echo $_GET['is_contact'];?>"<?php }?>>
                <option value="">请选择</option>
                <option value="0">未联系</option>
                <option value="1">已联系</option>
                </select>
            </td>
            <td colspan="2">
                <button type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;&nbsp;查询</button>
                <a id="reset" class="btn btn-info"><i class="icon-refresh icon-white"></i>&nbsp;&nbsp;重置</a>
            </td>

            <script type="text/javascript">

                $('#reset').click(function(){
                    $('#search-form :input').val('');
                    $('#search-form').submit();
                });
            </script>
        </tr>
    </table>
</form>
<table class="table" id="deliver-list">
	<tr>
        <th><input type="checkbox" class="select-all input-checkbox"></th>
		<th>姓名</th>
        <th>用户名</th>
        <th>联系方式</th>
        <th>性别</th>
        <th>年龄</th>
		<th>投递公司</th>
		<th>职位名称</th>
		<th>投递时间</th>
        <th>查看简历</th>
        <th>操作</th>
        <th>联系时间</th>
        <th>联系人</th>
	</tr>
	<?php if(is_array($deliver)):?><?php  foreach($deliver as $deliver){ ?>
	<tr>
        <td><input type="checkbox" class="input-checkbox" name="deliver_id" value="<?php echo $deliver['id'];?>"> </td>
		<td><?php echo $deliver['rel_name'];?></td>
        <td><?php echo $deliver['username'];?></td>
        <td><?php echo $deliver['phone'];?></td>
        <td><?php if($deliver['gender']==0){?> 男
            <?php  }elseif($deliver['gender']==1){ ?> 女
            <?php }?>
        </td>
        <td><?php echo $deliver['age'];?></td>
		<td><?php echo $deliver['company_name'];?></td>
		<td><?php echo $deliver['recruit_name'];?></td>
		<td><?php echo date('Y-m-d H:i:s',$deliver['sendtime']);?></td>
		<td><a href="http://www.hap-job.com/index.php/app/auth/viewResume/resume_id/<?php echo $deliver['resume_id'];?>" target="_blank">查看</a></td>
        <td><?php if($deliver['is_contact']==0){?><a href="http://www.hap-job.com/index.php/backend/company/isContact/id/<?php echo $deliver['id'];?>" class ="is_contact" title="标记已联系" value-id="<?php echo $deliver['id'];?>" >标记已联系</a>
            <?php  }else{ ?><span class="success">已联系&nbsp;&nbsp;<a href="http://www.hap-job.com/index.php/backend/company/restore/id/<?php echo $deliver['id'];?>" class ="restore" title="恢复操作" value-id="<?php echo $deliver['id'];?>" >恢复操作</a></span>
            <?php }?>
        </td>
        <td><?php if($deliver['contact_time']!=0){?><?php echo date('Y-m-d H:i:s',$deliver['contact_time']);?><?php }?></td>
        <td><?php if($deliver['contact']==''){?>暂未联系<?php }?><span class="success"><?php echo $deliver['contact'];?></span></td>
	</tr>
	<?php }?><?php endif;?>
    <tr class="well">
        <td><input type="checkbox" class="select-all input-checkbox"></td>
        <td class="opt" colspan="4">
            <a class="btn btn-mini btn-success" action="verify-pass"><i class="icon-ok icon-white"></i> 一键联系</a>
        </td>
        <td colspan="4">
            <?php echo $pages;?>
        </td>
        <td></td>
    </tr>
</table>
<div id="dialog" class="hide" title="">
	<img src="" alt="">
</div>
<script type="text/javascript">
    select_all('deliver-list');

    function contact(arg){
        $.post('http://www.hap-job.com/index.php/backend/company/contact',{"id":arg.id},function(data){
            if(data==1){
                window.location.reload();
            }
        },'html');
    }

    $('.opt a').click(function(){
        var _id_arr=[],
                _confirm_msg='一键联系',
                _action='contact',
                _fun="contact",//处理函数
                _checked=$('#deliver-list :checkbox[name]:checked');
        if(_checked.length==0){
            alert("请选择你需要操作的记录!");
            return false;
        }
        _checked.each(function(){
            _id_arr.push($(this).val());
        });

        if(confirm("确定"+_confirm_msg+"选中记录？")){
            eval(_fun+"({id:_id_arr,checked_obj:_checked})");
        }
        return false;
    });

    $("select[select]").each(function(e,obj){
        $(this).val($(this).attr('select'));
    });

    $(".is_contact").click(function(){
        if(confirm('确认标记已联系？')){
            return true;
        }else{
            return false;
        }
    });

    $(".restore").click(function(){
        if(confirm('确认恢复到未联系状态？')){
            return true;
        }else{
            return false;
        }
    });
</script>
</body>
</html>