/**
 * Created by zhouchao on 15/10/27.
 */
var hpweb;
var _system;

    $('.swiper-container').each(function(){

        var _id = $(this).parent().attr('id');

        var swiper =$(this).swiper({
            pagination: '#'+_id+' .swiper-pagination',
            paginationClickable: true
        });

        $(this).parent().hide();

    });


    $('.job2 li').click(function(){

        if($(this).data('value')!=1){
            $('.job2 li').data('value',0);
            $(this).data('value',1);
            var _index = $(this).index();
            $('.job2 li').removeClass('hover');
            $(this).addClass('hover');
            $('.job3').hide();
            $(this).parent().parent().find('.job3').eq(_index).show();
        }else{
            var _index = $(this).index();
            $(this).removeClass('hover');
            $(this).parent().parent().find('.job3').eq(_index).hide();
            $('.job2 li').data('value',0);
        }
    })

    $('.job3 li').click(function(){
        var _id = $(this).data('id');
        var _title = $(this).html();

        var _data = {
            'cid':_id,
            'ctitle':_title
        }
        console.log(_data);
        return false;
        // callWebView('getJobCategoryId',_data,function(response) {
        //     alert(response);
        // });
    })


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
        var data = { 'Javascript Responds':'Wee!' }
        responseCallback(data)
    })
    bridge.registerHandler('testJavascriptHandler', function(data, responseCallback) {
        var responseData = { 'Javascript Says':'Right back atcha!' }
        responseCallback(responseData)
    })
    hpweb = bridge;
    _system = 0;
})

function callWebView(methName,postData,callback){

    if(_system==0){//ios
        hpweb.callHandler(methName,postData, callback);
    }else{//android
        postData = JSON.stringify(postData);
//			window.hpweb.getJobCategoryId('ddawdnakl');
        var _meth = "window.hpweb."+methName+"('"+postData+"')";
//			alert(_meth);
        eval(_meth);
    }
}

