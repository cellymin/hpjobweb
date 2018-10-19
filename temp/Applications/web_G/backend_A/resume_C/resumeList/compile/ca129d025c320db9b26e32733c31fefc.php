<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
        <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/public.css"/>
        <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-ui-1.8.21.custom.min.js"></script>
    </head>
    <body>
        <div id="userList">
            <form action="http://www.hap-job.com/index.php/backend/resume/resumeList" method="get" id="search-form">
                <table class="table well" style="margin-bottom:-18px;">
                    <tr>
                        <th></th>
                        <th>联系电话</th>
                        <th>创建者</th>
                        <th>发布时间</th>
                        <th>更新时间</th>
                        <th>审核</th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                <tr>
                    <td></td>
                    <td><input type="text" name="telephone" class="input-medium" value="<?php if(isset($_GET['telephone'])){?><?php echo $_GET['telephone'];?><?php }?>"></td>
                    <td><input type="text" name="name" class="input-medium" value="<?php if(isset($_GET['name'])){?><?php echo $_GET['name'];?><?php }?>"></td>
                    <td>
                        <select name="created" class="input-medium" style="z-index:99" <?php if(isset($_GET['created'])){?>select="<?php echo $_GET['created'];?>"<?php }?>>
                            <option value="">请选择</option>
                            <option value="-1week">1星期内</option>
                            <option value="-1month">1月内</option>
                            <option value="-2month">2月内</option>
                            <option value="-3month">3月内</option>
                        </select>
                    </td>
                    <td>
                        <select name="updated" class="input-medium" style="z-index:99" <?php if(isset($_GET['updated'])){?>select="<?php echo $_GET['updated'];?>"<?php }?>>
                            <option value="">请选择</option>
                            <option value="-1week">1星期内</option>
                            <option value="-1month">1月内</option>
                            <option value="-3month">3月内</option>
                        </select>
                    </td>
                    <td>
                        <select name="verify" id="" class="input-mini" <?php if(isset($_GET['verify'])){?>select="<?php echo $_GET['verify'];?>"<?php }?>>
                            <option value="">请选择</option>
                            <option value="0">未通过</option>
                            <option value="1">通过</option>
                            <option value="2">审核中</option>
                        </select>
                    </td>
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
            <table class="table" id="recruit-list">
            	<tr>

            		<th><input type="checkbox" class="select-all input-checkbox"></th>
                    <td>简历预览</td>
                    <th>创建者</th>
                    <th>手机</th>
                    <th width="18%">创建时间</th>
            		<th>更新时间</th>
                    <th>头像</th>
            		<th>浏览次数</th>
                    <th>是否为默认简历</th>
            		<th>审核</th>
            		<th>操作</th>
            	</tr>
            	<?php if(is_array($resumes)):?><?php  foreach($resumes as $resume){ ?>
            	<tr>
            		<td><input type="checkbox" class="input-checkbox" name="resume_id" value="<?php echo $resume['resume_id'];?>"> </td>
                    <td><a href="http://www.hap-job.com/index.php/app/auth/viewResume/resume_id/<?php echo $resume['resume_id'];?>" target="_blank">查看</a></td>
                    <td><?php echo $resume['name'];?></td>
                    <td><?php echo $resume['telephone'];?></td>
                    <td><?php echo date('Y-m-d H:i:s',$resume['created']);?></td>
                    <td><?php echo date('Y-m-d H:i:s',$resume['updated']);?></td>
                    <td><?php if(!empty($resume['avatar'])){?> <a href="" path="<?php echo $resume['avatar'];?>" class="view-avatar">查看</a> <?php  }else{ ?> <span class="tips">无</span> <?php }?></td>
                    <td><?php echo $resume['views'];?></td>
                    <td><?php if($resume['default']==1){?>
                        <span><font color="green">是</font></span>
                        <?php  }elseif($resume['default']==0){ ?>
                        <span><font color="red">否</font></span>
                        <?php }?>
                    </td>
            		<td class="status"><?php if($resume['verify']==1){?> <span class="success">通过</span><?php  }elseif($resume['verify']==2){ ?> <span class="sys">审核中</span> <?php  }else{ ?> <span class="warning">未通过</span> <?php }?></td>
            		<td class="opt">
                        <a href="<?php echo $resume['resume_id'];?>" action="del" type="item">删除</a>
            		</td>
            	</tr>
            	<?php }?><?php endif;?>
            	<tr class="well">
            		<td><input type="checkbox" class="select-all input-checkbox"></td>
            		<td class="opt" colspan="4">
            			<a class="btn btn-mini btn-danger" action="del"><i class="icon-trash icon-white"></i> 删除</a>
            			<a class="btn btn-mini btn-info" action="verify-unpass"><i class="icon-ban-circle icon-white"></i> 未通过</a>
            			<a class="btn btn-mini btn-success" action="verify-pass"><i class="icon-ok icon-white"></i> 通过</a>
            		</td>
            		<td colspan="4">
            			<?php echo $page;?>
            		</td>
                    <td></td>
            	</tr>
            </table>
        </div>
        <div id="dialog" class="hide"></div>
        <script type="text/javascript">
        $('.view-avatar').click(function(){
            $('#dialog').html('<img src="'+$(this).attr('path')+'" title="有实力、做快乐。欢迎进入快乐论坛学习。">');
            $('#dialog').dialog({
                "title":"查看头像",
                "modal":true,
                "buttons":{
                    "关闭":function(){
                        $(this).dialog('close');
                    }
                }
            });
            return false;
        });
        $('.select-all').click(function(){
        	if($(this).attr('checked')){
        		$('.select-all').attr('checked',true);
        		$('.table :checkbox[name]').attr('checked',true);
        	}else{
        		$('.select-all').attr('checked',false);
        		$('.table :checkbox[name]').attr('checked',false);
        	}
        });
        //删除选中职位
        function del_resume(arg){
        	$.post('http://www.hap-job.com/index.php/backend/resume/delResume',{"resume_id":arg.id},function(data){
                if(data==1){
                    arg.checked_obj.parents('tr').fadeOut(function(){
                        arg.checked_obj.parents('tr').remove();
                    });
                }
            },'html');
        }
        //开启、审核职位
        function verify_resume(arg){
            $.post('http://www.hap-job.com/index.php/backend/resume/verifyResume',{"resume_id":arg.id,type:arg.a_type},function(data){
                if(data==1){
                    window.location.reload();
                }
            },'html');
        }
        $('.opt a').click(function(){
            if($(this).attr('type')=='item'){
                $(this).parent().siblings(':eq(0)').find('input').attr('checked',true);
            }
        	var _id=[],
        	_confirm_msg='删除',
        	_action=$(this).attr('action'),
            _fun="del_resume",//处理函数
            _type="",
        	_checked=$('#recruit-list :checkbox[name]:checked');
        	if(_checked.length==0){
        		alert("请选择你需要操作的简历!");
        		return false;
        	}
        	_checked.each(function(){
        		_id.push($(this).val());
        	});
        	switch(_action){
        		case "verify-pass":_confirm_msg="审核通过";_fun="verify_resume";break;
                case "verify-unpass":_confirm_msg="不通过";_fun="verify_resume";break;
        	}
        	if(confirm("确定"+_confirm_msg+"选中简历？")){
        		eval(_fun+"({id:_id,checked_obj:_checked,a_type:_action})");
        	}else{
                _checked.attr('checked',false);
            }
        	return false;
        });
        </script>
    </body>
</html>
