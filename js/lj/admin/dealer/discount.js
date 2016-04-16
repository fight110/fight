

define(['jquery'], function(require, exports, module) {
	var $jindu = $('#jindu'), Jindu = {
		set : function(current,total) {
			var num = $("#num").html("当前进度："+current+"/"+total);
			var width	=	608*current/total;
			$("#jindu").css('width',width);
			return num;
		}
	}, AjaxList = {
		run_num : 0,
		limit 	: 1,
		list 	: [],
		add 	: function(url, data, beforecallback, callback){
			this.list.push([url, data, beforecallback, callback]);
			this.run();
		},
		run 	: function() {
			if(this.run_num < this.limit) {
				var r = this.list.shift(), that = this;
				if(r) {
					var callback = function(json) {
						r[3].call(that, json);
						that.run_num--;
						that.run();
					};
					r[2].call(that);
					$.post(r[0], r[1], callback, 'json');
					this.run_num++;
				}
			}
		}
	};
    var $main = $('#HDT-main'), user_id = $main.attr('data-user-id'), field = $main.attr('data-field');
    $main.not('.apply_btn').on('change', 'input', function(){
        var name    = this.name, value = this.value, keyword_id = this.getAttribute('data-keyword-id'), data = {user_id:user_id,field:field, keyword_id:keyword_id};
        data.value  = value;
        $.post('/dealer/set_discount', data, function(json){
            require.async('jquery/jquery.notify', function(n){
                var message = json.message;
                n.message({title:'提示',text:message}, {expires:2000});
            });
        }, 'json');
    });
    $(".apply_btn").on('click',function(){
    	if(confirm("确认应用到全部？")){
    		$('.Bar').show();
	    	var keyword_id	=	$(this).attr('keyword-id');
	    	var value		=	$main.find("input[data-keyword-id="+keyword_id+"]").val();
	    	$.post('/dealer/get_user_list', {user_id:user_id},function(json){
	    		var ulist = json.list, len = ulist.length,j=0;
	    		for(var i = 0; i < len; i++) {			    	
			    	(function(){
	    				var name = ulist[i].name, num = null, last = i == len - 1 ? true : false;
	    				AjaxList.add('/dealer/set_discount',{user_id:ulist[i].id,field:field,keyword_id:keyword_id,value:value}, function() {
    						//num = Jindu.set(i,len);
	    				}, function(json) {
    						Jindu.set(++j,len);
	    					if(last) {
	    						require.async('jquery/jquery.notify', function(n){
	    			            	var message = json.message;
	    			            	n.message({title:'提示',text:message}, {expires:2000});
	    			            });
	    					}
	    				});
	    			})();
			    	
		    	}
	    	},'json');
    	}
    });
    
    $(".refuser").on('click',function(){
    	var user_id	=	$(this).attr('data-user');
    	$.post('/dealer/refresh_user', {user_id:user_id}, function(json){
            require.async('jquery/jquery.notify', function(n){
                var message = json.message;
                n.message({title:'提示',text:message}, {expires:2000});
            });
        }, 'json');
    });
    
    $(".refuser_all").on('click',function(){
    		$('.Bar').show();
	    	$.post('/dealer/get_user_list', {},function(json){
	    		var ulist = json.list, len = ulist.length,j=0;
	    		for(var i = 0; i < len; i++) {			    	
			    	(function(){
	    				var name = ulist[i].name, num = null, last = i == len - 1 ? true : false;
	    				AjaxList.add('/dealer/refresh_user',{user_id:ulist[i].id}, function() {
    						//num = Jindu.set(i,len);
	    				}, function(json) {
    						Jindu.set(++j,len);
	    					if(last) {
	    						require.async('jquery/jquery.notify', function(n){
	    			            	var message = json.message;
	    			            	n.message({title:'提示',text:message}, {expires:2000});
	    			            });
	    					}
	    				});
	    			})();
			    	
		    	}
	    	},'json');
    });
});