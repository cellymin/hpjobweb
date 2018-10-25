<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
        <link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
        <link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/web/backend/templates/css/public.css"/>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jqueryValidate/jquery.validate.min.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jqueryValidate/jquery.metadata.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jquery-ui-1.8.21.custom.min.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/My97DatePicker/WdatePicker.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/caches/js/linkage_data.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/linkage/linkage_style_1.js"></script>
    </head>
    <body>
        <div id="jquery-colour-picker" class="hide"></div>
        <div id="tabs">
            <ul>
                <li><a href="#tabs-2">广告列表</a></li>
                <li><a href="#tabs-1">添加广告</a></li>
                <li><a href="#tabs-3">广告位管理</a></li>
                <li><a href="#tabs-4">添加广告位</a></li>
            </ul>
            <div id="tabs-1">
                <div id="add-form">
                    <form action="http://www.192.168.3.131/hpjobweb/index.php/backend/ads/addAds" method="post" validate="true" enctype="multipart/form-data">
                        <table>
                            <tr>
                                <th>广告标题：</th>
                                <td><input type="text" name="ads_title" validate="{required:true}" /></td>
                            </tr>
                            <tr>
                                <th>位置：</th>
                                <td><select name="cate" id="cate" validate="{required:true,messages:'必须选择广告投放位置'}">
                                        <option value="">请选择</option>
                                        <?php if(is_array($cates)):?><?php  foreach($cates as $cate){ ?>
                                        <option cate="cate_c_<?php echo $cate['type'];?>" value="<?php echo $cate['id'];?>"><?php echo $cate['title'];?></option>
                                        <?php }?><?php endif;?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>开始时间：</th>
                                <td><input type="text" name="starttime" class="input-medium" onfocus="WdatePicker({minDate:'%yyyy-%MM-%dd %HH:%mm:%ss',alwaysUseStartDate:true,dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true,vel:'starttime'})" /></td>
                            </tr>
                            <tr>
                                <th>结束时间：</th>
                                <td><input type="text" name="endtime" class="input-medium" onfocus="WdatePicker({minDate:'%yyyy-%MM-%dd %HH:%mm:%ss',alwaysUseStartDate:true,dateFmt:'yyyy-MM-dd HH:mm:ss',autoPickDate:true,vel:'endtime'})" /></td>
                            </tr>
                            <tr>
                                <th>排序：</th>
                                <td><input type="text" name="sort" value="0" class="input-mini" validate="{digits:true}" /></td>
                            </tr>
                            <tr>
                                <th>状态：</th>
                                <td>
                                    <label><input type="radio" name="state" value="1" checked />开启</label>
                                    <label><input type="radio" name="state" value="0" />关闭</label>
                                </td>
                            </tr>
                            <tr>
                                <th>用户ID：</th>
                                <td><input type="text" name="uid" id="" class="input-mini"></td>
                            </tr>
                            <tr>
                                <th>投放城市：</th>
                                <td>
                                    <select style="margin-right:3px;" id="provice" name="provice" class="input-medium"></select><script>$(function(){$("#provice").linkage_style_1({
                data:city,
                field:'provice#city',
                html_attr:'class="input-medium"'
                })});</script>
                                </td>
                            </tr>
                        </table>
                        <table class="cate_c_1">
                            <tr class="cate-title">
                                <td colspan="2"><span  class="alert alert-info" style="display:block;width:100%">文字广告</span></td>
                            </tr>
                            <tr>
                                <th>文字内容：</th>
                                <td><textarea name="text" rows="3" /></textarea></td>
                            </tr>
                            <tr>
                                <th>链接地址：</th>
                                <td><input type="text" name="href" id="" /></td>
                            </tr>
                            <tr>
                                <th>颜色：</th>
                                <td>
                                   <input type="text" name="color" />
                                </td>
                            </tr>
                        </table>
                        <table class="cate_c_2 hide">
                            <tr class="cate-title">
                                <td colspan="2"><span  class="alert alert-info" style="display:block;width:100%">图片广告</span></td>
                            </tr>
                            <tr>
                                <th>上传图片：</th>
                                <td><?php $swfupload_path="http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250"?><?php $url="http://www.192.168.3.131/hpjobweb/index.php/backend/ads/swfupload"?><?php $delurl="http://www.192.168.3.131/hpjobweb/index.php/backend/ads/swfuploaddel"?><?php $size="2MB"?><?php $water_on="0"?><?php $upload_display_width="200"?><?php $upload_display_height="200"?><?php $imagesize=""?><?php $swfupload_size="2000000"?><?php $limit="1"?><?php $text="点击上传"?><?php $dir="uploads/ads"?><?php $session_name="sessionid"?><?php $session_id="kohut8l8c3ukqoque7eau4pm56"?><?php $type="*.jpg;*.png;*.gif"?><?php $swfupload_id=isset($swfupload_id)?++$swfupload_id:0?><?php $input_hidden="path"?><?php if($swfupload_id==0):?>
