<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>管理员登录</title>
    <link type="text/css" rel="stylesheet" href="http://localhost//hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="http://localhost/hpjobweb/web/backend/templates/css/login.css"/> 
    </head>
    <body>
        <div id="header">
        <a class="brand" href="http://www.houdunwang.com">开心求职招聘系统</a>
        </div>
    <div class="container-fluid">

        <div class="loginContainer" id="login-panel">
            <form method="post" action="http://localhost/hpjobweb/index.php/backend/auth/index" id="loginForm" >
                <table>
                    <tr>
                        <th><span class="login-input"><label for="name">用户名：</label></span></th>
                    </tr>
                    <tr>
                        <td><input type="text" class="input-xlarge" tabindex="1" name="name" id="name" /></td>
                    </tr>
                    <tr>
                        <th><span class="login-input"><label for="pwd">密码：</label></span> </th>
                    </tr>
                    <tr>
                        <td><input type="password" class="input-xlarge" tabindex="2" name="pwd" id="pwd" /></td>
                    </tr>
                    <?php if($show_captcha){?>
                    <tr>
                        <th><span class="login-input"><label for="code">验证码：</label></span></th>
                    </tr>
                    <tr>
                        <td class="code">
                            <input type="text" class="input-mini" tabindex="3" name="code" id="code" />
                            <img src="http://localhost/hpjobweb/index.php/index/auth/validateCode.html" onclick="javascript:this.src='http://localhost/hpjobweb/index.php/index/auth/validateCode?next='+Math.random();return false;">
                        </td>
                    </tr>
                    <?php }?>
                </table>
                <div id="login">
                    <label for="remember"><input type="checkbox" name="remember" id="remember" />记住我</label>
                    <button type="submit" tabindex="3" class="btn btn-info"><i class="icon-edit icon-white"></i>&nbsp;&nbsp;登录</button>
                </div>
            </form>
        </div>

    </div>
    <script type="text/javascript" src="http://localhost//hpjobweb/public/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript">
    $('#loginForm').submit(function(){
        if($('#name').val()==''){
            alert('请输入用户名！');
            return false;
        }
        if($('#pwd').val()==''){
            alert('请输入密码！');
            return false;
        }
        if($('#code').val()==''){
            alert('请输入验证码！');
            return false;
        }
    });
     window.onload=function(){(window.onresize=function(){

            //获取可见高度
            var _h=document.documentElement.clientHeight,
            _n_h=$('#login-panel').height();
            var _login_top=(_h-_n_h-100)/2;
            $('#login-panel').animate({'margin-top':_login_top+'px'},'slow');
        })()};
        $('#name').focus();
    </script>
    </body>
</html>
