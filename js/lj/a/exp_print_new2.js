

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), 
    api = new pager('/analysis/explist_print_new', {}, {autorun:true}),
    info_api = new pager('/analysis/explist_info',{},{autorun:true,id:'#HDT-info'});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:0});

    require.async('app/admin.select.new', function(select){
        select(api);
    });

    $('#HDT-select-menu').on('change','select',function(){
      info_api.set(this.name,this.value);
    })

    $('body').on('click','#searchBut',function(){
    	api.set('is_type','isUser',true);
    	api.set('sname',$.trim($('#nameFind').val()));
      info_api.set('sname',$.trim($('#nameFind').val()));
    })
    
    $('body').on('change','#nameFind',function(){
    	api.set('is_type','isUser',true);
		  api.set('sname',$.trim($('#nameFind').val()));
      info_api.set('sname',$.trim($('#nameFind').val()));
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
    
    var hide_select = function(){
		$('#HDT-select-menu').find('[name=fliter_zd]').hide();
		$('#HDT-select-menu').find('[name=fliter_uid]').hide();
		$('#HDT-select-menu').find('[name=area1]').hide();
    $('#HDT-select-menu').find('[name=property]').hide();
		$('#HDT-select-menu').find('[name=is_lock]').hide();
		$('#HDT-select-menu').find('.normalInput').hide();
		$('#HDT-select-menu').find('[name=order]').hide();
    }
    var show_select = function(){
    	$('#HDT-select-menu').find('[name=fliter_zd]').show();
    	//$('#HDT-select-menu').find('[name=fliter_uid]').show();
		$('#HDT-select-menu').find('[name=area1]').show();
		$('#HDT-select-menu').find('[name=property]').show();
		$('#HDT-select-menu').find('[name=is_lock]').show();
		$('#HDT-select-menu').find('.normalInput').show();
		$('#HDT-select-menu').find('[name=order]').show();
		$('.LabelText').find('.on').removeClass('on');
		$('a[data=isUser]').addClass('on');
    }
    
    $('.LabelText a').on('click',function(){
    	$('.LabelText').find('.on').removeClass('on');
    	$(this).addClass('on');
      	var data= $(this).attr('data');
      	if(data=='isUser'){
      		show_select();
      	}else{
      		hide_select();
      	}

      	api.reset('/analysis/explist_print_new', {is_type:data}, {autorun:true});
        info_api.reset('/analysis/explist_info', {},{autorun:true,id:'#HDT-info'});
    })
    
    $('body').on('click touchstart','.look_lower',function(){
    	var data_zd = $(this).attr('data-zd'), data_area1 = $(this).attr('data-area1'), data_property = $(this).attr('data-property');
    	if(data_zd){
        	api.reset('/analysis/explist_print_new', {is_type:'isUser',fliter_zd:data_zd}, {autorun:true});
          info_api.reset('/analysis/explist_info', {fliter_zd:data_zd},{autorun:true,id:'#HDT-info'});
        	show_select();
    	}else if(data_area1){
        	api.reset('/analysis/explist_print_new', {is_type:'isUser',area1:data_area1}, {autorun:true});
          show_select();
    	}else if(data_property){
    			api.reset('/analysis/explist_print_new', {is_type:'isUser',property:data_property}, {autorun:true});
    			show_select();
    	}
    })
});