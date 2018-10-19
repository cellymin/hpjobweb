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
    <form action="http://www.hap-job.com/index.php/backend/user/export_users" method="post" enctype="multipart/form-data" id="export_users" class="well form-horizontal">
        <table border=0 cellspacing=0 cellpadding=0 align=center width="100%">
            <tr>
                <td><input id="s_time" name="start_time" value="" type="text" style="display: none"></td>
                <td><input id="e_time" name="end_time" value="" type="text" style="display: none"></td>
                <td>
                    <input type="submit" value="导出" name="upload" id="submit">
                </td>
            </tr>
        </table>
    </form>
    <form action="http://www.hap-job.com/index.php/backend/user/userList" method="get" id="search-form">
        <table class="table well" style="margin-bottom:-18px;">
            <tr>
                <th>手机号<input type="text" name="username" class="input-medium" value="<?php if(isset($_GET['username'])){?><?php echo $_GET['username'];?><?php }?>"></th>
                <!--<th><input type="text" name="email" class="input-medium" value="<?php if(isset($_GET['email'])){?><?php echo $_GET['email'];?><?php }?>"></th>-->
                <th>角色
                    <select name="rid" id="" class="input-medium" <?php if(isset($_GET['rid'])){?>select="<?php echo $_GET['rid'];?>"<?php }?>>
                    <option value="">请选择</option>
                    <?php if(is_array($role_list)):?><?php  foreach($role_list as $role){ ?>
                    <option value="<?php echo $role['rid'];?>"><?php echo $role['title'];?></option>
                    <?php }?><?php endif;?>
                    </select>
                </th>
                <th>注册途径
                    <select name="type" class="input-medium" <?php if(isset($_GET['type'])){?>select="<?php echo $_GET['type'];?>"<?php }?>>
                    <option value="">请选择</option>
                    <option value="weixin">微信</option>
                    <option value="app">app</option>
                    </select>
                </th>
                <th>开始时间<input type="text" id="start_time" name="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></th>
                <th>结束时间<input type="text" id="end_time" name="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></th>
                <th>状态
                    <select name="banned" class="input-medium" <?php if(isset($_GET['banned'])){?>select="<?php echo $_GET['banned'];?>"<?php }?>>
                    <option value="">请选择</option>
                    <option value="0">未禁止</option>
                    <option value="1">已禁止</option>
                    </select>
                </th>
                <th>搜索结果：<font color="red"><?php echo $counts;?></font>条</th>
                <th colspan="2">
                    <button type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;&nbsp;查询</button>
                    <a id="reset" class="btn btn-info"><i class="icon-refresh icon-white"></i>&nbsp;&nbsp;重置</a>
                </th>
                <script type="text/javascript">
                    $("select[select]").each(function(e,obj){
                        $(this).val($(this).attr('select'));
                    })
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
            <th width="10%">手机号码</th>
            <th>注册时间</th>
            <th>用户角色</th>
            <th>状态</th>
            <th>注册途径</th>
            <th>来源</th>
            <th>真实性别</th>
            <th>操作</th>
            <th>审核</th>

        </tr>
        <?php if(is_array($users)):?><?php  foreach($users as $user){ ?> 
        <tr>
            <td><input type="checkbox" class="input-checkbox" name="uid" value="<?php echo $user['uid'];?>"> </td>
            <td><?php echo $user['username'];?><span class="tips">[ID:<?php echo $user['uid'];?>]</span></td>
            <td><?php echo date('Y-m-d H:i:s',$user['created']);?></td>
            <td><?php echo $user['hp_role'][0]['title'];?></td>
            <td class="status">
                <?php if($user['verify']==0){?>
                <span class="warning">未认证</span>
                <?php  }elseif($user['verify']==1){ ?>
                <span class="warning">待审核</span>
                <?php  }elseif($user['verify']==3){ ?>
                <span class="success">已通过审核</span>
                <?php  }elseif($user['verify']==2){ ?>
                <span class="warning">未通过审核</span>
                <?php }?>
            </td>
            <td><?php echo $user['type'];?></td> 

            <td>
                <?php if($user['hp_role'][0]['rid']==8){?>
                    <?php if($user['type']){?>
                        <?php if($user['branchname']){?>
                            <?php echo $user['branchname'];?>&nbsp;
                        <?php }?>
                        <?php if($user['salesmanname']){?>
                            <?php echo $user['salesmanname'];?>[ID:<?php echo $user['salesmanid'];?>]&nbsp;
                        <?php }?>
                        <?php if($user['salesmanphoneno']){?>
                            <?php echo $user['salesmanphoneno'];?>
                        <?php }?>   

                        <?php if($user['normalmanid']){?>
                        <?php
                            $normalman = M('user')->where(array('uid'=>$user['normalmanid']))->find();


                        ?>
                            <?php echo $normalman['username'];?>[ID:<?php echo $user['normalmanid'];?>]
                        <?php }?>  
                    <?php }?>
                <?php }?>
            </td>
            <td>
                <?php if($user['gender'] == 0){?>
                <span><font color="blue">男</font></span>
                <?php  }elseif($user['gender'] == 1){ ?>
                <span><font color="#c71585">女</font></span>
                <?php  }elseif($user['gender'] == 3){ ?>
                <span><font color="#006400">暂无</font></span>
                <?php }?>
            <td>
                <?php
                        $groups=array_pop($user);
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
            <td>
                <a <?php if(in_array('3',$rid)){?>href="http://www.hap-job.com/index.php/backend/user/viewUserInfo/id/<?php echo $user['uid'];?>/type/cu"<?php  }else{ ?>href="http://www.hap-job.com/index.php/backend/user/viewUserInfo/id/<?php echo $user['uid'];?>/type/pu"<?php }?>/if} c-name="<?php echo $user['username'];?>" action="view" class="opt-item"><i class="icon-user"></i>查看</a>&nbsp;&nbsp;
                <a href="http://www.hap-job.com/index.php/backend/user/editUserInfoForm/id/<?php echo $user['uid'];?>" c-name="<?php echo $user['username'];?>" action="edit" class="opt-item"><i class="icon-asterisk"></i>修改</a>&nbsp;&nbsp;
                <a href="###" rid="<?php echo implode('#',$rid);?>" uid="<?php echo $user['uid'];?>" username="<?php echo $user['username'];?>" class="config-role" title="配置用户组"><i class="icon-th"></i>用户组</a>
                <a href="http://www.hap-job.com/index.php/backend/user/getUserCommission/id/<?php echo $user['uid'];?>" class="commission" action="edit"><i class="icon-asterisk"></i>佣金转移</a>
                
                <?php if($user['hp_role'][0]['rid']==7){?>
                    <?php if(!$user['qrcode']){?>
                        &nbsp;&nbsp;<a href="http://www.hap-job.com/index.php/backend/user/getRecruitQR/id/<?php echo $user['uid'];?>" ><i class="icon-pi  cture"></i>生成二维码</a> 
                    <?php }?>
                    <?php if($user['qrcode']){?>
                        &nbsp;&nbsp;<a href="http://www.hap-job.com/<?php echo $user['qrcode'];?>"  target="_blank" ><i class="icon-picture"></i>查看二维码</a>
                    <?php }?>
                <?php }?>
            </td>
            <td>
                <a href="http://www.hap-job.com/index.php/backend/user/verifyRole?name=3&uid=<?php echo $user['uid'];?>">审核通过</a>
                <a href="http://www.hap-job.com/index.php/backend/user/verifyRole?name=2&uid=<?php echo $user['uid'];?>">审核未通过</a>
            </td>


        </tr>
        <?php }?><?php endif;?>
        <tr class="well">
            <td><input type="checkbox" class="select-all input-checkbox"></td>
            <td class="opt" colspan="2">
                <a href="#" class="user_message" id="user_message">发送消息</a>

            </td>

            <td colspan="4">
                <?php echo $page;?>
            </td>
        </tr>
    </table>
