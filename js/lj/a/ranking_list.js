

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/analysis/ranking_list_table',{}, {autorun:true});
    var $search = $('#HDT-FORM input');    
    var $form = $('#HDT-FORM');
    $form.on('submit', function(e) {
            e.preventDefault();
            input.blur();
            return false;
    }, false);

    new lazy('.foot', function(){api.next()}, {delay:100, top:0});
    if($('.Selects').length){
        require.async(['app/selects'], function(Selects){
            new Selects('/location/json', {dataType:'json', selector:'.Selects'});
        });
    }
        
    $('#HDT-select-menu').on('change', 'select', function(e){
        var target = e.currentTarget;
        api.set(target.name, target.value);
        console.log(target.name,target.value);
        api.next();
    });

    // $('#rank_search').on('keyup',function(e){
    //     api.set('rank_search',$(this).val());
    // });
    $search.on('keyup',function(e){
        api.set('rank_search',$(this).val());
    })
    $search[0].onchange = function(){};
    
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