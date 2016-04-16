

define(['jquery', 'app/pager'], function(require, exports, module) {
    var pager   = require('app/pager'), api, t = location.hash, menu = $('#HDT-menu-analysis'), orderby = $('select[name=orderby]');

    if(t){
        t = t.replace(/^\#/, '');
    }
    if(t){
        menu.find('a[data-t='+t+']').addClass("on");
    }else{
        menu.find("a:first").addClass("on");
    }

    api = new pager('/analysis/analysis_hpgc', {t:t, orderby:orderby.val()}, {autorun:true});
    orderby.on('change', function(){
        api.set('orderby', this.value);
    });
    api.next();

    menu.on('click', 'a[data-t]', function(e){
        var target  = e.currentTarget, t = target.getAttribute('data-t');
        menu.find('a.on').removeClass('on');
        target.className = "on";
        api.set("t", t);
    });

    $('select[name=fliter_uid]').on('change',function(){
    	api.set('fliter_uid', this.value);
    });

    /*require.async('app/admin.select', function(select){
        select(api);
    });*/
    
    var apiArr = [api];
    require.async('lj/fancybox',function(fancybox){
    	fancybox(apiArr,'');
    });
});