<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
        <link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
        <link type="text/css" rel="stylesheet" href="http://www.192.168.3.131/hpjobweb/web/backend/templates/css/public.css"/>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jqueryValidate/jquery.validate.min.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jqueryValidate/jquery.metadata.js"></script>
        <script type="text/javascript" src="http://www.192.168.3.131/hpjobweb/public/js/jquery-ui-1.8.21.custom.min.js"></script>
        <style type="text/css">
        .ui-widget-content a{
            color: #08C;
        }
        </style>
    </head>
    <body>
        <?php
        C('DEBUG',0);
        ?>
        <div id="opt">
            <div id="tabs">
                <ul>
                    <li><a href="#spread-list">推广列表</a></li>
                    <li><a href="#add-spread">添加推广</a></li>
                    <li><a href="#spread-cate-list">方案列表</a></li>
                    <!-- <li><a href="#add-spread-cate">添加方案</a></li> -->
                </ul>
                <div id="spread-list">
                    <table class="table">
                        <tr>
                            <th><input type="checkbox" class="select-all input-checkbox"> </th>
                            <th>推广职位</th>
                            <th>公司</th>
                            <th>推广方案</th>
                            <th>企业ID</th>
                            <th>开始时间</th>
                            <th>结束时间</th>
                            <th>操作</th>
                        </tr>
                        <?php if(is_array($spread_lists)):?><?php  foreach($spread_lists as $spread_l){ ?>
                        <tr>
                            <td><input type="checkbox" name="r-id" class="input-checkbox" value="<?php echo $spread_l['id'];?>"></td>
                            <td><a href="http://www.192.168.3.131/hpjobweb/index.php/index/search/jobs/id/<?php echo $spread_l['recruit_id'];?>" target="_blank"><?php echo $spread_l['recruit_name'];?></a></td>
                            <td><a href="http://www.192.168.3.131/hpjobweb/index.php/index/index/company/id/<?php echo $spread_l['uid'];?>" target="_blank"><?php echo $spread_l['company_name'];?></a></td>
                            <td><?php echo $spread_l['cate_name'];?></td>
                            <td><?php echo $spread_l['uid'];?></td>
                            <td><?php echo date('Y-m-d H:i:s',$spread_l['starttime']);?></td>
                            <td><?php echo date('Y-m-d H:i:s',$spread_l['endtime']);?></td>
                            <td>
                                <a href="http://www.192.168.3.131/hpjobweb/index.php/backend/company/editSpread/id/<?php echo $spread_l['id'];?>" class="edit-spread"><i class="icon-edit"></i>修改</a>
                            </td>
                        </tr>
                        <?php }?><?php endif;?>
                        <tr class="well opt">
                            <td><input type="checkbox" class="select-all input-checkbox"></td>
                            <td colspan="2">
                                <a class="btn btn-mini btn-danger" action="del" style="color: #fff;"><i class="icon-trash icon-white"></i> 删除</a>
                            </td>
                            <td colspan="5">
                                
                            </td>
                        </tr>
                    </table>
                    <script type="text/javascript">
                    $('.select-all').click(function(){
                        if($(this).attr('checked')){
                            $('.select-all').attr('checked',true);
                            $('#spread-list :checkbox[name]').attr('checked',true);
                        }else{
                            $('.select-all').attr('checked',false);
                            $('#spread-list :checkbox[name]').attr('checked',false);
                        }
                    });
                    </script>
                </div>
                <div id="add-spread">
                    <div id="search_recruit">
                            <table id="search_form">
                                <tr>
                                    <td colspan="2" class="do-title"><h4>查找职位<span style="font-weight:normal;color:#6D6D6D;font-size:10px;">(输入后按回车查询)</span></h4></td>
                                </tr>
                                <tr>
                                    <th class="text-left">通过企业名称</th>
                                    <td><input type="text" class="input-medium" name="company_name" /></td>
                                </tr>
                                <tr>
                                    <th>或通过企业ID&nbsp;&nbsp;&nbsp;</th>
                                    <td><input type="text" class="input-medium" name="uid" validate={digits:true} /></td>
                                </tr>
                                <tr>
                                    <th>或通过职位名称</th>
                                    <td><input type="text" class="input-medium" name="recruit_name" /></td>
                                </tr>
                                <tr>
                                    <th>或通过职位ID&nbsp;&nbsp;&nbsp;</th>
                                    <td><input type="text" class="input-medium" name="recruit_id" validate={digits:true} /></td>
                                </tr>
                            </table>
                        <form validate="true" id="add-s-form" action="http://www.192.168.3.131/hpjobweb/index.php/backend/company/addSpread.html" method="post">
                            <input type="hidden" name="uid" id="ruid" value="">
                            <table>
                                <tr>
                                    <td colspan="2" class="do-title"><h4>选择方案</h4></td>
                                </tr>
                                <tr>
                                    <th>推广天数</th>
                                    <td>
                                        <div class="input-append">
                                            <input class="input-mini" name="days" size="16" type="text" validate={required:true,digits:true}><span class="add-on">天</span>
                                        </div>
                                    </td>
                                <style type="text/css">
                                    .input-append input{
                                        float: left;
                                    }
                                    .add-on{
                                        display: block;
                                        float: left;
                                        border: 1px solid #CCC;
                                        width: 30px;
                                        height: 28px;
                                        text-align:center;
                                        border-radius:2px;
                                        line-height: 28px;
                                        background: #EEE;
                                    }
                                </style>
                                </tr>
                                <tr>
                                    <th>推广方案</th>
                                    <td>
                                        <select name="cate_id" id="select-spread-cate" class="input-small" validate={required:true}>
                                            <option value="">请选择</option>
                                            <?php if(is_array($spreads)):?><?php  foreach($spreads as $spread){ ?>
                                            <option value="<?php echo $spread['id'];?>"><?php echo $spread['cate_name'];?></option>
                                            <?php }?><?php endif;?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="picker-color" style="display:none;">
                                    <th>颜色代码</th>
                                    <td>
                                        <input type="text" name="color" id="color" class="input-medium" disabled="disabled" />
                                        <p>例如：#F00 、red</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th><input type="hidden" name="recruit_id" id="s-rid"></th>
                                    <td><input type="submit" class="btn btn-primary" value="添加推广" /></td>
                                </tr>
                            </table>
                            </form>
                    </div>
                    <div id="recruit_list">

                    </div>
                </div>
                <style type="text/css">
                    #search_recruit{
                        width: 320px;
                        border:1px solid #BCBCBC;
                        float: left;
                    }
                    #search_recruit table th{
                        width: 120px;
                        padding-right: 10px;
                        text-align: right;
                        font-weight:normal;
                    }
                    #search_recruit table .text-left{
                        text-align: center;
                    }
                    #search_recruit table .do-title{
                        padding-left: 20px;
                    }
                    #add-spread tr{
                        height: 38px;
                    }
                    #recruit_list{
                        width: 750px;
                        float: left;
                    }
                </style>
                <div id="spread-cate-list">
                    <table class="table table-striped">
                        <tr>
                            <th>方案名称</th>
                            <th>积分</th>
                            <th>状态</th>
                            <th>类型</th>
                            <th>编辑</th>
                        </tr>
                        <?php if(is_array($spreads)):?><?php  foreach($spreads as $spread){ ?>
                        <tr>
                            <td><?php echo $spread['cate_name'];?></td>
                            <td><?php echo $spread['cate_point'];?>/天</td>
                            <td><?php if($spread['state']){?>开启<?php  }else{ ?><span class="warning">禁用</span><?php }?></td>
                            <td><?php if($spread['is_sys']){?><span class="sys">内置</span><?php  }else{ ?>自定义<?php }?></td>
                            <td>
                                <a href="http://www.192.168.3.131/hpjobweb/index.php/backend/company/editSpreadCate/cate/<?php echo $spread['id'];?>.html"><i class="icon-edit"></i>修改</a>
                                <?php if($spread['is_sys']){?><span style="cursor: default" title="系统方案，禁止删除。">删除</span><?php  }else{ ?><a href="http://www.192.168.3.131/hpjobweb/index.php/backend/company/delSpreadCate/cate/<?php echo $spread['id'];?>.html"><i class="icon-trash"></i>删除</a><?php }?>
                            </td>
                        </tr>
                        <?php }?><?php endif;?>
                    </table>
                </div>
                <!-- <div id="add-spread-cate">
                    <form validate="true" action="http://www.192.168.3.131/hpjobweb/index.php/backend/company/addSpreadCate.html" class="well form-horizontal" method="post">
                        <div class="control-group">
                            <label for="cate_name" class="control-label">方案名称</label>
                            <div class="controls">
                                <input type="text" class="input-medium" name="cate_name" id="cate_name" validate={required:true} />
                                <p class="help-block">例如：紧急招聘、招聘变色……</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="cate_minday" class="control-label">最少推广</label>
                            <div class="controls">
                                <div class="input-append">
                                    <input class="input-mini" id="cate_minday" name="cate_minday" type="text" validate={required:true,digits:true}><span class="add-on">天</span>
                                </div>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="cate_maxday" class="control-label">最多推广</label>
                            <div class="controls">
                                <div class="input-append">
                                    <input class="input-mini" id="cate_maxday" name="cate_maxday" type="text" validate={required:true,digits:true}><span class="add-on">天</span>
                                </div>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="cate_point" class="control-label">每天消耗</label>
                            <div class="controls">
                                <div class="input-append">
                                    <input class="input-mini" id="cate_point" name="cate_point" type="text" validate={required:true,number:true}><span class="add-on">积分</span>
                                </div>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="cate_point" class="control-label">最多数量</label>
                            <div class="controls">
                                <input type="text" name="max_nums" class="input-medium" id="cate_point" validate={required:true,digits:true} />
                                <p class="help-block">0表示无限制。<br />允许此方案添加的最大值。超过此值后企业将无法购买。</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="on" class="control-label">状态</label>
                            <div class="controls">
                                <label for="on" class="radio inline"><input type="radio" checked name="state" id="on" value="1" class="radio" />开启</label>
                                <label for="off" class="radio inline"><input type="radio" name="state" id="off" value="0" class="radio" />禁用</label>
                                <p class="help-block"></p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="textarea">方案介绍</label>
                            <div class="controls">
                                <script type="text/javascript">LC_UEDITOR_ROOT="__LCPHP__/org/ueditor/";</script><script type="text/javascript" src="__LCPHP__/org/ueditor/editor_config.js"></script>
		<script type="text/javascript" src="__LCPHP__/org/ueditor/editor_all.js"></script>
		<link rel="stylesheet" href="__LCPHP__/org/ueditor/themes/default/ueditor.css"><script type="text/plain" name="cate_desc" id="cate_desc" style=" width:340px"></script><script type="text/javascript" >var editorOption = {
                         toolbars:[['Undo', 'Redo','Bold','Italic','Underline','JustifyLeft', 'JustifyCenter', 'JustifyRight','InsertOrderedList','InsertUnorderedList','FormatMatch','Link','Horizontal']],
                         imageUrl:"http://www.192.168.3.131/hpjobweb/index.php/backend/company/ueditorupload",
                         imagePath:"",
                         fileUrl:"http://www.192.168.3.131/hpjobweb/index.php/backend/company/ueditorupload",
                         filePath:"",
                         maximumWords:100000,
                         minFrameHeight:130
                    };var editor = new baidu.editor.ui.Editor(editorOption);
                    editor.render("cate_desc")</script>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">添加方案</button>
                        </div>
                    </form>
                 </div>-->
            </div>
        </div>
        <div id="dialog" class="hide" title="延长推广">
            <table class="table">
                <tr>
                    <th>延长推广</th>
                    <td>
                        <div>
                            <input class="input-mini pull-left" name="plus-day" id="plus-day" style="height:20px;" type="text"><span class="add-on" style="margin-left:-3px">天</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <script type="text/javascript">
            $('#tabs').tabs({ selected: <?php if(isset($_GET['action'])){?><?php echo $_GET['action'];?><?php  }else{ ?>0<?php }?>});
            $('.edit-spread').click(function(){
                var _this=$(this);
                $('#plus-day').val('');
                $('#dialog').dialog({
                    "width":"250",
                    "modal":"true",
                    "title":"修改推广",
                    "buttons":{
                        "修改":function() {
                            $.post(_this.attr('href'),{"days":$('#plus-day').val()},function(data){
                                window.location.reload();
                            });
                            }
                        }
                    });
                return false;
            });
            $('.opt a').click(function(){
                var _id=[],
                _confirm_msg='删除',
                _checked=$('#spread-list :checkbox[name]:checked');
                if(_checked.length==0){
                    alert("请选择你需要操作的推广!");
                    return false;
                }
                _checked.each(function(){
                    _id.push($(this).val());
                });
                if(confirm("确认"+_confirm_msg+"选中的推广？")){
                $.post('http://www.192.168.3.131/hpjobweb/index.php/backend/company/delSpread',{id:_id},function(data){
                    if(data==1){
                        _checked.parents('tr').fadeOut('slow',function(){
                            _checked.parents('tr').remove();
                        });
                    }
                },'html');
                }
                return false;
            });
            $('input[name="recruit_id"]').live("click",function(){
                $('#ruid').val($(this).attr('uid'));
                $('#s-rid').val($(this).val());
            });
            $('#add-s-form').submit(function(){
                if($('#s-rid').val()==''){
                    alert("请选择你要推广的职位！！");
                    return false;
                }
            });
            $('#select-spread-cate').change(function(){
                if($(this).find(':selected').text()=='职位变色'){
                    $('.picker-color input').attr('disabled',false);
                    $('.picker-color').show();
                }else{
                    $('.picker-color input').attr('disabled',true);
                    $('.picker-color').hide();
                }
            });
            $('#search_form input').keyup(function(e){
                if(e.keyCode==13){//如果按下回车
                    var _way=$(this).attr('name');
                    $.get('http://www.192.168.3.131/hpjobweb/index.php/backend/company/findRecruit',{'value':$(this).val(),'way':_way},function(data){
                    $('#recruit_list').html(data);
                    },'html');
                }
            });
        </script>
    </body>
</html>
