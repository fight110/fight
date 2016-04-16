
define(['jquery', 'lj/fancybox'], function(require, exports, module) {
	var $jindu = $('#jindu'), Jindu = {
		set : function(current,total) {
			var num = $("#num").html("当前进度："+current+"/"+total);
			var width	=	608*current/total;
			$("#jindu").css('width',width);
			return num;
		}
	}
    var output_analysis	=	function(ulist){
    	this.ulist	=	ulist;
    	this.len	=	ulist.length;
    }
    
    output_analysis.prototype = {
    		init   : function(){
    			
    		},
    		run : function(){
    			var i	=	0;
    			this.output(i);
    		},
    		output : function(i){
    			var	url = "/data/analysis_info/",ulist = this.ulist,len = this.len,that	=	this;
    			$("<iframe src='"+url+ulist[i]+"' style='display: none;'></iframe>").appendTo('.download').on('load',function(){
    				Jindu.set(i+1, len)
    				if(++i<len)
    					that.output(i);
	    		});
    		}
    };
    
    $('#analysis_output').on('click',function(){
    	$('.download').empty();
        $('body').off('click','#add_user');
        require.async(['app/model/user_select'],function(user_select){
            var select = new user_select;
                $('body').on('click','#add_user',function(){
                    var list    =   select.get_list();
                    $('.Bar').show();
                    var output  =   new output_analysis(list);
                    output.run();
                    select.close();
                });
        });
    })

    /*迷你服饰导出*/
    var output_mini =   function(ulist){
        this.ulist  =   ulist;
        this.len    =   ulist.length;
    }
    output_mini.prototype = {
            init   : function(){
                
            },
            run : function(){
                var i   =   0;
                this.output(i);
            },
            output : function(i){
                var url = "/data/mini_report/",ulist = this.ulist,len = this.len,that =   this;
                $("<iframe src='"+url+ulist[i]+"' style='display: none;'></iframe>").appendTo('.download').on('load',function(){
                    Jindu.set(i+1, len)
                    if(++i<len)
                        that.output(i);
                });
            }
    };
    
    $('#mini_output').on('click',function(){
        $('.download').empty();
        $('body').off('click','#add_user');

        require.async(['app/model/user_select'],function(user_select){
            var select = new user_select;
                $('body').on('click','#add_user',function(){
                    var list    =   select.get_list();
                    $('.Bar').show();
                    var output  =   new output_mini(list);
                    output.run();
                    select.close();
                });
        });
    })
});