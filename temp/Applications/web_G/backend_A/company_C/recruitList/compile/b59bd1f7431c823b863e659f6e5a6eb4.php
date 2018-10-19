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
    <form action="http://www.hap-job.com/index.php/backend/company/recruitList" method="get" id="search-form">
        <table class="table well" style="margin-bottom:-18px;">
            <tr>
                <th></th>
                <th>职位名称</th>
                <th>招聘公司</th>
                <th>开始时间</th>
                <th>结束时间</th>
                <!--<th>更新时间</th>-->
                <th>状态</th>
                <th>审核</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <td></td>
                <td><input type="text" name="recruit_name" class="input-medium" value="<?php if(isset($_GET['recruit_name'])){?><?php echo urldecode($_GET['recruit_name']);?><?php }?>"></td>
                <td><input type="text" name="company_name" class="input-medium" value="<?php if(isset($_GET['company_name'])){?><?php echo urldecode($_GET['company_name']);?><?php }?>"></td>
                <td><input type="text" name="start_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})" value="<?php if(isset($_GET['start_time'])){?><?php echo urldecode($_GET['start_time']);?><?php }?>"></td>
                <td><input type="text" name="end_time" class="input-medium" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true})" value="<?php if(isset($_GET['end_time'])){?><?php echo urldecode($_GET['end_time']);?><?php }?>"></td>
                <td>
                    <select name="state" class="input-mini" <?php if(isset($_GET['state'])){?>select="<?php echo $_GET['state'];?>"<?php }?>>
                    <option value="">请选择</option>
                    <option value="0">开启</option>
                    <option value="1">关闭</option>
                    <option value="2">已过期</option>
                    </select>
                </td>
                <td>
                    <select name="check" id="" class="input-mini" <?php if(isset($_GET['check'])){?>select="<?php echo $_GET['check'];?>"<?php }?>>
                    <option value="">请选择</option>
                    <option value="0">未通过</option>
                    <option value="1">通过</option>
                    <!--<option value="2">审核中</option>-->
                    </select>
                </td>
                <td colspan="2">
                    <button type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;&nbsp;查询</button>
                    <a id="reset" class="btn btn-info"><i class="icon-refresh icon-white"></i>&nbsp;&nbsp;重置</a>
                    <a id="insert" class="btn btn-small btn-primary"><i class="icon-plus-sign icon-white"></i>&nbsp;&nbsp;导入职位</a>
                    <a href="addRecruit.html" id="each_insert" class="btn btn-small btn-primary"><i class="icon-plus-sign icon-white"></i>&nbsp;&nbsp;增加职位</a>
                </td>
                <td></td>
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
    <table class="table" id="recruit-list">
        <tr>
            <!-- recruit_id,recruit_name,created,refresh_date,company_name,state,verify -->
            <th><input type="checkbox" class="select-all input-checkbox"></th>
            <th width="12%">职位名称</th>
            <th width="18%">招聘公司</th>
            <th>发布时间</th>
            <th>到期时间</th>
            <th>投递量</th>
            <!--<th>最后更新</th>-->
            <th>状态</th>
            <th>浏览量</th>
            <th>审核</th>
            <th>企业认证</th>
            <th>操作</th>
        </tr>
        <?php if(is_array($recruits)):?><?php  foreach($recruits as $recruit){ ?>
        <tr>
            <td><input type="checkbox" class="input-checkbox" name="rid" value="<?php echo $recruit['recruit_id'];?>"> </td>
            <td><?php echo $recruit['recruit_name'];?> <span class="tips">[ID:<?php echo $recruit['recruit_id'];?>]</span></td>
            <td><?php echo $recruit['company_name'];?></td>
            <td><?php echo date('Y-m-d H:i:s',$recruit['start_time']);?></td>
            <td><?php echo date('Y-m-d H:i:s',$recruit['expiration_time']);?></td>
            <td><?php echo $recruit['deliver_num'];?></td>
            <!--<td><?php echo date('Y-m-d H:i:s',$recruit['refresh_date']);?></td>-->
            <td>
                <?php if($recruit['state'] && $recruit['expiration_time']>time()){?><span class="success">招聘中</span><?php  }elseif(!$recruit['state']){ ?> <span class="warning">已关闭</span><?php  }else{ ?><span class="warning">已过期</span> <?php }?>
            </td>
            <td><?php echo $recruit['looks'];?></td>
            <td class="status"><?php if($recruit['check']==1){?> <span class="success">通过</span>
                <?php  }elseif($recruit['check']==0){ ?>
                <span class="warning">未通过</span>
                <?php  }else{ ?>
                <span class="sys">审核中</span>
                <?php }?>
            </td>
            <td class="status"><?php if($recruit['verify']==1){?> <span class="success">已认证</span>
                <?php  }elseif($recruit['verify']==0){ ?>
                <span class="warning">未认证</span>
                <?php }?>
            </td>
            <td>
                <!--<a href="http://www.hap-job.com/index.php/index/search/jobs/id/<?php echo $recruit['recruit_id'];?>" target="_blank">查看</a>-->
                <a href="http://www.hap-job.com/index.php/backend/company/highSalary/recruit_id/<?php echo $recruit['recruit_id'];?>" class ="high-salary" title="设为热门" value-id="<?php echo $recruit['recruit_id'];?>" ><?php if($recruit['high_salary']==0){?>设为热门<?php  }else{ ?><span class="warning">取消热门</span></a> <?php }?>
                <a href="http://www.hap-job.com/index.php/backend/company/isTop/recruit_id/<?php echo $recruit['recruit_id'];?>" class ="istop" title="设为置顶" value-id="<?php echo $recruit['recruit_id'];?>" ><?php if($recruit['istop']==0){?>设为置顶<?php  }else{ ?>取消置顶</a> <?php }?>
                <a href="http://www.hap-job.com/index.php/backend/company/editRecruit/recruit_id/<?php echo $recruit['recruit_id'];?>" title="修改职位信息" >修改</a>
                <a href="javascript:void (0)" class="code" value-id="<?php echo $recruit['recruit_id'];?>">查看二维码</a>
                <!-- <a href="">删除</a> -->
                <a href="http://www.hap-job.com/index.php/backend/company/pushRecruit/id/<?php echo $recruit['recruit_id'];?>" title="推送职位" class="opt-edit" >推送</a>
            </td>
        </tr>
        <?php }?><?php endif;?>
        <tr class="well">
            <td><input type="checkbox" class="select-all input-checkbox"></td>
            <td class="opt" colspan="4">
                <a class="btn btn-mini btn-danger" action="del"><i class="icon-trash icon-white"></i> 删除</a>
                <a class="btn btn-mini btn-info" action="close"><i class="icon-remove-sign icon-white"></i> 关闭</a>
                <a class="btn btn-mini btn-success" action="enable"><i class="icon-ok icon-white"></i> 开启</a>
                <a class="btn btn-mini btn-info" action="verify-unpass"><i class="icon-ban-circle icon-white"></i> 未通过</a>
                <a class="btn btn-mini btn-success" action="verify-pass"><i class="icon-ok icon-white"></i> 通过</a>
                <a class="btn btn-mini btn-info" action="high-salary"><i class="icon-ok icon-white"></i> 热门</a>
                <a class="btn btn-mini btn-success" action="is-top"><i class="icon-ok icon-white"></i> 置顶</a>
                <a class="btn btn-mini btn-danger" action="not-high"><i class="icon-remove-sign icon-white"></i> 取消热门</a>
                <a class="btn btn-mini btn-danger" action="not-top"><i class="icon-remove-sign icon-white"></i> 取消置顶</a>
                <!--<a class="btn btn-mini btn-success" action="push-user"><i class="icon-ok icon-white"></i> 推送</a>-->
            </td>
            <td colspan="4">
                <?php echo $page;?>
            </td>
            <td></td>
        </tr>
    </table>
