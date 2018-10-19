<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.validate.min.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.metadata.js"></script>
    </head>
    <body>
        <div id="user_add">
            <form action="http://www.hap-job.com/index.php/backend/user/addUser" method="post" id="add-user-form" class="well form-horizontal">
                <fieldset>
                    <div class="control-group">
                        <label class="control-label" for="rid">用户组</label>
                        <div class="controls">
                            <select name="rid" id="rid" validate="{required:true}">
                                <option value="-1"></option>
                                <?php if(is_array($role_list)):?><?php  foreach($role_list as $role){ ?>
                                <option value="<?php echo $role['rid'];?>"><?php echo $role['title'];?></option>
                                <?php }?><?php endif;?>
                            </select>
                        </div>
                    </div>

                     <div class="control-group" id="branch" style="display: none;">
                        <label class="control-label" for="branch">所属门店</label>
                        <div class="controls">
                            <select name="branchname"  validate="{required:true}">
                                <option value=""></option>
                                <?php if(is_array($branch_list)):?><?php  foreach($branch_list as $role){ ?>
                                <option value="<?php echo $role['name'];?>"><?php echo $role['name'];?></option>
                                <?php }?><?php endif;?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="username">用户名</label>
                        <div class="controls">
                            <input type="text" name="username" id="username" validate="{required:true,regexp:/[\w]{6,}/i,messages:{regexp:'请输入正确的用户名'}}" />
                            <p class="help-block">
                                数字、字母、下划线
                            </p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="password">密码</label>
                        <div class="controls">
                            <input type="password" name="password" id="password" validate="{required:true}" />
                            <p class="help-block">
                                密码至少为最少6位
                            </p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="re_password">重复密码</label>
                        <div class="controls">
                            <input type="password" name="re_password" id="re_password" validate="{required:true,equalTo:'#password'}" />
                            <p class="help-block">
                            </p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="email">Email</label>
                        <div class="controls">
                            <input type="text" name="email" validate="{email:true}" id="email"/>
                            <label class="checkbox"><input type="checkbox" name="email_verify" value="1">已验证</label>
                            <p class="help-block">
                            </p>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="salesmanname">真实姓名</label>
                        <div class="controls">
                            <input type="text" name="salesmanname" validate="{required:true}" />
                            <p class="help-block">
                            </p>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="salesmanphoneno">手机号码</label>
                        <div class="controls">
                            <input type="text" name="salesmanphoneno"  validate="{required:true}" />
                            <p class="help-block">
                            </p>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="email">状态</label>
                        <div class="controls">
                            <label class="radio"><input type="radio" name="banned" id="banned" value="0" checked>开启</label>
                            <label class="radio"><input type="radio" name="banned" value="1">禁止</label>
                        </div>
                    </div>
                    <div class="form-actions">
                        <input type="submit" name="userAdd" class="btn btn-primary" value="添加" />
                        <button class="btn">取消</button>
                    </div>
                </fieldset>
            </form>
        </div>
        <script type="text/javascript">
            $("#add-user-form").validate();
        </script>


        <script>
            $('#rid').change(function(){
                var a =  $('#rid option:selected').val();   
                if(a ==7){
                    $("#branch").show();
                }else{
                     $("#branch").hide();
                }               
            }); 
        </script>
    </body>
</html>
