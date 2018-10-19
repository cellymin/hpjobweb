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
    <form action="http://www.hap-job.com/index.php/backend/user/insertSalary_20170220" method="post" enctype="multipart/form-data" id="insertCommission" class="well form-horizontal">
        <table border=0 cellspacing=0 cellpadding=0 align=center width="100%">
            <tr>
                <td width=55 height=20 align="center"><input type="hidden" name="MAX_FILE_SIZE" value="2000000">文件： </td>
                <td height="16">
                    <input name="file" type="file"  value="浏览" >
                </td>
                <input name="laid" type="hidden" id="upload_laid">
                <td>
                    <input type="submit" value="导入(新)" name="upload">
                </td>
            </tr>
        </table>
    </form>

    <form action="http://www.hap-job.com/index.php/backend/user/insertSalary" method="post" enctype="multipart/form-data" id="insertCommission" class="well form-horizontal">
        <table border=0 cellspacing=0 cellpadding=0 align=center width="100%">
            <tr>
                <td width=55 height=20 align="center"><input type="hidden" name="MAX_FILE_SIZE" value="2000000">文件： </td>
                <td height="16">
                    <input name="file" type="file"  value="浏览" >
                </td>
                <input name="laid" type="hidden" id="upload_laid">
                <td>
                    <input type="submit" value="导入(旧)" name="upload">
                </td>
            </tr>
        </table>
    </form>
    <form action="http://www.hap-job.com/index.php/backend/user/salary" method="get" id="search-form">
        <table class="table well" style="margin-bottom:-18px;">
            <tr>
                <th></th>
                <th>姓名</th>
                <th>公司名称</th>
                <th>身份证号</th>
                <th>工号</th>
                <th>工资月份</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <td></td>
                <td><input type="text" name="name" class="input-medium" value="<?php if(isset($_GET['name'])){?><?php echo urldecode($_GET['name']);?><?php }?>"></td>
                <td><input type="text" name="company_name" class="input-medium" value="<?php if(isset($_GET['company_name'])){?><?php echo $_GET['company_name'];?><?php }?>"></td>
                <td><input type="text" name="id_number" class="input-medium" value="<?php if(isset($_GET['id_number'])){?><?php echo $_GET['id_number'];?><?php }?>"></td>
                <td><input type="text" name="job_number" class="input-medium" value="<?php if(isset($_GET['job_number'])){?><?php echo $_GET['job_number'];?><?php }?>"></td>
                <td><input type="text" name="salary_month" class="input-medium" value="<?php if(isset($_GET['salary_month'])){?><?php echo $_GET['salary_month'];?><?php }?>"></td>
                <td><input type="text" name="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
                <td><input type="text" name="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></td>
                <td colspan="2">
                    <button type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;&nbsp;查询</button>
                    <a id="reset" class="btn btn-info"><i class="icon-refresh icon-white"></i>&nbsp;&nbsp;重置</a>
                </td>
                <td></td>
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
            <th>记录id</th>
            <!--<th>用户id</th>-->
            <th>姓名</th>
            <!--<th>电话号码</th>-->
            <th>公司名称</th>
            <th>身份证号</th>
            <th>工号</th>
            <th>工资月份</th>
            <!--<th>基本工资</th>-->
            <th>应发工资</th>
            <th>扣款合计</th>
            <th>实发工资</th>
            <!--<th>年资</th>-->
            <!--<th>全勤奖</th>-->
            <!--<th>补贴合计</th>-->
            <!--<th>交通补贴</th>-->
            <!--<th>住房补贴</th>-->
            <!--<th>加班合计</th>-->
            <!--<th>中夜班费</th>-->
            <!--<th>奖金</th>-->
            <!--<th>季度奖</th>-->
            <!--<th>年终奖</th>-->
            <!--<th>补上月</th>-->
            <!--<th>档案费1</th>-->
            <!--<th>档案费2</th>-->
            <!--<th>缺勤扣款</th>-->
            <!--<th>其他扣款</th>-->
            <!--<th>代扣外包费</th>-->
            <!--<th>个社</th>-->
            <!--<th>公积金</th>-->
            <!--<th>住宿费</th>-->
            <!--<th>个税</th>-->
            <!--<th>工会费</th>-->
            <!--<th>开卡费</th>-->
        </tr>
        <?php if(is_array($salary)):?><?php  foreach($salary as $salary){ ?>
        <tr>
            <td><input type="checkbox" class="input-checkbox" name="id" value="<?php echo $salary['id'];?>"> </td>
            <td><?php echo $salary['id'];?></td>
            <!--<td><?php echo $salary['uid'];?></td>-->
            <td><?php echo $salary['name'];?></td>
            <!--<td><?php echo $salary['phone'];?></td>-->
            <td><?php echo $salary['company_name'];?></td>
            <td><?php echo $salary['id_number'];?></td>
            <td><?php echo $salary['job_number'];?></td>
            <td><?php echo $salary['salary_month'];?></td>
            <!--<td><?php echo $salary['basic_salary'];?></td>-->
            <td><?php echo $salary['should_salary'];?></td>
            <td><?php echo $salary['withhold_count'];?></td>
            <td><?php echo $salary['really_salary'];?></td>
            <td><a href="http://www.hap-job.com/index.php/backend/user/more_salary?id=<?php echo $salary['id'];?>">查看更多</a></td>
            <!--<td><?php echo $salary['seniority'];?></td>-->
            <!--<td><?php echo $salary['attendance_bonus'];?></td>-->
            <!--<td><?php echo $salary['subsidy_count'];?></td>-->
            <!--<td><?php echo $salary['subsidy_transportation'];?></td>-->
            <!--<td><?php echo $salary['subsidy_house'];?></td>-->
            <!--<td><?php echo $salary['overtime_count'];?></td>-->
            <!--<td><?php echo $salary['overtime_cost'];?></td>-->
            <!--<td><?php echo $salary['prize'];?></td>-->
            <!--<td><?php echo $salary['qr_prize'];?></td>-->
            <!--<td><?php echo $salary['end_prize'];?></td>-->
            <!--<td><?php echo $salary['last_month'];?></td>-->
            <!--<td><?php echo $salary['file_cost1'];?></td>-->
            <!--<td><?php echo $salary['file_cost2'];?></td>-->
            <!--<td><?php echo $salary['absence_deductions'];?></td>-->
            <!--<td><?php echo $salary['other_deductions'];?></td>-->
            <!--<td><?php echo $salary['outsource_cost'];?></td>-->
            <!--<td><?php echo $salary['jinpo'];?></td>-->
            <!--<td><?php echo $salary['cpf'];?></td>-->
            <!--<td><?php echo $salary['house_cost'];?></td>-->
            <!--<td><?php echo $salary['tax'];?></td>-->
            <!--<td><?php echo $salary['checkoff'];?></td>-->
            <!--<td><?php echo $salary['open_card'];?></td>-->
        </tr>
        <?php }?><?php endif;?>

        <tr class="well">
            <td><input type="checkbox" class="select-all input-checkbox"></td>
            <td class="opt" colspan="2">

                <a class="btn btn-mini btn-danger" action="del"><i class="icon-trash icon-white"></i> 删除</a>
            </td>
            <td colspan="9"><?php echo $page;?></td>
        </tr>
    </table>
