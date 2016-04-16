

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), type=$("#indicator_type").val(), api = new pager('/analysis/ad_indicator', {type:type}, {autorun:true});
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});
    
    $('.LabelText a').not('.ad').on('click',function(){
    	$('.LabelText').find('.on').removeClass('on');
    	$(this).addClass('on');
      	type= $(this).attr('data-type');
      	
      	/*api.reset('/analysis/ad_indicator', {type:type}, {autorun:true});
      	$('select[name=field]').val('');*/
        api.set('master_id',0,true);
      	api.set('type',type)
    })
    
    $('select[name=field]').on('change',function(){
    	var field	=	$(this).val()
    	//api.set('field',field);
    	api.reset('/analysis/ad_indicator', {field:field,type:type}, {autorun:true});
    	if(field){
    		$('select[name=keyword_id]').show();
    		$.get("/analysis/get_keyword_group", {field:field}, function(json){
                var list = json.list, len = list.length,html='<option value="" selected="selected">全部</option>';
                for(var i=0;i<len;i++){
                	html	+=	'<option value="'+list[i].keyword_id+'">'+list[i].keywords.name+'</option>';
                }

        		$('select[name=keyword_id]').html(html)
            }, 'json');
    	}else{
    		$('select[name=keyword_id]').hide();
    	}
    })
    if($('select[name=keyword_id]').length){
	    $('select[name=keyword_id]').on('change',function(){
	    	api.set('keyword_id',$(this).val());
	    })
	}
    
    $('select[name=order]').on('change',function(){
            api.set('order',$(this).val());
    })

    $('#show_all').on('click',function(){
        api.set('show_all',this.checked ? 1 :0);
    })

    $('#search_user').on('change',function(){
        api.set('search_user',$(this).val());
    })

    $('#HDT-main').on('click','.select_master',function(){
        var master_id = $(this).attr('data-master-id');
        api.set('master_id',master_id,true);
        api.set('type',1);
        $('.LabelText').find('.on').removeClass('on');
        $('.LabelText').find('[data-type="1"]').addClass('on');
    })
});