<link href='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/css/default.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/js/handlers.js'></script>
<script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/swfupload/swfupload.js'></script>
<script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/swfupload/swfupload.queue.js'></script>
<script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/js/fileprogress.js'></script>
  <script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/lc_set.js'></script>
<?php endif;?><script type="text/javascript">

    if(typeof swfupload=='undefined'){
        swfupload_file=[];//记录添加或删除文件数据
    }
    $(function(){
        var swfu;
        var settings = {
            flash_url : '<?php echo $swfupload_path; ?>/swfupload/swfupload.swf',
            flash9_url : '<?php echo $swfupload_path; ?>/swfupload/swfupload_fp9.swf',
            upload_url: '<?php echo $url; ?>',
            post_params: {},//POST参数
            file_size_limit : '<?php echo $size; ?>',
            file_types : '<?php echo $type; ?>',
            file_types_description : 'Files:',
            file_upload_limit :<?php echo $limit; ?>,
            file_queue_limit : 0,
            custom_settings : {
                progressTarget : 'fsUploadProgress<?php echo $swfupload_id ?>',
                cancelButtonId : 'btnCancel'
            },
            debug: false,
            button_width: '110',
            button_height: '26',
            button_placeholder_id: 'spanButtonPlaceHolder',
            button_text: '<span class="theFont"><?php echo $text; ?></span>',
            button_text_style: '.theFont { display:block;font-size: 14;font-weight:bold;color:#ffffff; }',
            button_text_left_padding: 30,
            button_text_top_padding: 3,
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
            button_cursor: SWFUpload.CURSOR.HAND,
            swfupload_preload_handler : preLoad,
            swfupload_load_failed_handler : loadFailed,
            file_queued_handler : fileQueued,
            file_queue_error_handler : fileQueueError,
            file_dialog_complete_handler : fileDialogComplete,
            upload_start_handler : uploadStart,
            upload_progress_handler : uploadProgress,
            upload_error_handler : uploadError,
            upload_success_handler : uploadSuccess,
            upload_complete_handler : uploadComplete,
            queue_complete_handler : queueComplete	// Queue plugin event
        };
        settings.post_params['<?php echo $session_name; ?>']='<?php echo $session_id; ?>';
        settings.post_params['dir']='<?php echo $dir; ?>';
        settings.post_params['upload_display_width']='<?php echo $upload_display_width; ?>';
        settings.post_params['upload_display_height']='<?php echo $upload_display_height; ?>';
        settings.post_params['imagesize']='<?php echo $imagesize; ?>';
        //settings.post_params['thumbon1']='< ? php echo $thumbon; ? >';
        settings.post_params['swfupload_size']='<?php echo $swfupload_size; ?>';
        settings.post_params['water_on']='<?php echo $water_on; ?>';
        swfu = new SWFUpload(settings);
        var file_upload={
            "swfuobj":swfu,//上传对象
            "file_upload_limit":swfu.settings.file_upload_limit,//允许上传的总的文件数量，删除操作不会改变
            "file_allow_total":swfu.settings.file_upload_limit,//允许上传的总的文件数量,删除操作会改变
            "file_success_nums":0,//成功上传的文件数量
            "file_nums":0,//已经上传的文件数量，包括删除的文件
            "delurl":"<?php echo $delurl; ?>",
            "input_name":"<?php echo $input_hidden;?>"
        };
        swfupload_file.push(file_upload);
    })
