<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
        <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/web/backend/templates/css/public.css"/>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.validate.min.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.metadata.js"></script>
    </head>
    <body>
        <div id="add-role">
            <form method="post" id="add-role-form" action="http://www.hap-job.com/index.php/backend/user/addRole" class="well form-horizontal">
                <div class="control-group">
                    <label class="control-label" for="rname">父级角色</label>
                    <div class="controls">
                        <select name="pid" id="">
                            <option value="">请选择</option>
                            <option value="0">顶级角色</option>
                            <?php if(is_array($roles)):?><?php  foreach($roles as $role){ ?>
                            <option value="<?php echo $role['rid'];?>">&nbsp;&nbsp;&nbsp;&nbsp;--<?php echo $role['title'];?></option>
                            <?php }?><?php endif;?>
                        </select>
                        <p class="help-block sys">
                            子角色将会继承父级角色的权限
                        </p>
                    </div>
                </div>
                <!-- <div class="control-group">
                    <label class="control-label" for="rname">角色名</label>
                    <div class="controls">
                        <input type="text" name="rname" id="rname" validate="{required:true}" />
                        <p class="help-block">
                            数字、字母、下划线
                        </p>
                    </div>
                </div> -->
                <div class="control-group">
                    <label class="control-label" for="title">角色别名</label>
                    <div class="controls">
                        <input type="text" name="title" id="title" validate="{required:true}"/>
                        <p class="help-block">
                            对角色的中文描述，例如：网站编辑、管理员
                        </p>
                    </div>
                </div>
                 <div class="control-group">
                    <label class="control-label" for="sort">角色排序</label>
                    <div class="controls">
                        <input type="text" name="sort" id="sort" class="input-mini" value="0" />
                        <p class="help-block">
                        </p>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for=""></label>
                    <div class="controls">
                        <label class="radio inline">
                            <input type="radio" name="state" value="1" checked />开启角色
                        </label>
                        <label class="radio inline">
                            <input type="radio" name="state" value="0" />关闭角色
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <input type="submit" name="addRole" class="btn btn-primary" value="添加" />
                    <button class="btn">取消</button>
                </div>
            </form>
        </div>
        <script type="text/javascript">
            $("#add-role-form").validate();
        </script>
    </body>
</html>
