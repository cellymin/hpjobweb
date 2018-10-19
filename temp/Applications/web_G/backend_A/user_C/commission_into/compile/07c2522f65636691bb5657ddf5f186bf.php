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
<div id="userList">
    <!--<form action="http://www.hap-job.com/index.php/backend/user/insertCommission" method="post" enctype="multipart/form-data" id="insertCommission" class="well form-horizontal">-->
        <!--<table border=0 cellspacing=0 cellpadding=0 align=center width="100%">-->
            <!--<tr>-->
                <!--<td width=55 height=20 align="center"><input type="hidden" name="MAX_FILE_SIZE" value="2000000">文件： </td>-->
                <!--<td height="16">-->
                    <!--<input name="file" type="file"  value="浏览" >-->
                <!--</td>-->
                <!--<input name="laid" type="hidden" id="upload_laid">-->
                <!--<td>-->
                    <!--<input type="submit" value="导入" name="upload">-->
                <!--</td>-->
            <!--</tr>-->
        <!--</table>-->
    <!--</form>-->
    <form action="http://www.hap-job.com/index.php/backend/user/commission_into" method="get" id="search-form">
        <table class="table well" style="margin-bottom:-18px;">
            <tr>
                <th></th>
                <th>身份证号</th>
                <th>佣金</th>
                <th>公司名称</th>
                <th>工作时间</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th></th>
            </tr>
            <tr>
                <td></td>
                <td><input type="text" name="id_number" class="input-medium" value="<?php if(isset($_GET['id_number'])){?><?php echo urldecode($_GET['id_number']);?><?php }?>"></td>
                <td><input type="text" name="commission" class="input-medium" value="<?php if(isset($_GET['commission'])){?><?php echo urldecode($_GET['commission']);?><?php }?>"></td>
                <td><input type="text" name="company_name" class="input-medium" value="<?php if(isset($_GET['company_name'])){?><?php echo urldecode($_GET['company_name']);?><?php }?>"></td>
                <td><input type="text" name="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
                <td><input type="text" name="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
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
    <table id="user-list" class="table">
        <tr>
            <th><input type="checkbox" class="select-all input-checkbox"></th>
            <th>id</th>
            <th>内容</th>
            <th>返现佣金</th>
            <th>公司名称</th>
            <th>工作时间</th>
            <th>身份证号</th>
            <th>导入人</th>
        </tr>
        <?php if(is_array($commissions)):?><?php  foreach($commissions as $commission){ ?>
        <tr>
            <td><input type="checkbox" class="input-checkbox" name="uid" value="<?php echo $commission['id'];?>"> </td>
            <td><?php echo $commission['id'];?></td>
            <td><?php echo $commission['content'];?></td>
            <td><?php echo $commission['commission'];?></td>
            <td><?php echo $commission['company_name'];?></td>
            <td><?php echo $commission['job_time'];?></td>
            <td><?php echo $commission['id_number'];?></td>
            <td><?php echo $commission['root'];?></td>
        </tr>
        <?php }?><?php endif;?>

        <tr class="well">
            <td><input type="checkbox" class="select-all input-checkbox"></td>
            <td class="opt" colspan="2">
                <a class="btn btn-mini btn-warning" action="ban"><i class="icon-ban-circle icon-white"></i>删除</a>
            </td>
            <td colspan="6">
                <?php echo $page;?>
            </td>
        </tr>

    </table>
</div>
<div id="dialog" style="z-idnex:999"></div>
<script type="text/javascript">
    $('.config-role').click(function(){
        $('#config-role .user').text($(this).attr('username'));
        $('#config-uid').val($(this).attr('uid'));
        var rid=$(this).attr('rid').split('#');
        $('#config-role :checkbox').each(function(){
            if(jQuery.inArray($(this).val(),rid)>=0){
                $(this).attr('checked',true);
            }else{
                $(this).attr('checked',false);
            }
        });
        //导入入职返现记录
        $("#insertCommission").click(function(){
            $("#insertCommission").dialog({
                //modal:true,
                title:'导入职位',
                resizable: true,
                width:450

            });
            $("#upload #title").val('').focus();
            return false;
        });
        return false;
    });
    select_all('user-list');//全选
    //删除选中用户

    function delInto(arg){
        console.log(arg);
        $.post('http://www.hap-job.com/index.php/backend/user/delInto',{id:arg.id},function(data){
            if(data.status==1){

                    location.reload();
            }
        },'Json');
    }
    $('.opt a').click(function(){
        var _id_arr=[],
                _confirm_msg='删除',
                _action=$(this).attr('action'),
                _fun="delInto",//处理函数
                _name="",
                _checked=$('.table :checkbox[name]:checked');
        _checked.each(function(){
            _id_arr.push($(this).val());
        });
        if(_checked.length==0){
            alert("请选择你需要操作的记录!");
            return false;
        }

        switch(_action){
            case "ban":_confirm_msg="删除";_fun="delInto";_name=2;break;
        }
        if(confirm("确定"+_confirm_msg+"选择记录？")){

            eval(_fun+"({id:_id_arr,checked_obj:_checked,a_name:_name})");

        }
        return false;
    });
</script>
</body>
</html>