</div>

<div id="insertRecruits" style="display: none">
    <form action="http://www.hap-job.com/index.php/backend/company/insertRecruits" method="post" enctype="multipart/form-data" id="insert_recruits" class="well form-horizontal">
        <table border=0 cellspacing=0 cellpadding=0 align=center width="100%">
            <tr>
                <td width=55 height=20 align="center"><input type="hidden" name="MAX_FILE_SIZE" value="2000000">文件： </td>
                <td height="16">
                    <input name="file" type="file"  value="浏览" >
                </td>
                <input name="laid" type="hidden" id="upload_laid">
                <td>
                    <input type="submit" value="导入" name="upload">
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="showQR" style="display: none">
    <img id="qr" >
</div>

<div id="dialog" style="z-idnex:999"></div>
<script type="text/javascript">
    select_all('recruit-list');
    //删除选中职位
    function del_recruit(arg){
        $.post('http://www.hap-job.com/index.php/backend/company/delRecruit',{"recruit_id":arg.id},function(data){
            if(data==1){
                arg.checked_obj.parents('tr').fadeOut(function(){
                    arg.checked_obj.parents('tr').remove();
                });
            }
        },'html');
    }
    //导入职位
    $("#insert").click(function(){
        $("#insertRecruits").dialog({
            //modal:true,
            title:'导入职位',
            resizable: true,
            width:450

        });
        $("#upload #title").val('').focus();
        return false;
    });

    //生成二维码
    $(".code").click(function(){
        var _id = $(this).attr('value-id');
        $('#qr').attr('src','http://www.hap-job.com/index.php/backend/company/getRecruitQR/recruitId/'+_id);
        $("#showQR").dialog({
            //modal:true,
            title:'查看二维码',
            resizable: true,
            width:450,
            buttons:{
                '确定':function(){

                    $('#qr').attr('src','http://www.hap-job.com/index.php/backend/company/getRecruitQR/recruitId/'+_id);
                }
            }
        });

    });
    //开启、审核职位
    function verify_recruit(arg){
        $.post('http://www.hap-job.com/index.php/backend/company/verifyRecruit',{"recruit_id":arg.id,type:arg.a_type},function(data){
            if(data==1){
                window.location.reload();
            }
        },'html');
    }
    $('.opt a').click(function(){
        var _id_arr=[],
                _confirm_msg='删除',
                _action=$(this).attr('action'),
                _fun="del_recruit",//处理函数
                _type="",
                _checked=$('#recruit-list :checkbox[name]:checked');
        if(_checked.length==0){
            alert("请选择你需要操作的职位!");
            return false;
        }
        _checked.each(function(){
            _id_arr.push($(this).val());
        });
        switch(_action){
            case "close":_confirm_msg="关闭";_fun="verify_recruit";break;
            case "enable":_confirm_msg="开启";_fun="verify_recruit";break;
            case "verify-pass":_confirm_msg="审核通过";_fun="verify_recruit";break;
            case "verify-unpass":_confirm_msg="不通过";_fun="verify_recruit";break;
            case "high-salary":_confirm_msg="设为热门";_fun="verify_recruit";break;
            case "is-top":_confirm_msg="置顶";_fun="verify_recruit";break;
            case "not-high":_confirm_msg="取消热门";_fun="verify_recruit";break;
            case "not-top":_confirm_msg="取消置顶";_fun="verify_recruit";break;
//            case "push-user":_confirm_msg="推送";_fun="verify_recruit";break;
        }
        if(confirm("确定"+_confirm_msg+"选中职位？")){
            eval(_fun+"({id:_id_arr,checked_obj:_checked,a_type:_action})");
        }
        return false;
    });
    $('.opt-edit').click(function() {
        var _this = $(this);
        $.get($(this).attr('href'), function (data) {
            $('#dialog').html(data);
            $('#dialog').dialog({
                "title": "修改信息",
                "width": "470",
                "height": "452",
                "modal": true,
                "buttons": {
                    "确定": function () {
                        $("#dialog form").submit();
                    }
                }
            });
        }, 'html');
        return false;
    });
</script>
</body>
</html>
