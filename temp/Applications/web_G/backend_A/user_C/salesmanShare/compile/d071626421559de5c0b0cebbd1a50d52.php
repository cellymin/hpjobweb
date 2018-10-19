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
    <form action="http://www.hap-job.com/index.php/backend/user/export_salesmanShare" method="post" enctype="multipart/form-data" id="insertCommission" class="well form-horizontal">
        <table border=0 cellspacing=0 cellpadding=0 align=center width="100%">
            <tr>
                <td><input id="s_time" name="start_time" value="" type="text" style="display: none"></td>
                <td><input id="e_time" name="end_time" value="" type="text" style="display: none"></td>
                <td>
                    <input type="submit" id="select_submit" value="导出" name="upload" id="submit">
                </td>
            </tr>
        </table>
    </form>

    <form action="http://www.hap-job.com/index.php/backend/user/salesmanShare" method="get" id="search-form">
        <table class="table well" style="margin-bottom:-18px;">
            <tr>
                <th></th>
                <th>业务员标识</th>
                <th>被邀请人手机号码</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th></th>
            </tr>
            <tr>
                <td></td>
                <td><input type="text" name="username" class="input-medium" value="<?php if(isset($_GET['username'])){?><?php echo urldecode($_GET['username']);?><?php }?>"></td>
                <td><input type="text" name="from_id" class="input-medium" value="<?php if(isset($_GET['from_id'])){?><?php echo urldecode($_GET['from_id']);?><?php }?>"></td>
                <td><input type="text" name="start_time" id="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
                <td><input type="text" name="end_time" id="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
                <td colspan="2">
                    <button type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;&nbsp;查询</button>

                    <a id="reset" class="btn btn-info"><i class="icon-refresh icon-white"></i>&nbsp;&nbsp;重置</a>
                </td>
                <td>搜索结果共：<?php echo $counts;?> 条</td>
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
            <th>用户id</th>
            <th>业务标识</th>
            <th>被邀请人注册时间</th>
            <th>被邀请人姓名</th>
            <th>被邀请人手机号码</th>
            <th>被邀请人性别</th>
            <th>被邀请人身份证号</th>
        </tr>
        <?php if(is_array($commission)):?><?php  foreach($commission as $commission){ ?>
        <tr>
            <td><input type="checkbox" class="input-checkbox" name="uid" value="<?php echo $commission['id'];?>"> </td>
            <td><?php echo $commission['uid'];?></td>
            <td><?php echo $commission['username'];?></td>
            <td><?php echo date('Y-m-d H:i:s',$commission['created_at']);?></td>
            <td><?php echo $commission['f_name'];?></td>
            <td><?php echo $commission['f_mobile'];?></td>
            <td><?php if($commission['f_gender']==0){?>
                <span>男</span>
                <?php  }elseif($commission['f_gender']==1){ ?>
                <span>女</span>
                <?php }?>
            </td>
            <td><?php echo $commission['f_id_number'];?></td>
        </tr>
        <?php }?><?php endif;?>

        <tr class="well">
            <td><input type="checkbox" class="select-all input-checkbox"></td>
            <td class="opt" colspan="2">
                <a class="btn btn-mini btn-warning" action="ban"><i class="icon-ban-circle icon-white"></i> 审核不通过</a>
                <a class="btn btn-mini btn-success" action="unban" name=><i class="icon-ok icon-white"></i> 审核通过</a>

            </td>
            <td colspan="10">
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
        $('#config-role').dialog({
            "width":"400",
            "modal":true,
            "buttons":{
                "修改":function(){
                    if($('#config-role :checked').length==0){
                        alert('必须为用户设置一个角色');
                        return false;
                    }
                    $('#config-role form').submit();
                }
            }
        });
        return false;
    });
    select_all('user-list');//全选
    //全选审核
    function shareVerifyCheck(arg){
        console.log(arg);
        $.post('http://www.hap-job.com/index.php/backend/user/shareVerifyCheck',{id:arg.id,type:arg.a_name},function(data){
            if(data.status==1){
                arg.checked_obj.parents('td').fadeOut(function(){
                    location.reload();
                });
            }
        },'json');
    }

    /**
     * 导出前筛选
     */
    $('#submit').click(function(){
        var start_time = $('#start_time').val();

        var end_time = $('#end_time').val();

        $('#s_time').val(start_time);

        $('#e_time').val(end_time);

        $('#insertCommission').submit();

        return false;

    });

    $('.opt a').click(function(){
        var _id_arr=[],
                _confirm_msg='审核',
                _action=$(this).attr('action'),
                _fun="shareVerifyCheck",//处理函数
                _name="",
                _checked=$('.table :checkbox[name]:checked');
        _checked.each(function(){
            _id_arr.push($(this).val());
        });
        if(_checked.length==0){
            alert("请选择你需要操作的数据!");
            return false;
        }

        switch(_action){
            case "ban":_confirm_msg="不通过";_fun="shareVerifyCheck";_name=2;break;
            case "unban":_confirm_msg="通过";_fun="shareVerifyCheck";_name=1;break;
        }
        if(confirm("确定"+_confirm_msg+"选择用户？")){
            eval(_fun+"({id:_id_arr,checked_obj:_checked,a_name:_name})");
        }
        return false;
    });

    /**
     * 导出前筛选
     */
    $('#select_submit').click(function(){
        var start_time = $('#start_time').val();

        var end_time = $('#end_time').val();

        if(start_time == '' || end_time == ''){

            alert('请在搜索栏输入时间段');

            return false;
        }

        $('#s_time').val(start_time);

        $('#e_time').val(end_time);

        $('#insertCommission').submit();

        return false;

    });


</script>
</body>
</html>
