 
define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/register/examine_table', {}, {autorun:true,message:"松开刷新"});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

    $('#HDT-select-menu').on('change','select',function(e){
        var target = e.currentTarget;
        api.set(target.name, target.value);
    })

    $('body').on('click','.examine',function(){
		var id    = $(this).attr("data-id");
		var that = this;
        $.get("/register/set_status/"+id,{},function(json){
        	if(json.message){
        		$(that).html('<font color="green">已审核</font>');
        	}
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:json.message}, {expires:2000});
            });
        });
	});
});