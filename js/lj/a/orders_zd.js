

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
	var area1=$('select[name=area1]'),area2=$('select[name=area2]');
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/orderlist/orders_zd', {limit:15,area2:area2.val()}, {autorun:true});

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