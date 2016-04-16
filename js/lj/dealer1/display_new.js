

define(['jquery', 'lightbox' ,'app/pager', 'app/lazy'], function(require, exports, module) {
	var did = $('#HDT-main').attr('data-id');
    lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/dealer1/display_group', {did:did}, {autorun:true,aftercallback:function(html){
    	if(html=='none'){
    		alert('当前陈列不存在或失效！');
    		location.href = '/dealer1/display_new';
    	}
    }});
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});
	$('.displayOne').on('click',function(){
		$(this).addClass('hasSelected').siblings().removeClass('hasSelected');
		var nowId=$(this).attr('data-id');
		api.set('did',nowId);
		history.pushState({},'','/dealer1/display_new/'+nowId);
	})
	
	$('body').on('click','.seemore',function(){
		var that = $(this);
		if(that.hasClass('hasLoad')){
			that.find('.displayImg').eq(0).trigger('click');
		}else{
			if(!that.hasClass('hasClick')){
				that.addClass('hasClick');
				var id = that.attr('data-id');
				$.post('/dealer1/display_img',{id:id},function(html){
					that.append(html).addClass('hasLoad').removeClass('hasClick');
					that.find('.displayImg').eq(0).trigger('click');
				})
			}

		}
		
		

	})
});