

define(['jquery', 'app/pager'], function(require, exports, module) {
    var pager   = require('app/pager'), api, t = location.hash, menu = $('#HDT-menu-analysis'), orderby = $('select[name=orderby]'), $selectMenu = $('#HDT-select-menu');
    
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
    $selectMenu.on('change', 'select', function(){
        api.set(this.name, this.value);
    });
    api.next();

    menu.on('click', 'a[data-t]', function(e){
        var target  = e.currentTarget, t = target.getAttribute('data-t');
        menu.find('a.on').removeClass('on');
        target.className = "on";
        api.set("t", t);
    });

    var apiArr = [api];
    require.async('lj/fancybox',function(fancybox){
    	fancybox(apiArr,'');
    });
});