</script>
            <!--上传显示DIV-->
        <div class='fieldset flash' id='fsUploadProgress<?php echo $swfupload_id;?>'>
        </div>

        <div class='swfupload_button'  href='javascript:;'>
            <span id='spanButtonPlaceHolder'></span>
            <i class='ico-btn ib-upload' style='position:absolute;left:10px;top:5px;'></i>
        </div>
        <div id='swfupload_message<?php echo $swfupload_id;?>'></div>
        <div  class='swfupload_file_show' id='swfupload_file_show<?php echo $swfupload_id;?>'>
        <?php if($swfupload_id==0):?>
            <div class='swfupload_input'></div>
            <?php endif;?>
            <ul></ul>
        </div>
        </td>
                            </tr>
                            <tr>
                                <th>或图片地址：</th>
                                <td><input type="text" name="path[web_url]" value="" /><span class="help-block tips">例如：uploads/img/1.jpg 或 http://www.houdunwang.com/images/logo.gif</span></td>
                            </tr>
                            <tr>
                                <th>图片链接：</th>
                                <td>
                                    <input type="text" name="href" value="http://" />
                                </td>
                            </tr>
                            <tr>
                                <th>图片文字：</th>
                                <td>
                                    <input type="text" name="text" />
                                </td>
                            </tr>
                        </table>
                        <table class="cate_c_3 hide">
                            <tr class="cate-title">
                                <td colspan="2"><span class="alert alert-info" style="display:block;width:100%">FLASH广告</span></td>
                            </tr>
                            <tr>
                                <th>上传FLASH：</th>
                                <td><?php $swfupload_path="http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250"?><?php $url="http://www.192.168.3.131/hpjobweb/index.php/backend/ads/swfupload"?><?php $delurl="http://www.192.168.3.131/hpjobweb/index.php/backend/ads/swfuploaddel"?><?php $size="2MB"?><?php $water_on="0"?><?php $upload_display_width="200"?><?php $upload_display_height="200"?><?php $imagesize=""?><?php $swfupload_size="2000000"?><?php $limit="1"?><?php $text="点击上传"?><?php $dir="uploads/ads/flash"?><?php $session_name="sessionid"?><?php $session_id="kohut8l8c3ukqoque7eau4pm56"?><?php $type="*.swf"?><?php $swfupload_id=isset($swfupload_id)?++$swfupload_id:0?><?php $input_hidden="path"?><?php if($swfupload_id==0):?>
<link href='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/css/default.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/js/handlers.js'></script>
<script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/swfupload/swfupload.js'></script>
<script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/swfupload/swfupload.queue.js'></script>
<script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/js/fileprogress.js'></script>
  <script type='text/javascript' src='http://www.192.168.3.131/hpjobweb/lcphp/org/swfupload250/lc_set.js'></script>
