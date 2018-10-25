<?php if(!defined("PATH_LC"))exit;?>
<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>后台管理</title>
<link type="text/css" rel="stylesheet" href="http://localhost//hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
<link type="text/css" rel="stylesheet" href="http://localhost//hpjobweb/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
<link type="text/css" rel="stylesheet" href="http://localhost/hpjobweb/web/backend/templates/css/public.css"/>
<script type="text/javascript" src="http://localhost//hpjobweb/public/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://localhost/hpjobweb/web/backend/templates/js/public.js"></script>
<script type="text/javascript" src="http://localhost//hpjobweb/public/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="http://localhost//hpjobweb/public/js/jqueryValidate/jquery.validate.min.js"></script>
<script type="text/javascript" src="http://localhost//hpjobweb/public/js/jqueryValidate/jquery.metadata.js"></script>
<script type="text/javascript" src="http://localhost//hpjobweb/public/js/My97DatePicker/WdatePicker.js"></script>
</head>
<body>
<form action="http://localhost/hpjobweb/index.php/backend/user/wait_verify" method="get" id="search-form">
    <table class="table well" style="margin-bottom:-18px;">
        <tr>
            <th></th>
            <th>身份证号</th>
            <th>开始时间</th>
            <th>结束时间</th>
            <th></th>
        </tr>
        <tr>
            <td></td>
            <td><input type="text" name="id_number" class="input-medium" value="<?php if(isset($_GET['id_number'])){?><?php echo $_GET['id_number'];?><?php }?>"></td>
            <td><input type="text" name="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
            <td><input type="text" name="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
            <td colspan="2">
                <button type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;&nbsp;查询</button>
                <a id="reset" class="btn btn-info"><i class="icon-refresh icon-white"></i>&nbsp;&nbsp;重置</a>
                <a class="btn btn-info" href="http://www.hap-job.com/index.php/backend/user/wait_verify" title="刷新" ><i class="icon-refresh icon-white"></i>刷新</a>
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
<div id="userList">
    <table id="user-list" class="table">
        <tr>
            <th><input type="checkbox" class="select-all input-checkbox"></th>
            <th>用户id</th>
            <th>姓名</th>
            <th>手机号码</th>
            <th>性别</th>
            <th>身份证号</th>
            <th>自拍</th>
            <th>身份证脸面</th>
            <th>身份证国徽面</th>
            <th>发起时间</th>
            <th>审核</th>
        </tr>
        <?php if(is_array($user)):?><?php  foreach($user as $user){ ?>
        <tr>
            <td><input type="checkbox" class="input-checkbox" name="uid" value="<?php echo $user['uid'];?>"> </td>
            <td><?php echo $user['uid'];?></td>
            <td><?php if($user['name']==null){?>
                <span>无</span>
                <?php  }elseif($user['name']!=null){ ?>
                <span><?php echo $user['name'];?></span>
                <?php }?>
            </td>
            <td><?php echo $user['username'];?></td>
            <td><?php if($user['gender']==0){?>
                <span>男</span>
                <?php  }elseif($user['gender']==1){ ?>
                <span>女</span>
                <?php }?>
            </td>
            <td><?php echo $user['id_number'];?></td>
            <td><a href="<?php echo $user['photo'];?>" target="_blank"><img src="<?php echo $user['photo'];?>" width="120" height="100"></a></td>
            <td><a href="<?php echo $user['face_base'];?>" target="_blank"><img src="<?php echo $user['face_base'];?>" width="120" height="100"></a></td>
            <td><a href="<?php echo $user['back_base'];?>" target="_blank"><img src="<?php echo $user['back_base'];?>" width="120" height="100"></a></td>
            <td>
                <?php if($user['apply_time']!=0){?>
                <span><?php echo date('Y-m-d H:i:s',$user['apply_time']);?></span>
                <?php  }elseif($user['apply_time']==0){ ?>
                <span>暂无</span>
                <?php }?>
            </td>
            <td>
                <a href="http://localhost/hpjobweb/index.php/backend/user/verifyRole?name=3&uid=<?php echo $user['uid'];?>">审核通过</a>
                <a href="http://localhost/hpjobweb/index.php/backend/user/verifyRole?name=2&uid=<?php echo $user['uid'];?>">审核未通过</a>
            </td>

        </tr>
        <?php }?><?php endif;?>
        <tr class="well">
            <!--<td><input type="checkbox" class="select-all input-checkbox"></td>-->
            <td><input type="checkbox" class="select-all input-checkbox"></td>
            <td class="opt" colspan="2">
                <a class="btn btn-mini btn-warning" action="ban"><i class="icon-ban-circle icon-white"></i> 审核不通过</a>
                <a class="btn btn-mini btn-success" action="unban" name=><i class="icon-ok icon-white"></i>审核通过</a>
            </td>
            <td colspan="4">
                <?php echo $pages;?>
            </td>
        </tr>

    </table>
</div>
<div id="dialog" class="hide"></div>
<script type="text/javascript">
    //查看头像
    $('.view-avatar').click(function(){
        $('#dialog').html('<img src="'+$(this).attr('path')+'" title="身份证照片">');
        $('#dialog').dialog({
            "title":$(this).attr('id_number'),
            "modal":true,
            "buttons":{
                "关闭":function(){
                    $(this).dialog('close');
                }
            }
        });
        return false;
    });

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

    function updateAuditCheck(arg){//批量处理（审核）
        console.log(arg);
        $.post('http://localhost/hpjobweb/index.php/backend/user/updateAuditCheck',{id:arg.id,type:arg.a_name},function(data){
//            if(data.verify==1){
                arg.checked_obj.parents('td').fadeOut(function(){
                    location.reload();
                });
//            }
        },'json');
    }
    $('.opt a').click(function(){
        var _id_arr=[],
                _confirm_msg='修改',
                _action=$(this).attr('action'),
                _fun="updateAuditCheck",//处理函数
                _name="",
                _checked=$('.table :checkbox[name]:checked');
        _checked.each(function(){
            _id_arr.push($(this).val());
        });
        if(_checked.length==0){
            alert("请选择你需要操作的用户!");
            return false;
        }

        switch(_action){
            case "ban":_confirm_msg="不通过";_fun="updateAuditCheck";_name=2;break;
            case "unban":_confirm_msg="通过";_fun="updateAuditCheck";_name=3;break;
        }
        if(confirm("确定"+_confirm_msg+"选择用户？")){
            eval(_fun+"({id:_id_arr,checked_obj:_checked,a_name:_name})");
        }
        return false;
    });

    function verify_recruit(arg){
        $.post('http://localhost/hpjobweb/index.php/backend/user/verifyRecruit',{"recruit_id":arg.id,type:arg.a_type},function(data){
            if(data==1){
                window.location.reload();
            }
        },'html');
    }

//



</script>
</body>
</html>