</div>
<div id="config-role" title="配置用户角色" class="hide">
    <form action="http://www.hap-job.com/index.php/backend/user/configUserRole" method="post">
        <table class="table">
            <tr>
                <th>用户：</th>
                <td>
                    <span class="user"></span>
                </td>
            </tr>
            <tr>
                <?php
                        $l_role=formatLevelData2($role_list,array('rid','pid'));
                        function bulid_role($role)
                        {
                            $str='<ul>';
                foreach ($role as $value) {
                if(!empty($value['son_data'])){
                $str.='<li><label class="checkbox"><input type="checkbox" class="input-checkbox" name="rid[]" value="'.$value['rid'].'" />'.$value['title'].'</label>'.bulid_role($value['son_data']).'</li>';
                }else{
                $str.='<li><label class="checkbox"><input type="checkbox" class="input-checkbox" name="rid[]" value="'.$value['rid'].'">'.$value['title'].'</label></li>';
                }
                }
                $str.='</ul>';
                return $str;
                }
                ?>
                <th>角色：</th>
                <style type="text/css">
                    .role-level ul{
                        list-style: none;
                    }
                </style>
                <td class="role-level">
                    <input type="hidden" name="uid" id="config-uid" />
                    <?php echo bulid_role($l_role);?>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="sendMsg" title="发送消息" class="hide">
    <div id="tabs">

        <form class="well" style="margin-top:10px;" id="message_form" action="http://www.hap-job.com/index.php/backend/user/send_message" method="post" validate="true">

            <style>
                .th_width{width: 66px;display: block;}
            </style>
            <table>

                <tr>
                    <th class="th_width">消息内容：</th>
                    <td><textarea name="content" rows="2" cols="20" id="content"></textarea></td>
                    <td><input type="hidden" name="uids" value="" id="message_content"></td>
                </tr>

            </table>

        </form>
    </div>
