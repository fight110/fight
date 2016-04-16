
define(['jquery', 'iscroll.ref', 'app/pager'], function(require, exports, module) {
    var myScroll = null, api = null, pager = require('app/pager'), $body = $('body'), $form = $('#HDT-form-product'), $w = $(window), $loading = $('.loading');

    var data = {};
    api = new pager('/group', data, {autorun:true, aftercallback:function(html){
        if(!html){
            $loading.remove();
        }
        myScroll.refresh();
    }});

    $(function(){
        myScroll = require('iscroll.ref');
        myScroll.on('ScrollEnd', function(){
            var wh = $w.height(), offset = $loading.offset();
            if(wh && offset.top && wh > offset.top){
                if(api !== null) api.next();
            }
        });
    });
    

    setTimeout(function(){
        var wh = $(window).height(), offset = $('.loading').offset();
        if(offset && offset.top < wh){
            $('.loading').remove();
        }
    }, 2000);
    


});