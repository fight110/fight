

define(['jquery', 'app/selects'], function(require, exports, module) {
    var $search  = $('#HDT-search-slave'), $searchlist = $("#HDT-search-list"), Selects = require('app/selects'),
        $slavelist = $('#HDT-slave-list'), ad_id = $search[0].getAttribute('data-user-id');
    new Selects('/location/json', {dataType:'json'});
    $search.on('change', function(e){
        var query = {};
        $search.find('input,select').each(function(){
            query[this.name] = this.value;
        });
        query.filter = $slavelist.find("input").map(function(){return this.value}).get().join(',');
        $.post('/ad/slave_user_list', query, function(html){
            $searchlist.html(html);
        }, 'html');
        $('.selectall').attr('checked',false);
    }).trigger('change');

    /*$searchlist.on('click', ':checkbox', function(e){
        var target  = e.currentTarget, parent = $(target).parent();
        parent.appendTo($slavelist);
    });*/

    $('body').on('click', ":checkbox", function(e){
        var target = e.currentTarget, user_slave_id = this.value, checked = this.checked;
    	if($(target).parent().parent().html()==$slavelist.html()){
        if(ad_id && user_slave_id){
            $.post('/ad/set_slave/', {ad_id:ad_id, user_slave_id:user_slave_id, status:checked ? 1 : 0});
        }
        if(!checked){//将取消的单选框移开
        		parent = $(target).parent();
        		if(parent[0].nodeName=="SPAN")
        			parent.appendTo($searchlist);
        }
    	}
    });

    var user_list = new Array;
    $('body').on('click','.selectall',function(e){//全选框
    	var target = e.currentTarget, checked = target.checked;
		var el = $('#HDT-search-list').children();
	    var len = el.length;
	    var area1 = $('select[name=area1]').val();
    	if(checked==true){
    	    for(var i=0; i<len; i++)
    	    {
    	        if((el[i].nodeName=="SPAN"))
    	        {
    	        	var check 			= el[i].children[0];
    	        	if(check.checked)	
    	        		check.checked= false;
    	        	else
    	        		check.checked= true;
    	        	var parent 			= $(check).parent();
    	        	var user_slave_id	= check.value;
    	        	user_list.push({ad_id:ad_id, user_slave_id:user_slave_id});
    	        }
    	    }
    	}else{
    		for(var i=0; i<len; i++)
    	    {
    	        if((el[i].nodeName=="SPAN"))
    	        {
    	        	var check 			= el[i].children[0];
    	        	if(check.checked)	
    	        		check.checked= false;
    	        	else
    	        		check.checked= true;
    	        }
    	    }
    	}
    });
    $('body').on('click','#HDT-sub-btn',function(e){//保存按钮
    	if(confirm("确认保存？")){
    		var el = $('#HDT-search-list').children();
    		var len = el.length;
    		var area1 = $('select[name=area1]').val();
	    
    		for(var i=0; i<len; i++)
    		{
    			if((el[i].nodeName=="SPAN"))
    			{
    				var check 			= el[i].children[0];
    				if(check.checked){
    					var parent 		= $(check).parent();
    					parent.appendTo($slavelist);
    				}
    			}
    		}
    		if(user_list.length){
    			$.post('/ad/set_slave_list/', {user_list:user_list});
    		}
    	}
	});
	$searchlist.on('click', ':checkbox', function(e){
        var target  = e.currentTarget, parent = $(target).parent();
        /*parent.appendTo($slavelist);*/
        user_list.push({ad_id:ad_id, user_slave_id:target.value});
    });
});