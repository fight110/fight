

define(['jquery', 'app/pager'], function(require, exports, module) {
	var master_user_id = $("select[name='master_user_id']").val();
    var pager   = require('app/pager'), api = new pager('/custom/meforever_report_table',{master_user_id:master_user_id}, {autorun:true});
    //new lazy('.foot', function(){api.next()}, {delay:100, top:0}); 
    
    $('#HDT-select-menu').on('change', 'select', function(e){
        var target = e.currentTarget;
        api.set(target.name, target.value);
        api.next();
    });
});