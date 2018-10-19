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
            <form action="http://www.hap-job.com/index.php/backend/user/groupLists" method="get" id="search-form">
                <table class="table well" style="margin-bottom:-18px;">
                    <tr>
                        <th></th>
                        <th>群名称</th>
                        <th></th>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="text" name="owner" class="input-medium" value="<?php if(isset($_GET['owner'])){?><?php echo $_GET['owner'];?><?php }?>"></td>
                        <td colspan="2">
                            <button type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;&nbsp;查询</button>
                            <a id="reset" class="btn btn-info"><i class="icon-refresh icon-white"></i>&nbsp;&nbsp;重置</a>
                        </td>
                    </tr>
                </table>

            </form>
            <table id="user-list" class="table" style="margin-left: 50px">
            	<tr>
                    <th><input type="checkbox" class="select-all input-checkbox"></th>
            		<th width="12%">群组名</th>
            		<th>创建时间</th>
            		<th>类型</th>
            		<th>操作</th>
                    <th>解散</th>
            	</tr>
            	<?php if(is_array($groups)):?><?php  foreach($groups as $groups){ ?>
            	<tr>
                    <td><input type="checkbox" class="input-checkbox" name="gid" value="<?php echo $groups['gid'];?>"> </td>
            		<td><?php echo $groups['owner'];?> <span class="tips">[ID:<?php echo $groups['gid'];?>]</span></td>
            		<td><?php echo date('Y-m-d H:i:s',$groups['created']);?></td>
            		<td>
                        <?php if($groups['type']==0){?>
                        <span class="success">普通群组</span>
                        <?php  }else{ ?>
                        <span class="warning">推荐群组</span>
                        <?php }?>
                    </td>
            		<td class="type"><a href="http://www.hap-job.com/index.php/backend/user/recommendGroup/gid/<?php echo $groups['gid'];?>" class ="recommend-group" title="设为推荐" value-gid="<?php echo $groups['gid'];?>" ><?php if($groups['type']==0){?>设为推荐群组<?php  }else{ ?>取消推荐</a> <?php }?></td>
                    <td><a href="http://www.hap-job.com/index.php/backend/user/delGroup/group_id/<?php echo $groups['group_id'];?>/gid/<?php echo $groups['gid'];?>" class="del_group">解散该群</a></td>
                    </tr>
                <?php }?><?php endif;?>
                <tr class="well">
                    <td><input type="checkbox" class="select-all input-checkbox"></td>
                    <td class="opt" colspan="4">
                        <a class="btn btn-mini btn-success" action="set-recommend"><i class="icon-ok icon-white"></i> 一键推荐</a>
                        <a class="btn btn-mini btn-danger" action="cancel-recommend"><i class="icon-remove-sign icon-white"></i> 取消推荐</a>
                    </td>
                    <td colspan="4">
                        <?php echo $pages;?>
                    </td>
                    <td></td>
                </tr>

            </table>

            <script type="text/javascript">

                $(".del_group").click(function(){
                    if(confirm('确定解散该群')){
                        return true;
                    }else{
                        return false;
                    }
                });

                $('#reset').click(function(){
                    $('#search-form :input').val('');
                    $('#search-form').submit();
                });

                select_all('user-list');

                function recommend(arg){
                    $.post('http://www.hap-job.com/index.php/backend/user/onekeyRecommend',{"gid":arg.id,type:arg.a_type},function(data){
                        if(data==1){
                            window.location.reload();
                        }
                    },'html');
                }

                $('.opt a').click(function(){
                    var _id_arr=[],
                            _confirm_msg='设成推荐群组',
                            _action=$(this).attr('action'),
                            _fun="contact",//处理函数
                            _checked=$('#user-list :checkbox[name]:checked');
                    if(_checked.length==0){
                        alert("请选择你需要操作的群组!");
                        return false;
                    }
                    _checked.each(function(){
                        _id_arr.push($(this).val());
                    });

                    switch(_action){
                        case "set-recommend":_confirm_msg="设为推荐";_fun="recommend";break;
                        case "cancel-recommend":_confirm_msg="取消推荐";_fun="recommend";break;
                    }

                    if(confirm("确定将选中群组"+_confirm_msg+"？")){
                        eval(_fun+"({id:_id_arr,checked_obj:_checked,a_type:_action})");
                    }
                    return false;
                });

                $("select[select]").each(function(e,obj){
                    $(this).val($(this).attr('select'));
                });
            </script>
        </div>
    </body>
</html>
