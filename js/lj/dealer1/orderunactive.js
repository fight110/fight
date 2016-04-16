

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/product/list', {ordered:'unactive',myorder:'on'}, {autorun:true,message:"松开刷新"});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

    var $menu   = $('#HDT-select-menu');
    $menu.on('change', 'select', function(e){
        var target = e.currentTarget;
        if(target.name == "category_id") {
            $.get('/location/get_classes_list', {category_id:target.value}, function(html){
                $menu.find('select[name=classes_id]').replaceWith(html);
            });
            api.set(target.name, target.value, true);
            api.set('classes_id', 0, true);
            api.reload();
        }else{
        	api.set(target.name, target.value);
        }
    });

});