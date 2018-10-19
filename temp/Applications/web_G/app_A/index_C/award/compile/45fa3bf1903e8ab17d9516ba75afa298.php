<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <title>幸运大转轮</title>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
        <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <link rel="stylesheet" type="text/css" href="http://www.hap-job.com/templates/default/award/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="http://www.hap-job.com/templates/default/award/css/common.css" />
        <script type="text/javascript" src="http://www.hap-job.com/templates/default/award/js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/templates/default/award/js/jquery.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/templates/default/award/js/scroll.js"></script>
        <!--easydialog-->
        <script type="text/javascript">
            $(document).ready(function(){
                $('.list_lh li:even').addClass('lieven');
            });
            $(function(){
                $("div.list_lh").myScroll({
                    speed:40, //数值越大，速度越慢
                    rowHeight:44 //li的高度
                });
            });
        </script>
        <style type="text/css">
        	html,body{margin:0px;padding:0px;display:block;background:#e84c3d;height:100%;-webkit-font-smoothing:subpixel-antialiased;-webkit-tap-highlight-color:transparent;-webkit-touch-callout: none;  -webkit-user-select: none;}
            #container {
                width: 320px;
                overflow: hidden;
                display: block;
                overlow:hidden;
                margin:0px auto;
            }
            #turnplatewrapper {
                height: 320px;
                width: 320px;
                background: url('http://www.hap-job.com/templates/default/award/img/turnplate/plate_06.png') top left no-repeat;
                position: absolute;
                -moz-user-select: none;
                user-select: none;
                margin: 10px auto;
                display: block;
                overflow: hidden;
            }

            #turnplate {
                height: 320px;
                width: 320px;
                background: url('http://www.hap-job.com/templates/default/award/img/turnplate/plate_05.png') top left no-repeat;
            }

            #turnplate #awards {
                position: absolute;
                width: 100%;
                height: 100%;
                background: url('http://www.hap-job.com/templates/default/award/img/turnplate/plate_13.png') top left no-repeat;
                /*_background:url('http://www.hap-job.com/templates/default/award/img/turnplate/plate_03_8.png') top left no-repeat;*/
            }

            #platebtn {
                position: absolute;
                top: 118px;
                left: 118px;
                background: url('http://www.hap-job.com/templates/default/award/img/turnplate/plate_04.png') top left no-repeat;
                /*_background:url('http://www.hap-job.com/templates/default/award/img/turnplate/plate_04_8.png') top left no-repeat;*/
                height: 85px;
                width: 85px;
                cursor: pointer;
            }

            #platebtn.hover {
                background-position: 0 -85px
            }

            #turnplate #platebtn.click {
                background-position: 0 -170px
            }

            #gift {
                width: 320px;
                height: 89px;
                /*_background:url('http://www.hap-job.com/templates/default/award/img/turnplate/plate_gift_01_8.png') top left no-repeat;*/
                position: absolute;
                /*left: 90px;
                 top: 720px;*/
            }

            #msg {
                position: absolute;
                display: none;
                top: 65px;
                left: 98px;
                border-radius: 5px;
                color: #333;
                border: 3px solid #FED33f;
                box-shadow: 2px 2px 2px rgba(0,0,0,0.6);
                background: #fffcf2;
                width: 100px;
                padding: 5px;
                text-align: center;
            }

            #content {
                width: 100%;
                margin: 0 auto;
            }

            #init {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 100px;
                height: 30px;
                border-radius: 5px;
                color: #333;
                border: 3px solid #FED33f;
                box-shadow: 2px 2px 2px rgba(0,0,0,0.6);
                background: #fffcf2;
                width: 250px;
                padding: 10px;
                margin-top: -30px;
                margin-left: -138px;
                text-align: center;
                opacity: 0.9;
                filter: alpha(opacity=90);
            }
            #lotteryMsg .msg {
                font-size: 16px;
                color: red;
                font-weight: bold;
                text-align: center;
                font-size: 26px;
                color: #ba0f54;
                line-height: 1;
                margin-bottom: 10px;
            }

            #lotteryMsg .tips {
                text-align: center;
                font-size: 14px;
            }

            #lotteryMsg .sp {
                border-top: 1px solid #c3c3c3;
                border-bottom: 0 none transparent;
                border-right: 0 none transparent;
                border-left: 0 none transparent;
                overflow: hidden;
                height: 0;
                margin: 10px 0;
            }

            #lotteryMsg a {
                color: #138cbe;
            }

            #lotteryMsg a:hover {
                color: #039;
            }
            .mydialog{zoom: 1;z-index: 10010;width: 153px;height: 84px;margin: 7.4% 5%;position:absolute;display:block;overflow:hidden;background:url("http://www.hap-job.com/templates/default/award/img/sorry.png");background-repeat:no-repeat;}
            .dialog_body{}
            .wrapper {
                width: 320px;
                margin: 0 auto;
                color: #fff;
                margin-top:360px;
            }
            .shuoming{height: 280px;overflow: hidden;margin-top: 0px; background: url("http://www.hap-job.com/templates/default/award/img/shuoming.png");padding: 0px 20px;}
            .title{padding: 2px 10px;border-top-right-radius:25px;font-size:14px;background-color: #F29209;width: 100px;color: #FFFFFF;margin: 5px 0px 5px 0px;}
            .neirong{color: #994225;font-size: 12px;margin-left: 0px;}
            .zjmd{ width:100%; margin: 0 auto; background: #fff; border-radius: 5px; overflow: hidden; margin-top: 20px;}
            .zjmd p{ width:100%; line-height: 44px; border-bottom: 1px solid #ccc; margin: 0 auto; background: #fff; color: #68676f; text-align: center;}
        </style>
    </head>
    <body>
        <div id="container">
            <div id="turnplatewrapper" onselectstart="return false;">
                <div id="turnplate">
                    <div id="awards"></div>
                    <div id="platebtn"></div>
                    <p id="msg"></p>
                    <p id="init" class="dn">
                        初始化中，请稍后<span></span>
                    </p>
                </div>
            </div>
            <div id="gift">
                
            </div>
	        <!--<div class="wrapper">-->
	                <!--<P style="color: #ffef89; font-size: 16px;">说明：</P>-->
                    <!--<P style="color: #ffef89; font-size: 14px;">说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明说明</P>-->
                    <!--<div class="zjmd">-->
                        <!--<P>138****9861&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;五等奖</P>-->
                        <!--<P>138****9861&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;五等奖</P>-->
                        <!--<P>138****9861&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;五等奖</P>-->
                        <!--<P>138****9861&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;五等奖</P>-->
                        <!--<P>138****9861&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;五等奖</P>-->
                        <!--<P>138****9861&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;五等奖</P>-->
                        <!--<P>138****9861&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;五等奖</P>-->
                    <!--</div>-->
	        <!--</div>-->
            <div class="wrapper">
                <P style="color: #ffef89; font-size: 16px;">说明：</P>
                <P style="color: #ffef89; font-size: 14px;">
                    1.每次抽奖需要100积分。<br/>
                    2.用户抽奖后获取的积分，可在个人中心积分签到中查询。<br/>
                    3.用户中奖后，请及时关注系统消息中的领奖方式，如有疑问及时联系客服4006920099。。<br/>
                    4.奖项以转盘设置内容为准，最终解释权归属于开心工作。<br/>
                    5.所有抽奖活动与苹果公司（Apple Inc）无关。<br>
                </P>
            <div class="box" style="margin-bottom:12px;">
                <div class="bcon">
                    <div class="list_lh">
                        <ul>
                            <?php if(is_array($logs)):?><?php  foreach($logs as $log){ ?>
                            <li>
                                <P><?php echo substr_replace($log['username'],'****',3,4);?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $log['name'];?></P>
                            </li>
                            <?php }?><?php endif;?>

                        </ul>
                    </div>
                    <!-- 代码结束 -->
                </div>
            </div>
                </div>
       		 <!--弹出层-->
	        <div class="mydialog" style="display:none;">
	        </div>
        </div>
        <script type="text/javascript">

            var ttouch;
            var _system;

            function connectWebViewJavascriptBridge(callback) {
                if (window.WebViewJavascriptBridge) {
                    callback(WebViewJavascriptBridge)
                } else {
                    document.addEventListener('WebViewJavascriptBridgeReady', function() {
                        callback(WebViewJavascriptBridge)
                    }, false)
                }

            }

            connectWebViewJavascriptBridge(function(bridge) {

                bridge.init(function(message, responseCallback) {
                    var data = { 'Javascript Responds':'Wee!' };
                    responseCallback(data)
                });
                bridge.registerHandler('testJavascriptHandler', function(data, responseCallback) {

                    alert(1);
                    var responseData = { 'Javascript Says':'Right back atcha!' };
                    responseCallback(responseData);
                });

                ttouch = bridge;
                _system = 0;
            });

            function callWebView(methName,postData,callback){

                if(_system==0){//ios
                    ttouch.callHandler(methName,postData, callback);
                }else{//android
                    postData = JSON.stringify(postData);
                    var _meth = "window.hpweb."+methName+"('"+postData+"')";
                    eval(_meth);
                }
            }

        var turnplate = {
            turnplateBox : $('#turnplate'),
            turnplateBtn : $('#platebtn'),
            lightDom : $('#turnplatewrapper'),
            freshLotteryUrl : '#',//刷新转盘的地址
            msgBox : $('#msg'),
            lotteryUrl : '#',//奖品地址
            height : '320', //帧高度
            lightSwitch : 0, //闪灯切换开关. 0 和 1间切换
            minResistance : 5, //基本阻力
            Cx : 0.01, //阻力系数 阻力公式：  totalResistance = minResistance + curSpeed * Cx;
            accSpeed : 15, //加速度
            accFrameLen : 40, //加速度持续帧数
            maxSpeed : 250, //最大速度
            minSpeed : 20, //最小速度
            frameLen : 8, //帧总数
            totalFrame : 0, //累计帧数,每切换一帧次数加1
            curFrame : 0, //当前帧
            curSpeed : 20, //上帧的速度
            lotteryTime : 2, //抽奖次数
            lotteryIndex : 6, //奖品index
            errorIndex : 2, //异常时的奖品指针
            initBoxEle : $('#turnplate #init'),
            progressEle : $('#turnplate #init span'),
            initProgressContent : '~~~^_^~~~', //初始化进度条的内容
            initFreshInterval : 500, //进度条刷新间隔
            virtualDistance : 10000, //虚拟路程,固定值，速度越快，切换到下帧的时间越快: 切换到下帧的时间 = virtualDistance/curSpeed
            isStop : true, //抽奖锁,为true时，才允许下一轮抽奖
            timer2 : 10,//计时器
            initTime : 5,
            showMsgTime : 2000, //消息显示时间
            lotteryChannel: '',
            lotteryMsg:'',
            log_id:0,

            channelList: {
            'login': '每日登录',
            'consume': '购买空间'
            },

            lotteryType : {
            '5M' : 0,
            '80M' : 1,
            '1G' : 2,
            '200M' : 3,
            '100M' : 4,
            '20M' : 5,
            'empty' : 6,
            '10G' : 7,
            '50M' : 8,
            '1T' : 9
            },

            //初始化
            init : function() {
                this.initAnimate();
               	this.freshLottery();
                this.turnplateBtn.click($.proxy(function(){
                this.click();
                },this));
            },
            //初始化动画
            initAnimate : function() {
                this.initBoxEle.show();
                clearTimeout(this.initTimer);
                var curLength = this.progressEle.text().length,
                totalLength = this.initProgressContent.length;
                if (curLength < totalLength) {
                    this.progressEle.text(this.initProgressContent.slice(0, curLength + 1));
                }else{
                    this.progressEle.text('');
                }
                this.initTimer = setTimeout($.proxy(this.initAnimate, this), this.initFreshInterval);
            },
            //停止初始化动画
            stopInitAnimate : function() {
	            clearTimeout(this.initTimer);
	            	this.initBoxEle.hide();
	            },
	            freshLotteryTime : function() {
	            	$('#top-menu').find('.lottery-times').html(this.lotteryTime);
            },
            //更新抽奖次数
            freshLottery : function() {
                this.stopInitAnimate();
                this.setBtnHover();
                this.isStop = true;
                this.lotteryTime = 1;
                this.freshLotteryTime();
            },
            //设置按钮三态
            setBtnHover : function() {
                //按钮三态
                $('#platebtn').hover(function(){
                    $(this).addClass('hover');
                },function() {
                    $(this).removeClass('hover click');
                }).mousedown(function(){
                    $(this).addClass('click');
                }).mouseup(function(){
                    $(this).removeClass('click');
                });
            },
            //获取奖品
            lottery : function() {
                this.lotteryIndex = undefined;
                this.lotteryTime--;
                this.freshLotteryTime();
                this.totalFrame = 0;
                this.curSpeed = this.minSpeed;
                this.turnAround();
                this.lotteryIndex = typeof this.lotteryType[2] !== 'undefined' ? this.lotteryType[2] : this.errorIndex;
                // this.lotteryChannel = typeof this.channelList[1] !== 'undefined' ? this.channelList[1] : '';
            },
            //点击抽奖
            click : function() {

                var _uid = "<?php echo $_GET['uid'];?>";

//                if(_uid==''){
//
//                    window.location.href='http://www.hap-job.com/app/auth/adsUnlogin';
//                }

                //加锁时
                if(this.isStop == false) {
                    this.showMsg('正在抽奖中...');
                    return;
                }

                var _validate = false;

                var _validat_msg = '';

                var _validat_index = '';

                var _that = this;


                $.ajax({
                    type: 'POST',
                    url: 'http://www.hap-job.com/index.php/app/index/doAward',
                    data: {
                        'uid':'<?php echo $_GET['uid'];?>'
                    },
                    success: function(data){
                        if(data.status==1){
                            _validate = true;
                            _validat_index= data.data.rid;
                            _that.log_id = data.data.log_id;
                            _validat_msg = data.msg;
                        }else if(data.status==20){

                            callWebView('validatePhone',{});

                        }else{
                            _validat_msg = data.msg;
                        }
                    },
                    dataType: "json",
                    async:false
                });

                if(!_validate){
                    this.showMsg(_validat_msg);
                    return;
                }else{
                    this.lotteryMsg = _validat_msg;
                    this.errorIndex = _validat_index;
                }

                this.lottery();

            },
            
            //更新当前速度
            freshSpeed : function() {
                var totalResistance = this.minResistance + this.curSpeed * this.Cx;
                var accSpeed = this.accSpeed;
                var curSpeed = this.curSpeed;
                if(this.totalFrame >= this.accFrameLen) {
                    accSpeed = 0;
                }
                curSpeed = curSpeed - totalResistance + accSpeed;
                if(curSpeed < this.minSpeed){
                    curSpeed = this.minSpeed;
                    }else if(curSpeed > this.maxSpeed){
                    curSpeed = this.maxSpeed;
                }
                this.curSpeed  = curSpeed;
            },
            
            //闪灯,切换到下一张时调用
            switchLight : function() {
                this.lightSwitch = this.lightSwitch === 0 ? 1 : 0;
                var lightHeight = -this.lightSwitch * this.height;
                this.lightDom.css('backgroundPosition','0 ' + lightHeight.toString() + 'px');
            },
            
            //旋转,trunAround,changeNext循环调用
            turnAround : function() {
                //加锁
                this.isStop = false;
                var intervalTime = parseInt(this.virtualDistance/this.curSpeed);
                this.timer = window.setTimeout('turnplate.changeNext()', intervalTime);
            },
            
            //弹出奖品
            showAwards : function(){
                if(this.log_id!=0){
                    $.ajax({
                        type: 'POST',
                        url: 'http://www.hap-job.com/index.php/app/index/awardPush',
                        data: {
                            'log_id':this.log_id
                        },
                        success: function(data){
                        },
                        dataType: "json",
                        async:false
                    });
                }
            /*弹出获奖提示的地方*/

                this.showMsg( this.lotteryMsg);
            },
            
            //显示提示信息
            showMsg : function(msg, isFresh) {
            isFresh = typeof isFresh == 'undefined' ? false : true;
            if(typeof msg != 'string'){
                msg = '今天已经抽过奖了，明天再来吧';
            }
            var msgBox = this.msgBox;
            var display = msgBox.css('display');
            
            msgBox.html(msg);
            
            window.clearTimeout(this.timer2);
            msgBox.stop(true,true).show();
            var fadeOut = $.proxy(function() {
            this.msgBox.fadeOut('slow');
            }, this);
            this.timer2 = window.setTimeout(fadeOut, this.showMsgTime);
            },
            
            //切换到下帧
            changeNext : function() {
            //判断是否应该停止
	            if(this.lotteryIndex !== undefined && this.curFrame == this.lotteryIndex && this.curSpeed <= this.minSpeed+10 && this.totalFrame > this.accFrameLen) {
	            this.isStop = true;
	            this.showAwards();
	            return;
            }
            
            var nextFrame =  this.curFrame+1 < this.frameLen ? this.curFrame+1 : 0;
            var bgTop = - nextFrame * this.height;
            this.turnplateBox.css('backgroundPosition','0 ' + bgTop.toString() + 'px');
            this.switchLight();
            this.curFrame = nextFrame;
            this.totalFrame ++;
            this.freshSpeed();
            this.turnAround();
            }
            }

        </script>
        <script type="text/javascript">
            turnplate.init();
        </script>

    </body>
</html>