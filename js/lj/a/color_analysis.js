

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/analysis/color_analysis_table',{}, {autorun:true});
    
    //new lazy('.foot', function(){api.next()}, {delay:100, top:0});
    
    $("#change").on('change',function(){
    	var type	=	$(this).val();
    	api.set('type',type);
    });
});