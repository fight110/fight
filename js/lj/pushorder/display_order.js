

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/pushorder/displaylist', {}, {autorun:true,message:"松开刷新"});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

	$('#HDT-select-menu').on('change','.select',function(e){
		var target = e.currentTarget;
		if(target.name == "pd_type") {
             $.get('/pushorder/get_display_type_list', {pd_type:target.value}, function(html){
                $('#HDT-select-menu').find('select[name=pd_type2]').replaceWith(html);
            });
            api.set(target.name, target.value, true);
            api.set('pd_type2', '', true);
            api.reload();
        }else{
			api.set(target.name,target.value);
        }
	})
});