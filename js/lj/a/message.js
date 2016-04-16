

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/message/mymessage', {limit:15}, {autorun:true,message:"松开刷新"});

    new lazy('.foot', function(){api.next()}, {delay:100, top:0});

});