

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api;
    api = new pager('/analysis/list', {t:'user',zongdai:1}, {autorun:true});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});


});