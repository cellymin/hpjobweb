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
<div id="userList">
    <form action="http://www.hap-job.com/index.php/backend/user/export" method="post" enctype="multipart/form-data" id="insertCommission" class="well form-horizontal">
        <table border=0 cellspacing=0 cellpadding=0 align=center width="100%">
            <tr>

                <td>
                    <input type="submit" value="导出" name="upload">
                </td>
            </tr>
        </table>
    </form>
    <form action="http://www.hap-job.com/index.php/backend/user/commission_withdra" method="get" id="search-form">
        <table class="table well" style="margin-bottom:-18px;">
            <tr>
                <th></th>
                <th>手机号</th>
                <th>开户名</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th>状态</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <td></td>
                <td><input type="text" name="phone" class="input-medium" value="<?php if(isset($_GET['phone'])){?><?php echo $_GET['phone'];?><?php }?>"></td>
                <td><input type="text" name="account_name" class="input-medium" value="<?php if(isset($_GET['account_name'])){?><?php echo urldecode($_GET['account_name']);?><?php }?>"></td>
                <td><input type="text" name="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
                <td><input type="text" name="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
                <td>
                    <select name="status" id="" class="input-mini" <?php if(isset($_GET['status'])){?>select="<?php echo $_GET['status'];?>"<?php }?>>
                    <option value="">请选择</option>
                    <option value="0">未审核</option>
                    <option value="3">已打款</option>
                    <option value="2">审核未通过</option>
                    </select>
                </td>
                <td colspan="2">
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
    <table id="user-list" class="table">
        <tr>
            <th><input type="checkbox" class="select-all input-checkbox"></th>
            <th>ID</th>
            <th>手机号</th>
            <th>姓名</th>
            <th>性别</th>
            <th>身份证号</th>
            <th>提现金额</th>
            <th>提现到哪</th>
            <th>提现账号</th>
            <th>开户名</th>
            <th>开户行</th>
            <th>状态</th>
            <th>申请时间</th>
            <th>操作</th>
            <th>操作时间</th>
            <th>操作人</th>
        </tr>
        <?php if(is_array($result)):?><?php  foreach($result as $result){ ?>
        <tr>
            <td><input type="checkbox" class="input-checkbox" name="uid" value="<?php echo $result['cwid'];?>"> </td>
            <td><?php echo $result['cwid'];?></td>
            <td><?php echo $result['phone'];?></td>
            <td><?php echo $result['name'];?></td>
            <td><?php if($result['gender']==0){?>
                <span>男</span>
                <?php  }elseif($result['gender']==1){ ?>
                <span>女</span>
                <?php }?>
            </td>
            <td><?php echo $result['id_number'];?></td>
            <td><?php echo $result['amount'];?></td>
            <td>
                <?php if($result['type']==1){?>
                <span>银行卡</span>
                <?php  }elseif($result['type']==2){ ?>
                <span>支付宝</span>
                <?php }?>
            </td>
            <td><?php echo $result['bank_account'];?></td>
            <td><?php echo $result['account_name'];?></td>
            <td><?php echo $result['bank'];?></td>
            <td><?php if($result['status']==0){?>
                <span class="warning">未打款</span>
                <?php  }elseif($result['status']==3){ ?>
                <span class="success">已打款</span>
                <?php  }elseif($result['status']==2){ ?>
                <span class="warning">审核未通过</span>
                <?php }?></td>
            <td><?php echo date('Y-m-d H:i:s',$result['create_time']);?></td>
            <td>
                <a href="http://www.hap-job.com/index.php/backend/user/getUserDra/id/<?php echo $result['cwid'];?>" c-name="<?php echo $result['phone'];?>" action="edit" class="opt-item"><i class="icon-asterisk"></i>修改</a>&nbsp;&nbsp;
                <a href="http://www.hap-job.com/index.php/backend/user/updateWithdrawal?name=3&cwid=<?php echo $result['cwid'];?>">已打款</a>
                <a href="http://www.hap-job.com/index.php/backend/user/updateWithdrawal?name=2&cwid=<?php echo $result['cwid'];?>"><font color=" ">审核不通过</font></a>
            </td>
            <td><?php if($result['root_time'] == 0){?>
                <span>暂无</span>
                <?php  }else{ ?>
                <?php echo date('Y-m-d H:i:s',$result['root_time']);?></td>
            <?php }?></td>
            <td><?php echo $result['root'];?></td>
            <?php
            			$groups=array_pop($commission);
            			$group='';
                        $rid=array();
                        if(is_array($groups)){
                			foreach ($groups as $value) {
                				$group.=$value['title'].'、';
                                $rid[]=$value['rid'];
                			}
                        }else{
                            $rid[]=$user['rid'];
                            $group=$user['title'];
                        }
            		?>
            <!--<td><?php echo trim($group,'、');?></td>
            <td><?php echo date('Y-m-d H:i:s',$user['created']);?></td>
            <td><?php echo date('Y-m-d H:i:s',$user['last_login']);?></td>-->
            <!--<td class="status"><?php if($user['banned']){?> <span class="warning" title="<?php echo $user['ban_reason'];?>">已禁止</span><?php  }else{ ?> <span class="success">已开启</span> <?php }?></td>-->
            <!--<td>
                <a <?php if(in_array('3',$rid)){?>href="http://www.hap-job.com/index.php/backend/user/viewUserInfo/id/<?php echo $user['uid'];?>/type/cu"<?php  }else{ ?>href="http://www.hap-job.com/index.php/backend/user/viewUserInfo/id/<?php echo $user['uid'];?>/type/pu"<?php }?> c-name="<?php echo $user['username'];?>" action="view" class="opt-item"><i class="icon-user"></i>查看</a>&nbsp;&nbsp;
                <a href="http://www.hap-job.com/index.php/backend/user/editUserInfoForm/id/<?php echo $user['uid'];?>" c-name="<?php echo $user['username'];?>" action="edit" class="opt-item"><i class="icon-asterisk"></i>修改</a>&nbsp;&nbsp;
                <a href="###" rid="<?php echo implode('#',$rid);?>" uid="<?php echo $user['uid'];?>" username="<?php echo $user['username'];?>" class="config-role" title="配置用户组"><i class="icon-th"></i>用户组</a>
            </td>-->
        </tr>
        <?php }?><?php endif;?>

        <tr class="well">
            <td><input type="checkbox" class="select-all input-checkbox"></td>
            <td class="opt" colspan="2">
                <a class="btn btn-mini btn-warning" action="ban"><i class="icon-ban-circle icon-white"></i> 审核不通过</a>
                <a class="btn btn-mini btn-success" action="unban" name=><i class="icon-ok icon-white"></i> 已打款</a>
            </td>
            <td colspan="10">
                <?php echo $pages;?>
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
//    //删除选中用户
//    function del_user(arg){
//        $.post('http://www.hap-job.com/index.php/backend/user/delUser',{id:arg.id},function(data){
//            if(data==1){
//                arg.checked_obj.parents('tr').fadeOut(function(){
//                    arg.checked_obj.parents('tr').remove();
//                });
//            }
//        },'html');
//    }

    //修改提现资料
    $('.opt-item').click(function() {
        var _this = $(this),
                _name = _this.attr('c-name'),
                _id = _this.attr('href');
        $.post($(this).attr('href'), function (data) {
            $('#dialog').html(data);
//            $('#dialog').dialog({
//                "title": _name + "用户提现资料",
//                "width": "500",
//                "height": "auto",
//                "modal": true,
//                "buttons": {
//                    "确定": function () {
//                        if (_this.attr('action') == 'view') {
//                            $(this).dialog("close");
//                        } else {
//                            $('#dialog form').submit();
//                        }
//                    }
//                }
//            });
        }, 'html');
        return false;
    });


    function verify_recruit(arg){
        $.post('http://www.hap-job.com/index.php/backend/user/verifyRecruit',{"recruit_id":arg.id,type:arg.a_type},function(data){
            if(data==1){
                window.location.reload();
            }
        },'html');
    }

    function ban_user(arg){
        $.post('http://www.hap-job.com/index.php/backend/user/banUser',{id:arg.id,type:arg.a_type},function(data){
            if(data==1){
                if(arg.a_type==1){
                    arg.checked_obj.parent().siblings('.status').html('<span class="warning" title="用户已被禁止">已禁止</span>');
                }else{
                    arg.checked_obj.parent().siblings('.status').html('<span class="success" title="">已开启</span>');
                }
            }
        },'html');
    }
    function updateWithdrawalCheck(arg){
        console.log(arg);
        $.post('http://www.hap-job.com/index.php/backend/user/updateWithdrawalCheck',{id:arg.id,type:arg.a_name},function(data){
            if(data.status==1){
                arg.checked_obj.parents('td').fadeOut(function(){
                    location.reload();
                });
            }
        },'json');
    }
    $('.opt a').click(function(){
        var _id_arr=[],
                _confirm_msg='修改',
                _action=$(this).attr('action'),
                _fun="updateWithdrawalCheck",//处理函数
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
            case "ban":_confirm_msg="不通过";_fun="updateWithdrawalCheck";_name=2;break;
            case "unban":_confirm_msg="已打款";_fun="updateWithdrawalCheck";_name=3;break;
        }
        if(confirm("确定"+_confirm_msg+"选择用户？")){
            eval(_fun+"({id:_id_arr,checked_obj:_checked,a_name:_name})");
        }
        return false;
    });
    $('.opt-item').click(function(){
        var _this=$(this),
                _name=_this.attr('c-name'),
                _id=_this.attr('href');
        $.post($(this).attr('href'),function(data){
            $('#dialog').html(data);
            $('#dialog').dialog({
                "title":_name+"用户资料",
                "width":"500",
                "height":"auto",
                "modal":true,
                "buttons":{
                    "确定":function(){
                        if(_this.attr('action')=='view'){
                            $(this).dialog("close");
                        }else{
                            $('#dialog form').submit();
                        }
                    }
                }
            });
        },'html');
        return false;
    });
</script>
</body>
</html>
