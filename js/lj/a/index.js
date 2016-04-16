

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = null, menu = $('#HDT-menu-right-top'),
        $menu   = $('#HDT-select-menu');
    	$leftMenu = $('.rsubmenu'),
        $search = $('#HDT-FORM input');

    // $(window).on('hashchange', function(e){
    //     var t = location.hash.replace(/^\#/, '');
    //     if(!t)  t = "all";

    //     // $leftMenu.find('li.on').removeClass("on");
    //     // $leftMenu.find('#menu_ad'+t).addClass("on");
    //     var select_val = t == "all" ? "" : t;
    //     $menu.find("select[name=ordered]").val(select_val);

    //     if(api == null){
    //         var data = {ordered:t};
    //         $menu.find("select").each(function(){
    //             data[this.name] = this.value;
    //         });
    //         api = new pager('/product/list3', data, {autorun:true});
    //     }else{
    //         api.set("ordered", t);
    //     }

    // }).trigger('hashchange');
    var data = {};
    $menu.find('select').each(function(){
        data[this.name] = this.value;
    });
    data['q']  = $search.val();
    api = new pager('/product/list3', data, {autorun:true,message:"松开刷新"});
    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

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
    $search.on('keyup', function(){
        api.set('q', this.value);
    });
    $search[0].onchange = function(){};
    
    var message = $('#HDT-message').val();
    if(message){
        require.async('jquery/jquery.notify', function(n){
            n.message({title:"提示", text:message}, {expires:3000});
        });
    }
    
    var apiArr = [api];
    require.async('lj/fancybox',function(fancybox){
    	fancybox(apiArr,'');
    });
    require.async(['jquery/jquery.pin/jquery.pin.min'], function(){
        $(".pinned").pin();
    });
    var time = null;
    $('body').on('lastProduct',function(){
        if(null===time){
            api.next();
            time = 10000;
        }
        setTimeout(function(){
            time=null;
        },10000);
    });
});