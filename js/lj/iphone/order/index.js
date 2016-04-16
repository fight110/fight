
define(['jquery', 'iscroll.ref', 'app/pager'], function(require, exports, module) {
    var myScroll = null, api = null, pager = require('app/pager'), $body = $('body'), $w = $(window), $loading = $('.loading');

    api     = new pager('/orderlist/iphonelist', {}, {autorun:true, aftercallback:function(html){
        if(!html){
            $loading.hide();
        }else{
            var wh = $w.height(), offset = $loading.offset();
            if(offset.top < wh){
                $loading.hide();
            }else{
                $loading.show();
            }
        }
        myScroll.refresh();
    }});

    $body.on('change', 'select', function(e){
        var target = e.currentTarget;
        $loading.show();
        api.set(target.name, target.value);
    });

    $body.on('keyup', 'input[name=search]', function(e){
        var target = e.currentTarget;
        $loading.show();
        api.set(target.name, target.value);
    });



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