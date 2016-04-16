

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/product/displaylist/2', {}, {autorun:true,message:"松开刷新"});

    new lazy('.foot', function(){api.next()}, {delay:100, top:100});


});