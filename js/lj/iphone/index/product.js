
define(['jquery', 'iscroll.ref', 'app/pager'], function(require, exports, module) {
    var myScroll = null, api = null, pager = require('app/pager'), $body = $('body'), $form = $('#HDT-form-product'), $w = $(window), $loading = $('.loading');

    var data = {};
    $form.find('input').each(function(){
        data[this.name] = this.value;
    });
    api = new pager('/product', data, {autorun:true, aftercallback:function(html){
        if(!html){
            $loading.hide();
        }
        myScroll.refresh();
    }});

    myScroll = require('iscroll.ref');
    myScroll.on('ScrollEnd', function(){
        var wh = $w.height(), offset = $loading.offset();
        if(wh && offset.top && wh > offset.top){
            if(api !== null) api.next();
        }
    });

    setTimeout(function(){
        var wh = $(window).height(), offset = $('.loading').offset();
        if(offset && offset.top < wh){
            $('.loading').hide();
        }
    }, 400);
    


});