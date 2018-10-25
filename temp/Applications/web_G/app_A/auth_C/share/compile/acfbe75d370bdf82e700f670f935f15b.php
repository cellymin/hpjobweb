<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>找工作 赢大奖 享服务</title>
    <meta name="keywords" content="【省钱、轻松】找工作随时随地，知心服务，拿返现，还能赢大奖，小伙伴们，一起下载开心工作！">
    <meta name="description" content="【省钱、轻松】找工作随时随地，知心服务，拿返现，还能赢大奖，小伙伴们，一起下载开心工作！">
    <script src="http://localhost/hpjobweb/templates/default/app/js/WeixinApi.js"></script>
    <link href="http://localhost/hpjobweb/templates/default/app/css/css.css" rel="stylesheet" type="text/css">
    <script src="http://localhost/hpjobweb/templates/default/app/js/zepto.min.js"></script>
    <script src="http://localhost/hpjobweb/templates/default/app/js/layer.m/layer.m.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
</head>
<body id="yem">
    <div class="page">
        <section>
            <div class="top" style="visibility: hidden;"><img src="http://localhost/hpjobweb/templates/default/app/images/yuan.png"></div>

                <div class="bottom" style="margin-top: 30px;">
                    <!--<input onkeyup="value=value.replace(/[^\d]/g,'') "onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))">-->
                    <P><input id="phone" maxlength="11" onkeyup="value=value.replace(/[^\d]/g,'') "onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))" value="输入您的手机号">
                        <input type="hidden" id="branch" value="<?php echo $_GET['branch_id'];?>">

                        <input type="hidden" id="salesman" value="<?php echo $_GET['salesmanid'];?>"> 


                         <input type="hidden" id="normalman" value="<?php echo $_GET['normalmanid'];?>">  
                    </P>

                    <p class="yzm">
                        <input id="code" maxlength="6" onkeyup="value=value.replace(/[^\d]/g,'') "onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))" value="输入验证码">
                        <button onclick="sendSms(this)" id="hqyzm">发送验证</button>
                        <!--onclick="sendSms()"-->
                    </p>

                    <img src="http://localhost/hpjobweb/templates/default/app/images/xzapp.png" onclick="dosubmit();">
                </div>
        </section>
        <input type="hidden" id="from" name="from" value="<?php echo $_GET['from'];?>">
    </div>
</body>
</html>
<script type="text/javascript">
    wx.config({
        debug: false,
        appId: 	'wx30c0a3cfe135eac8',
        timestamp: 1447758402,
        nonceStr: '332196',
        signature: '0503c61b9ef02bb04783c6a0b41dab65d8b0a216',
        jsApiList: [
            'checkJsApi',
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'onMenuShareQQ',
            'onMenuShareWeibo',
            'openLocation',
            'getLocation',
            'addCard',
            'chooseCard',
            'openCard',
            'hideMenuItems'
        ]
    });
</script>
<script>
    wx.ready(function () {
        wx.showOptionMenu();
    });
</script>
<script>

    function dosubmit(){

        var _phone = $('#phone').val();

        var _from = $('#from').val();

        var _code = $('#code').val();

        var branch_id = $('#branch').val();

        var salesmanid = $("#salesman").val();

        var normalmanid = $("#normalman").val();

        if($.trim(_phone)==''|| $.trim(_phone)=='输入您的手机号'|| $.trim(_code)==''){

            layer.open({
                content: '等待跳转...',
                style: 'text-align:center',
                time: 2
            });
            // location.href="http://a.app.qq.com/o/simple.jsp?pkgname=com.tongyu.luck.happywork";
            location.href="content://com.tongyu.luck.happywork/openwith?name=zhangsan&age=26";

        }


        if($.trim(_phone)==''|| $.trim(_phone)=='输入您的手机号'){

            layer.open({
                content: '请填写手机号码',
                style: 'text-align:center',
                time: 2
            });

            return false;
        }

        if($.trim(_phone).length!=11){
            layer.open({
                content: '手机号码错误',
                style: 'text-align:center',
                time: 2
            });

            return false;
        }


        if($.trim(_code)==''||$.trim(_code)=='输入验证码'){
            layer.open({
                content: '输入验证码',
                style: 'text-align:center',
                time: 2
            });

            return false;
        }

        if($.trim(_code).length!=4){
            layer.open({
                content: '验证码错误',
                style: 'text-align:center',
                time: 2
            });

            return false;
        }

        $.post('http://localhost/hpjobweb/index.php/app/auth/share',{
            'code':_code,
            'phone':_phone,
            'from':_from,
            'branch_id':branch_id,
            'salesmanid':salesmanid,
            'normalmanid':normalmanid,
        },function(data){
            if(data.status){
                layer.open({
                    content: '等待跳转...',
                    style: 'text-align:center',
                    time: 2
                });
                location.href=data.data;
            }else{
                layer.open({
                    content: data.msg,
                    style: 'text-align:center',
                    time: 2
                });
            }

        },'json')

    }

    function sendSms(object){

        var _phone = $('#phone').val();

        if($.trim(_phone)==''|| $.trim(_phone)=='输入您的手机号'){

            layer.open({
                content: '请填写手机号码',
                style: 'text-align:center',
                time: 2
            });
            return false;
        }

        if($.trim(_phone).length!=11){
            layer.open({
                content: '手机号码错误',
                style: 'text-align:center',
                time: 2
            });
            return false;
        }

        $.post('http://localhost/hpjobweb/index.php/app/auth/sendSmsForShare',{
            'phone':_phone
        },function(data){
            layer.open({
                content: data.msg,
                style: 'text-align:center',
                time: 2
            });
        },'json')
        time(object);
    }

//    document.getElementById("hqyzm").onclick=function(){,sendSms();}
    var wait=60;
    function time(o) {
        console.log(o);
        if (wait == 0) {
            o.removeAttribute("disabled");
            o.innerHTML="免费获取验证码";
            o.style.cssText = '';
            wait = 60;
        } else {
            o.setAttribute("disabled", true);
            o.innerHTML="重新发送(" + wait + ")";
            o.style.cssText = 'background:url("http://localhost/hpjobweb/templates/default/app/images/bka.png") center no-repeat;background-size: 100%;';
            wait--;
            setTimeout(function() {
                        time(o)
                    },
                    1000)
        }
    }
</script>