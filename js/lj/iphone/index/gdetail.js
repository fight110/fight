
define(['jquery', 'iscroll.ref', 'app/pager'], function(require, exports, module) {
    var myScroll = null, groupapi = null, pager = require('app/pager'), $body = $('body'), group_id = $('#HDT-group-id').val(), $w = $(window);

    groupapi = new pager('/product', {group_id:group_id,limit:12}, {autorun:true, id:'#HDT-group-list', aftercallback:function(html){
        myScroll.refresh();
    }});

    $(function(){
        myScroll = require('iscroll.ref');
        myScroll.on('ScrollEnd', function(){
            var wh = $w.height(), offset;
            if(groupapi !== null){
                offset = $('#HDT-group-list').offset();
                if(offset.top < wh){
                    groupapi.next();
                }
            }
        });

        new iScroll('bigimage', {
            snap: 'li',
            momentum: false,
            hScrollbar: false,
            vScrollbar: false
        });

    });

});