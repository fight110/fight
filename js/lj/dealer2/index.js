

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = null, menu = $('#HDT-menu-right-top'), q = $('#HDT-hidden-q').val(),
        $search = $('#HDT-FORM input'),
        $menu   = $('#HDT-select-menu');
    
    $(window).on('hashchange', function(e){
        var t = location.hash.replace(/^\#/, '');
        if(!t)  t = "all";

        menu.find('li.on').removeClass("on");
        menu.find('#menu_dealer2'+t).addClass("on");
        var select_val = t == "all" ? "" : t;
        $menu.find("select[name=ordered]").val(select_val);
        
        if(api == null){
            var data = {ordered:t,q:q};
            $menu.find('select').each(function(){
                data[this.name] = this.value;
            });
            api = new pager('/product/list2', data, {autorun:true});
        }else{
            api.set("ordered", t);
        }
    }).trigger('hashchange');

    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

    $search.on('keyup', function(){
        api.set('q', this.value);
    });
    var message = $('#HDT-message').val();
    if(message){
        require.async('jquery/jquery.notify', function(n){
            n.message({title:"提示", text:message}, {expires:3000});
        });
    }
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

    var apiArr = [api];
    require.async('lj/fancybox',function(fancybox){
    	fancybox(apiArr,'');
    });
});