
$(function(){

	$(window).swipeUp(function(event){
		_page++;
		$('#loadmore').trigger('click');
	})

	loadMore();

	$('.more1').click(function(){
		$(this).parent().css('height','auto');
		$(this).parent().css('padding','0');
		$(this).css('display','none')
	})

	$('#body').delegate('#loadmore','click',function(){
		$(this).addClass('y_morebuttonloging');
		var next = $(this).attr('next');
		if($.trim(next)==''){
			$(this).removeClass('y_morebuttonloging');
			$(this).html('没有了');
			$(this).removeAttr('id');
		}else{
			$(this).remove();
			var has = $('#body').html();
			$('#body').load(next,function(){
				$('#body').prepend(has);
			});
		}
	})

	$('#body').delegate('.comment','click',function(){

		var _id = $(this).data('id');

		var _data = {
			'cid':_id
		}

		var that = $(this);

		callWebView('addReply',_data,function(response) {

			that.parent().prev().prepend(response);

		});

	})

	$('#body').delegate('.hit','click',function(){

		var _id = $(this).data('id');

		var _data = {
			'cid':_id
		}

		var that = $(this);

		callWebView('addHit',_data,function(response) {

			that.children('.hitNum').text(response);

			if(that.data('hit')==1){
				that.data('hit',0);
				that.children('img').attr('src','http://www.hap-job.com/templates/default/app/images/zan.png')
			}else{
				that.data('hit',1);
				that.children('img').attr('src','http://www.hap-job.com/templates/default/app/images/yizan.png')
			}


		});

	})
	$('#body').delegate('.report','click',function(){

		var _id = $(this).data('id');

		var _data = {
			'cid':_id
		}

		console.log(_data);

		var that = $(this);

		callWebView('report',_data,function(response) {

		});

	})


	$('#body').delegate('.sns_img','click',function(){

		var _imgArr = [];

		var _img = $(this).data('original');

		var _imgs_obj = $(this).parent().parent().find('.sns_img');

		var _index = _imgs_obj.index(this);

		_imgs_obj.each(function(){
			var img = {
				img:$(this).data('original')
			};
			_imgArr.push(img);
		})

		var _data = {
			current: _img,
			urls: _imgArr,
			'index':_index
		};

		console.log(_data);


		callWebView('clickImg',_data,function(response) {
		});

	})

	$('#body').delegate('.showUser','click',function(){

		var _uid = $(this).data('uid');

		var _data = {
			'uid':_uid
		}

		console.log(_data);

		var that = $(this);

		callWebView('showUser',_data,function(response) {

		});

	})

	$('#body').delegate('.AtComment','click',function(){

		var _uid = $(this).data('uid');

		var _nickname = $(this).data('nickname');

		var _username = $(this).data('username');

		if(_nickname==''){
			_nickname = _username;
		}


		var _sns_id = $(this).data('snsid');

		var _data = {
			'uid':_uid,
			'nickname':_nickname,
			'sns_id':_sns_id
		}

		var that = $(this);

		callWebView('AtComment',_data,function(response) {

			that.parent().parent().prepend(response);

		});

	})

	$('#body').delegate('.delSns','click',function(){

		var _id = $(this).data('id');

		$.post(
			'__CONTROL__/delSns', {
				'sid':_id
			}, function (data) {
				if(data.status){
					$('.sns_'+_id).remove();
				}else{
				}
				layer.open({
					content: data.msg,
					style:'text-align: center;',
					time: 1
				});

			}, 'json');
	})

})

function addHitsCallBack(cid,hits){

	$('#body .hit').each(function(){

		if($(this).data('id')==cid){

			$(this).children('.hitNum').text(hits);

			if($(this).data('hit')==1){
				$(this).data('hit',0);
				$(this).children('img').attr('src','http://www.hap-job.com/templates/default/app/images/zan.png')
			}else{
				$(this).data('hit',1);
				$(this).children('img').attr('src','http://www.hap-job.com/templates/default/app/images/yizan.png')
			}
		}
	})
}

function addReplyCallBack(cid,content){

	$('#body .comment').each(function(){

		if($(this).data('id')==cid){
			$(this).parent().prev().prepend(content);
		}
	})
}

function AtCommentCallBack(sid,content){

	$('.sns_'+sid +' .content_1').prepend(content);

}

function loadMore(){
	var _url = _item_url+_page;
	var has = $('#body').html();
	$('#body').load(_url,function(){
		$('#body').prepend(has);
	});
}

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
		var _meth = "window.hpweb."+methName+"('"+postData+"')";
		eval(_meth);
	}
}