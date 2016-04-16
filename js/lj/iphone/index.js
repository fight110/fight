
define(['jquery', 'iscroll.ref', 'app/pager'], function(require, exports, module) {
    var myScroll = null, api = null, pager = require('app/pager'), $body = $('body'), $form = $('#HDT-search-form');

    var is_submit = false;
    $form.on('submit', function(){
        $form.find('input').trigger("blur");
        if(is_submit == false){
            setTimeout(function(){
                $form.trigger('submit')
            }, 200);
            is_submit = true;
            return false;
        }
    });


    $body.on('click', '.HDT-list-t', function(e){
        var target = e.currentTarget, $t = $(target);
        $t.parent().find("ul").toggle();
        $t.toggleClass("hover");
        myScroll.refresh();
        return false;
    });

    myScroll = require('iscroll.ref');



});