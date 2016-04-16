

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/analysis/explist_new', {limit:20}, {autorun:true});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:0});

   // require.async('app/admin.select', function(select){
   //     select(api);
    //});
//
    //$('#is_lock').on('click', function(){
    //	api.set('is_lock', this.checked ? 1 : 0);
    //});
});