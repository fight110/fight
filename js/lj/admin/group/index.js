

define(['jquery'], function(require, exports, module) {
    var $select_menu = $('.select_menu');
    $select_menu.on('click', 'p', function(e){
        var target = e.currentTarget, $target = $(target), $ul = $target.next();
        $ul.toggle();
    });
    
    $('body').on('click', 'a.HDT-delete', function(e){
        return confirm("确定移除该搭配?移除后不能恢复");
    });

});