<?php endif;?><script type="text/javascript">

    if(typeof swfupload=='undefined'){
        swfupload_file=[];//记录添加或删除文件数据
    }
    $(function(){
        var swfu;
        var settings = {
            flash_url : '<?php echo $swfupload_path; ?>/swfupload/swfupload.swf',
            flash9_url : '<?php echo $swfupload_path; ?>/swfupload/swfupload_fp9.swf',
            upload_url: '<?php echo $url; ?>',
            post_params: {},//POST参数
            file_size_limit : '<?php echo $size; ?>',
            file_types : '<?php echo $type; ?>',
            file_types_description : 'Files:',
            file_upload_limit :<?php echo $limit; ?>,
            file_queue_limit : 0,
            custom_settings : {
                progressTarget : 'fsUploadProgress<?php echo $swfupload_id ?>',
                cancelButtonId : 'btnCancel'
            },
            debug: false,
            button_width: '110',
            button_height: '26',
            button_placeholder_id: 'spanButtonPlaceHolder',
            button_text: '<span class="theFont"><?php echo $text; ?></span>',
            button_text_style: '.theFont { display:block;font-size: 14;font-weight:bold;color:#ffffff; }',
            button_text_left_padding: 30,
            button_text_top_padding: 3,
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
            button_cursor: SWFUpload.CURSOR.HAND,
            swfupload_preload_handler : preLoad,
            swfupload_load_failed_handler : loadFailed,
            file_queued_handler : fileQueued,
            file_queue_error_handler : fileQueueError,
            file_dialog_complete_handler : fileDialogComplete,
            upload_start_handler : uploadStart,
            upload_progress_handler : uploadProgress,
            upload_error_handler : uploadError,
            upload_success_handler : uploadSuccess,
            upload_complete_handler : uploadComplete,
            queue_complete_handler : queueComplete	// Queue plugin event
        };
        settings.post_params['<?php echo $session_name; ?>']='<?php echo $session_id; ?>';
        settings.post_params['dir']='<?php echo $dir; ?>';
        settings.post_params['upload_display_width']='<?php echo $upload_display_width; ?>';
        settings.post_params['upload_display_height']='<?php echo $upload_display_height; ?>';
        settings.post_params['imagesize']='<?php echo $imagesize; ?>';
        //settings.post_params['thumbon1']='< ? php echo $thumbon; ? >';
        settings.post_params['swfupload_size']='<?php echo $swfupload_size; ?>';
        settings.post_params['water_on']='<?php echo $water_on; ?>';
        swfu = new SWFUpload(settings);
        var file_upload={
            "swfuobj":swfu,//上传对象
            "file_upload_limit":swfu.settings.file_upload_limit,//允许上传的总的文件数量，删除操作不会改变
            "file_allow_total":swfu.settings.file_upload_limit,//允许上传的总的文件数量,删除操作会改变
            "file_success_nums":0,//成功上传的文件数量
            "file_nums":0,//已经上传的文件数量，包括删除的文件
            "delurl":"<?php echo $delurl; ?>",
            "input_name":"<?php echo $input_hidden;?>"
        };
        swfupload_file.push(file_upload);
    })
