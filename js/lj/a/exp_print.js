

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/analysis/explist_print', {limit:50}, {autorun:true});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:0});

    require.async('app/admin.select', function(select){
        select(api);
    });
    
    $('body').on('click','#searchBut',function(){
    	api.set('sname',$.trim($('#nameFind').val()));
    })
    
    $('body').on('change','#nameFind',function(){
    	api.set('sname',$.trim($('#nameFind').val()));
    })

    $('body').on('click','.confirm_order',function(){
    	var data = {};
    	data['uid'] = $(this).attr('data-uid');
    	var that = $(this);
    	if(confirm('确认更改吗？')){
    		$.post('/ad/update_order_status',data,function(res){
    			if(res.error==='false'){
    				that.text(res.message);
				}else{
					alert('更新失败');
				}
			},'json');
    	}
		return false;
    })
    
     $('body').on('click','.refused_order',function(){
    	var data = {};
     	data['uid'] = $(this).attr('data-uid');
     	var that = $(this);
     	if(confirm('确认驳回该订单吗？')){
     		$.post('/user/refuse_order',data,function(res){
     			if(res.error==='false'){
     				that.removeClass('refused_order').text('已驳回');
				}else{
					alert('更新失败');
				}
 			},'json');
     	}
 		return false;
    })
});