
define(['jquery', 'iscroll.ref', 'app/pager'], function(require, exports, module) {
    var myScroll = null, api = null, pager = require('app/pager'), $loading = $('.loading'), t = $('input[name=t]').val(), $w = $(window);

    api     = new pager("/list/"+t, {}, {autorun:true, aftercallback:function(html){
        if(!html)   $loading.hide();
        myScroll.refresh();
        setTimeout(function(){
            var wh = $w.height(), offset = $loading.offset();
            if(offset.top < wh){
                $loading.hide();
            }
        }, 1000)
    }});


    myScroll = require('iscroll.ref');
    myScroll.on('ScrollEnd', function(){
        var wh = $w.height(), offset;
        if(api !== null){
            offset = $loading.offset();
            if(offset.top < wh){
                api.next();
            }
        }
    });

});