</script>
            <!--上传显示DIV-->
        <div class='fieldset flash' id='fsUploadProgress<?php echo $swfupload_id;?>'>
        </div>

        <div class='swfupload_button'  href='javascript:;'>
            <span id='spanButtonPlaceHolder'></span>
            <i class='ico-btn ib-upload' style='position:absolute;left:10px;top:5px;'></i>
        </div>
        <div id='swfupload_message<?php echo $swfupload_id;?>'></div>
        <div  class='swfupload_file_show' id='swfupload_file_show<?php echo $swfupload_id;?>'>
        <?php if($swfupload_id==0):?>
            <div class='swfupload_input'></div>
            <?php endif;?>
            <ul></ul>
        </div>
        </td>
                            </tr>
                            <tr>
                                <th>或FLASH地址：</th>
                                <td><input type="text" name="path[web_url]" value="" /></td>
                            </tr>
                            <tr>
                                <th>宽：</th>
                                <td><input type="text" name="width" id="" /></td>
                            </tr>
                            <tr>
                                <th>高：</th>
                                <td><input type="text" name="height" id="" /></td>
                            </tr>
                        </table>
                        <script type="text/javascript">
                            $('#cate').change(function(){
                                $('[class^=cate_c_]').hide();
                                $('[class^=cate_c_] input').attr('disabled',true);
                                $('.'+$(this).find(':checked').attr('cate')).show();
                                $('.'+$(this).find(':checked').attr('cate')+' input').attr('disabled',false);
                            });
                        </script>
                        <table>
                            <tr>
                                <th></th>
                                <td><button type="submit" class="btn btn-primary"><i class="icon-plus icon-white"></i> 添加</button></td>
                            </tr>
                        </table>
                    </form>
                </div>
                <style type="text/css">
                    table th{
                        width: 95px;
                        text-align:right;
                    }
                    .cate-title{
                        width: 200px;
                        height: 40px;
                    }
                </style>
            </div>
            <div id="tabs-2">
                <table class="table">
                    <tr>
                        <th>广告标题</th>
                        <th>广告位</th>
                        <th>类型</th>
                        <th>添加时间</th>
                        <th>开始/结束时间</th>
                        <th>状态</th>
                        <th>排序</th>
                        <th>编辑</th>
                    </tr>
                    <?php if(is_array($ads)):?><?php  foreach($ads as $value){ ?>
                    <tr>
                        <td><?php echo $value['ads_title'];?></td>
                        <td><?php echo $value['title'];?><span class="tips">(ID:<?php echo $value['cate'];?>)</span></td>
                        <td><?php echo $ads_type[$value['type']];?></td>
                        <td><?php echo date('Y-m-d H:i:s',$value['addtime']);?></td>
                        <td>
                            <?php if($value['endtime']<time()){?>
                            <span class="warning" title="已过期">
                                 <?php echo date('Y-m-d H:i:s',$value['starttime']);?><br /><?php echo date('Y-m-d H:i:s',$value['endtime']);?>
                            </span>
                            <?php  }else{ ?>
                                 <?php echo date('Y-m-d H:i:s',$value['starttime']);?><br /><?php echo date('Y-m-d H:i:s',$value['endtime']);?>
                            <?php }?>
                        </td>
                        <td>
                            <?php if(!$value['state']){?>
                            <span class="warning">已关闭</span>
                            <?php  }else{ ?>
                            <span class="success">已开启</span>
                            <?php }?>
                        </td>
                        <td><input type="text" name="sort" class="input-mini" value="<?php echo $value['sort'];?>" /></td>
                        <td><a href="http://www.192.168.3.131/hpjobweb/index.php/backend/ads/editAds/id/<?php echo $value['id'];?>"><i class="icon-edit"></i>编辑</a>&nbsp;&nbsp;<a href="<?php echo $value['id'];?>" class="del-ads"><i class="icon-trash"></i>删除</a></td>
                    </tr>
                    <?php }?><?php endif;?>
                    <tr class="well">
                        <td colspan="6"></td>
                        <td><input type="submit" value="排序" class="btn btn-mini btn-primary" /></td>
                        <td></td>
                    </tr>
                </table>
                <style type="text/css">
                    .input-mini{
                        width: 30px;
                        text-align:center;
                    }
                </style>
                <script type="text/javascript">
                    $('.del-ads').click(function(){
                        if(confirm('确认删除？')){
                            var _obj=$(this).parents('tr');
                            $.post(
                            'http://www.192.168.3.131/hpjobweb/index.php/backend/ads/delAds',
                            {id:$(this).attr('href')},
                            function(data) {
                                if(data){
                                    _obj.fadeOut(350);
                                }
                            },'html'
                        );
                        }
                        return false;
                    });
                </script>
            </div>
            <div id="tabs-3">
                <table class="table">
                    <tr>
                        <th>广告位名称</th>
                        <th>调用ID</th>
                        <th>调用名称</th>
                        <th>属性</th>
                        <th>系统</th>
                        <th>编辑</th>
                    </tr>
                    <?php if(is_array($cates)):?><?php  foreach($cates as $cate){ ?>
                    <tr>
                        <td><?php echo $cate['title'];?></td>
                        <td><?php echo $cate['id'];?></td>
                        <td><?php echo $cate['tname'];?></td>
                        <td><?php echo $ads_type[$cate['type']];?></td>
                        <td><?php if($cate['is_sys']){?><span class="sys">系统内置</span><?php  }else{ ?><span>自定义广告位</span><?php }?></td>
                        <td><a href="<?php echo $cate['id'];?>" class="edit-advert"><i class="icon-edit"></i>修改</a><?php if($cate['is_sys']){?>&nbsp;<span class="tips">删除</span><?php  }else{ ?>&nbsp;&nbsp;<a href="<?php echo $cate['id'];?>" class="del-advert">删除</a><?php }?></td>
                    </tr>
                    <?php }?><?php endif;?>
                </table>
                <div id="editAdvert">

                </div>
                <script type="text/javascript">
                    $('.del-advert').click(function(){
                        if(confirm('确认删除？')){
                            var _obj=$(this).parents('tr');
                            $.post(
                            'http://www.192.168.3.131/hpjobweb/index.php/backend/ads/delAdvert',
                            {id:$(this).attr('href')},
                            function(data) {
                                if(data){
                                    _obj.fadeOut(350);
                                }
                            },'html'
                        );}
                        return false;
                    });
                    $('.edit-advert').click(function(){
                        var _id=$(this).attr('href');
                        var _edit=$('#tabs-4').clone();
                        $('tr:last',$(_edit)).html('<input type="hidden" name="id" value="'+_id+'" />').hide();
                        $('tr:eq(0) input',$(_edit)).val($(this).parent().siblings('td:eq(0)').text());
                        $('tr:eq(1) input',$(_edit)).val($(this).parent().siblings('td:eq(2)').text());
                        $('tr:eq(2) label:contains('+$(this).parent().siblings('td:eq(2)').text()+') input',$(_edit)).attr('checked',true);
                        $('form',$(_edit)).attr('action','http://www.192.168.3.131/hpjobweb/index.php/backend/ads/editAdvert');
                        $(_edit).dialog({
                            width:540,title:'修改广告位',modal:true,buttons:{
                                '更新':function(){
                                    var _data=$('form',$(_edit)).serializeArray();
                                    $.post(
                                    'http://www.192.168.3.131/hpjobweb/index.php/backend/ads/editAdvert',
                                    _data,
                                    function(data) {
                                        if(data){
                                            window.location.reload();
                                        }
                                    },'html'
                                );
                                }
                            }
                        })
                        return false;
                    });
                </script>
            </div>
            <div id="tabs-4">
                <form validate="true" action="http://www.192.168.3.131/hpjobweb/index.php/backend/ads/addAdvert" method="post">
                    <table>
                        <tr>
                            <th>广告位名称：</th>
                            <td><input type="text" name="title" id="" validate="{required:true}" /><span class="tips">中文</span></td>
                        </tr>
                        <tr>
                            <th>调用名称：</th>
                            <td><input type="text" name="tname" id="" validate="{required:true,regexp:/^[a-z]+$/i}" /><span class="tips">在模板中调用，只能为字母</span></td>
                        </tr>
                        <tr>
                            <th>广告位类型：</th>
                            <td><?php if(is_array($ads_type)):?><?php  foreach($ads_type as $key=>$type){ ?><label><input type="radio" name="type" value="<?php echo $key;?>" <?php if($key==1){?>checked<?php }?> /><?php echo $type;?></label><?php }?><?php endif;?></td>
                        </tr>
                        <tr class="well">
                            <th></th>
                            <td><input type="submit" value="添加" class="btn btn-primary" /></td>
                        </tr>
                    </table>
                </form>
                <style type="text/css">
                    #tabs-4 table tr{
                        height: 50px;
                        line-height: 50px;
                    }
                    #tabs-4 table th{
                        width: 90px;
                    }
                    table label{
                        width: 70px;
                        float: left;
                        text-indent:0.3em;
                    }
                    table label input{
                        display:block;
                        padding-right:20px;
                        float: left;
                    }
                </style>
            </div>
        </div>
        <script type="text/javascript">
            $('#tabs').tabs({ selected: <?php if(!empty($_GET['action'])){?><?php echo $_GET['action'];?><?php  }else{ ?>0<?php }?>});
        </script>
    </body>
</html>