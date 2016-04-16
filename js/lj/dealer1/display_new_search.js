

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
	var q = $('#HDT-main').attr('data-q');
	var t = $('#HDT-main').attr('data-t');
    lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/dealer1/display_group_search', {q:q,t:t}, {autorun:true});
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});
});