</div>
<div id="config-role" title="配置用户角色" class="hide">
    <form action="http://www.hap-job.com/index.php/backend/user/configUserRole" method="post">
        <table class="table">
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
    //删除选中用户
    function delete_salary(arg){
        $.post('http://www.hap-job.com/index.php/backend/user/delete_salary',{id:arg.id},function(data){
            if(data==1){
                arg.checked_obj.parents('tr').fadeOut(function(){
                    arg.checked_obj.parents('tr').remove();
                });
            }
        },'html');
    }
    $('.opt a').click(function() {
        var _id_arr = [],
                _confirm_msg = '删除',
                _action = $(this).attr('action'),
                _fun = "delete_salary",//处理函数
                _type = "",
                _checked = $('#user-list :checkbox[name]:checked');
        if (_checked.length == 0) {
            alert("请选择你需要操作的用户!");
            return false;
        }
        _checked.each(function () {
            _id_arr.push($(this).val());
        });
        if(confirm("确定"+_confirm_msg+"选中用户？")){
            eval(_fun+"({id:_id_arr,checked_obj:_checked,a_type:_action})");
        }
        return false;
        });
    function verify_recruit(arg){
        $.post('http://www.hap-job.com/index.php/backend/user/verifyRecruit',{"recruit_id":arg.id,type:arg.a_type},function(data){
            if(data==1){
                window.location.reload();
            }
        },'html');
    }

    //禁止、解禁用户
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
    function delete_salary(arg){
        $.post('http://www.hap-job.com/index.php/backend/user/delete_salary',{"id":arg.id},function(data){
            if(data==1){
                arg.checked_obj.parents('tr').fadeOut(function(){
                    arg.checked_obj.parents('tr').remove();
                });
            }
        },'html');
    }
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
