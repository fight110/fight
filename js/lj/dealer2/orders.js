

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/orderlist/zongdai', {limit:15}, {autorun:true});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:0});

    var $dataorder = $('[data-order]');
    $dataorder.on('click', function(e){
        var target = e.currentTarget, order = target.getAttribute('data-order');
        api.set('order', order);
        $dataorder.find('span').remove();
        $(target).append("<span>↓</span>");
    });

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