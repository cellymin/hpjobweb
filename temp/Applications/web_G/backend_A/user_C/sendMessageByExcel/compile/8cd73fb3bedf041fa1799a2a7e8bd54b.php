<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE HTML>
<html lang="en-US" xmlns="http://www.w3.org/1999/html">
<head>
<meta charset="UTF-8">
<title></title>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/public.css"/>
<link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-ui-1.8.21.custom.min.js"></script>
</head>
<body>


<div id="dialog" class="hide" title="">
	<img src="" alt="">
</div>

<div id="insertUserFrom" >
    <form action="http://www.hap-job.com/index.php/backend/user/sendMessageByExcel" method="post" enctype="multipart/form-data" id="insert_recruits" class="well form-horizontal">
        <table border=0 cellspacing=0 cellpadding=0 align=center width="100%">
            <tr>
                <td width=100 height=50 align="center"><input type="hidden" name="MAX_FILE_SIZE" value="2000000">文件： </td>
                <td height="18">
                    <input name="file" type="file"  value="浏览" >
                </td>
            </tr>
            <tr>
                <th>消息标题</th>
                <td><input type="text" name="title" /></td>
            </tr>

            <tr>
                <th>消息logo: </th>
                <td><?php $swfupload_path="http://www.hap-job.com/lcphp/org/swfupload250"?><?php $url="http://www.hap-job.com/index.php/backend/user/swfupload"?><?php $delurl="http://www.hap-job.com/index.php/backend/user/swfuploaddel"?><?php $size="2MB"?><?php $water_on="0"?><?php $upload_display_width="200"?><?php $upload_display_height="200"?><?php $imagesize=""?><?php $swfupload_size="2000000"?><?php $limit="1"?><?php $text="选择配图"?><?php $dir="uploads/message/img"?><?php $session_name="sessionid"?><?php $session_id="4lm6q1rj9ifu0fpq9g9n64bv22"?><?php $type="*.jpg;*.jpeg;*.png;*.gif"?><?php $swfupload_id=isset($swfupload_id)?++$swfupload_id:0?><?php $input_hidden="path"?><?php if($swfupload_id==0):?>
<link href='http://www.hap-job.com/lcphp/org/swfupload250/css/default.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='http://www.hap-job.com/lcphp/org/swfupload250/js/handlers.js'></script>
<script type='text/javascript' src='http://www.hap-job.com/lcphp/org/swfupload250/swfupload/swfupload.js'></script>
<script type='text/javascript' src='http://www.hap-job.com/lcphp/org/swfupload250/swfupload/swfupload.queue.js'></script>
<script type='text/javascript' src='http://www.hap-job.com/lcphp/org/swfupload250/js/fileprogress.js'></script>
  <script type='text/javascript' src='http://www.hap-job.com/lcphp/org/swfupload250/lc_set.js'></script>
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
                <th>消息内容&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <td><textarea name="content" id="content" rows="6" cols="60"></textarea></td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="确认发送" name="upload">
                </td>
            </tr>
        </table>
    </form>
</div>

<script type="text/javascript">
    $("#confirm").click(function(){
        if(confirm('确认发送消息？')){
            return true;
        }else{
            return false;
        }
    });

    $("#insert").click(function(){
        $("#insertUserFrom").dialog({
            //modal:true,
            title:'导入用户',
            resizable: true,
            width:450

        });
        $("#upload #title").val('').focus();
        return false;
    });
</script>
</body>
</html>