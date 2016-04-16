
define(['jquery', 'lj/fancybox'], function(require, exports, module) {
	var $console = $('#console'), Console = {
		add : function(message) {
			var p = $("<p>").html(message);
			$console.prepend(p);
			return p;
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
					$.get(r[0], r[1], callback, 'json');
					this.run_num++;
				}
			}
		}
	};
    var output_datail	=	function(type){
		this.type		=	type;
    	this.init();
    }
    
    output_datail.prototype = {
    		init   : function(){
    			
    		},
    		dealer : function(){
    			var	url = this.url,download=this.download,type_url=this.type_url,all_product=this.all_product;
    		    	$.get(url, function(json){
    		    		var ulist = json.list, len = ulist.length, main = Console.add("总客户数:" + len);
    		    		for(var i = 0; i < len; i++) {
    		    			(function(){
    		    				var name = ulist[i].name, t = null, last = i == len - 1 ? true : false;
    		    				AjaxList.add(type_url + ulist[i].id, {all_product:all_product}, function() {
    		    					t = Console.add(name);
    		    				}, function(json) {
    		    					t.append("..ok");
    		    					if(last) {
    		    						/*location.href= download;*/
    		    						$("<iframe src='"+download+"' style='display: none;'></iframe>").appendTo('.download');
    		    					}
    		    				});
    		    			})();
    		    		}
    		    	}, 'json');
    		},
    		total : function(){
    	        //location.href= download;
    			$("<iframe src='"+this.download+"' style='display: none;'></iframe>").appendTo('.download');
    		},
    		set : function(username){
    			this.username	=	username;
    			var all_product = $('#all_product')[0].checked ? 1 : 0,select_type = $('#select_type').val();
    			this.all_product=all_product;
    			if(this.type==0){
    				this.url	=	username ? "/data/ulist/" + username : "/data/ulist";
    				var download_url = select_type==1 ? "/data/index_detail_user_count/" : "/data/index_detail_user_count_sku/";
    				this.download = username ? download_url + username : download_url;
    				this.type_url = select_type==1 ? "/data/index_detail_user/" : "/data/index_detail_user_sku/";
    				
    				this.dealer();
    			}else{
    				this.download = select_type==1 ? 
	        	    		"/data/total_detail_user/?"+"username="+username+"&all_product="+all_product
	        	    		: "/data/total_detail_user_sku/?"+"username="+username+"&all_product="+all_product;
    				this.total();
    			}
    		}
    };
    var hasClick = false;
    $('#user_select').on('click',function(){
        require.async(['app/model/user_select'],function(user_select){
            var select = new user_select;
            if(false === hasClick){
                $('body').on('click','#add_user',function(){
                    var list    =   select.get_list();
                    $('#username').val(list.join(';'));
                    select.close();
                })
                hasClick=true;
            }
        })
    })
    $('.output').on('click',function(){
    	$('.download').empty();
    	
    	var type	=	$(this).attr('data-type');
    	var output 	= new output_datail(type);
    	var t		= $('#username').val();
    	if(t==''){
    		output.set('');
    	}else{
    		var list	= t.split(';');
    		for(i=0;i<list.length;++i){
    			output.set(list[i]);
    		}
    	}
    });

});