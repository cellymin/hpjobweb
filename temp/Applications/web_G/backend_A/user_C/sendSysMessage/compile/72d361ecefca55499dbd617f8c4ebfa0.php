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
    <script type="text/javascript" src="http://www.hap-job.com/web/backend/templates/js/ueditor.config.js"></script>
    <script type="text/javascript" src="http://www.hap-job.com/web/backend/templates/js/ueditor.all.js"></script>
</head>
<body>
    <div>
        <form action="http://www.hap-job.com/index.php/backend/user/sendSysMessage" method="post" validate="true" enctype="multipart/form-data">
                <table>
                    <tr>
                        <th>消息标题:</th>
                        <td><input type="text" name="title" validate="{required:true}" /></td>
                    </tr>
                    <tr>
                        <th>消息内容: </th>
                        <td><textarea name="content" rows="3" validate="{required:true}"/></textarea></td>
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
        (200*200)</td>
                    </tr>
                    <tr>
                        <th>消息分类: </th>
                        <td>
                            <select name="massage_type" class="input">
                                <option value="101">抽奖活动</option>
                                <option value="103">限时抢兑</option>
                                <option value="105">系统消息</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>跳转链接</th>
                        <td><input type="text" name="link_url" /></td>
                    </tr>
                    <tr>
                        <th>隐藏编辑器: </th>
                        <td>
                            <input class="btn" type="button" id="check-jump" value="否">
                        </td>
                    </tr>
                    <tr>
                        <th>自定义跳转页面</th>
                        <td>
                            <script id="editor" type="text/plain" style="width:1024px;height:500px;"></script>
                        </td>
                    </tr>
                    <tr>
                        <th>选择对象&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td>
                            <select name="type" class="input-small">
                                <option value="1">求职者</option>
                                <option value="2">业务员</option>
                                <option value="0">全部用户</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><button id="confirm" class="btn" type="submit">发送</button></td>
                        <td></td>
                    </tr>
                </table>
        </form>
        <style type="text/css">
            table th{
                width: 95px;
                text-align:right;
            }
        </style>
    </div>

<script type="text/javascript">

    //实例化编辑器
    //建议使用工厂方法getEditor创建和引用编辑器实例，如果在某个闭包下引用该编辑器，直接调用UE.getEditor('editor')就能拿到相关的实例
    var ue = UE.getEditor('editor');


    function isFocus(e){
        alert(UE.getEditor('editor').isFocus());
        UE.dom.domUtils.preventDefault(e)
    }
    function setblur(e){
        UE.getEditor('editor').blur();
        UE.dom.domUtils.preventDefault(e)
    }
    function insertHtml() {
        var value = prompt('插入html代码', '');
        UE.getEditor('editor').execCommand('insertHtml', value)
    }
    function createEditor() {
        enableBtn();
        UE.getEditor('editor');
    }
    function getAllHtml() {
        alert(UE.getEditor('editor').getAllHtml())
    }
    function getContent() {
        var arr = [];
        arr.push("使用editor.getContent()方法可以获得编辑器的内容");
        arr.push("内容为：");
        arr.push(UE.getEditor('editor').getContent());
        alert(arr.join("\n"));
    }
    function getPlainTxt() {
        var arr = [];
        arr.push("使用editor.getPlainTxt()方法可以获得编辑器的带格式的纯文本内容");
        arr.push("内容为：");
        arr.push(UE.getEditor('editor').getPlainTxt());
        alert(arr.join('\n'))
    }
    function setContent(isAppendTo) {
        var arr = [];
        arr.push("使用editor.setContent('欢迎使用ueditor')方法可以设置编辑器的内容");
        UE.getEditor('editor').setContent('欢迎使用ueditor', isAppendTo);
        alert(arr.join("\n"));
    }
    function setDisabled() {
        UE.getEditor('editor').setDisabled('fullscreen');
        disableBtn("enable");
    }

    function setEnabled() {
        UE.getEditor('editor').setEnabled();
        enableBtn();
    }

    function getText() {
        //当你点击按钮时编辑区域已经失去了焦点，如果直接用getText将不会得到内容，所以要在选回来，然后取得内容
        var range = UE.getEditor('editor').selection.getRange();
        range.select();
        var txt = UE.getEditor('editor').selection.getText();
        alert(txt)
    }

    function getContentTxt() {
        var arr = [];
        arr.push("使用editor.getContentTxt()方法可以获得编辑器的纯文本内容");
        arr.push("编辑器的纯文本内容为：");
        arr.push(UE.getEditor('editor').getContentTxt());
        alert(arr.join("\n"));
    }
    function hasContent() {
        var arr = [];
        arr.push("使用editor.hasContents()方法判断编辑器里是否有内容");
        arr.push("判断结果为：");
        arr.push(UE.getEditor('editor').hasContents());
        alert(arr.join("\n"));
    }
    function setFocus() {
        UE.getEditor('editor').focus();
    }
    function deleteEditor() {
        disableBtn();
        UE.getEditor('editor').destroy();
    }
    function disableBtn(str) {
        var div = document.getElementById('btns');
        var btns = UE.dom.domUtils.getElementsByTagName(div, "button");
        for (var i = 0, btn; btn = btns[i++];) {
            if (btn.id == str) {
                UE.dom.domUtils.removeAttributes(btn, ["disabled"]);
            } else {
                btn.setAttribute("disabled", "true");
            }
        }
    }
    function enableBtn() {
        var div = document.getElementById('btns');
        var btns = UE.dom.domUtils.getElementsByTagName(div, "button");
        for (var i = 0, btn; btn = btns[i++];) {
            UE.dom.domUtils.removeAttributes(btn, ["disabled"]);
        }
    }

    function getLocalData () {
        alert(UE.getEditor('editor').execCommand( "getlocaldata" ));
    }

    function clearLocalData () {
        UE.getEditor('editor').execCommand( "clearlocaldata" );
        alert("已清空草稿箱")
    }
</script>
<script type="text/javascript">
    $("#check-jump").click(function(){
        if($(this).val()=="否"){
            $(this).attr("value","是");
            UE.getEditor('editor').setHide();
        }else {
            $(this).attr("value","否");
            UE.getEditor('editor').setShow();
        }
    });
    $("#confirm").click(function(){
        if(confirm('确认发送消息？')){
            return true;
        }else{
            return false;
        }
    });
</script>
</body>
</html>