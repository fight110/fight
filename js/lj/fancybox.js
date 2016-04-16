define(['jquery'], function(require, exports, module) {
	var fancybox = function(arr,fun){
		require.async(['jquery/fancybox/jquery.fancybox']);
	function fancyResize(){
		$.fancybox._setDimension();
		$.fancybox.reposition();
	}
	function reFitBorder(){
		$('.selectItem').removeClass('lastItem');
		$('.selectItem:visible:last').addClass('lastItem');
	}
	
	function changeURLArg(arg,arg_val){
		url = location.href;
		var pattern=arg+'=([^&]*)';
		var replaceText=arg+'='+arg_val;
		if(url.match(pattern)){
			var tmp='/('+ arg+'=)([^&]*)/gi';
			tmp=url.replace(eval(tmp),replaceText);
			return tmp;
		}else{
			if(url.match('[\?]')){
				return url+'&'+replaceText;
			}else{
				return url+'?'+replaceText;
			}
		}
		return url+'\n'+arg+'\n'+arg_val;
	}
	//console.log(changeURLArg('order_ud',''));
	
	$('#selectArea .selectValue ul').each(function(){
		var that = $(this);
		var check = that.attr('data-checked');
		if(check){
			var select = that.find('li[data-value="'+check+'"]');
			var html = '<b>'+that.parents('.selectItem').find('.selectKey').text()+'</b>'+select.html();
    		var _index = $('.selectValue ul').index(that);
    		$('.selectItem').eq(_index).hide();
    		$('#selectList').append('<li data-index="'+_index+'">'+html+'<span class="dontSelect"></span></li>');
		}
	})
	
	var html = $.trim($('#selectList').html());
	if(html!=''){
		$('.selectFrontList').html('<div class="headerTitle">筛选条件:</div><ul class="selectFrontUl">'+html+'</ul>');
	}
	
	$('.advanceSearch').click(function(){
		var that = $(this);
		$.fancybox.open({
			type : 'inline',
			href : '#advanceSearchMain',
			afterShow : function(){
				if(!that.hasClass('hadClick')){
					$('#selectArea .selectValue ul').each(function(){
						if($(this).height()>60){
							$(this).parents('.selectItem').find('.viewMore').css('visibility','visible');
						}
					})
					that.addClass('hadClick');						
				}		
			},
			beforeShow : function(){
				reFitBorder()
			},
			afterClose : function(){
				var html = $.trim($('#selectList').html());
				if(html!=''){
					$('.selectFrontList').html('<div class="headerTitle">筛选条件:</div><ul class="selectFrontUl">'+html+'</ul>');
				}else{
					$('.selectFrontList').html('');
				}
			},
		})
	})

	$('.viewMore').click(function(){
		var that = $(this);
		if(that.hasClass('viewLess')){
			that.removeClass('viewLess');
			that.text('更多');
			$(this).parents('.selectItem').find('.selectValue').addClass('limitHeight');
		}else{
			that.addClass('viewLess');
			that.text('收起');
			$(this).parents('.selectItem').find('.selectValue').removeClass('limitHeight');
		}
		fancyResize();
	})

	
	    	$('.selectValue ul li').click(function(){
	    		var that = $(this);
	    		var html = '<b>'+that.parents('.selectItem').find('.selectKey').text()+'</b>'+that.html();
	    		var _index = $('.selectValue ul').index(that.parents('ul'));
	    		$('.selectItem').eq(_index).hide();
	    		$('#selectList').append('<li data-index="'+_index+'">'+html+'<span class="dontSelect"></span></li>');
	    		fancyResize();
	    		var data_name = that.parents('ul').attr('data-name');
	    		var data_value = that.attr('data-value');
	    		if(arr!=''){
	    		if(fun!=''){
	    			for(var i=0;i<arr.length;i++){
		    			 if(arr[i]){
		    				 arr[i].set(data_name,data_value);
		    			 }
		    			}
	    			fun.call();
	    		}else{
	    			for(var i=0;i<arr.length;i++){
		    			 if(arr[i]){
		    				 arr[i].set(data_name,data_value);
		    				 if(arr[i].options.autorun===false){
		    					 arr[i].next();
		    				 }
		    			 }
		    			}
	    		}
	    		}else{
	    			var url = changeURLArg(data_name,data_value)
	    			location.href = url;
	    		}    		    		
	    		$.fancybox.close();
	    	})

	    	$('body').on('click','#selectList li',function(){
	    		var that = $(this);
	    		var _index = that.attr('data-index');
	    		$('.selectItem').eq(_index).show();
	    		that.remove();
	    		fancyResize();
	    		var data_name = $('.selectItem').eq(_index).find('.selectValue ul').attr('data-name');
	    		var data_default = $('.selectItem').eq(_index).find('.selectValue ul').attr('data-default');
	    		if(arr!=''){    		
	    		if(fun!=''){
	    			for(var i=0;i<arr.length;i++){
		    			 if(arr[i]){
		    				 arr[i].set(data_name,data_default?data_default:'');
		    			 }
		    			}
	    			fun.call();
	    		}else{
	    			for(var i=0;i<arr.length;i++){
		    			 if(arr[i]){
		    				 arr[i].set(data_name,data_default?data_default:'');  
		    				 if(arr[i].options.autorun===false){
		    					 arr[i].next();
		    				 }
		    			 }
		    			}
	    		}
	    		}else{
	    			var url = changeURLArg(data_name,data_default?data_default:'')
	    			location.href = url;
	    		}
	    		$.fancybox.close();
	    	})
	    	
	    	$('body').on('click','.selectFrontUl li',function(){
	    		var that = $(this);
	    		var _index = that.attr('data-index');
	    		var _cur = $('.selectFrontUl li').index(this);
	    		$('.selectItem').eq(_index).show();
	    		that.remove();
	    		$('#selectList li').eq(_cur).remove();
	    		var curHtml = $.trim($('.selectFrontUl').html());
	    		//console.log(curHtml);
	    		if(curHtml==''){
	    			$('.selectFrontList').html('');
	    		}
	    		var data_name = $('.selectItem').eq(_index).find('.selectValue ul').attr('data-name');
	    		var data_default = $('.selectItem').eq(_index).find('.selectValue ul').attr('data-default');
	    		if(arr!=''){
	    		if(fun!=''){
	    			for(var i=0;i<arr.length;i++){
		    			 if(arr[i]){
		    				 arr[i].set(data_name,data_default?data_default:'');
		    			 }
		    			}
	    			fun.call();
	    		}else{
		    		for(var i=0;i<arr.length;i++){
		    			 if(arr[i]){
		    				 arr[i].set(data_name,data_default?data_default:'');    				 
		    				 if(arr[i].options.autorun===false){
		    					 arr[i].next();
		    				 }
		    			 }
		    			}
	    		}
	    		}else{
	    			var url = changeURLArg(data_name,data_default?data_default:'')
	    			location.href = url;
	    		}
	    	})


};
	return fancybox;
});
