

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/analysis/three_analysis_table',{}, {autorun:true});
    
    //new lazy('.foot', function(){api.next()}, {delay:100, top:0});
    if($('.Selects').length){
        require.async('app/admin.select', function(select){
            select(api);
        });
    }
        
    $('#HDT-select-menu').on('change', 'select', function(e){
        var target = e.currentTarget;
        api.set(target.name, target.value);
        console.log(target.name,target.value);
        api.next();
    });
});