</div>
<div id="dialog"></div>
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

    $('.opt a').click(function(){
        var _id_arr=[],

                _checked=$('.table :checkbox[name]:checked');
        if(_checked.length==0){
            alert("请选择你需要操作的用户!");
            return false;
        }
        _checked.each(function(){
            _id_arr.push($(this).val());
        });

        return false;
    });
    $('.opt-item').click(function() {
        var _this = $(this),
                _name = _this.attr('c-name'),
                _id = _this.attr('href');
        $.post($(this).attr('href'), function (data) {
            $('#dialog').html(data);
            $('#dialog').dialog({
                "title": _name + "用户资料",
                "width": "500",
                "height": "auto",
                "modal": true,
                "buttons": {
                    "确定": function () {
                        if (_this.attr('action') == 'view') {
                            $(this).dialog("close");
                        } else {
                            $('#dialog form').submit();
                        }
                    }
                }
            });
        }, 'html');
        return false;
    });
    /*
     佣金转移
     */
    $('.commission').click(function() {
        var _this = $(this),
                _name = _this.attr('c-name'),
                _id = _this.attr('href');
        $.post($(this).attr('href'), function (data) {
            $('#dialog').html(data);
            $('#dialog').dialog({
                "title": "佣金转移",
                "width": "500",
                "height": "auto",
                "modal": true,
                "buttons": {
                    "确定": function () {
                        if (_this.attr('action') == 'view') {
                            $(this).dialog("close");
                        } else {
                            $('#dialog form').submit();
                        }
                    }
                }
            });
        }, 'html');
        return false;
    });

    /**
     * 发消息
     */
    $('.user_message').click(function() {
        var _id_arr=[],
                _checked=$('.table :checkbox[name]:checked');

        _checked.each(function(){
            _id_arr.push($(this).val());
        });
        if(_checked.length==0){

            return false;
        }
        var uids = document.getElementById('message_content');

        uids.value = _id_arr;

        $('#dialog').html($('#sendMsg').html());
        $('#dialog').dialog({
            "width":"500",
            "height":"auto",
            "modal":true,
            "title":"添加栏目",
            "buttons":{
                "发送":function () {

                    var _content = $("#dialog #content").val();

                    var _content1 = $.trim(_content);

                    if(_content1==''){
                        alert('请输入消息内容');
                        return false;
                    }
                    $('#dialog #message_form').submit();
                    $(this).dialog("close");

                },
            }


        });
        return false;
    });

    /**
     * 导出前筛选
     */
    $('#submit').click(function(){
        var start_time = $('#start_time').val();

        var end_time = $('#end_time').val();

        if(start_time == '' || end_time == ''){

            alert('请在搜索栏输入时间段');

            return false;
        }

        $('#s_time').val(start_time);

        $('#e_time').val(end_time);

        $('#export_users').submit();

        return false;

    });

</script>
<script>
    function is_cheack(k){
        var a = k;
        if(a == ''){
            alert("请先点击生成二维码！");
        }
    }

</script>
</body>
</html>
