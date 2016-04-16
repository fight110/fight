

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/product/grouplist', {}, {autorun:true,message:"松开刷新"});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

    $('#HDT-select-menu').on('change','select',function(e){
        var target = e.currentTarget;
        api.set(target.name, target.value);
    })
});