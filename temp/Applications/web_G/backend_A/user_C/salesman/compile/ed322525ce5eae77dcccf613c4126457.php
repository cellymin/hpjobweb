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
    <form action="http://www.hap-job.com/index.php/backend/user/salesman" method="get" id="search-form">
        <table class="table well" style="margin-bottom:-18px;">
            <tr>
                <th></th>
                <th>用户名</th>
                <th>请输入完整的姓名</th>
                <th>地址</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <td></td>
                <td><input type="text" name="username" class="input-medium" value="<?php if(isset($_GET['username'])){?><?php echo $_GET['username'];?><?php }?>"></td>
                <td><input type="text" name="name" class="input-medium" value="<?php if(isset($_GET['name'])){?><?php echo $_GET['name'];?><?php }?>"></td>
                <td><input type="text" name="content" class="input-medium" value="<?php if(isset($_GET['content'])){?><?php echo urldecode($_GET['content']);?><?php }?>"></td>
                <th><input type="text" name="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></th>
                <th><input type="text" name="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})"></th>
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
            <!--<th>ID</th>-->
            <th>用户id</th>
            <th>用户名</th>
            <th>姓名</th>
            <th>地址</th>
            <th>经度</th>
            <th>纬度</th>
            <th>时间</th>
        </tr>
        <?php if(is_array($salas)):?><?php  foreach($salas as $salas){ ?>
        <tr>
            <!--<td><?php echo $salas['id'];?></td>-->
            <td><?php echo $salas['uid'];?></td>
            <td><?php echo $salas['username'];?></td>
            <td><?php echo $salas['name'];?></td>
            <td><?php echo $salas['content'];?></td>
            <td><?php echo $salas['lng'];?></td>
            <td><?php echo $salas['lat'];?></td>
            <td><?php echo date('Y-m-d H:i:s',$salas['created']);?></td>
        </tr>
        <?php }?><?php endif;?>
        <tr class="well">
            <!--<td><input type="checkbox" class="select-all input-checkbox"></td>-->
            <td class="opt" colspan="2">
                <!--<a class="btn btn-mini btn-danger" action="del"><i class="icon-trash icon-white"></i> 删除</a>-->
                <a class="btn btn-mini btn-info" action="ban"><i class="icon-ban-circle icon-white"></i> 禁止</a>
                <a class="btn btn-mini btn-success" action="unban"><i class="icon-ok icon-white"></i> 解禁</a>
            </td>
            <td colspan="4">
                <?php echo $pages;?>
            </td>
        </tr>

    </table>
</div>

<!--<div id="dialog" style="z-idnex:999"></div>-->

</body>
</html>
