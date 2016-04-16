

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = null, menu = $('#HDT-menu-right');
    
    $(window).on('hashchange', function(e){
        var t = location.hash.replace(/^\#/, '');
        if(!t)  t = "hotproduct";
        if(api == null){
            api = new pager('/list/'+t, {}, {autorun:true});
        }else{
            api.seturl("/list/" + t);
        }
        menu.find('li.on').removeClass("on");
        menu.find('#menu_'+t).addClass("on");
    }).trigger('hashchange');

    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

});